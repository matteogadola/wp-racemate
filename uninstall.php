<?php
/**
 * Script di disinstallazione per Lolle Custom Plugin.
 *
 * Questo script viene eseguito quando il plugin viene eliminato tramite
 * l'interfaccia di amministrazione di WordPress. Non viene eseguito alla disattivazione.
 *
 * @package Lolle_Custom_Plugin
 */

// Se uninstall non è chiamato da WordPress, esci.
if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit;
}

// Verifica che l'utente che sta disinstallando abbia i permessi necessari.
if (!current_user_can('activate_plugins')) {
  return;
}

// Qui inserisci la logica di pulizia:
// Esempio: Eliminare opzioni del plugin dalla tabella wp_options
// delete_option( 'lolle_custom_plugin_settings' );
// delete_option( 'lolle_custom_plugin_version' );

// Esempio: Eliminare tabelle custom del database (USARE CON ESTREMA CAUTELA!)
/*
global $wpdb;
$table_name_one = $wpdb->prefix . 'lolle_custom_table_one';
$table_name_two = $wpdb->prefix . 'lolle_custom_table_two';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name_one}" );
$wpdb->query( "DROP TABLE IF EXISTS {$table_name_two}" );
*/

// Esempio: Rimuovere CPT e tassonomie (solitamente non necessario se il codice che li registra non è più attivo)
// Tuttavia, potresti voler pulire i post di quel CPT.
/*
$lolle_posts = get_posts( array( 'post_type' => 'lolle_cpt', 'numberposts' => -1 ) );
foreach ( $lolle_posts as $lolle_post ) {
    wp_delete_post( $lolle_post->ID, true ); // true per forzare la cancellazione bypassando il cestino
}
*/

// Esempio: Rimuovere ruoli utente custom (se il plugin li ha creati)
// remove_role( 'lolle_custom_role' );

// Pulire i cron job schedulati
// $timestamp = wp_next_scheduled( 'lolle_custom_cron_hook' );
// if ( $timestamp ) {
//     wp_unschedule_event( $timestamp, 'lolle_custom_cron_hook' );
// }

// Nota: Sii MOLTO cauto con le operazioni di cancellazione, specialmente DROP TABLE.
// Spesso gli utenti preferiscono che i dati vengano conservati.
// Potresti offrire un'opzione nel plugin per "Rimuovere tutti i dati alla disinstallazione".
