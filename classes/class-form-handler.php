<?php

/**
 * Form handler.
 */
class CM_IDIN_FormHandler {
	/**
	 * Construct.
	 */
	public function __construct( $plugin ) {
		$this->plugin  = $plugin;
		$this->service = $plugin->service;
	}

	/**
	 * Setup.
	 */
	public function setup() {
		add_action( 'init', array( $this, 'maybe_process_form' ) );
		add_action( 'init', array( $this, 'maybe_register_user' ) );
	}

	/**
	 * Maybe process form.
	 */
	public function maybe_process_form() {
		// Verify nonce.
		if ( ! filter_has_var( INPUT_POST, 'cm-idin-nonce' ) ) {
			return;
		}

		$nonce = filter_input( INPUT_POST, 'cm-idin-nonce' );

		if ( ! wp_verify_nonce( $nonce, 'cm-din' ) ) {
			return;
		}

		// Nonce ok.
		$sendback = add_query_arg( 'cm_idin_message', false );

		$issuer_id = filter_input( INPUT_POST, 'cm_idin_issuer', FILTER_SANITIZE_STRING );

		if ( empty( $issuer_id ) ) {
			$sendback = add_query_arg( 'cm_idin_message', '1', $sendback );

			wp_redirect( $sendback );

			exit;
		}

		$issuers = $this->service->get_issuers();

		if ( ! isset( $issuers[ $issuer_id ] ) ) {
			$sendback = add_query_arg( 'cm_idin_message', '2', $sendback );

			wp_redirect( $sendback );

			exit;
		}

		// Type
		$type = null;

		if ( filter_has_var( INPUT_POST, 'cm_idin_register' ) ) {
			$type = 'register';
		}

		if ( filter_has_var( INPUT_POST, 'cm_idin_login' ) ) {
			$type = 'login';
		}

		if ( filter_has_var( INPUT_POST, 'cm_idin_verify_age' ) ) {
			$type = 'verify_age';
		}

		$issuer_name = $issuers[ $issuer_id ];

		$data = new stdClass();
		$data->merchant_token = get_option( 'cm_idin_merchant_token' );

		// Services
		$data->identity         = false;
		$data->name             = false;
		$data->gender           = false;
		$data->address          = false;
		$data->date_of_birth    = false;
		$data->{'18y_or_older'} = false;
		$data->email_address    = false;
		$data->telephone_number = false;

		switch ( $type ) {
			case 'register' :
				$data->identity         = true;
				$data->name             = true;
				$data->address          = true;
				$data->email_address    = true;
				$data->telephone_number = true;

				break;
			case 'login' :
				$data->identity = true;

				break;
			case 'verify_age' :
				$data->{'18y_or_older'} = true;

				break;
		}

		// Other
		$data->issuer_id           = $issuer_id;
		$data->entrance_code       = wp_generate_password( 16, false );
		$data->merchant_return_url = home_url( '/cm-idin/' ); // @see https://github.com/cmdisp/idin-magento/search?utf8=%E2%9C%93&q=getFinishRegistrationUrl&type=
		$data->language            = substr( get_locale(), 0, 2 );

		$response = $this->service->request( 'POST', 'transaction', $data );

		if ( ! is_object( $response ) ) {
			return false;
		}

		$user_id = get_current_user_id();

		global $wpdb;

		$result = $wpdb->insert(
			$wpdb->cm_idin_transactions,
			array(
				'type'                      => $type,
				'transaction_id'            => $response->transaction_id,
				'merchant_reference'        => $response->merchant_reference,
				'issuer_id'                 => $issuer_id,
				'issuer_name'               => $issuer_name,
				'issuer_authentication_url' => $response->issuer_authentication_url,
				'entrance_code'             => $data->entrance_code,
				'return_url'                => $sendback,
				'user_id'                   => empty( $user_id ) ? null : $user_id,
			),
			array(
				'type'                      => '%s',
				'transaction_id'            => '%s',
				'merchant_reference'        => '%s',
				'issuer_authentication_url' => '%s',
				'issuer_id'                 => '%s',
				'issuer_name'               => '%s',
				'entrance_code'             => '%s',
				'return_url'                => '%s',
				'user_id'                   => '%d',
			)
		);

		wp_redirect( $response->issuer_authentication_url );

		exit;
	}

	public function maybe_register_user() {
		// Verify nonce.
		if ( ! filter_has_var( INPUT_POST, 'cm-idin-nonce' ) ) {
			return;
		}

		$nonce = filter_input( INPUT_POST, 'cm-idin-nonce' );

		if ( ! wp_verify_nonce( $nonce, 'cm-din-register' ) ) {
			return;
		}

		// Nonce ok.
		$email = filter_input( INPUT_POST, 'email', FILTER_SANITIZE_STRING );

		if ( ! is_email( $email ) ) {
			return;
		}

		$transaction_id = filter_input( INPUT_POST, 'cm-idin-trxid', FILTER_SANITIZE_STRING );
		$entrance_code  = filter_input( INPUT_POST, 'cm-idin-ec', FILTER_SANITIZE_STRING );

		$transaction = $this->plugin->transactions->get_transaction_by_trxid_ec( $transaction_id, $entrance_code );

		if ( empty( $transaction ) ) {
			return;
		}

		$transaction->email_address = $email;

		$this->plugin->maybe_register_user( $transaction );

		$url = add_query_arg( array(
			'cm-idin-message' => $transaction->message,
		), $transaction->return_url );

		wp_redirect( $url );

		exit;
	}
}
