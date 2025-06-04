<?php

//namespace Magda\Racemate\Admin;

defined('ABSPATH') || exit;

class RacemateAdmin {
  private $db;
  private $text_domain = 'prova';

  public function __construct(RacemateDatabase $db) {
    $this->db = $db;

    add_action('admin_menu', array($this, 'setup_menu'));

    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
    // AJAX handler per caricare i dati della modale (opzionale, ma buon approccio)
    add_action( 'wp_ajax_racemate_get_race_details_for_modal', array( $this, 'ajax_get_race_details_for_modal' ) );
  }

  public function setup_menu() {
    add_menu_page(
      'Racemate',             // Page title
      'Racemate',             // Menu title
      'manage_options',       // Capability required to see this menu
      'racemate-races',       // Menu slug (should be unique)
      null,                   // Function to display the page content
      'dashicons-superhero',  // Icon URL or Dashicon class
      30                      // Position in the menu order
    );

    add_submenu_page(
      'racemate-races',
      'Gare',
      'Gare',
      'manage_options',
      'racemate-races',
      array($this, 'render_races_page'),
    );

    add_submenu_page(
      'racemate-races',
      'Iscrizioni',
      'Iscrizioni',
      'manage_options',
      'racemate-entries',
      array($this, 'render_entries_page'),
    );

    add_submenu_page(
      'racemate-races',
      'Accounts',
      'Accounts',
      'manage_options',
      'racemate-accounts',
      array($this, 'render_accounts_page'),
    );
  }

    /**
     * Mostra il contenuto per la pagina di sottomenu "Gare".
     *
     * @since    1.0.0
     */
    public function render_races_page() {
      if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Non hai i permessi sufficienti per accedere a questa pagina.', $this->text_domain));
      }

      $races = $this->db->getRaces();

      $thead = sprintf('
        <tr>
          <td>ID</td>
          <td>SLUG</td>
          <td>NAME</td>
          <td>DATE</td>
          <td>STATUS</td>
          <td>PRICE</td>
          <td>ACCOUNT_ID</td>
        </tr>'
      );
    
    function cb2($a) {
      return sprintf('
        <tr>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
        </tr>',
        $a['id'],
        $a['slug'],
        $a['name'],
        $a['date'],
        $a['status'],
        $a['price'],
        $a['account_id'],
      );
    }
    $tbody = implode(array_map('cb2', $races));
    

    $table = sprintf(
      '<table class="table">
        <thead>%s</thead>
        <tbody>%s</tbody>
      </table>',
      $thead,
      $tbody,
    );

    $output = sprintf(
      '<div>
        <h1>Gare</h1>
        %s
      </div>',
      $table
    );

    echo $output;

/*
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Qui potrai gestire le gare del plugin Racemate.', $this->text_domain ); ?></p>
            </div>
        <?php
*/
    }

    /**
     * Mostra il contenuto per la pagina di sottomenu "Accounts".
     *
     * @since    1.0.0
     */
    public function render_accounts_page() {
        /*if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Non hai i permessi sufficienti per accedere a questa pagina.', $this->text_domain ) );
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Qui potrai gestire gli account degli utenti o dei team.', $this->text_domain ); ?></p>
            </div>
        <?php*/

/*if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Non hai i permessi sufficienti per accedere a questa pagina.', $this->text_domain));
      }

      $accounts = $this->db->getAccounts();

    $thead = sprintf('
        <tr>
          <td>ID</td>
          <td>SLUG</td>
          <td>NAME</td>
          <td>SECRET</td>
          <td>WEBHOOK</td>
        </tr>'
      );

    function cb2($a) {
      return sprintf('
        <tr>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
        </tr>',
        $a['id'],
        $a['slug'],
        $a['name'],
        $a['stripe_secret_key'],
        $a['stripe_webhook_key'],
      );
    }
    $tbody = implode(array_map('cb2', $accounts));
    

    $table = sprintf(
      '<table class="table">
        <thead>%s</thead>
        <tbody>%s</tbody>
      </table>',
      $thead,
      $tbody,
    );

    $output = sprintf(
      '<div>
        <h1>Accounts</h1>
        %s
      </div>',
      $table
    );

    echo $output;*/
      $accounts = $this->db->getAccounts();
      ?>

      <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Qui potrai gestire le gare del plugin Racemate.', $this->text_domain ); ?></p>

            <?php if ( ! empty( $accounts ) ) : ?>
                <table class="wp-list-table widefat fixed striped racemate-gare-table">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column column-id"><?php esc_html_e( 'ID', $this->text_domain ); ?></th>
                            <th scope="col" class="manage-column column-nome"><?php esc_html_e( 'Nome', $this->text_domain ); ?></th>
                            <th scope="col" class="manage-column column-data"><?php esc_html_e( 'Data Svolgimento', $this->text_domain ); ?></th>
                            <th scope="col" class="manage-column column-luogo"><?php esc_html_e( 'Luogo', $this->text_domain ); ?></th>
                            <th scope="col" class="manage-column column-actions"><?php esc_html_e( 'Azioni', $this->text_domain ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="the-list">
                        <?php foreach ( $accounts as $account ) : ?>
                            <tr id="race-<?php echo esc_attr( $account->id ); ?>">
                                <td class="column-id"><?php echo esc_html( $account->id ); ?></td>
                                <td class="column-nome"><strong><?php echo esc_html( $account->name ); ?></strong></td>
                                <td class="column-data"><?php echo esc_html( date_i18n( get_option('date_format'), strtotime($account->name) ) ); ?></td>
                                <td class="column-luogo"><?php echo esc_html( $account->luogo ); ?></td>
                                <td class="column-actions">
                                    <button type="button" class="button racemate-open-modal-button" data-race-id="<?php echo esc_attr( $account->id ); ?>">
                                        <?php esc_html_e( 'Dettagli', $this->text_domain ); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php esc_html_e( 'Nessuna gara trovata.', $this->text_domain ); ?></p>
            <?php endif; ?>

            <div id="racemate-details-modal" class="racemate-modal" style="display:none;">
                <div class="racemate-modal-content">
                    <button type="button" class="racemate-modal-close button-link"><span class="dashicons dashicons-no-alt"></span></button>
                    <h2 id="racemate-modal-title"><?php esc_html_e( 'Dettagli Gara', $this->text_domain ); ?></h2>
                    <div id="racemate-modal-body">
                        <p><?php esc_html_e( 'Caricamento dettagli...', $this->text_domain ); ?></p>
                    </div>
                    <div class="racemate-modal-footer">
                        <button type="button" class="button racemate-modal-close"><?php esc_html_e( 'Chiudi', $this->text_domain ); ?></button>
                    </div>
                </div>
            </div>

        </div>
        <?php



    }

    /**
     * Mostra il contenuto per la pagina di sottomenu "Iscrizioni".
     *
     * @since    1.0.0
     */
    public function render_entries_page() {
        if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Non hai i permessi sufficienti per accedere a questa pagina.', $this->text_domain));
      }

      $entries = $this->db->getEntries();

    $thead = sprintf('
        <tr>
          <td>ID</td>
          <td>GARA</td>
          <td>NOME</td>
          <td>COGNOME</td>
          <td>ANNO</td>
          <td>GENERE</td>
          <td>PAGAMENTO</td>
          <td>STATO</td>
        </tr>'
      );

    function cb2($a) {
      return sprintf('
        <tr>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
        </tr>',
        $a['id'],
        $a['race_id'],
        $a['first_name'],
        $a['last_name'],
        $a['birth_year'],
        $a['gender'],
        $a['payment_method'],
        $a['payment_status'],
      );
    }
    $tbody = implode(array_map('cb2', $entries));
    

    $table = sprintf(
      '<table class="table">
        <thead>%s</thead>
        <tbody>%s</tbody>
      </table>',
      $thead,
      $tbody,
    );

    $output = sprintf(
      '<div>
        <h1>Iscrizioni</h1>
        %s
      </div>',
      $table
    );

    echo $output;
    }








    /**
     * Accoda script e stili specifici per le pagine admin del plugin.
     *
     * @param string $hook_suffix Lo slug della pagina admin corrente.
     */
    public function enqueue_admin_assets( $hook_suffix ) {
        // Accoda solo nelle pagine del nostro plugin, es. la pagina Gare
        // Lo slug della pagina Gare è 'racemate_main_page' per la prima pagina o lo slug del sottomenu,
        // nel nostro setup, il primo sottomenu Gare ha slug 'racemate_gare'.
        // Il menu principale 'Racemate' (con slug 'racemate_main_page') reindirizza a 'racemate_gare'.
        // Il $hook_suffix per la pagina "Gare" sarà qualcosa tipo: 'racemate_page_racemate_gare' o 'toplevel_page_racemate_main_page'
        // se la prima pagina è quella principale. Controlla con un error_log($hook_suffix) per sicurezza.
        
        $pages_slugs = array(
            'toplevel_page_racemate_main_page', // Se "Gare" è la prima pagina del menu principale
            'racemate_page_racemate_gare',      // Slug specifico della sottomenu "Gare"
            'racemate_page_racemate_accounts',
            'racemate_page_racemate_iscrizioni'
        );

        if ( in_array($hook_suffix, $pages_slugs) ) {
            wp_enqueue_style(
                $this->plugin_name . '-admin-table-modal', // Handle univoco per lo stile
                plugin_dir_url( dirname( __FILE__, 2 ) ) . 'assets/css/admin-table.css', // Percorso al CSS
                array( 'wp-components' ), // 'wp-components' per stili base modale WP se li usi
                $this->version
            );

            wp_enqueue_script(
                $this->plugin_name . '-admin-modal-script', // Handle univoco per lo script
                plugin_dir_url( dirname( __FILE__, 2 ) ) . 'assets/js/admin-modal.js', // Percorso al JS
                array( 'jquery' ), // Dipendenza da jQuery
                $this->version,
                true // Carica nel footer
            );
            
            // Passa dati a JavaScript, come l'URL per AJAX e un nonce
            wp_localize_script(
                $this->plugin_name . '-admin-modal-script',
                'racemate_admin_modal_params',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'racemate_modal_nonce' )
                )
            );
        }
    }




    /**
     * Handler AJAX per recuperare i dettagli di una gara per la modale.
     */
    public function ajax_get_race_details_for_modal() {
        // Verifica il nonce per sicurezza
        check_ajax_referer( 'racemate_modal_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Non autorizzato.', $this->text_domain ) ), 403 );
        }

        if ( ! isset( $_POST['race_id'] ) ) {
            wp_send_json_error( array( 'message' => __( 'ID Gara mancante.', $this->text_domain ) ), 400 );
        }

        $race_id = absint( $_POST['race_id'] );
        $race_details = $this->db_handler->get_race_details_for_modal( $race_id );

        if ( $race_details ) {
            // Costruisci l'HTML o i dati da inviare per la modale
            // Qui inviamo dati strutturati, il JS costruirà l'HTML
            $data_for_modal = array(
                'title' => sprintf(esc_html__('Dettagli: %s', $this->text_domain), $race_details->nome_gara),
                'id' => $race_details->id,
                'nome' => esc_html($race_details->nome_gara),
                'data' => esc_html(date_i18n(get_option('date_format'), strtotime($race_details->data_svolgimento))),
                'luogo' => esc_html($race_details->luogo),
                'descrizione' => nl2br(esc_html($race_details->descrizione_estesa)), // Assumendo colonna descrizione_estesa
                'organizzatore' => esc_html($race_details->organizzatore) // Assumendo colonna organizzatore
            );
            wp_send_json_success( $data_for_modal );
        } else {
            wp_send_json_error( array( 'message' => __( 'Gara non trovata.', $this->text_domain ) ), 404 );
        }
    }
}
