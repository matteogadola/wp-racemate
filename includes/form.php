<?php

require_once RMIAP_PLUGIN_PATH . 'vendor/autoload.php';
require_once RMIAP_PLUGIN_PATH . 'includes/tin.php';
require_once RMIAP_PLUGIN_PATH . 'includes/mail.php';

class RmiapForm {
  private $version = '1.0.0';
  private $form = array();
  private $db;
  private $mail;

  private $error;

  public function __construct(RmiapDatabase $db) {
    $this->db = $db;
    $this->mail = new RmiapMail($db);

    add_shortcode('racemate-form', [$this, 'form_shortcode_handler']);
    add_action('wp_enqueue_scripts', [$this, 'enqueue_form_assets']);
    //add_action('admin_post_checkout', array($this, 'checkout_callback'));
    //add_action('admin_post_nopriv_checkout', array($this, 'checkout_callback'));
    add_action('wp_ajax_rmiap_form_checkout', array($this, 'ajax_form_checkout'));
    add_action('wp_ajax_nopriv_rmiap_form_checkout', array($this, 'ajax_form_checkout'));
    add_action('wp_ajax_rmiap_form_clubs', array($this, 'ajax_form_clubs'));
    add_action('wp_ajax_nopriv_rmiap_form_clubs', array($this, 'ajax_form_clubs'));
  }

  public function form_shortcode_handler($atts, $content = null) {
    global $wp;

    $atts = shortcode_atts(
      array(
        'race'          => null,
        //'race-id'       => null,
        'show-entries'  => 'true',
        'sale-closed'   => '',
        'id'            => '',            // ID HTML opzionale
        'class'         => '',            // Classi CSS aggiuntive opzionali
        'style'         => 'default'      // Stile predefinito (es. 'default', 'primary', 'secondary')
      ),
      $atts,
      'racemate-form'
    );

    return $this->render_form($wp, $atts, $_GET);
  }

  private function render_form($wp, $atts, $params) {
    $race = $this->db->get_race($atts['race']);
    $entries = $atts['show-entries'] === 'false' ? array() : $this->db->get_entries_view($atts['race']);
    ob_start();
    ?>
      <div>
        <button type="button" class="btn btn-link <?php echo count($entries) > 0 ? '' : 'd-none'; ?>" data-bs-toggle="modal" data-bs-target="#race-<?php echo esc_attr( $race->id ); ?>-entries-modal" data-race-id="<?php echo esc_attr( $race->id ); ?>">
          Vedi elenco iscritti
        </button>
        <?php
          if (isset($race->end_sale_date)) {
            $end_sale_date = new DateTime($race->end_sale_date);
          } else {
            $end_sale_date = (new DateTime($race->date))->sub(new DateInterval('PT46H'));
          }

          $today = new DateTime();
          if ($today > $end_sale_date) {
            echo "<p>". (!empty($atts['sale-closed']) ? $atts['sale-closed'] : "Iscrizioni online chiuse. Sarà possibile iscriversi alla partenza.") . "</p>";
          } else {
        ?>

        <form id="racemate-form" method="post" action="<?php echo esc_attr(admin_url('admin-post.php')); ?>">
          <?php if (isset($params['success'])) { ?>
            <div class="alert alert-success pt-2 pb-6" role="alert">
              <h4 class="alert-heading h4 pt-4">Iscrizione effettuata con successo!</h4>
              <p>A breve riceverai una mail di conferma.</p>
            </div>
          <?php } ?>
          <div class="grid pt-4">
            <input type="hidden" name="action" value="checkout">
            <input type="hidden" name="url" value="<?php echo esc_attr($wp->request); ?>">
            <input type="hidden" name="race_id" value="<?php echo esc_attr($atts['race']); ?>">
            <input type="hidden" name="race_price" value="<?php echo esc_attr($race->price); ?>">
            <!-- First name -->
            <div class="form-floating">
              <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Nome">
              <label for="first_name">Nome *</label>
            </div>
            <div class="form-floating">
              <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Cognome">
              <label for="last_name">Cognome *</label>
            </div>
            <div class="form-floating">
              <input type="text" class="form-control" id="tin" name="tin" placeholder="Codice Fiscale">
              <label for="tin">Codice Fiscale *</label>
            </div>
            <div class="form-floating">
              <input type="text" class="form-control" list="clubOptions" id="club" name="club" placeholder="Società">
              <label for="club">Società</label>
              <datalist id="clubOptions"></datalist>
            </div>
            <div class="form-floating">
              <input type="text" class="form-control" id="email" name="email" placeholder="Email">
              <label for="email">Email *</label>
            </div>
            <div class="form-floating">
              <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="Telefono">
              <label for="phone_number">Telefono</label>
            </div>
            <div class="grid-full py-4">
              <span>Metodo di Pagamento</span>
              <div class="form-check mt-2">
                <input class="form-check-input" type="radio" name="payment_method" id="stripe" value="stripe" checked>
                <label class="form-check-label" for="stripe">Online</label>
              </div>
              <div class="form-check mt-2">
                <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash">
                <label class="form-check-label" for="cash">Contanti</label>
              </div>
            </div>
            <div id="racemate-form-error" class="alert alert-danger grid-full" role="alert"></div>
            <button type="button" name="checkout" class="btn btn-primary"></button>
          </div>
        </form>
        <?php } ?>

        <!-- Modal -->
        <div class="modal fade" id="race-<?php echo esc_attr( $race->id ); ?>-entries-modal" tabindex="-1" aria-labelledby="race-<?php echo esc_attr( $race->id ); ?>-entries-modal-label" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title fs-5" id="race-<?php echo esc_attr( $race->id ); ?>-entries-modal-label">Iscritti (<?php echo esc_attr(count($entries)); ?>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">

                <table class="table">
                  <thead>
                    <tr>
                      <th scope="col">Cognome</th>
                      <th scope="col">Nome</th>
                      <th scope="col">Anno</th>
                      <th scope="col">Sesso</th>
                      <th scope="col">Team</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($entries as $entry): ?>
                    <tr>
                      <td><?= $entry->last_name; ?></td>
                      <td><?= $entry->first_name; ?></td>
                      <td><?= $entry->birth_year; ?></td>
                      <td><?= $entry->gender; ?></td>
                      <td><?= $entry->club; ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
                
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php
    return ob_get_clean();
  }

  public function enqueue_form_assets() {
    // Accoda lo stile (puoi farlo condizionatamente se lo shortcode è presente,
    // ma per semplicità lo accodiamo globalmente qui.
    // Per un'ottimizzazione, potresti accodarlo solo se la pagina/post contiene lo shortcode).
    //if (in_array($hook_suffix, $pages_slugs)) {

    wp_enqueue_style(
      'rmiap-form-style',
      plugin_dir_url(__DIR__) . 'assets/css/form.css',
      array(),
      $this->version,
    );

    wp_enqueue_script(
      'rmiap-form-script',
      plugin_dir_url(__DIR__) . 'assets/js/form.js',
      array('jquery'),
      $this->version,
    );

    wp_enqueue_script(
      'rmiap-form-script-bootstrap',
      plugin_dir_url(__DIR__) . 'assets/js/bootstrap.min.js',
      array('jquery'),
      $this->version,
    );

    wp_localize_script(
      'rmiap-form-script',
      'rmiap_form_params',
      array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('rmiap_form_nonce')
      )
    );
  }

  function ajax_form_checkout () {
    check_ajax_referer('rmiap_form_nonce', 'nonce');

    $form = array();
    parse_str($_POST['form'], $form);

    if (empty($form['race_id']) || empty($form['payment_method'])) {
      wp_send_json_error(array('message' => 'Errore. Prova a ricaricare la pagina'), 400);
    }
    if (empty($form['first_name'])) {
      wp_send_json_error(array('message' => 'Il campo nome è obbligatorio'), 400);
    }
    if (empty($form['last_name'])) {
      wp_send_json_error(array('message' => 'Il campo cognome è obbligatorio'), 400);
    }
    if (empty($form['tin'])) {
      wp_send_json_error(array('message' => 'Il campo codice fiscale è obbligatorio'), 400);
    }
    if (empty($form['email'])) {
      wp_send_json_error(array('message' => 'Il campo email è obbligatorio'), 400);
    }

    $tin = null;
    try {
      $tin = RmiapTin::verifyTin($form['tin'], $form['first_name'], $form['last_name']);
    } catch (Exception $e) {
      wp_send_json_error(array('message' => $e->getMessage()), 400);
    }

    if (!$tin) {
      wp_send_json_error(array('message' => 'Errore interno'), 500);
    }

    $form['first_name'] = ucfirst(strtolower($form['first_name']));
    $form['last_name'] = ucfirst(strtolower($form['last_name']));
    $form['email'] = strtolower($form['email']);
    $form['tin'] = strtoupper($form['tin']);
    $form['payment_status'] = $form['payment_method'] == 'stripe' ? 'intent' : 'pending';
    $form['gender'] = $tin['gender'];
    $form['birth_date'] = $tin['birth_date'];
    $form['birth_year'] = $tin['birth_year'];

    if ($this->db->entry_exists($form['race_id'], $form['tin'])) {
      $error_message = sprintf(
        '%s %s risulta già %s',
        $form['first_name'],
        $form['last_name'],
        $form['gender'] == 'F' ? 'iscritta' : 'iscritto'
      );
      wp_send_json_error(array('message' => $error_message), 400);
    }

    try {
      $race = $this->db->get_race($form['race_id']);
      $form['race_name'] = $race->name;

      if ($form['payment_method'] == 'stripe') {
        $stripeTax = 25 + round($race->price * 0.015);
        $stripeTaxIva = round($stripeTax * 0.22);
        $stripeFee = ceil(($stripeTax + $stripeTaxIva) / 50) * 50;
        $form['amount'] = $race->price + $stripeFee;
      } else {
        $form['amount'] = $race->price;
      }
      $form['items'] = '[]';

      $id = $this->db->create_entry($form);
      
      $success_url = home_url(add_query_arg(
        array('success' => base64_encode(http_build_query(array('id' => $id)))),
        $form['url'])
      );
      $cancel_url = home_url(add_query_arg(
        array('reset' => base64_encode(http_build_query($form))),
        $form['url']
      ));
      
      if ($form['payment_method'] == 'stripe') {
        $url = $this->createStripeSession($race->account_id, array_merge($form, [
          'id' => $id,
          "race_name" => $race->name,
          'success_url' => $success_url,
          'cancel_url' => $cancel_url,
        ]));
        wp_send_json_success(array('id' => $id, 'url' => $url));
      } else {
        $this->mail->sendCheckout(array_merge($form, [
          'id' => $id,
          'account_id' => $race->account_id,
        ]));
        wp_send_json_success(array('id' => $id, 'url' => $success_url));
      }
    } catch (Exception $e) {
      error_log("(checkout_callback) " . $e->getMessage());
      wp_send_json_error(array('message' => $e->getMessage()), 400);
    }
  }

  /*function checkout_callback() {
    try {
      $race = $this->db->get_race($_POST['race_id']);

      if ($_POST['payment_method'] == 'stripe') {
        $stripeTax = 25 + round($race->price * 0.015);
        $stripeTaxIva = round($stripeTax * 0.22);
        $stripeFee = ceil(($stripeTax + $stripeTaxIva) / 50) * 50;
        $amount = $race->price + $stripeFee;
      } else {
        $amount = $race->price;
      }

      $entry = $this->create_entry(array_merge($_POST, [
        'amount' => $amount,
        'items' => '[]',
      ]));

      if ($_POST['payment_method'] == 'stripe') {
        $this->createStripeSession($race->account_id, array_merge($entry, [
          "race_name" => $race->name,
          "account_id" => $race->account_id,
        ]));
      } else {
        wp_mail($_POST['email'], "Conferma iscrizione: " . $race->name, "blabla ti confermaimo...");
        $current_url = base64_decode($entry['url']);
        wp_redirect($current_url . '?checkout=success');
        exit();
        //$_SESSION["action"] = "checkout_confirm";
        //header("Location: " . admin_url('admin-post.php'));
        //wp_redirect(admin_url('admin-post.php'));
        //header("Location: racemate-checkout-confirm.php");
      }
    } catch (Exception $e) {
      error_log("(checkout_callback) " . $e->getMessage());
      wp_redirect($current_url . '?checkout=failure&message=' . base64_encode($e->getMessage));
      exit();
    }
  }*/

  function ajax_form_clubs() {
    check_ajax_referer('rmiap_form_nonce', 'nonce');

    $clubs = json_decode(file_get_contents(RMIAP_PLUGIN_PATH . "assets/data/clubs.json"));

    $filtered = array_filter($clubs, function($v) {
      return str_contains($v, strtoupper($_POST['value']));
    });

    wp_send_json_success($filtered);
  }

/* function create_entry($entry) {
  $inverseCalculator = new InverseCalculator($entry['tin']);
  $tin = $inverseCalculator->getSubject();
  
  $entry['first_name'] = ucfirst(strtolower($entry['first_name']));
  $entry['last_name'] = ucfirst(strtolower($entry['last_name']));
  $entry['email'] = strtolower($entry['email']);
  $entry['tin'] = strtoupper($entry['tin']);

  $gender = $tin->getGender();
  $birth_date = $tin->getBirthDate()->format('Y-m-d');
  $birth_year = $tin->getBirthDate()->format('Y');
  $payment_status = $entry['payment_method'] == 'stripe' ? 'intent' : 'pending';

  $id = $this->db->create_entry(array_merge($entry, [
    'gender' => $gender,
    'birth_date' => $birth_date,
    'birth_year' => $birth_year,
    'payment_status' => $payment_status,
  ]));

  return array_merge($entry, [
    'id' => $id,
  ]);
}*/

private function createStripeSession($account_id, $entry) {
  $account = $this->db->get_account($account_id);

  if (!$account->stripe_secret_key) {
    error_log('Errore nel recupero delle chiavi Stripe: ' . print_r($account, true));
    http_response_code(500);
    exit();
  }

  $stripeSecret = $account->stripe_secret_key;

  \Stripe\Stripe::setApiKey($stripeSecret);

$checkout_session = \Stripe\Checkout\Session::create([
  "mode" => "payment",
  "submit_type" => "pay",
  "currency" => "eur",
  "customer_email" => $entry["email"],
  "line_items" => [
    [
      "quantity" => 1,
      "price_data" => [
        "currency" => "eur",
        "unit_amount" => $entry['amount'],
        "product_data" => [
          "name" => $entry["race_name"],
          "description" => $entry["first_name"] . " " . $entry["last_name"],
          "metadata" => [
            "entry_id" => $entry["id"],
            "race_id" => $entry["race_id"],
          ]
        ]
      ]
    ]
  ],
  "automatic_tax" => [
    "enabled" => false,
  ],
  "metadata" => [
    "entry_id" => $entry["id"],
  ],
  "payment_intent_data" => [
    "metadata" => [
      "entry_id" => $entry["id"],
    ],
  ],
  "success_url" => $entry['success_url'],
  "cancel_url" => $entry['cancel_url'],
]);

//http_response_code(303);
//header("Location: " . $checkout_session->url);
return $checkout_session->url;
}

}
