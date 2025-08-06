<?php

/*
 * Plugin Name:       Racemate
 * Plugin URI:        https://github.com/matteogadola/wp-racemate
 * Description:       Add Stripe payment and opinionated subscription forms to your WordPress site in minutes.
 * Version:           0.9.1
 * Requires at least: 6.7
 * Requires PHP:      8.2
 * Author:            Matteo Gadola
 * Author URI:        https://www.linkedin.com/in/matteogadola
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        false
 * Text Domain:       rmiap
 */

defined('ABSPATH') || exit;

if (!defined('RMIAP_PLUGIN_FILE')) {
  define('RMIAP_PLUGIN_FILE', __FILE__);
}

if(!defined('RMIAP_PLUGIN_PATH')) {
  define('RMIAP_PLUGIN_PATH', plugin_dir_path(__FILE__));
}

if(!defined('RMIAP_PLUGIN_URL')) {
  define('RMIAP_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if(!defined('RMIAP_PLUGIN_BASENAME')) {
  define('RMIAP_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

/**
 * Classe principale del Plugin.
 *
 * Carica tutte le dipendenze, definisce l'internazionalizzazione,
 * e registra gli hook per l'admin e per il frontend.
 */
final class RacematePlugin {
  private static $instance;
  private $db;
  private $mail;
  private $version;

  private function __construct() {
    $this->loadDependencies();
    add_action('plugins_loaded', array( $this, 'load_textdomain'));

    /*add_action( 'init', function () {
      add_rewrite_tag('%rmiap_checkout_confirm%', '([^/]+)');
      add_permastruct('rmiap_checkout_confirm', '/%rmiap_checkout_confirm%');
    });
    add_action( 'template_include', function ($original_template) {
      return 'hello-world.php';

        if ($query_var = get_query_var('rmiap_checkout_confirm')) {
                header("Content-Type: text/plain");
                echo 'This is my custom content!!!!';
                exit; // Don't forget the exit. If so, WordPress will continue executing the template rendering and will not fing anything, throwing the 'not found page' 
        }
    });*/
  }

  public static function getInstance() {
    if ( null === self::$instance ) {
        self::$instance = new self();
    }
    return self::$instance;
  }

  private function loadDependencies() {
    require_once RMIAP_PLUGIN_PATH . 'includes/form.php';
    //require_once RMIAP_PLUGIN_PATH . 'includes/rmiapShortcodes.php';
    require_once RMIAP_PLUGIN_PATH . 'includes/rest-api.php';
    require_once RMIAP_PLUGIN_PATH . 'includes/database.php';

    $this->db = new RmiapDatabase();

    new RmiapRestApi($this->db);
    new RmiapForm($this->db);

    if (is_admin()) {
      require_once RMIAP_PLUGIN_PATH . 'admin/admin.php';

      new RmiapAdmin($this->db);
    }
  }

  public function load_textdomain() {
    /*load_plugin_textdomain(
        'lolle-custom-plugin',
        false,
        dirname( LOLLE_CUSTOM_PLUGIN_BASENAME ) . '/languages/'
    );*/
  }

  /**
   * Codice da eseguire all'attivazione del plugin.
   * Esempio: creazione tabelle custom, flush rewrite rules.
   */
  public static function activate() {
    require_once RMIAP_PLUGIN_PATH . 'includes/activator.php';
    RmiapActivator::activate();

    // Esempio: impostare un'opzione
    //if ( get_option('rmiap_plugin_version') === false) {
    //    add_option('rmiap_plugin_version', LOLLE_CUSTOM_PLUGIN_VERSION);
    //} else {
    //    update_option('rmiap_plugin_version', LOLLE_CUSTOM_PLUGIN_VERSION);
    //}

    // Esempio: flush rewrite rules se registri Custom Post Type o Rewrite Rules
    // flush_rewrite_rules();
  }

  /**
   * Codice da eseguire alla disattivazione del plugin.
   * Esempio: rimozione di cron job, pulizia temporanea.
   */
  public static function deactivate() {
    require_once RMIAP_PLUGIN_PATH . 'includes/deactivator.php';
    RmpiaDeactivator::deactivate();
  }
}

// Registra gli hook di attivazione e disattivazione
register_activation_hook(__FILE__, array('RacematePlugin', 'activate'));
register_deactivation_hook(__FILE__, array('RacematePlugin', 'deactivate'));

function rmiapPluginRun() {
  return RacematePlugin::getInstance();
}
rmiapPluginRun();

?>
