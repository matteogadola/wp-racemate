<?php

class RmiapShortcodes {

  public function __construct() {
    add_shortcode('racemate-form', [$this, 'shortcode_handler']);

    // Registra e accoda gli stili CSS
    add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

    add_action('admin_post_checkout', array($this, 'checkout_callback'));
    add_action('admin_post_nopriv_checkout', array($this, 'checkout_callback'));
  }

  public function shortcode_handler($atts, $content = null) {
    global $wp;
    $current_url = home_url(add_query_arg(array(), $wp->request));

    $atts = shortcode_atts(
      array(
        'race'      => null,
        'race-id'      => null,
        'id'        => '',                                  // ID HTML opzionale
        'class'     => '',                                  // Classi CSS aggiuntive opzionali
        'style'     => 'default'                            // Stile predefinito (es. 'default', 'primary', 'secondary')
      ),
      $atts,
      'racemate-form'
    );


    $fields = array();

    array_push($fields, '<input type="hidden" name="action" value="checkout">');
    array_push($fields, '<input type="hidden" name="url" value="'.base64_encode($current_url).'">');
    array_push($fields, '<input type="hidden" name="race_id" value="'.$atts['race'].'">');

    array_push($fields, '<div class="cell"><div class="field">
  <label class="label is-required">Nome</label>
  <div class="control">
    <input class="input" type="text" name="first_name" placeholder="">
  </div>
</div></div>');
array_push($fields, '<div class="cell"><div class="field">
  <label class="label is-required">Cognome</label>
  <div class="control">
    <input class="input" type="text" name="last_name" placeholder="">
  </div>
</div></div>');
array_push($fields, '<div class="cell"><div class="field">
  <label class="label is-required">Codice Fiscale</label>
  <div class="control">
    <input class="input" type="text" name="tin" placeholder="">
  </div>
</div></div>');
array_push($fields, '<div class="cell"><div class="field">
  <label class="label">Società</label>
  <div class="control">
    <input class="input" type="text" name="club" placeholder="Opzionale">
  </div>
</div></div>');
array_push($fields, '<div class="cell"><div class="field">
  <label class="label is-required">Email</label>
  <div class="control">
    <input class="input" type="email" name="email" placeholder="">
  </div>
</div></div>');
array_push($fields, '<div class="cell"><div class="field">
  <label class="label">Telefono</label>
  <div class="control">
    <input class="input" type="text" name="phone_number" placeholder="Opzionale">
  </div>
</div></div>');

array_push($fields, '
<div>
<span>Metodo di Pagamento</span>
<div class="control radio-list">
  <label class="radio">
    <input type="radio" name="payment_method" value="stripe" checked />
    Online
  </label>
  <label class="radio">
    <input type="radio" name="payment_method" value="cash" />
    Contanti
  </label>
</div></div>');



array_push($fields, '<div class="cell is-col-span-2"><button class="button is-info is-light">Iscriviti</button></div>');


    $grid = sprintf(
      '<div class="fixed-grid has-1-cols has-2-cols-tablet"><div class="grid">%s</div></div>',
      implode(" ", $fields)
    );

    $output = sprintf(
      '<form method="post" action="%s">%s</form>',
      esc_attr(admin_url('admin-post.php')),
      $grid
    );

    return $output;
}

  public function lolle_custom_button_shortcode_handler( $atts, $content = null ) {
    // 1. Definisci gli attributi di default e uniscili con quelli passati
    $atts = shortcode_atts(
      array(
          'text'      => __( 'Clicca Qui', 'lolle-plugin' ), // Testo di default
          'link'      => '#',                                 // Link di default
          'race'      => null,
          'id'        => '',                                  // ID HTML opzionale
          'class'     => '',                                  // Classi CSS aggiuntive opzionali
          'new_tab'   => false,                               // Aprire in una nuova scheda (true/false)
          'style'     => 'default'                            // Stile predefinito (es. 'default', 'primary', 'secondary')
      ),
      $atts,
      'racemate-form' // Nome dello shortcode (per filtri futuri)
    );

    // 2. Sanitizzazione e preparazione degli attributi
    $button_text = esc_html( $atts['text'] );
    $button_link = esc_url( $atts['link'] );
    $button_id = $atts['id'] ? 'id="' . esc_attr( $atts['id'] ) . '"' : '';
    $button_target = filter_var( $atts['new_tab'], FILTER_VALIDATE_BOOLEAN ) ? 'target="_blank" rel="noopener noreferrer"' : '';

    // 3. Costruisci le classi CSS
    $button_classes = 'lolle-custom-button'; // Classe base per lo styling
    if ( ! empty( $atts['class'] ) ) {
        $button_classes .= ' ' . esc_attr( $atts['class'] );
    }
    if ( ! empty( $atts['style'] ) ) {
        $button_classes .= ' lolle-button-style-' . esc_attr( $atts['style'] );
    }

    // 4. Genera l'HTML del bottone
    $output = sprintf(
        '<a href="%s" class="%s" %s %s>%s</a>',
        $button_link,
        esc_attr( $button_classes ),
        $button_id,
        $button_target,
        $button_text
    );

    return $output;
}

public function racemate_form_enqueue_styles() {
    // Registra il file CSS. Assicurati che il percorso sia corretto.
    // Ad esempio, se hai una cartella 'assets/css/' nel tuo plugin.
    wp_register_style(
        'racemate-form-style', // Handle univoco
        plugin_dir_url( __DIR__ ) . 'assets/css/form.css', // URL del file CSS
        array(), // Dipendenze (altri handle di stili)
        '1.0.0' // Versione del file (per il cache busting)
    );

    // Accoda lo stile (puoi farlo condizionatamente se lo shortcode è presente,
    // ma per semplicità lo accodiamo globalmente qui.
    // Per un'ottimizzazione, potresti accodarlo solo se la pagina/post contiene lo shortcode).
    wp_enqueue_style( 'racemate-form-style' );
}









function checkout_callback() {
  try {
    $race = $this->db->getRace($_POST['race_id']);

    $entry = array_filter($_POST, function($k) {
      return !in_array($k, array('action', 'url'));
    }, ARRAY_FILTER_USE_KEY);

    $entry_id = $this->createEntry(array_merge($entry, [
      'amount' => $race['price'],
      'items' => '[]',
    ]));

    if ($_POST['payment_method'] == 'stripe') {
      $this->createStripeSession(array_merge($_POST, [
        "id" => $entry_id,
        "race_name" => $race['name'],
        "race_price" => $race['price'],
        "account_id" => $race['account_id'],
      ]));
    } else {
      header("Location: racemate-checkout-confirm.php");
    }
  } catch (Exception $e) {
    error_log("(checkout_callback) " . $e->getMessage());

    //$_SESSION["error"] = "Please enter a password.";
    //header("Location: pagename.php"); // current url + param error
  }



  /*status_header(200);
  die("Server received '{$_REQUEST['data']}' from your browser.");

  $url = $_POST['_wp_http_referer'];
  wp_redirect($url);*/

}

private function createEntry($entry) {
  $inverseCalculator = new InverseCalculator($entry['tin']);
  $tin = $inverseCalculator->getSubject();

  $gender = $tin->getGender();
  $birth_date = $tin->getBirthDate()->format('Y-m-d');
  $birth_year = $tin->getBirthDate()->format('Y');
  $payment_status = $entry['payment_method'] == 'stripe' ? 'intent' : 'pending';

  $id = $this->db->createEntry(array_merge($entry, [
    'gender' => $gender,
    'birth_date' => $birth_date,
    'birth_year' => $birth_year,
    'payment_status' => $payment_status,
  ]));

  return $id;
}

private function createStripeSession($entry) {
  $account = $this->db->getAccount($entry['account_id']);

  if (!$account['stripe_secret_key']) {
    error_log('Errore nel recupero delle chiavi Stripe: ' . print_r($account, true));
    http_response_code(500);
    exit();
  }

  $stripeSecret = $account['stripe_secret_key'];

  \Stripe\Stripe::setApiKey($stripeSecret);

  $current_url = base64_decode($entry['url']);

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
        "unit_amount" => 1000,
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
  "success_url" => "http://localhost:8080/success",
  "cancel_url" => $current_url,
]);

http_response_code(303);
header("Location: " . $checkout_session->url);
}










  /**
   * Validate and set form
   *
   * @param string        $key A valid key; switch statement ensures validity
   * @param string | bool $val A valid value; validated for each key
   *
   * @return bool
   */
  function set_att( $key, $val ) {

    switch ( $key ) :

      case 'action':
        break;

      case 'method':
        if ( ! in_array( $val, array( 'post', 'get' ) ) ) {
          return false;
        }
        break;

      case 'enctype':
        if ( ! in_array( $val, array( 'application/x-www-form-urlencoded', 'multipart/form-data' ) ) ) {
          return false;
        }
        break;

      case 'markup':
        if ( ! in_array( $val, array( 'html', 'xhtml' ) ) ) {
          return false;
        }
        break;

      case 'class':
      case 'id':
        if ( ! $this->_check_valid_attr( $val ) ) {
          return false;
        }
        break;

      case 'novalidate':
      case 'add_honeypot':
      case 'form_element':
      case 'add_submit':
        if ( ! is_bool( $val ) ) {
          return false;
        }
        break;

      case 'add_nonce':
        if ( ! is_string( $val ) && ! is_bool( $val ) ) {
          return false;
        }
        break;

      default:
        return false;

    endswitch;

    $this->form[ $key ] = $val;

    return true;

  }

  /**
   * Add an input field to the form for outputting later
   *
   * @param string $label
   * @param string $args
   * @param string $slug
   */
  function add_input( $label, $args = '', $slug = '' ) {

    if ( empty( $args ) ) {
      $args = array();
    }

    // Create a valid id or class attribute
    if ( empty( $slug ) ) {
      $slug = $this->_make_slug( $label );
    }

    $defaults = array(
      'type'             => 'text',
      'name'             => $slug,
      'id'               => $slug,
      'label'            => $label,
      'value'            => '',
      'placeholder'      => '',
      'class'            => array(),
      'min'              => '',
      'max'              => '',
      'step'             => '',
      'autofocus'        => false,
      'checked'          => false,
      'selected'         => false,
      'required'         => false,
      'add_label'        => true,
      'options'          => array(),
      'wrap_tag'         => 'div',
      'wrap_class'       => array( 'form_field_wrap' ),
      'wrap_id'          => '',
      'wrap_style'       => '',
      'before_html'      => '',
      'after_html'       => '',
      'request_populate' => true
    );

    // Combined defaults and arguments
    // Arguments override defaults
    $args                  = array_merge( $defaults, $args );
    $this->inputs[ $slug ] = $args;

  }

  /**
   * Add multiple inputs to the input queue
   *
   * @param $arr
   *
   * @return bool
   */
  function add_inputs( $arr ) {

    if ( ! is_array( $arr ) ) {
      return false;
    }

    foreach ( $arr as $field ) {
      $this->add_input(
        $field[0], isset( $field[1] ) ? $field[1] : '',
        isset( $field[2] ) ? $field[2] : ''
      );
    }

    return true;
  }

  /**
   * Build the HTML for the form based on the input queue
   *
   * @param bool $echo Should the HTML be echoed or returned?
   *
   * @return string
   */
  function build_form( $echo = true ) {

    $output = '';

    if ( $this->form['form_element'] ) {
      $output .= '<form method="' . $this->form['method'] . '"';

      if ( ! empty( $this->form['enctype'] ) ) {
        $output .= ' enctype="' . $this->form['enctype'] . '"';
      }

      if ( ! empty( $this->form['action'] ) ) {
        $output .= ' action="' . $this->form['action'] . '"';
      }

      if ( ! empty( $this->form['id'] ) ) {
        $output .= ' id="' . $this->form['id'] . '"';
      }

      if ( count( $this->form['class'] ) > 0 ) {
        $output .= $this->_output_classes( $this->form['class'] );
      }

      if ( $this->form['novalidate'] ) {
        $output .= ' novalidate';
      }

      $output .= '>';
    }

    // Add honeypot anti-spam field
    if ( $this->form['add_honeypot'] ) {
      $this->add_input( 'Leave blank to submit', array(
        'name'             => 'honeypot',
        'slug'             => 'honeypot',
        'id'               => 'form_honeypot',
        'wrap_tag'         => 'div',
        'wrap_class'       => array( 'form_field_wrap', 'hidden' ),
        'wrap_id'          => '',
        'wrap_style'       => 'display: none',
        'request_populate' => false
      ) );
    }

    // Add a WordPress nonce field
    if ( $this->form['add_nonce'] && function_exists( 'wp_create_nonce' ) ) {
      $this->add_input( 'WordPress nonce', array(
        'value'            => wp_create_nonce( $this->form['add_nonce'] ),
        'add_label'        => false,
        'type'             => 'hidden',
        'request_populate' => false
      ) );
    }

    // Iterate through the input queue and add input HTML
    foreach ( $this->inputs as $val ) :

      $min_max_range = $element = $end = $attr = $field = $label_html = '';

      // Automatic population of values using $_REQUEST data
      if ( $val['request_populate'] && isset( $_REQUEST[ $val['name'] ] ) ) {

        // Can this field be populated directly?
        if ( ! in_array( $val['type'], array( 'html', 'title', 'radio', 'checkbox', 'select', 'submit' ) ) ) {
          $val['value'] = $_REQUEST[ $val['name'] ];
        }
      }

      // Automatic population for checkboxes and radios
      if (
        $val['request_populate'] &&
        ( $val['type'] == 'radio' || $val['type'] == 'checkbox' ) &&
        empty( $val['options'] )
      ) {
        $val['checked'] = isset( $_REQUEST[ $val['name'] ] ) ? true : $val['checked'];
      }

      switch ( $val['type'] ) {

        case 'html':
          $element = '';
          $end     = $val['label'];
          break;

        case 'title':
          $element = '';
          $end     = '
          <h3>' . $val['label'] . '</h3>';
          break;

        case 'textarea':
          $element = 'textarea';
          $end     = '>' . $val['value'] . '</textarea>';
          break;

        case 'select':
          $element = 'select';
          $end     .= '>';
          foreach ( $val['options'] as $key => $opt ) {
            $opt_insert = '';
            if (
              // Is this field set to automatically populate?
              $val['request_populate'] &&

              // Do we have $_REQUEST data to use?
              isset( $_REQUEST[ $val['name'] ] ) &&

              // Are we currently outputting the selected value?
              $_REQUEST[ $val['name'] ] === $key
            ) {
              $opt_insert = ' selected';

            // Does the field have a default selected value?
            } else if ( $val['selected'] === $key ) {
              $opt_insert = ' selected';
            }
            $end .= '<option value="' . $key . '"' . $opt_insert . '>' . $opt . '</option>';
          }
          $end .= '</select>';
          break;

        case 'radio':
        case 'checkbox':

          // Special case for multiple check boxes
          if ( count( $val['options'] ) > 0 ) :
            $element = '';
            foreach ( $val['options'] as $key => $opt ) {
              $slug = $this->_make_slug( $opt );
              $end .= sprintf(
                '<input type="%s" name="%s[]" value="%s" id="%s"',
                $val['type'],
                $val['name'],
                $key,
                $slug
              );
              if (
                // Is this field set to automatically populate?
                $val['request_populate'] &&

                // Do we have $_REQUEST data to use?
                isset( $_REQUEST[ $val['name'] ] ) &&

                // Is the selected item(s) in the $_REQUEST data?
                in_array( $key, $_REQUEST[ $val['name'] ] )
              ) {
                $end .= ' checked';
              }
              $end .= $this->field_close();
              $end .= ' <label for="' . $slug . '">' . $opt . '</label>';
            }
            $label_html = '<div class="checkbox_header">' . $val['label'] . '</div>';
            break;
          endif;

        // Used for all text fields (text, email, url, etc), single radios, single checkboxes, and submit
        default :
          $element = 'input';
          $end .= ' type="' . $val['type'] . '" value="' . $val['value'] . '"';
          $end .= $val['checked'] ? ' checked' : '';
          $end .= $this->field_close();
          break;

      }

      // Added a submit button, no need to auto-add one
      if ( $val['type'] === 'submit' ) {
        $this->has_submit = true;
      }

      // Special number values for range and number types
      if ( $val['type'] === 'range' || $val['type'] === 'number' ) {
        $min_max_range .= ! empty( $val['min'] ) ? ' min="' . $val['min'] . '"' : '';
        $min_max_range .= ! empty( $val['max'] ) ? ' max="' . $val['max'] . '"' : '';
        $min_max_range .= ! empty( $val['step'] ) ? ' step="' . $val['step'] . '"' : '';
      }

      // Add an ID field, if one is present
      $id = ! empty( $val['id'] ) ? ' id="' . $val['id'] . '"' : '';

      // Output classes
      $class = $this->_output_classes( $val['class'] );

      // Special HTML5 fields, if set
      $attr .= $val['autofocus'] ? ' autofocus' : '';
      $attr .= $val['checked'] ? ' checked' : '';
      $attr .= $val['required'] ? ' required' : '';

      // Build the label
      if ( ! empty( $label_html ) ) {
        $field .= $label_html;
      } elseif ( $val['add_label'] && ! in_array( $val['type'], array( 'hidden', 'submit', 'title', 'html' ) ) ) {
        if ( $val['required'] ) {
          $val['label'] .= ' <strong>*</strong>';
        }
        $field .= '<label for="' . $val['id'] . '">' . $val['label'] . '</label>';
      }

      // An $element was set in the $val['type'] switch statement above so use that
      if ( ! empty( $element ) ) {
        if ( $val['type'] === 'checkbox' ) {
          $field = '
          <' . $element . $id . ' name="' . $val['name'] . '"' . $min_max_range . $class . $attr . $end .
                   $field;
        } else {
          $field .= '
          <' . $element . $id . ' name="' . $val['name'] . '"' . $min_max_range . $class . $attr . $end;
        }
      // Not a form element
      } else {
        $field .= $end;
      }

      // Parse and create wrap, if needed
      if ( $val['type'] != 'hidden' && $val['type'] != 'html' ) :

        $wrap_before = $val['before_html'];
        if ( ! empty( $val['wrap_tag'] ) ) {
          $wrap_before .= '<' . $val['wrap_tag'];
          $wrap_before .= count( $val['wrap_class'] ) > 0 ? $this->_output_classes( $val['wrap_class'] ) : '';
          $wrap_before .= ! empty( $val['wrap_style'] ) ? ' style="' . $val['wrap_style'] . '"' : '';
          $wrap_before .= ! empty( $val['wrap_id'] ) ? ' id="' . $val['wrap_id'] . '"' : '';
          $wrap_before .= '>';
        }

        $wrap_after = $val['after_html'];
        if ( ! empty( $val['wrap_tag'] ) ) {
          $wrap_after = '</' . $val['wrap_tag'] . '>' . $wrap_after;
        }

        $output .= $wrap_before . $field . $wrap_after;
      else :
        $output .= $field;
      endif;

    endforeach;

    // Auto-add submit button
    if ( ! $this->has_submit && $this->form['add_submit'] ) {
      $output .= '<div class="form_field_wrap"><input type="submit" value="Submit" name="submit"></div>';
    }

    // Close the form tag if one was added
    if ( $this->form['form_element'] ) {
      $output .= '</form>';
    }

    // Output or return?
    if ( $echo ) {
      echo $output;
    } else {
      return $output;
    }
  }

  // Easy way to auto-close fields, if necessary
  function field_close() {
    return $this->form['markup'] === 'xhtml' ? ' />' : '>';
  }

  // Validates id and class attributes
  // TODO: actually validate these things
  private function _check_valid_attr( $string ) {

    $result = true;

    // Check $name for correct characters
    // "^[a-zA-Z0-9_-]*$"

    return $result;

  }

  // Create a slug from a label name
  private function _make_slug( $string ) {

    $result = '';

    $result = str_replace( '"', '', $string );
    $result = str_replace( "'", '', $result );
    $result = str_replace( '_', '-', $result );
    $result = preg_replace( '~[\W\s]~', '-', $result );

    $result = strtolower( $result );

    return $result;

  }

  // Parses and builds the classes in multiple places
  private function _output_classes( $classes ) {

    $output = '';

    
    if ( is_array( $classes ) && count( $classes ) > 0 ) {
      $output .= ' class="';
      foreach ( $classes as $class ) {
        $output .= $class . ' ';
      }
      $output .= '"';
    } else if ( is_string( $classes ) ) {
      $output .= ' class="' . $classes . '"';
    }

    return $output;
  }
}