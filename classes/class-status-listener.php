<?php

/**
 * Status listener.
 */
class CM_IDIN_StatusListener {
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
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
	}

	/**
	 * Template redirect.
	 */
	public function template_redirect() {
		if ( null === get_query_var( 'cm-idin', null ) ) {
			return;
		}

		if ( ! isset( $_GET['trxid'], $_GET['ec'] ) ) { // WPCS: CSRF ok, input var okay.
			return;
		}

		$transaction_id = sanitize_text_field( wp_unslash( $_GET['trxid'] ) ); // WPCS: CSRF ok, input var okay.
		$entrance_code  = sanitize_text_field( wp_unslash( $_GET['ec'] ) ); // WPCS: CSRF ok, input var okay.

		global $wpdb;

		$transaction = $this->plugin->transactions->get_transaction_by_trxid_ec( $transaction_id, $entrance_code );

		if ( empty( $transaction ) ) {
			return;
		}

		$response = $this->service->get_status( $transaction->transaction_id, $transaction->merchant_reference );

		if ( false === $response ) {
			return;
		}

		$transaction->status = $response->status;
		$transaction->status_response = $response;
		$transaction->message = false;

		if ( 'cancelled' === $transaction->status ) {
			$transaction->message = 'cancelled';
		}

		// Status can only be requisted once, if requested a second time there is only a status property in the response object.
		if ( count( get_object_vars( $response ) ) > 1 ) {
			$fields = array(
				'bin',
				'name',
				'address',
				'age',
				'telephone_number',
				'email_address',
			);

			foreach ( $fields as $key ) {
				if ( isset( $response->$key ) ) {
					$transaction->$key = $response->$key;
				}
			}

			if ( 'verify_age' === $transaction->type ) {
				if ( isset( $transaction, $transaction->age, $transaction->age->{'18y_or_older'} ) && $transaction->age->{'18y_or_older'} ) {
					$transaction->message = 'age-verified';
				} else {
					$transaction->message = 'age-not-verified';
				}
			}

			do_action( 'cm_idin_transaction_update', $transaction );

			$wpdb->update(
				$wpdb->cm_idin_transactions,
				array(
					'status'          => $transaction->status,
					'status_response' => wp_json_encode( $response ),
					'user_id'         => $transaction->user_id,
				),
				array(
					'id' => $transaction->id,
				),
				array(
					'status'          => '%s',
					'status_response' => '%s',
					'user_id'         => '%d',
				),
				array(
					'id' => '%d',
				)
			);
		}

		$url = add_query_arg( array(
			'cm-idin-trxid'   => $transaction->transaction_id,
			'cm-idin-ec'      => $transaction->entrance_code,
			'cm-idin-status'  => $transaction->status,
			'cm-idin-message' => $transaction->message,
		), $transaction->return_url );

		wp_redirect( $url );

		exit;
	}
}
