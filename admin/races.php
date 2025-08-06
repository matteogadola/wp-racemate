<?php

defined('ABSPATH') || exit;

class RmiapAdminRaces {
  private $db;

  public function __construct(RmiapDatabase $db) {
    $this->db = $db;

    
    //add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    add_action('wp_ajax_rmiap_race_get', array($this, 'ajax_rmiap_race_get'));
    add_action('wp_ajax_rmiap_race_update', array($this, 'ajax_rmiap_race_update'));

    // AJAX handler per caricare i dati della modale (opzionale, ma buon approccio)
    //add_action( 'wp_ajax_racemate_get_race_details_for_modal', array( $this, 'ajax_get_race_details_for_modal' ) );
  }

  public function render_page() {
    $races = $this->db->get_races();
    ?>

    <div class="pt-3 pe-3">
      <div class="d-flex justify-content-between align-items-center">
        <h1 class="h1"><?php echo esc_html(get_admin_page_title()); ?></h1>
        <div style="max-height: 32px;">
          <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#race-details-modal">
            Nuova gara
          </button>
        </div>
      </div>
      <p>
        <?php /*esc_html_e( 'Qui potrai gestire le gare del plugin Racemate.', $this->text_domain );*/ ?>
      </p>

      <?php if (!empty($races)) : ?>
        <table class="table table-light table-striped">
          <thead>
            <tr>
              <th scope="col">ID</th>
              <th scope="col">NOME</th>
              <th scope="col">DATA</th>
              <th scope="col">STATO</th>
              <th scope="col">COSTO</th>
              <th scope="col">ACCOUNT</th>
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody style="vertical-align: middle;">
            <?php foreach ($races as $race) : ?>
              <tr id="race-<?php echo esc_attr( $race->id ); ?>">
                <td><?php echo esc_html($race->id); ?></td>
                <td><?php echo esc_html($race->name); ?></td>
                <td><?php echo esc_html($race->date); ?></td>
                <td><?php echo esc_html($race->status); ?></td>
                <td><?php echo esc_html($race->price / 100 . '€'); ?></td>
                <td><?php echo esc_html($race->account_id); ?></td>
                <td>
                  <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#race-details-modal" data-race-id="<?php echo esc_attr( $race->id ); ?>">
                    <span class="dashicons dashicons-edit" style="vertical-align: sub;"></span>
                  </button>
                  <!--button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#race-<?php echo esc_attr( $race->id ); ?>-details-modal" data-race-id="<?php echo esc_attr( $race->id ); ?>">
                    <span class="dashicons dashicons-edit" style="vertical-align: sub;"></span>
                  </button-->

                  <!-- Modal -->
                  <!--div class="modal fade" id="race-<?php echo esc_attr( $race->id ); ?>-details-modal" tabindex="-1" aria-labelledby="race-<?php echo esc_attr( $race->id ); ?>-details-modal-label" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title fs-5" id="race-<?php echo esc_attr( $race->id ); ?>-details-modal-label">Modifica gara</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                          <form id="race-<?php echo esc_attr( $race->id ); ?>-details-modal-form" method="post" action="">
                            <input type="hidden" name="id" value="<?php echo esc_html($race->id); ?>">
                            <div class="grid">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo esc_html($race->name); ?>" placeholder="Nome">
                                <label for="name">Nome</label>
                              </div>
                              <div class="form-floating">
                                <input type="text" class="form-control" id="slug" name="slug" value="<?php echo esc_html($race->slug); ?>" placeholder="Slug">
                                <label for="slug">Slug</label>
                              </div>
                              <div class="form-floating">
                                <input type="text" class="form-control" id="date" name="date" value="<?php echo esc_html($race->date); ?>" placeholder="Data">
                                <label for="date">Data</label>
                              </div>
                              <div class="form-floating">
                                <input type="text" class="form-control" id="price" name="price" value="<?php echo esc_html($race->price); ?>" placeholder="Prezzo">
                                <label for="price">Prezzo</label>
                              </div>
                              <div class="form-floating">
                                <input type="text" class="form-control" id="status" name="status" value="<?php echo esc_html($race->status); ?>" placeholder="Stato">
                                <label for="status">Stato</label>
                              </div>
                              <div class="form-floating">
                                <input type="text" class="form-control" id="account_id" name="account_id" value="<?php echo esc_html($race->account_id); ?>" placeholder="Account">
                                <label for="account_id">Account</label>
                              </div>
                            </div>
                          </form>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                          <button type="button" class="btn btn-primary race-details-save-button" data-race-id="<?php echo esc_attr( $race->id ); ?>">Salva</button>
                        </div>
                      </div>
                    </div>
                  </div-->
                </td>
                  </tr>
              <?php endforeach; ?>

            <div class="modal fade" id="race-details-modal" tabindex="-1" aria-labelledby="race-details-modal-label" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title fs-5" id="race-details-modal-label">Nuova gara</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">

                    <form id="race-details-modal-form" method="post" action="">
                      <input type="hidden" name="id">
                      <div class="grid">
                        <div class="form-floating">
                          <input type="text" class="form-control" id="form_name" name="name" value="" placeholder="Nome">
                          <label for="name">Nome</label>
                        </div>
                        <div class="form-floating">
                          <input type="text" class="form-control" id="slug" name="slug" placeholder="Slug">
                          <label for="slug">Slug</label>
                        </div>
                        <div class="form-floating">
                          <input type="text" class="form-control" id="date" name="date" placeholder="Data">
                          <label for="date">Data</label>
                        </div>
                        <div class="form-floating">
                          <input type="text" class="form-control" id="price" name="price" placeholder="Prezzo">
                          <label for="price">Prezzo</label>
                        </div>
                        <div class="form-floating">
                          <input type="text" class="form-control" id="status" name="status" placeholder="Stato">
                          <label for="status">Stato</label>
                        </div>
                        <div class="form-floating">
                          <input type="text" class="form-control" id="account_id" name="account_id" placeholder="Account">
                          <label for="account_id">Account</label>
                        </div>
                        <!--select class="form-select" aria-label="Account" id="account_id" name="account_id">
                          <option value="1" selected>Team Valtellina</option>
                        </select-->
                      </div>
                    </form>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="button" class="btn btn-primary race-details-save-button">Salva</button>
                  </div>
                </div>
              </div>
            </div>

          </tbody>
        </table>
        <?php else : ?>
          <p>Nessuna iscrizione trovata.</p>
        <?php endif; ?>

      </div>

      <?php
    }

    function render_checked($value) {
      return $value ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no"></span>';
    }

    public function ajax_rmiap_race_get() {
      check_ajax_referer('rmiap_admin_nonce', 'nonce');

      if (!current_user_can('manage_options')) {
        wp_send_json_error(array( 'message' => 'Non autorizzato.'), 403);
      }

      try {
        $race = $this->db->get_race($_GET['id']);
        wp_send_json_success($race);
      } catch (Exception $e) {
        error_log('(ajax_rmiap_race_update): ' . $e->getMessage);
        wp_send_json_error(array('message' => __('Errore in update')), 500);
      }
    }

    public function ajax_rmiap_race_update() {
      check_ajax_referer('rmiap_admin_nonce', 'nonce');

      if (!current_user_can('manage_options')) {
        wp_send_json_error(array( 'message' => 'Non autorizzato.'), 403);
      }

      $form = array();
      parse_str($_POST['form'], $form);

      try {
        $key = array_search('id', array_keys($form), true);
        if ($key !== false) {
          $id = array_splice($form, $key, 1);

          if (!$id['id']) {
            $id = $this->db->create_race($form);
            wp_send_json_success(array('id' => $id));
          } else {
            $this->db->update_race($id['id'], $form);
            wp_send_json_success(array('id' => $id['id']));
          }
        }
      } catch (Exception $e) {
        error_log('(ajax_rmiap_race_update): ' . $e->getMessage);
        wp_send_json_error(array('message' => __('Errore in update')), 500);
      }
    }
}
