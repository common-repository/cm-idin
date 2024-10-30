<?php

/**
 * Settings.
 */
class CM_IDIN_Settings {
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
		add_action( 'init', array( $this, 'init' ) );

		// @see https://github.com/WordPress/WordPress/blob/4.8/wp-includes/option.php#L273-L285
		$option = 'cm_idin_merchant_token';
		add_action( 'pre_update_option_' . $option, array( $this, 'update_merchant_info' ) );

		$option = 'cm_idin_registration_active';
		add_action( 'pre_update_option_' . $option, array( $this, 'require_service_email' ) );

		$option = 'cm_idin_login_active';
		add_action( 'pre_update_option_' . $option, array( $this, 'require_service_email' ) );

		$option = 'cm_idin_age_verification_active';
		add_action( 'pre_update_option_' . $option, array( $this, 'require_service_18y_or_older' ) );
	}

	/**
	 * Initialize.
	 */
	public function init() {
		register_setting( 'cm-idin', 'cm_idin_mode', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		register_setting( 'cm-idin', 'cm_idin_merchant_token', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		register_setting( 'cm-idin', 'cm_idin_registration_active', array(
			'type'    => 'boolean',
			'default' => false,
		) );

		register_setting( 'cm-idin', 'cm_idin_login_active', array(
			'type'    => 'boolean',
			'default' => false,
		) );

		register_setting( 'cm-idin', 'cm_idin_age_verification_active', array(
			'type'    => 'boolean',
			'default' => false,
		) );

		register_setting( 'cm-idin', 'cm_idin_age_verification_required', array(
			'type'    => 'string',
			'default' => 'always',
		) );

		register_setting( 'cm-idin', 'cm_idin_age_show_product_notice', array(
			'type'    => 'boolean',
			'default' => false,
		) );

		register_setting( 'cm-idin', 'cm_idin_age_product_notice', array(
			'type'    => 'string',
			'default' => __( 'To order this product you must be 18 years or older.', 'cm-idin' ),
		) );

		register_setting( 'cm-idin', 'cm_idin_age_show_cart_notice', array(
			'type'    => 'boolean',
			'default' => false,
		) );

		register_setting( 'cm-idin', 'cm_idin_age_cart_notice', array(
			'type'    => 'string',
			'default' => __( 'You must be 18 years or older to order the products listed below.', 'cm-idin' ),
		) );
	}

	/**
	 * Update merchant token.
	 *
	 * @param string $value
	 * @return string
	 */
	public function update_merchant_info( $value ) {
		$merchant_info = $this->plugin->service->get_merchant_info( $value );

		update_option( 'cm_idin_merchant_info', $merchant_info );

		if ( is_object( $merchant_info ) ) {
			// @see https://github.com/cmdisp/idin-magento/blob/master/app/code/community/CMGroep/Idin/Model/Adminhtml/Observer.php#L75-L88
			if ( false === $merchant_info->services->email_address ) {
				update_option( 'cm_idin_registration_active', 0 );
				update_option( 'cm_idin_login_active', 0 );
			}

			if ( false === $merchant_info->services->{'18y_or_older'} ) {
				update_option( 'cm_idin_age_verification_active', 0 );
			}
		}

		return $value;
	}

	/**
	 * Require service email.
	 *
	 * @param string $value
	 * @return string
	 */
	public function require_service_email( $value ) {
		$merchant_info = $this->plugin->get_merchant_info();

		if ( is_object( $merchant_info ) && false === $merchant_info->services->email_address ) {
			return false;
		}

		return $value;
	}

	/**
	 * Require service 18y or older.
	 *
	 * @param string $value
	 * @return string
	 */
	public function require_service_18y_or_older( $value ) {
		$merchant_info = $this->plugin->get_merchant_info();

		if ( is_object( $merchant_info ) && false === $merchant_info->services->{'18y_or_older'} ) {
			return false;
		}

		return $value;
	}
}
