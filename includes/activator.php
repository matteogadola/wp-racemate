<?php

//require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

class RmiapActivator {

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
      notification_service VARCHAR(50),
      notification_template VARCHAR(50),
      notification_apikey VARCHAR(150),
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

    
CREATE TABLE IF NOT EXISTS events (
  id uuid primary key,
  name text not null,
  slug text not null,
  date timestamp not null,
  type text not null,
  status text not null default 'internal',
  summary text,
  summary_image text,
  description text,
  flyer text,
  capacity smallint,
  details jsonb,
  regulation text
);

CREATE TABLE IF NOT EXISTS products (
  id uuid primary key,
  name text not null,
  slug text not null,
  type text not null,
  status text not null default 'internal',
  price smallint not null,
  stock smallint,
  summary text,
  description text,
  start_sale_date timestamp,
  end_sale_date timestamp,
  entry_form text,
  payment_methods json
);

CREATE TABLE IF NOT EXISTS events_products (
  event_id uuid references events(id) ON DELETE CASCADE,
  product_id uuid references products(id) ON DELETE CASCADE,
  primary key (event_id, product_id)
);




    $sqlRaces = "CREATE TABLE {$prefix}_events (
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
      notification_id VARCHAR(50),
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

    $sql = "CREATE OR REPLACE VIEW {$prefix}_entries_ext AS
    SELECT E.*, R.name AS race_name, R.account_id
    FROM {$prefix}_entries AS E
    INNER JOIN {$prefix}_races AS R ON E.race_id = R.id;";

    $sql2 = "CREATE OR REPLACE VIEW {$prefix}_entries_view AS
    SELECT E.*, R.name AS race_name, R.account_id
    FROM {$prefix}_entries AS E
    INNER JOIN {$prefix}_races AS R ON E.race_id = R.id
    WHERE payment_status = 'paid' OR (payment_method IN ('cash', 'sepa') AND payment_status = 'pending')
    ORDER BY race_id, last_name, first_name;";

    $sql3 = "TRUNCATE {$prefix}_entries;";
    $sql4="ALTER TABLE {$prefix}_entries AUTO_INCREMENT=995;";
    //dbDelta($sqlAccounts);
    //dbDelta($sqlRaces);
    //dbDelta($sqlEntries);
    try {
      //$wpdb->query($sql3);
      //$wpdb->query($sql4);
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
  }
}
