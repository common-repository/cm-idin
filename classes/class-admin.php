<?php

/**
 * Admin.
 */
class CM_IDIN_Admin {
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
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'show_user_profile', array( $this, 'user_profile' ) );
		add_action( 'edit_user_profile', array( $this, 'user_profile' ) );
	}

	/**
	 * Admin initialize.
	 */
	public function admin_init() {
		// Settings - General
		add_settings_section(
			'cm-idin-general',
			__( 'General', 'cm-idin' ),
			'__return_false',
			'cm-idin'
		);

		add_settings_field(
			'cm_idin_mode',
			__( 'Mode', 'cm-idin' ),
			array( $this, 'form_control_select' ),
			'cm-idin',
			'cm-idin-general', // section
			array(
				'label_for' => 'cm_idin_mode',
				'options'   => array(
					'test' => __( 'Test', 'cm-idin' ),
					'live' => __( 'Live', 'cm-idin' ),
				),
			)
		);

		add_settings_field(
			'cm_idin_merchant_token',
			__( 'Merchant Token', 'cm-idin' ),
			array( $this, 'form_control_merchant_token' ),
			'cm-idin',
			'cm-idin-general', // section
			array(
				'label_for' => 'cm_idin_merchant_token',
			)
		);

		// Settings - Registration with iDIN
		add_settings_section(
			'cm-idin-registration',
			__( 'Registration with iDIN', 'cm-idin' ),
			'__return_false',
			'cm-idin'
		);

		add_settings_field(
			'cm_idin_registration_active',
			__( 'Active', 'cm-idin' ),
			array( $this, 'form_control_select' ),
			'cm-idin',
			'cm-idin-registration', // section
			array(
				'label_for'         => 'cm_idin_registration_active',
				'options'           => array(
					'0' => __( 'No', 'cm-idin' ),
					'1' => __( 'Yes', 'cm-idin' ),
				),
				'required_services' => array(
					'email_address',
				),
			)
		);

		// Settings - Login with iDIN
		add_settings_section(
			'cm-idin-login',
			__( 'Login with iDIN', 'cm-idin' ),
			'__return_false',
			'cm-idin'
		);

		add_settings_field(
			'cm_idin_login_active',
			__( 'Active', 'cm-idin' ),
			array( $this, 'form_control_select' ),
			'cm-idin',
			'cm-idin-login',
			array(
				'label_for'         => 'cm_idin_login_active',
				'options'           => array(
					'0' => __( 'No', 'cm-idin' ),
					'1' => __( 'Yes', 'cm-idin' ),
				),
				'required_services' => array(
					'email_address',
				),
			)
		);

		// Settings - 18+ Age Verififcation with iDIN
		add_settings_section(
			'cm-idin-age-verification',
			__( '18+ Age Verification with iDIN', 'cm-idin' ),
			'__return_false',
			'cm-idin'
		);

		add_settings_field(
			'cm_idin_age_verification_active',
			__( 'Active', 'cm-idin' ),
			array( $this, 'form_control_select' ),
			'cm-idin',
			'cm-idin-age-verification',
			array(
				'label_for'         => 'cm_idin_age_verification_active',
				'options'           => array(
					'0' => __( 'No', 'cm-idin' ),
					'1' => __( 'Yes', 'cm-idin' ),
				),
				'required_services' => array(
					'18y_or_older',
				),
			)
		);

		// @see https://github.com/cmdisp/idin-magento/blob/master/app/code/community/CMGroep/Idin/Model/System/Config/Source/Verificationrequired.php
		// @see https://github.com/cmdisp/idin-magento/blob/master/app/code/community/CMGroep/Idin/etc/system.xml#L170-L182
		add_settings_field(
			'cm_idin_age_verification_required',
			__( 'Age Verification Required', 'cm-idin' ),
			array( $this, 'form_control_select' ),
			'cm-idin',
			'cm-idin-age-verification',
			array(
				'label_for'         => 'cm_idin_age_verification_required',
				'options'           => array(
					'always'               => __( 'Always', 'cm-idin' ),
					'only_18plus_products' => __( 'Only 18+ products', 'cm-idin' ),
				),
				'required_services' => array(
					'18y_or_older',
				),
			)
		);

		add_settings_field(
			'cm_idin_age_show_product_notice',
			__( 'Show notice on product page', 'cm-idin' ),
			array( $this, 'form_control_select' ),
			'cm-idin',
			'cm-idin-age-verification',
			array(
				'label_for'         => 'cm_idin_age_show_product_notice',
				'options'           => array(
					'0' => __( 'No', 'cm-idin' ),
					'1' => __( 'Yes', 'cm-idin' ),
				),
				'required_services' => array(
					'18y_or_older',
				),
			)
		);

		add_settings_field(
			'cm_idin_age_product_notice',
			__( 'Product page notice', 'cm-idin' ),
			array( $this, 'form_control_textarea' ),
			'cm-idin',
			'cm-idin-age-verification',
			array(
				'label_for'         => 'cm_idin_age_product_notice',
				'required_services' => array(
					'18y_or_older',
				),
			)
		);

		add_settings_field(
			'cm_idin_age_show_cart_notice',
			__( 'Show shopping cart notice', 'cm-idin' ),
			array( $this, 'form_control_select' ),
			'cm-idin',
			'cm-idin-age-verification',
			array(
				'label_for'         => 'cm_idin_age_show_cart_notice',
				'options'           => array(
					'0' => __( 'No', 'cm-idin' ),
					'1' => __( 'Yes', 'cm-idin' ),
				),
				'required_services' => array(
					'18y_or_older',
				),
			)
		);

		add_settings_field(
			'cm_idin_age_cart_notice',
			__( 'Shopping cart notice', 'cm-idin' ),
			array( $this, 'form_control_textarea' ),
			'cm-idin',
			'cm-idin-age-verification',
			array(
				'label_for'         => 'cm_idin_age_cart_notice',
				'required_services' => array(
					'18y_or_older',
				),
			)
		);
	}

	/**
	 * Form control merchant token.
	 *
	 * @param array $args
	 */
	public function form_control_merchant_token( $args ) {
		$this->form_control_input( $args );

		$merchant_token = $this->plugin->get_merchant_token();

		if ( empty( $merchant_token ) ) {
			return;
		}

		$merchant_info = $this->plugin->get_merchant_info();

		$icon = false !== $merchant_info ? 'yes' : 'no';

		printf( '<span class="dashicons dashicons-%s" style="vertical-align: text-bottom;"></span>', esc_attr( $icon ) );

		if ( is_object( $merchant_info ) ) {
			printf(
				'<p>%s (%s)</p>',
				esc_html( $merchant_info->name ),
				esc_html( $merchant_info->status )
			);
		}
	}

	/**
	 * Input text.
	 */
	public function form_control_input( $args ) {
		$defaults = array(
			'label_for' => false,
			'type'      => 'text',
			'classes'   => 'regular-text',
		);

		$args = wp_parse_args( $args, $defaults );

		printf(
			'<input name="%s" id="%s" type="%s" value="%s" class="%s" />',
			esc_attr( $args['label_for'] ),
			esc_attr( $args['label_for'] ),
			esc_attr( $args['type'] ),
			esc_attr( get_option( $args['label_for'] ) ),
			esc_attr( $args['classes'] )
		);
	}

	/**
	 * Input text.
	 */
	public function form_control_textarea( $args ) {
		$defaults = array(
			'label_for' => false,
			'classes'   => 'regular-text',
		);

		$args = wp_parse_args( $args, $defaults );

		printf(
			'<textarea name="%s" id="%s" class="%s" cols="60" rows="3">%s</textarea>',
			esc_attr( $args['label_for'] ),
			esc_attr( $args['label_for'] ),
			esc_attr( $args['classes'] ),
			esc_textarea( get_option( $args['label_for'] ) )
		);
	}

	private function is_enabled( $services ) {
		$merchant_info = $this->plugin->get_merchant_info();

		if ( ! is_object( $merchant_info ) ) {
			return false;
		}

		foreach ( $services as $service ) {
			if ( false === $merchant_info->services->$service ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Input dropdown.
	 */
	public function form_control_select( $args ) {
		$defaults = array(
			'label_for'         => false,
			'options'           => array(),
			'required_services' => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$name    = $args['label_for'];
		$current = get_option( $name );

		printf(
			'<select name="%s" id="%s" %s>',
			esc_attr( $name ),
			esc_attr( $name ),
			disabled( $this->is_enabled( $args['required_services'] ), false, false )
		);

		foreach ( $args['options'] as $value => $label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $value ),
				selected( $value, $current, false ),
				esc_html( $label )
			);
		}

		echo '</select>';
	}

	/**
	 * Admin menu.
	 */
	public function admin_menu() {
		add_menu_page(
			__( 'CM iDIN', 'cm-idin' ),
			__( 'CM iDIN', 'cm-idin' ),
			'manage_options',
			'cm-idin',
			array( $this, 'page_dashboard' ),
			'data:image/svg+xml;base64,' . base64_encode( file_get_contents( plugin_dir_path( $this->plugin->file ) . 'images/idin-icon-menu.svg' ) )
		);

		add_submenu_page(
			'cm-idin',
			__( 'CM iDIN Transactions', 'cm-idin' ),
			__( 'Transactions', 'cm-idin' ),
			'manage_options',
			'cm-idin-transactions',
			array( $this, 'page_transactions' )
		);

		add_submenu_page(
			'cm-idin',
			__( 'CM iDIN Settings', 'cm-idin' ),
			__( 'Settings', 'cm-idin' ),
			'manage_options',
			'cm-idin-settings',
			array( $this, 'page_options' )
		);

		add_options_page(
			__( 'CM iDIN', 'cm-idin' ),
			__( 'CM iDIN', 'cm-idin' ),
			'manage_options',
			'cm-idin-options',
			array( $this, 'page_options' )
		);

		global $submenu;

		if ( isset( $submenu['cm-idin'] ) ) {
			$submenu['cm-idin'][0][0] = __( 'Dashboard', 'cm-idin' ); // WPCS: override ok.
		}
	}

	public function page_dashboard() {
		include plugin_dir_path( $this->plugin->file ) . 'admin/page-dashboard.php';
	}

	public function page_transactions() {
		$id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_STRING );

		if ( ! empty( $id ) ) {
			return $this->page_transaction( $id );
		}

		$transactions_list_table = new CM_IDIN_TransactionsListTable();

		$transactions_list_table->prepare_items();

		include plugin_dir_path( $this->plugin->file ) . 'admin/page-transactions.php';
	}

	public function page_transaction( $id ) {
		global $wpdb;

		$transaction = $this->plugin->transactions->get_transaction_by_id( $id );

		if ( empty( $transaction ) ) {
			return;
		}

		include plugin_dir_path( $this->plugin->file ) . 'admin/page-transaction.php';
	}

	public function page_options() {
		include plugin_dir_path( $this->plugin->file ) . 'admin/page-settings.php';
	}

	/**
	 * User profile.
	 *
	 * @since 1.1.6
	 * @see https://github.com/WordPress/WordPress/blob/4.5.2/wp-admin/user-edit.php#L578-L600
	 */
	public function user_profile( $user ) {
		include plugin_dir_path( $this->plugin->file ) . 'admin/user-profile.php';
	}
}
