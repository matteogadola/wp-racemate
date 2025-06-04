<?php

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

Class RacemateActivator {

  public static function activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $prefix = $wpdb->prefix . 'racemate';

    /*$sqlAccounts = "CREATE TABLE {$prefix}_accounts (
      id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
      slug VARCHAR(100) UNIQUE NOT NULL,
      name VARCHAR(50) UNIQUE NOT NULL,
      legal_name VARCHAR(100),
      stripe_account_id VARCHAR(50),
      stripe_secret_key VARCHAR(120),
      stripe_publishable_key VARCHAR(120),
      stripe_webhook_key VARCHAR(120),
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $sqlRaces = "CREATE TABLE {$prefix}_races (
      id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
      slug VARCHAR(100) UNIQUE NOT NULL,
      name VARCHAR(100) UNIQUE NOT NULL,
      date DATETIME NOT NULL,
      status VARCHAR(10) DEFAULT 'internal' NOT NULL,
      price SMALLINT UNSIGNED NOT NULL,
      capacity SMALLINT UNSIGNED,
      start_sale_date DATETIME,
      end_sale_date DATETIME,
      payment_methods VARCHAR(20) DEFAULT 'stripe,cash',
      account_id SMALLINT UNSIGNED,
      PRIMARY KEY  (id),
      FOREIGN KEY (account_id) REFERENCES {$prefix}_accounts(id)
    ) $charset_collate;";

    $sqlEntries = "CREATE TABLE {$prefix}_entries (
      id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
      race_id SMALLINT UNSIGNED NOT NULL,
      first_name VARCHAR(50) NOT NULL,
      last_name VARCHAR(50) NOT NULL,
      birth_date DATE,
      birth_year SMALLINT UNSIGNED NOT NULL,
      gender CHAR(1) NOT NULL,
      country VARCHAR(3),
      club VARCHAR(80),
      tin VARCHAR(20),
      fidal_card VARCHAR(20),
      email VARCHAR(50) NOT NULL,
      phone_number VARCHAR(20),
      notification_date DATETIME,
      notification_status VARCHAR(10),
      payment_id VARCHAR(50),
      payment_date DATETIME,
      payment_method VARCHAR(10),
      payment_status VARCHAR(10),
      amount SMALLINT UNSIGNED NOT NULL,
      items JSON NOT NULL,
      PRIMARY KEY  (id),
      FOREIGN KEY (race_id) REFERENCES {$prefix}_races(id)
    ) $charset_collate;";*/


    /*add_rewrite_tag('%rmiap_checkout_confirm%', '([^/]+)');
    add_permastruct('rmiap_checkout_confirm', '/%rmiap_checkout_confirm%');
    flush_rewrite_rules();*/
    
    $sql = "CREATE OR REPLACE VIEW {$prefix}_entries_view AS
    SELECT *
    FROM {$prefix}_entries
    WHERE payment_status = 'paid' OR (payment_method IN ('cash', 'sepa') AND payment_status = 'pending') $charset_collate;";

    // unique constraints (race_id, tin) SOLO dove payment_status = 'paid'
    // SI....rivedi...

    //dbDelta($sqlAccounts);
    //dbDelta($sqlRaces);
    //dbDelta($sqlEntries);
    dbDelta($sql);
  }
}
