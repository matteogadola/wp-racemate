<?php

require_once RMIAP_PLUGIN_PATH . 'vendor/autoload.php';
use \Mailjet\Resources;

class RmiapMail {
  private $db;
  private $mj;
  private $from;

  public function __construct(RmiapDatabase $db, int $account_id = 2) {
    $this->db = $db;

    $account = $this->db->get_account($account_id);
    $apikey = explode(':', $account->notification_apikey);
    $this->from = explode(':', $account->notification_from);

    $this->mj = new \Mailjet\Client(
      $apikey[0],//'b09e18e719fa7eed7f4170b8080ec095',//'e2f7088eb7bf2f15c714c6eac0067445',//getenv('MJ_APIKEY_PUBLIC'),
      $apikey[1],//'2ed4b73b66d26d17db542989fb62cc00',//'68c3b4defbc04eaa4f2e4c57bc0bad69',//getenv('MJ_APIKEY_PRIVATE'),
      true,
      ['version' => 'v3.1']
    );
  }

  public function sendCheckout($entry) {
    try {
      $body = [
        'Messages' => [
          [
            'From' => [
              'Email' => (array_key_exists(0, $this->from) ? $this->from[0] : null) ?: 'noreply@teamvaltellina.com',
              'Name' => (array_key_exists(1, $this->from) ? $this->from[1] : null) ?: 'Team Valtellina',
            ],
            'To' => [
              [
                'Email' => $entry['email'],
                'Name' => $entry['first_name'] . ' ' . $entry['last_name'],
              ]
            ],
            'TemplateID' => $entry['account_id'] == 2 ? 7037767 : 7132866,
            'TemplateLanguage' => true,
            'CustomID' => strval($entry['id']),
            'Subject' => "Conferma iscrizione",
            'Variables' => remove_nulls($entry)
          ]
        ]
      ];

      $response = $this->mj->post(Resources::$Email, ['body' => $body]);
      $data = $response->getData();
    
      if ($response->success()) {
        return array(
          'notification_id'     => $data['Messages'][0]['To'][0]['MessageID'],//['MessageUUID'],
          'notification_date'   => date('c'),
          'notification_status' => $data['Messages'][0]['Status'],
        );
      } else {
        error_log('(sendCheckout) failed: ' . print_r($data, true));

        return array(
          'notification_id'     => null,
          'notification_date'   => date('c'),
          'notification_status' => 'failed',
        );
      }
    } catch (Exception $e) {
      error_log('(sendCheckout) '. $e.getMessage());
    }
  }
}

function remove_nulls($arr) {
  return array_filter($arr, function($v) {
    return $v !== null;// && $v !== false && $v !== "";
  });
}
