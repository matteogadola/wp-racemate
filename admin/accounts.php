<?php

defined('ABSPATH') || exit;

class RmiapAdminAccounts {
  private $db;

  public function __construct(RmiapDatabase $db) {
    $this->db = $db;

    //add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

    // AJAX handler per caricare i dati della modale (opzionale, ma buon approccio)
    add_action('wp_ajax_rmiap_account_update', array($this, 'ajax_rmiap_account_update'));
  }

  public function render_page() {
    $accounts = $this->db->get_accounts();
    ?>

    <div class="pt-3 pe-3">
      <h1 class="h1"><?php echo esc_html(get_admin_page_title()); ?></h1>
      <p><?php /*esc_html_e( 'Qui potrai gestire le gare del plugin Racemate.', $this->text_domain );*/ ?></p>

      <?php if (!empty($accounts)) : ?>
        <table class="table table-light table-striped">
          <thead>
            <tr>
              <th scope="col">ID</th>
              <th scope="col">NOME</th>
              <th scope="col">SECRET</th>
              <th scope="col">WEBHOOK</th>
              <th scope="col">MAIL</th>
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody style="vertical-align: middle;">
            <?php foreach ($accounts as $account) : ?>
              <tr id="account-<?php echo esc_attr( $account->id ); ?>">
                <td><?php echo esc_html($account->id); ?></td>
                <td><?php echo esc_html($account->name); ?></td>
                <td><?php echo $this->render_checked($account->stripe_secret_key); ?></td>
                <td><?php echo $this->render_checked($account->stripe_webhook_key); ?></td>
                <td><?php echo $this->render_checked($account->notification_apikey); ?></td>
                <td>
                  <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#account-<?php echo esc_attr( $account->id ); ?>-details-modal" data-account-id="<?php echo esc_attr( $account->id ); ?>">
                    <span class="dashicons dashicons-edit" style="vertical-align: sub;"></span>
                  </button>

                  <!-- Modal -->
                  <div class="modal fade" id="account-<?php echo esc_attr( $account->id ); ?>-details-modal" tabindex="-1" aria-labelledby="account-<?php echo esc_attr( $account->id ); ?>-details-modal-label" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title fs-5" id="account-<?php echo esc_attr( $account->id ); ?>-details-modal-label">Modifica account</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                          <form id="account-<?php echo esc_attr( $account->id ); ?>-details-modal-form" method="post" action="">
                            <input type="hidden" name="id" value="<?php echo esc_html($account->id); ?>">
                            <div class="grid">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo esc_html($account->name); ?>" placeholder="Nome">
                                <label for="name">Nome</label>
                              </div>
                              <div class="form-floating">
                                <input type="text" class="form-control" id="slug" name="slug" value="<?php echo esc_html($account->slug); ?>" placeholder="Slug">
                                <label for="slug">Slug</label>
                              </div>
                              <div class="form-floating">
                                <input type="text" class="form-control" id="stripe_secret_key" name="stripe_secret_key" value="<?php echo esc_html($account->stripe_secret_key); ?>" placeholder="Stripe secret">
                                <label for="stripe_secret_key">Stripe secret</label>
                              </div>
                              <div class="form-floating">
                                <input type="text" class="form-control" id="stripe_webhook_key" name="stripe_webhook_key" value="<?php echo esc_html($account->stripe_webhook_key); ?>" placeholder="Stripe webhook">
                                <label for="stripe_webhook_key">Stripe webhook</label>
                              </div>
                              <div class="form-floating">
                                <input type="text" class="form-control" id="notification_apikey" name="notification_apikey" value="<?php echo esc_html($account->notification_apikey); ?>" placeholder="Mail apikey">
                                <label for="notification_apikey">Mail apikey</label>
                              </div>
                              <div class="form-floating">
                                <input type="text" class="form-control" id="notification_from" name="notification_from" value="<?php echo esc_html($account->notification_from); ?>" placeholder="Mail mittente">
                                <label for="notification_from">Mail mittente</label>
                              </div>
                            </div>
                          </form>
                          
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                          <button type="button" class="btn btn-primary account-details-save-button" data-account-id="<?php echo esc_attr( $account->id ); ?>">Salva</button>
                        </div>
                      </div>
                    </div>
                  </div>

                </td>
                  </tr>
              <?php endforeach; ?>
          </tbody>
        </table>
        <?php else : ?>
          <p>Nessuna gara trovata.</p>
        <?php endif; ?>

      </div>

      <?php
    }

    function render_checked($value) {
      return $value ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no"></span>';
    }

    /**
     * Handler AJAX per recuperare i dettagli di una gara per la modale.
     */
    public function ajax_rmiap_account_update() {
      check_ajax_referer('rmiap_admin_nonce', 'nonce');

      if (!current_user_can('manage_options')) {
        wp_send_json_error(array( 'message' => __('Non autorizzato.') ), 403);
      }

      $form = array();
      parse_str($_POST['form'], $form);

      try {
        $key = array_search('id', array_keys($form), true);
        if ($key !== false) {
          $id = array_splice($form, $key, 1);
          $this->db->update_account($id['id'], $form);
          wp_send_json_success(array('id' => $id['id']));
        }
      } catch (Exception $e) {
        error_log('(ajax_rmiap_account_update): ' . $e->getMessage);
        wp_send_json_error(array('message' => __('Errore in update')), 500);
      }
      

        /*if ( ! isset( $_POST['race_id'] ) ) {
            wp_send_json_error( array( 'message' => __( 'ID Gara mancante.', $this->text_domain ) ), 400 );
        }*/

        /*$race_id = absint( $_POST['race_id'] );
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
        }*/
    }
}
