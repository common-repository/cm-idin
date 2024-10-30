<?php

/**
 * Install.
 */
class CM_IDIN_Install {
	/**
	 * Construct.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Setup.
	 */
	public function setup() {
		add_action( 'init', array( $this, 'maybe_install' ) );
	}

	/**
	 * Initialize.
	 */
	public function maybe_install() {
		if ( get_option( 'cm_idin_version' ) !== $this->plugin->get_version() ) {
			$this->install();
		}
	}

	/**
	 * Install.
	 */
	public function install() {
		global $wpdb;

		// Tables
		$this->install_table( 'cm_idin_transactions', '
			id BIGINT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
			created TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
			type VARCHAR(16) DEFAULT NULL,
			transaction_id VARCHAR(16) DEFAULT NULL,
			issuer_id VARCHAR(8) DEFAULT NULL,
			issuer_name VARCHAR(64) DEFAULT NULL,
			issuer_authentication_url VARCHAR(512) DEFAULT NULL,
			merchant_reference VARCHAR(64) DEFAULT NULL,
			entrance_code VARCHAR(40) DEFAULT NULL,
			return_url VARCHAR(512) DEFAULT NULL,
			status VARCHAR(16) DEFAULT NULL,
			status_response TEXT DEFAULT NULL,
			user_id BIGINT(20) UNSIGNED DEFAULT NULL,
			PRIMARY KEY  (id)
		' );

		maybe_convert_table_to_utf8mb4( $wpdb->cm_idin_transactions );

		// Rwrite rules
		flush_rewrite_rules();

		// Update version
		update_option( 'cm_idin_version', $this->plugin->get_version() );
	}

	/**
	 * Install table.
	 *
	 * @param string $name
	 * @param string $columns
	 */
	private function install_table( $name, $columns ) {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$full_table_name = $wpdb->$name;

		$charset_collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}

			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}
		}

		$table_options = $charset_collate;

		dbDelta( "CREATE TABLE $full_table_name ( $columns ) $table_options" );
	}
}
