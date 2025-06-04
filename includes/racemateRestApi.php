<?php

//namespace Magda\Racemate\Api;

//defined('ABSPATH') || exit;

require_once RMIAP_PLUGIN_PATH . 'vendor/autoload.php';

class RacemateRestApi {
  private $namespace = 'racemate-plugin/v1';
  private $db;

  public function __construct(RacemateDatabase $db) {
    $this->db = $db;

    add_action('rest_api_init', array($this, 'register_routes'));
  }

  public function register_routes() {
    register_rest_route(
      $this->namespace,
      '/webhooks/team-valtellina',
      array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => array($this, 'webhook_callback'),
        'permission_callback' => array($this, 'webhook_permissions_check'),
      )
    );
  }

  public function webhook_callback(WP_REST_Request $request) {
    $account = $this->db->getAccount(1);

    if (!$account['stripe_secret_key'] || !$account['stripe_webhook_key']) {
      error_log('Errore nel recupero delle chiavi Stripe: ' . print_r($account, true));
      http_response_code(500);
      exit();
    }

    $stripeSecret = $account['stripe_secret_key'];
    $stripeWebhookSecret = $account['stripe_webhook_key'];
    $stripe = new \Stripe\StripeClient($stripeSecret);

    $payload = @file_get_contents('php://input');
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    $event = null;

    try {
      $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $stripeWebhookSecret
      );
    } catch(\UnexpectedValueException $e) {
      // Invalid payload
      http_response_code(400);
      exit();
    } catch(\Stripe\Exception\SignatureVerificationException $e) {
      // Invalid signature
      http_response_code(400);
      exit();
    }

    if ($event->livemode !== true) {
      //error_log('webhook is for live only' . print_r($event, true));
      // webhook is for live only
      //http_response_code(200);
      //exit();
    }

    switch ($event->type) {
      case 'payment_intent.canceled':
      case 'payment_intent.payment_failed':
        $payment = $event->data->object;
        $entry_id = $payment->metadata->entry_id;

        error_log('payment_intent.canceled: ' . print_r($payment, true));
        error_log('payment_intent.canceled: ' . $entry_id);
        //console.warn(`Pagamento fallito per ${order_id}`);

        if ($payment->id && !is_nan($entry_id)) {
          $creation_date = new DateTime('@' . $payment->created);
          $payment_date = $creation_date->setTimeZone(new DateTimeZone('Europe/Rome'))->format('Y-m-d H:i:s');
          $this->db->updateEntry($entry_id, [
            'payment_id' => $payment->payment_intent,
            'payment_status' => 'failed',
            'payment_date' => $payment_date,//date('Y-m-d H:i:s', $session->created),
          ]);
        }
        break;
      case 'checkout.session.expired':
        $session_exp = $event->data->object;
        $entry_id = $session_exp->metadata->entry_id;

        error_log('checkout.session.expired: ' . print_r($session_exp, true));
        error_log('checkout.session.expired: ' . $entry_id);
        //console.info(`Checkout expired per ${order_id}`);

        if ($session_exp->payment_intent && !is_nan($entry_id)) {
          $creation_date = new DateTime('@' . $session_exp->created);
          $payment_date = $creation_date->setTimeZone(new DateTimeZone('Europe/Rome'))->format('Y-m-d H:i:s');
          $this->db->updateEntry($entry_id, [
            'payment_id' => $sessionExp->payment_intent,
            'payment_status' => 'failed',
            'payment_date' => $payment_date,//date('Y-m-d H:i:s', $session->created),
          ]);
        }
        break;
      case 'checkout.session.completed':
        $session = $event->data->object;
        $entry_id = $session->metadata->entry_id;

        error_log('checkout.session.completed: ' . print_r($session, true));
        error_log('checkout.session.completed: ' . $entry_id);
        //console.info(`Checkout completed per ${order_id}`);

        if ($session->payment_intent && !is_nan($entry_id)) {
          $entry = $this->db->getEntry($entry_id);

          if ($entry === null) {
            return new WP_REST_Response([
              'success' => false,
              'message' => "Errore nel recupero di entry_id",
              'received_data' => $entry_id
            ], 400 );
          }

          $creation_date = new DateTime('@' . $session->created);
          $payment_date = $creation_date->setTimeZone(new DateTimeZone('Europe/Rome'))->format('Y-m-d H:i:s');
          $this->db->updateEntry($entry_id, [
            'payment_id' => $session->payment_intent,
            'payment_status' => 'paid',
            'payment_date' => $payment_date,//date('Y-m-d H:i:s', $session->created),
          ]);

          //await sendCheckoutMail(order);
        } else {
          //console.error(`Checkout completed terminato in errore: ${JSON.stringify(session)}`);
        }
        break;
      //case 'payment_intent.succeeded':
      default:
        echo 'Received unknown event type ' . $event->type;
    }

    http_response_code(200);
    //return new WP_REST_Response(200);
/*
    // Ottieni i parametri dalla richiesta.
    // Se il webhook invia JSON, puoi usare $request->get_json_params();
    // Altrimenti, per parametri POST/GET standard, usa $request->get_params();
    $params = $request->get_params();
    $json_data = $request->get_json_params(); // Preferibile se il webhook invia JSON

    // Esempio: loggare i dati ricevuti (per debug)
    // In produzione, rimuovi o usa un sistema di logging più robusto.
    error_log( 'Webhook Lolle Plugin Ricevuto: ' . print_r( $json_data ?: $params, true ) );

    // Qui inserisci la logica per processare i dati del webhook:
    // - Salvare dati nel database
    // - Inviare email
    // - Aggiornare opzioni
    // - Ecc.

    // Esempio: verificare un campo specifico
    if ( isset( $json_data['event_type'] ) && $json_data['event_type'] === 'something_happened' ) {
        // Fai qualcosa di specifico per questo evento
        $message = 'Evento "' . esc_html( $json_data['event_type'] ) . '" processato con successo.';
        // Esempio: salvare un'opzione
        // update_option('lolle_webhook_last_event_data', $json_data);
    } else {
        $message = 'Dati del webhook ricevuti, ma nessun evento specifico gestito.';
    }

    // Prepara una risposta.
    $response_data = array(
        'success' => true,
        'message' => $message,
        'received_data' => $json_data ?: $params // Invia indietro i dati ricevuti per conferma (opzionale)
    );

    return new WP_REST_Response( $response_data, 200 ); // 200 OK
*/
  }

  public function webhook_permissions_check(WP_REST_Request $request) {
    return true;
    // Metodo 1: Controllare un token segreto passato in un header.
    $expected_secret_token = 'IL_TUO_TOKEN_SEGRETO_MOLTO_SICURO'; // Conservalo in modo sicuro!
    $received_token = $request->get_header( 'X-Lolle-Webhook-Secret' ); // Nome dell'header personalizzato

    if ( ! empty( $received_token ) && hash_equals( $expected_secret_token, $received_token ) ) {
        return true; // Il token è valido, permesso accordato.
    }

    // Metodo 2: (Meno comune per webhook server-to-server) Verificare un nonce se la richiesta
    // dovesse provenire da un utente loggato WordPress, ma per webhook esterni è meglio un token.

    // Metodo 3: Controllare l'indirizzo IP del chiamante (se il webhook proviene sempre da IP noti)
    // $allowed_ips = array('192.168.1.100', ' dominiofidato.com'); // Esempio
    // $remote_ip = $_SERVER['REMOTE_ADDR'];
    // if (in_array($remote_ip, $allowed_ips)) {
    //     return true;
    // }

    // Se nessuna condizione di permesso è soddisfatta:
    // Logga il tentativo di accesso non autorizzato (opzionale ma utile)
    error_log('Tentativo di accesso non autorizzato al webhook Lolle Plugin da IP: ' . $_SERVER['REMOTE_ADDR'] . ' con token: ' . $received_token);
    
    return new WP_Error(
        'rest_forbidden_context',
        __( 'Accesso non autorizzato.', 'lolle-plugin' ),
        array( 'status' => 403 ) // 403 Forbidden
    );
    // Potresti anche restituire false, che di default dà un 401 Unauthorized.
    // return false;
  }
}
