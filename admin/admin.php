<?php

defined('ABSPATH') || exit;

require_once RMIAP_PLUGIN_PATH . 'admin/accounts.php';
require_once RMIAP_PLUGIN_PATH . 'admin/races.php';
require_once RMIAP_PLUGIN_PATH . 'admin/entries.php';

class RmiapAdmin {
  private $version = '1.0.0';
  private $db;
  private $accounts;
  private $races;
  private $entries;

  public function __construct(RmiapDatabase $db) {
    $this->db = $db;
    $this->accounts = new RmiapAdminAccounts($db);
    $this->races = new RmiapAdminRaces($db);
    $this->entries = new RmiapAdminEntries($db);

    add_action('admin_menu', array($this, 'setup'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
  }

  public function setup() {
    add_menu_page(
      'Racemate',             // Page title
      'Racemate',             // Menu title
      'manage_options',       // Capability required to see this menu
      'rmiap-entries',        // Menu slug (should be unique)
      null,                   // Function to display the page content
      'dashicons-superhero',  // Icon URL or Dashicon class
      30                      // Position in the menu order
    );

    add_submenu_page(
      'rmiap-entries',
      'Iscrizioni',
      'Iscrizioni',
      'manage_options',
      'rmiap-entries',
      array($this->entries, 'render_page'),
    );

    add_submenu_page(
      'rmiap-entries',
      'Gare',
      'Gare',
      'manage_options',
      'rmiap-races',
      array($this->races, 'render_page'),
    );

    add_submenu_page(
      'rmiap-entries',
      'Accounts',
      'Accounts',
      'manage_options',
      'rmiap-accounts',
      array($this->accounts, 'render_page'),
    );
  }

  public function enqueue_admin_assets($hook_suffix) {
    $pages_slugs = array(
      'toplevel_page_rmiap-entries',
      'racemate_page_rmiap-accounts',
      'racemate_page_rmiap-entries',
      'racemate_page_rmiap-races',
    );

    if (in_array($hook_suffix, $pages_slugs)) {
      wp_enqueue_style(
        'rmiap-admin-style',
        plugin_dir_url(__DIR__) . 'assets/css/admin.css',
        array('wp-components'), // 'wp-components' per stili base modale WP se li usi
        $this->version,
      );

      wp_enqueue_script(
        'rmiap-admin-script',
        plugin_dir_url(__DIR__) . 'assets/js/admin.js',
        array('jquery'), // Dipendenza da jQuery
        $this->version,
      );

      wp_enqueue_script(
        'rmiap-admin-script-bootstrap',
        plugin_dir_url(__DIR__) . 'assets/js/bootstrap.min.js',
        array('jquery'),
        $this->version,
      );

      wp_localize_script(
        'rmiap-admin-script',
        'rmiap_admin_params',
        array(
          'ajax_url' => admin_url('admin-ajax.php'),
          'nonce'    => wp_create_nonce('rmiap_admin_nonce')
        ),
      );
    }
  }
}
