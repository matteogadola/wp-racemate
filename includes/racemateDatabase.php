<?php
/**
 * Gestisce tutte le interazioni con il database per il plugin Racemate.
 *
 * @package RacematePlugin
 * @subpackage RacematePlugin/includes
 */

//namespace Magda\Racemate\Database;

defined('ABSPATH') || exit;

class RacemateDatabase {
    private $wpdb;
    private $table_races;
    private $table_accounts;
    private $table_entries;

    public function __construct() {
      global $wpdb;
      $this->wpdb = $wpdb;

      // Definisci i nomi delle tue tabelle custom una volta sola
      $this->table_races = $this->wpdb->prefix . 'racemate_races';
      $this->table_accounts = $this->wpdb->prefix . 'racemate_accounts';
      $this->table_entries = $this->wpdb->prefix . 'racemate_entries';
    }

    public function getAccounts() {
      return $this->wpdb->get_results(
        $this->wpdb->prepare("SELECT * FROM {$this->table_accounts}"),
        //'ARRAY_A'
      );
    }

    public function getRaces() {
      return $this->wpdb->get_results(
        $this->wpdb->prepare("SELECT * FROM {$this->table_races}"),
        'ARRAY_A'
      );
    }

    public function getEntries() {
      return $this->wpdb->get_results(
        $this->wpdb->prepare("SELECT * FROM {$this->table_entries}"),
        'ARRAY_A'
      );
    }

    /**
     * Recupera una gara tramite il suo ID.
     *
     * @param int $id ID della gara.
     * @return object|null Oggetto della gara o null se non trovata.
     */
    public function getRace($id) {
      $id = absint($id);
      return $this->wpdb->get_row(
        $this->wpdb->prepare("SELECT * FROM {$this->table_races} WHERE id = %d", $id),
        'ARRAY_A'
      );
    }

    public function getAccount($id) {
      $id = absint($id);
      return $this->wpdb->get_row(
        $this->wpdb->prepare("SELECT * FROM {$this->table_accounts} WHERE id = %d", $id),
        'ARRAY_A'
      );
    }

    public function getEntry($id) {
      $id = absint($id);
      return $this->wpdb->get_row(
        $this->wpdb->prepare("SELECT * FROM {$this->table_entries} WHERE id = %d", $id),
        'ARRAY_A'
      );
    }

    public function createEntry($entry) {
      $inserted = $this->wpdb->insert($this->table_entries, $entry);

      if ($inserted === false) {
        throw new Exception('Errore in create');
      }
      return $this->wpdb->insert_id;
    }

    public function updateEntry($id, $params) {
      $id = absint($id);
      $updated = $this->wpdb->update($this->table_entries, $params, [ 'id' => $id ]);

      if ($updated === false) {
        throw new Exception('Errore in update');
      }
      return $updated;
    }

    /**
     * Esempio: Recupera un account tramite un identificativo univoco (es. fornito da Splunk).
     *
     * @param string $splunk_ref L'identificativo di riferimento.
     * @return object|null Oggetto dell'account o null se non trovato.
     */
    public function get_account_by_splunk_ref( $splunk_ref ) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare( "SELECT * FROM {$this->table_accounts} WHERE splunk_reference_id = %s", $splunk_ref )
        );
    }

    /**
     * Inserisce un log dell'evento Splunk.
     * Assicurati che la tabella $this->table_splunk_logs esista con le colonne appropriate.
     * Esempio colonne: id (AUTO_INCREMENT), received_at (DATETIME), search_name (VARCHAR), raw_payload (TEXT)
     *
     * @param array $splunk_payload Il payload JSON decodificato da Splunk.
     * @return int|false ID dell'inserimento o false in caso di errore.
     */
    public function log_splunk_event( array $splunk_payload ) {
        $data_to_insert = array(
            'received_at' => current_time( 'mysql', 1 ), // Data e ora GMT
            'search_name' => isset( $splunk_payload['search_name'] ) ? sanitize_text_field( $splunk_payload['search_name'] ) : null,
            'raw_payload' => wp_json_encode( $splunk_payload ) // Salva l'intero payload come JSON
        );

        // Definisci i formati per ogni colonna (%s per stringa, %d per intero, %f per float)
        $formats = array(
            '%s', // received_at
            '%s', // search_name
            '%s'  // raw_payload
        );

        $result = $this->wpdb->insert( $this->table_splunk_logs, $data_to_insert, $formats );
        
        if ( $result ) {
            return $this->wpdb->insert_id;
        }
        return false;
    }

    // Aggiungi qui altri metodi per inserire, aggiornare, eliminare o recuperare dati...
    // Esempio:
    // public function insert_race( array $data ) { ... }
    // public function update_account_status( $account_id, $new_status ) { ... }

}
