<?php

/**
 * Plugin.
 */
final class CM_IDIN_Plugin {
	/**
	 * Version.
	 */
	private $version = '1.0.1';

	/**
	 * Instance.
	 */
	protected static $instance = null;

	/**
	 * Instance.
	 */
	public static function instance( $file = null ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $file );
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Construct.
	 */
	public function __construct( $file ) {
		$this->file   = $file;
		$this->plugin = $this;

		$this->install         = new CM_IDIN_Install( $this );
		$this->service         = new CM_IDIN_Service( $this );
		$this->scripts         = new CM_IDIN_Scripts( $this );
		$this->settings        = new CM_IDIN_Settings( $this );
		$this->form_handler    = new CM_IDIN_FormHandler( $this );
		$this->status_listener = new CM_IDIN_StatusListener( $this );
		$this->transactions    = new CM_IDIN_TransactionsDB();

		if ( is_admin() ) {
			$this->admin = new CM_IDIN_Admin( $this );
		}
	}

	/**
	 * Get the version number of this plugin.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Setup.
	 */
	public function setup() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

		add_action( 'init', array( $this, 'init' ), 5 );

		add_action( 'cm_idin_transaction_update', array( $this, 'maybe_register_user' ) );
		add_action( 'cm_idin_transaction_update', array( $this, 'maybe_login_user' ) );
		add_action( 'cm_idin_transaction_update', array( $this, 'maybe_update_user' ) );

		// Other
		$this->install->setup();
		$this->scripts->setup();
		$this->settings->setup();
		$this->form_handler->setup();
		$this->status_listener->setup();

		if ( isset( $this->admin ) ) {
			$this->admin->setup();
		}
	}

	/**
	 * Initialize.
	 */
	public function init() {
		// Text Domain
		load_plugin_textdomain( 'cm-idin', false, dirname( plugin_basename( $this->file ) ) . '/languages' );

		// Rewrite Endpoint
		add_rewrite_endpoint( 'cm-idin', EP_ROOT );

		// Tables
		global $wpdb;

		$wpdb->cm_idin_transactions = $wpdb->prefix . 'cm_idin_transactions';
	}

	/**
	 * Plugins loaded.
	 */
	public function plugins_loaded() {
		if ( function_exists( 'WC' ) ) {
			$this->woocommerce = new CM_IDIN_WooCommerce( $this );
			$this->woocommerce->setup();
		}
	}

	/**
	 * Check if registration is active.
	 *
	 * @return boolean
	 */
	public function is_registration_active() {
		return '1' === get_option( 'cm_idin_registration_active' );
	}

	/**
	 * Check if login is active.
	 *
	 * @return boolean
	 */
	public function is_login_active() {
		return '1' === get_option( 'cm_idin_login_active' );
	}

	/**
	 * Check if age verification is active.
	 *
	 * @return boolean
	 */
	public function is_age_verification_active() {
		return '1' === get_option( 'cm_idin_age_verification_active' );
	}

	/**
	 * Get default user role.
	 *
	 * @return string
	 */
	public function get_default_role() {
		$role = get_option( 'default_role' );

		$role = apply_filters( 'cm_din_default_role', $role );

		return $role;
	}

	/**
	 * Check if current user is 18 years or older.
	 *
	 * @return boolean true if current user is 18 years or older, false otherwise.
	 */
	public function is_current_user_18y_or_older() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$user = wp_get_current_user();

		$is_18y_or_older = ( 'yes' === get_user_meta( $user->ID, 'cm_idin_18y_or_older', true ) );

		return $is_18y_or_older;
	}

	/**
	 * Maybe show message.
	 */
	public function maybe_show_message() {
		if ( ! isset( $_GET['cm-idin-message'] ) ) { // WPCS: input var okay, CSRF ok.
			return;
		}

		include plugin_dir_path( $this->plugin->file ) . 'templates/message.php';

		unset( $_GET['cm-idin-message'] ); // WPCS: input var okay.
	}

	/**
	 * Age verification form.
	 */
	public function age_verification_form() {
		echo '<form method="post" action="">';

		wp_nonce_field( 'cm-din', 'cm-idin-nonce' );

		include plugin_dir_path( $this->plugin->file ) . 'templates/age-verification.php';

		echo '</form>';
	}

	/**
	 * Show register/login.
	 *
	 * @return boolean
	 */
	public function show_register_login() {
		if ( is_user_logged_in() ) {
			return false;
		}

		return $this->is_registration_active() || $this->is_login_active();
	}

	/**
	 * Register/login form.
	 */
	public function register_login_form() {
		if ( ! $this->show_register_login() ) {
			return;
		}

		$transaction = null;

		if ( filter_has_var( INPUT_GET, 'cm-idin-trxid' ) && filter_has_var( INPUT_GET, 'cm-idin-ec' ) ) {
			$transaction_id = filter_input( INPUT_GET, 'cm-idin-trxid', FILTER_SANITIZE_STRING );
			$entrance_code  = filter_input( INPUT_GET, 'cm-idin-ec', FILTER_SANITIZE_STRING );

			$transaction = $this->plugin->transactions->get_transaction_by_trxid_ec( $transaction_id, $entrance_code );
		}

		echo '<form method="post" action="">';

		if ( is_object( $transaction ) && ( 'success' === $transaction->status ) && ( 'register' === $transaction->type ) && empty( $transaction->email_address ) ) {
			wp_nonce_field( 'cm-din-register', 'cm-idin-nonce' );

			include plugin_dir_path( $this->plugin->file ) . 'templates/register-login-finish.php';
		} else {
			wp_nonce_field( 'cm-din', 'cm-idin-nonce' );

			include plugin_dir_path( $this->plugin->file ) . 'templates/register-login.php';
		}

		echo '</form>';
	}

	/**
	 * Maybe register user.
	 *
	 * @param
	 * @return
	 */
	public function maybe_register_user( $transaction ) {
		if ( 'success' !== $transaction->status ) {
			return;
		}

		if ( 'register' !== $transaction->type ) {
			return;
		}

		if ( empty( $transaction->email_address ) ) {
			$transaction->message = 'email-empty';

			return false;
		}

		$user = $this->get_user_by_bin( $transaction->bin );

		if ( is_object( $user ) ) {
			$transaction->message = 'user-bin-exists';

			return false;
		}

		$user = get_user_by( 'email', $transaction->email_address );

		if ( is_object( $user ) ) {
			$transaction->message = 'user-email-exists';

			return false;
		}

		if ( false === $user ) {
			$data = array(
				'user_login' => $transaction->email_address,
				'user_pass'  => null,
				'user_email' => $transaction->email_address,
				'first_name' => $transaction->name->first_name,
				'last_name'  => trim( $transaction->name->last_name_prefix . ' ' . $transaction->name->last_name ),
				'role'       => $this->plugin->get_default_role(),
			);

			$result = wp_insert_user( $data );

			if ( is_wp_error( $result ) ) {
				return false;
			}

			$transaction->message = 'registered';

			do_action( 'cm_idin_user_register', $result, $transaction );

			$user = get_user_by( 'id', $result );
		}

		if ( false === $user ) {
			return false;
		}

		$transaction->user_id = $user->ID;

		// Send new user notification.
		wp_new_user_notification( $user->ID, null, 'both' );

		$transaction->message = 'check-email';
	}

	/**
	 * Get user by BIN.
	 *
	 * @param string $bin
	 * @return WP_User|false
	 */
	public function get_user_by_bin( $bin ) {
		$users = get_users( array(
			'meta_key'    => 'cm_idin_bin',
			'meta_value'  => $bin,
			'number'      => 1,
			'count_total' => false,
		) );

		$user = current( $users );

		return $user;
	}

	/**
	 * Maybe login user.
	 *
	 * @param
	 * @return
	 */
	public function maybe_login_user( $transaction ) {
		if ( 'success' !== $transaction->status ) {
			return;
		}

		if ( 'login' !== $transaction->type ) {
			return;
		}

		if ( ! isset( $transaction->bin ) ) {
			return;
		}

		if ( empty( $transaction->bin ) ) {
			return;
		}

		$user = $this->get_user_by_bin( $transaction->bin );

		if ( false === $user ) {
			$transaction->message = 'user-not-found';

			return;
		}

		if ( is_object( $user ) ) {
			// Auto login
			wp_set_current_user( $user->ID, $user->user_login );

			wp_set_auth_cookie( $user->ID );

			do_action( 'wp_login', $user->user_login, $user );

			$transaction->user_id = $user->ID;
			$transaction->message = 'logged-in';
		}
	}

	public function maybe_update_user( $transaction ) {
		$user_id = null;

		if ( isset( $transaction->user_id ) ) {
			$user_id = $transaction->user_id;
		}

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$user = get_user_by( 'id', $user_id );

		if ( false === $user ) {
			return;
		}

		// Update meta
		update_user_meta( $user->ID, 'cm_idin_status', $transaction->status );

		if ( isset( $transaction->bin ) ) {
			update_user_meta( $user->ID, 'cm_idin_bin', $transaction->bin );
		}

		if ( isset( $transaction->name ) ) {
			$name = $transaction->name;

			$data = array(
				'gender'           => 'cm_idin_gender',
				'initials'         => 'cm_idin_initials',
				'first_name'       => 'cm_idin_first_name',
				'last_name'        => 'cm_idin_last_name',
				'last_name_prefix' => 'cm_idin_last_name_prefix',
			);

			foreach ( $data as $key => $meta_key ) {
				if ( isset( $name->$key ) ) {
					update_user_meta( $user->ID, $meta_key, $name->$key );
				}
			}
		}

		if ( isset( $transaction->address ) ) {
			$address = $transaction->address;

			$data = array(
				'street'              => 'cm_idin_street',
				'house_number'        => 'cm_idin_house_number',
				'house_number_suffix' => 'cm_idin_house_number_suffix',
				'postal_code'         => 'cm_idin_postal_code',
				'city'                => 'cm_idin_city',
				'country'             => 'cm_idin_country',
			);

			foreach ( $data as $key => $meta_key ) {
				if ( isset( $address->$key ) ) {
					update_user_meta( $user->ID, $meta_key, $address->$key );
				}
			}
		}

		if ( isset( $transaction->age ) ) {
			$age = $transaction->age;

			if ( isset( $age->date_of_birth ) ) {
				update_user_meta( $user->ID, 'cm_idin_date_of_birth', $age->date_of_birth );
			}

			if ( isset( $age->{'18y_or_older'} ) ) {
				update_user_meta( $user->ID, 'cm_idin_18y_or_older', $age->{'18y_or_older'} ? 'yes' : 'no' );
			}
		}

		if ( isset( $transaction->telephone_number ) ) {
			update_user_meta( $user->ID, 'cm_idin_telephone_number', $transaction->telephone_number );
		}

		if ( isset( $transaction->email_address ) ) {
			update_user_meta( $user->ID, 'cm_idin_email_address', $transaction->email_address );
		}
	}

	/**
	 * Get merchant token.
	 *
	 * @return string
	 */
	public function get_merchant_token() {
		return get_option( 'cm_idin_merchant_token' );
	}

	/**
	 * Get merchant info.
	 *
	 * @return stdClass
	 */
	public function get_merchant_info() {
		return get_option( 'cm_idin_merchant_info' );
	}

	/**
	 * Get status label of specified status.
	 *
	 * @param string $status
	 * @return string
	 */
	public function get_status_label( $status ) {
		switch ( $status ) {
			case 'success' :
				return __( 'Success', 'cm-idin' );
			case 'cancelled' :
				return __( 'Cancelled', 'cm-idin' );
			default :
				return $status;
		}
	}

	/**
	 * Get gender label of specified gender.
	 *
	 * @param string $gender
	 * @return string
	 */
	public function get_gender_label( $gender ) {
		switch ( $gender ) {
			case 'male' :
				return __( 'Male', 'cm-idin' );
			case 'female' :
				return __( 'Female', 'cm-idin' );
			default :
				return $gender;
		}
	}

	/**
	 * Get type label of specified type.
	 *
	 * @param string $gender
	 * @return string
	 */
	public function get_type_label( $type ) {
		switch ( $type ) {
			case 'register' :
				return __( 'Registration', 'cm-idin' );
			case 'login' :
				return __( 'Login', 'cm-idin' );
			case 'verify_age' :
				return __( 'Age Verification', 'cm-idin' );
			default :
				return $type;
		}
	}
}
