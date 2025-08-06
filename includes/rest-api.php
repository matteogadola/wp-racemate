<?php

//namespace Magda\Racemate\Api;

//defined('ABSPATH') || exit;

require_once RMIAP_PLUGIN_PATH . 'vendor/autoload.php';
require_once RMIAP_PLUGIN_PATH . 'includes/mail.php';

class RmiapRestApi {
  private $namespace = 'racemate-plugin/v1';
  private $db;
  private $mail;
  private $stripeIps = array(
    '127.0.0.1',
    '3.18.12.63',
    '3.130.192.231',
    '13.235.14.237',
    '13.235.122.149',
    '18.211.135.69',
    '35.154.171.200',
    '52.15.183.38',
    '54.88.130.119',
    '54.88.130.237',
    '54.187.174.169',
    '54.187.205.235',
    '54.187.216.72'
  );


  public function __construct(RmiapDatabase $db) {
    $this->db = $db;
    $this->mail = new RmiapMail($db);

    add_action('rest_api_init', array($this, 'register_routes'));
  }

  public function register_routes() {
    register_rest_route(
      $this->namespace,
      '/webhooks/stripe',
      array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => array($this, 'webhook_callback'),
        'permission_callback' => array($this, 'webhook_permissions_check'),
        'args'                => array(
          'account_id' => array(
            'validate_callback' => function( $param, $request, $key ) {
              return is_numeric($param) && absint($param) > 0;
            }
          ),
        ),
      )
    );

    register_rest_route(
      $this->namespace,
      '/webhooks/mail',
      array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => array($this, 'mail_callback'),
        'permission_callback' => array($this, 'mail_permissions_check'),
        /*'args'                => array(
          'CustomID' => array(
            'validate_callback' => function( $param, $request, $key ) {
              return is_numeric($param) && absint($param) > 0;
            }
          ),
        ),*/
      )
    );
  }

  public function webhook_callback(WP_REST_Request $request) {
    $account_id = $request->get_param('account_id');

    try {
      $account = $this->db->get_account($account_id);
    } catch (\Exception $e) {
      error_log($e->getMessage());
      return new WP_REST_Response(array(
        'success' => false,
        'message' => $e->getMessage(),
      ), 400);
    }

    if (!$account->stripe_secret_key || !$account->stripe_webhook_key) {
      error_log('Errore nel recupero delle chiavi Stripe: ' . print_r($account, true));
      return new WP_REST_Response(500);
    }

    $stripeSecret = $account->stripe_secret_key;
    $stripeWebhookSecret = $account->stripe_webhook_key;
    $stripe = new \Stripe\StripeClient($stripeSecret);

    $payload = @file_get_contents('php://input');
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    $event = null;

    try {
      $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $stripeWebhookSecret
      );
    } catch(\UnexpectedValueException $e) {
      error_log($e->getMessage());
      return new WP_REST_Response(array(
        'success' => false,
        'message' => $e->getMessage(),
      ), 400);
    } catch(\Stripe\Exception\SignatureVerificationException $e) {
      error_log($e->getMessage());
      return new WP_REST_Response(array(
        'success' => false,
        'message' => $e->getMessage(),
      ), 400);
    }

    if ($event->livemode !== true) {
      return new WP_REST_Response(array(
        'success' => false,
        'message' => 'webhook is for live only',
      ), 200);
    }

    try {
      switch ($event->type) {
        case 'payment_intent.canceled':
        case 'payment_intent.payment_failed':
          $payment = $event->data->object;
          $entry_id = $payment->metadata->entry_id;

          if ($payment->id && !is_nan($entry_id)) {
            $creation_date = new DateTime('@' . $payment->created);
            $payment_date = $creation_date->setTimeZone(new DateTimeZone('Europe/Rome'))->format('Y-m-d H:i:s');
            $this->db->update_entry($entry_id, [
              'payment_id' => $payment->payment_intent,
              'payment_status' => 'failed',
              'payment_date' => $payment_date,//date('Y-m-d H:i:s', $session->created),
            ]);
          }
          break;
        case 'checkout.session.expired':
          $session_exp = $event->data->object;
          $entry_id = $session_exp->metadata->entry_id;

          if ($session_exp->payment_intent && !is_nan($entry_id)) {
            $creation_date = new DateTime('@' . $session_exp->created);
            $payment_date = $creation_date->setTimeZone(new DateTimeZone('Europe/Rome'))->format('Y-m-d H:i:s');
            $this->db->update_entry($entry_id, [
              'payment_id' => $sessionExp->payment_intent,
              'payment_status' => 'failed',
              'payment_date' => $payment_date,//date('Y-m-d H:i:s', $session->created),
            ]);
          }
          break;
        case 'checkout.session.completed':
          $session = $event->data->object;
          $entry_id = $session->metadata->entry_id;

          if ($session->payment_intent && !is_nan($entry_id)) {
            $entry = $this->db->get_entry($entry_id);

            if ($entry === null) {
              return new WP_REST_Response([
                'success' => false,
                'message' => "Errore nel recupero di entry_id",
                'received_data' => $entry_id
              ], 400 );
            }

            $creation_date = new DateTime('@' . $session->created);
            $payment_date = $creation_date->setTimeZone(new DateTimeZone('Europe/Rome'))->format('Y-m-d H:i:s');
            $this->db->update_entry($entry_id, [
              'payment_id' => $session->payment_intent,
              'payment_status' => 'paid',
              'payment_date' => $payment_date,//date('Y-m-d H:i:s', $session->created),
            ]);

            $this->mail->sendCheckout((array) $entry);
          } else {
            error_log('checkout.session.completed : ' . print_r($session, true));
          }
          break;
        default:
          return new WP_REST_Response(array(
            'success' => false,
            'message' => 'Received unknown event type',
          ), 200);
      }
      return new WP_REST_Response(200);
    } catch (\Exception $e) {
      error_log($e->getMessage());
      return new WP_REST_Response(500);
    }
  }

  public function webhook_permissions_check(WP_REST_Request $request) {
    // Verifico ip chiamante
    $remote_ip = $_SERVER['REMOTE_ADDR'];
    if (!in_array($remote_ip, $this->stripeIps)) {
      return false;
    }

    if (!$request->get_param('account_id')) {
      return false;
    }

    return true;
  }

  public function mail_callback(WP_REST_Request $request) {
    $entry_id = $request->get_param('CustomID');

    if (!$entry_id) {
      error_log('Errore in chiamata: ' . print_r($request, true));
      return new WP_REST_Response(500);
    }

    $this->db->update_entry($entry_id, array(
      'notification_id'     => $request->get_param('MessageID'),
      'notification_date'   => date('c', $request->get_param('time')),
      'notification_status' => $request->get_param('event') === 'sent' ? 'delivered' : 'failed',
    ));
    return new WP_REST_Response(200);
  }

  public function mail_permissions_check(WP_REST_Request $request) {
    return true;
  }
}
