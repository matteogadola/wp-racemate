<?php
/**
 * Gestisce tutte le interazioni con il database per il plugin Racemate.
 *
 * @package RacematePlugin
 * @subpackage RacematePlugin/includes
 */

//namespace Magda\Racemate\Db;

defined('ABSPATH') || exit;

class RmiapDatabase {
  private $wpdb;
  private $table_races;
  private $table_accounts;
  private $table_entries;
  private $table_entries_view;
  private $table_entries_ext;

  public function __construct() {
    global $wpdb;
    $this->wpdb = $wpdb;

    // Definisci i nomi delle tue tabelle custom una volta sola
    $this->table_races = $this->wpdb->prefix . 'racemate_races';
    $this->table_accounts = $this->wpdb->prefix . 'racemate_accounts';
    $this->table_entries = $this->wpdb->prefix . 'racemate_entries';
    $this->table_entries_view = $this->wpdb->prefix . 'racemate_entries_view';
    $this->table_entries_ext = $this->wpdb->prefix . 'racemate_entries_ext';
  }

  public function get_accounts() {
    return $this->wpdb->get_results("SELECT * FROM {$this->table_accounts}");
  }

  public function get_account($id) {
    $id = absint($id);
    return $this->wpdb->get_row(
      $this->wpdb->prepare("SELECT * FROM {$this->table_accounts} WHERE id = %d", $id)
    );
  }

  public function update_account($id, $params) {
    $updated = $this->wpdb->update($this->table_accounts, $params, [ 'id' => $id ]);

    if ($updated === false) {
      throw new Exception('Errore in update');
    }
    return $updated;
  }

  public function get_races() {
    return $this->wpdb->get_results("SELECT * FROM {$this->table_races}");
  }

  public function create_race($race) {
    $race = array_filter($race, function($k) {
      return in_array($k, array(
        'slug',
        'name',
        'date',
        'status',
        'price',
        'capacity',
        'start_sale_date',
        'end_sale_date',
        'payment_methods',
        'account_id',
      ));
    }, ARRAY_FILTER_USE_KEY);

    $inserted = $this->wpdb->insert($this->table_races, $race);

    if ($inserted === false) {
      throw new Exception('Errore in create');
    }
    return $this->wpdb->insert_id;
  }

  public function update_race($id, $params) {
    $updated = $this->wpdb->update($this->table_races, $params, [ 'id' => $id ]);

    if ($updated === false) {
      throw new Exception('Errore in update');
    }
    return $updated;
  }

  

  public function get_entries() {
    return $this->wpdb->get_results("SELECT * FROM {$this->table_entries}");
  }

  public function get_entries_view($race_id = null) {
    if (isset($race_id)) {
      $race_id = absint($race_id);
      return $this->wpdb->get_results(
        $this->wpdb->prepare("SELECT * FROM {$this->table_entries_view} WHERE race_id = %d", $race_id)
      );
    } else {
      return $this->wpdb->get_results("SELECT * FROM {$this->table_entries_view}");
    }
  }

  public function get_race($id) {
    $id = absint($id);
    return $this->wpdb->get_row(
      $this->wpdb->prepare("SELECT * FROM {$this->table_races} WHERE id = %d", $id)
    );
  }

  public function get_entry($id) {
    $id = absint($id);
    return $this->wpdb->get_row(
      $this->wpdb->prepare("SELECT * FROM {$this->table_entries_ext} WHERE id = %d", $id)
    );
  }

  public function entry_exists($race_id, $tin) {
    $race_id = absint($race_id);
    $exist = $this->wpdb->get_row(
      $this->wpdb->prepare("
        SELECT * FROM {$this->table_entries_view}
        WHERE race_id = %d AND tin = %s",
        $race_id, $tin
      )
    );
    return isset($exist);
  }

  public function create_entry($entry) {
    $entry = array_filter($entry, function($k) {
      return in_array($k, array(
        'race_id',
        'first_name',
        'last_name',
        'birth_date',
        'birth_year',
        'gender',
        'country',
        'club',
        'tin',
        'fidal_card',
        'email',
        'phone_number',
        'payment_id',
        'payment_date',
        'payment_method',
        'payment_status',
        'amount',
        'items',
      ));
    }, ARRAY_FILTER_USE_KEY);

    $inserted = $this->wpdb->insert($this->table_entries, $entry);

    if ($inserted === false) {
      throw new Exception('Errore in create');
    }
    return $this->wpdb->insert_id;
  }

  public function update_entry($id, $params) {
    $id = absint($id);
    $updated = $this->wpdb->update($this->table_entries, $params, [ 'id' => $id ]);

    if ($updated === false) {
      throw new Exception('Errore in update');
    }
    return $updated;
  }
}
