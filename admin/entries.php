<?php

defined('ABSPATH') || exit;

class RmiapAdminEntries {
  private $db;

  public function __construct(RmiapDatabase $db) {
    $this->db = $db;

    //add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    // AJAX handler per caricare i dati della modale (opzionale, ma buon approccio)
    //add_action( 'wp_ajax_racemate_get_race_details_for_modal', array( $this, 'ajax_get_race_details_for_modal' ) );
    add_action('wp_ajax_rmiap_entry_confirm', array($this, 'ajax_rmiap_entry_confirm'));
    //add_action('wp_ajax_rmiap_entries_export', array($this, 'ajax_rmiap_entries_export'));

    add_action('admin_post_export', array($this, 'export'));
  }

  public function render_page() {
    $entries = $this->db->get_entries_view(2);
    ?>

    <div class="pt-3 pe-3">
      <div class="d-flex justify-content-between align-items-center">
        <h1 class="h1"><?php echo esc_html(get_admin_page_title()); ?></h1>
        <div style="max-height: 32px;">
          <button type="button" class="btn btn-secondary d-none" data-bs-toggle="modal" data-bs-target="#entry-add-modal">
            Nuova iscrizione
          </button>
        </div>
      </div>
      <p><?php /*esc_html_e( 'Qui potrai gestire le gare del plugin Racemate.', $this->text_domain );*/ ?></p>

      <?php if (!empty($entries)) : ?>
        <div class="entries-bar">
          <div class="filters-bar">
            <div class="form-floating">
              <select class="form-select" id="race_id_filter" disabled>
                <option value="1">Vertical Montemezzo</option>
                <option value="2" selected>Bivacco Rovedatti Vertical</option>
              </select>
              <label for="race_id_filter">Gara</label>
            </div>
            <div class="form-floating">
              <select class="form-select" id="payment_status_filter" disabled>
                <option value="" selected>Tutti</option>
                <option value="pending">In attesa</option>
                <option value="paid">Pagato</option>
              </select>
              <label for="payment_status_filter">Esito pagamento</label>
            </div>
            <div class="form-floating d-none">
              <input type="text" class="form-control" id="last_name_filter">
              <label for="last_name_filter">Cognome</label>
            </div>
          </div>
          <div class="actions-bar">
            <a href="<?php echo admin_url('admin-post.php'); ?>?action=export&race_id=2" class="btn btn-secondary" style="display: flex; align-items: center;">
              <span class="dashicons dashicons-download"></span>
            </a>
          </div>
        </div>
        <table class="table table-light table-striped">
          <thead>
            <tr>
              <th scope="col">ID</th>
              <th scope="col">GARA</th>
              <th scope="col">NOME</th>
              <th scope="col">COGNOME</th>
              <th scope="col">SESSO</th>
              <th scope="col">ANNO</th>
              <th scope="col">CLUB</th>
              <th scope="col">PAGAMENTO</th>
              <th scope="col">ESITO</th>
              <th scope="col">MAIL</th>
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody style="vertical-align: middle;">
            <?php foreach ($entries as $entry) : ?>
              <tr id="entry-<?php echo esc_attr( $entry->id ); ?>">
                <td><?php echo esc_html($entry->id); ?></td>
                <td><?php echo esc_html($entry->race_name); ?></td>
                <td><?php echo esc_html($entry->first_name); ?></td>
                <td><?php echo esc_html($entry->last_name); ?></td>
                <td><?php echo esc_html($entry->gender); ?></td>
                <td><?php echo esc_html($entry->birth_year); ?></td>
                <td><?php echo esc_html($entry->club); ?></td>
                <td><?php echo esc_html($entry->payment_method); ?></td>
                <td><?php echo esc_html($entry->payment_status); ?></td>
                <td><?php echo esc_html($entry->notification_status); ?></td>
                <td>
                  <?php if ($entry->payment_method !== 'stripe' && $entry->payment_status === 'pending') { ?>
                  <button type="button" class="btn btn-success entry-confirm-button" data-entry-id="<?php echo esc_attr( $entry->id ); ?>">
                    Conferma
                  </button>
                  <?php } ?>
                  <!--button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#entry-delete-modal" data-entry-id="<?php echo esc_attr( $entry->id ); ?>">
                    <span class="dashicons dashicons-trash" style="vertical-align: sub;"></span>
                  </button-->
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else : ?>
          <p>Nessuna iscrizione trovata.</p>
        <?php endif; ?>

      <!-- Modal (add) -->
      <div class="modal fade" id="entry-add-modal" tabindex="-1" aria-labelledby="entry-add-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title fs-5" id="entry-add-modal-label">Nuova iscrizione</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            
              <form id="entry-add-modal-form">
                <div class="grid">
                  <input type="hidden" name="payment_method" value="cash">
                  <select class="form-select grid-full" name="race_id" aria-label="Default select example">
                    <option value="1" selected>Vertical Montemezzo</option>
                    <option value="2">...</option>
                  </select>
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
                    <input type="text" class="form-control" id="club" name="club" placeholder="Società">
                    <label for="club">Società</label>
                  </div>
                  <div class="form-floating">
                    <input type="text" class="form-control" id="email" name="email" placeholder="Email">
                    <label for="email">Email *</label>
                  </div>
                  <div class="form-floating">
                    <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="Telefono">
                    <label for="phone_number">Telefono</label>
                  </div>
                  <div class="alert alert-danger grid-full d-none" role="alert"></div>
                </div>
              </form>

            </div>
            <div class="modal-footer">
              <button type="button" id="entry-add-close-button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
              <button type="button" id="entry-add-button" class="btn btn-primary">Conferma</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php
  }

  public function ajax_rmiap_entry_confirm() {
    check_ajax_referer('rmiap_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error(array( 'message' => 'Non autorizzato.'), 403);
    }

    if (empty($_POST['id'])) {
      wp_send_json_error(array('message' => 'Errore. Prova a ricaricare la pagina'), 400);
    }

    try {
      $id = $_POST['id'];
      $params = array(
        'payment_status' => 'paid',
        'payment_date' => date('c'),
      );
      $this->db->update_entry($id, $params);
      wp_send_json_success(array_merge($params, ['id' => $id]));
    } catch (Exception $e) {
      error_log('(ajax_rmiap_entry_confirm): ' . $e->getMessage);
      wp_send_json_error(array('message' => __('Errore in update')), 500);
    }
  }

  public function ajax_rmiap_entry_delete() {
    check_ajax_referer('rmiap_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error(array( 'message' => 'Non autorizzato.'), 403);
    }

    if (empty($_POST['id'])) {
      wp_send_json_error(array('message' => 'Errore. Prova a ricaricare la pagina'), 400);
    }

    try {
      $id = $_POST['id'];
      $this->db->delete_entry($id);
      wp_send_json_success(array('id' => $id));
    } catch (Exception $e) {
      error_log('(ajax_rmiap_entry_delete): ' . $e->getMessage);
      wp_send_json_error(array('message' => __('Errore in delete')), 500);
    }
  }

  public function export() {
      /*header("Content-type: application/force-download");
      header("Content-disposition: csv" . date("Y-m-d") . "csv");
      header( "Content-disposition: filename=".$filename);
      header('Content-Description: File Transfer');

      echo $filename;
      exit;*/
	$race_id = isset($_GET['race_id']) ? $_GET['race_id'] : null;
    $entries = $this->db->get_entries_view($race_id);

    $delimiter = ";";
    $columns = array('id', 'first_name', 'last_name', 'birth_year', 'gender', 'club', 'tin', 'email', 'phone_number', 'payment_method', 'payment_status');
    $filename = strtolower(preg_replace('/\s+/', '', $entries['0']->race_name)) . ".csv";

    
    
    
    header( 'Content-Type: application/csv' );
    header( 'Content-Disposition: attachment; filename="' . $filename . '";' );

    // clean output buffer
    ob_end_clean();
    
    $handle = fopen( 'php://output', 'w' );

    // use keys as column titles
    //fputcsv( $handle, array_keys( (array)$entries['0'] ), ";");
    fputcsv( $handle, $columns, $delimiter);

    foreach ($entries as $value) {
      fputcsv($handle, array_intersect_key((array)$value, array_flip($columns)), $delimiter);
    }

    fclose( $handle );

    // flush buffer
    //ob_flush();
    
    // use exit to get rid of unexpected output afterward
    exit();
    //header("Content-type: text/x-csv");
    //header("Content-type: text/csv");
    //header("Content-type: application/csv");
    //header("Content-Disposition: attachment; filename=search_results.csv");
    //echo $out;
    //exit;

    
    //header("Content-Type: text/csv; charset=UTF-16LE");
    //header("Content-Disposition: attachment;filename=$filename");
    //echo file_get_contents($filename);
    //readfile($filename);
    //exit();
  }
}
