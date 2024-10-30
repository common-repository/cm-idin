<?php

/**
 * Service.
 */
class CM_IDIN_Service {
	/**
	 * Construct.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Get service URL.
	 *
	 * @return string
	 */
	private function get_url() {
		if ( 'live' === get_option( 'cm_idin_mode' ) ) {
			return 'https://idin.cmtelecom.com/idin/v1.0';
		}

		return 'https://idin.cmtelecom.com/idin/v1.0/test';
	}

	/**
	 * Request the specified method on endpoint with data.
	 *
	 * @param string $method
	 * @param string $endpoint
	 * @param mixed $data
	 * @return mixed
	 */
	public function request( $method, $endpoint, $data = null ) {
		// @see https://docs.cmtelecom.com/idin/v1.0.0#/directory%7Cpost
		$url = $this->get_url() . '/' . $endpoint;

		$args = array(
			'method'  => $method,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
			),
		);

		if ( null !== $data ) {
			$args['body'] = wp_json_encode( $data );
		}

		$response = wp_remote_request( $url, $args );

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		$data = json_decode( $body );

		if ( empty( $data ) ) {
			return false;
		}

		return $data;
	}

	/**
	 * Get status of the specified transaction.
	 *
	 * @param string $transaction_id
	 * @param string $merchant_reference
	 * @return
	 */
	public function get_status( $transaction_id, $merchant_reference ) {
		$data = new stdClass();
		$data->merchant_token     = $this->plugin->get_merchant_token();
		$data->transaction_id     = $transaction_id;
		$data->merchant_reference = $merchant_reference;

		$response = $this->request( 'POST', 'status', $data );

		if ( ! is_object( $response ) ) {
			return false;
		}

		if ( defined( 'CM_IDIN_SIMULATE_STATUS' ) ) {
			$response->status = CM_IDIN_SIMULATE_STATUS;
		}

		if ( defined( 'CM_IDIN_SIMULATE_BIN' ) ) {
			$response->bin = CM_IDIN_SIMULATE_BIN;
		}

		if ( defined( 'CM_IDIN_SIMULATE_EMAIL' ) ) {
			$response->email_address = CM_IDIN_SIMULATE_EMAIL;
		}

		return $response;
	}

	/**
	 * Get directory.
	 *
	 * @return array
	 */
	public function get_directory() {
		$data = new stdClass();
		$data->merchant_token = $this->plugin->get_merchant_token();

		$response = $this->request( 'POST', 'directory', $data );

		if ( ! is_array( $response ) ) {
			return false;
		}

		return $response;
	}

	/**
	 * Get issuers.
	 *
	 * @return array
	 */
	public function get_issuers() {
		$directory = $this->get_directory();

		if ( ! is_array( $directory ) ) {
			return false;
		}

		$issuers = array();

		foreach ( $directory as $item ) {
			foreach ( $item->issuers as $issuer ) {
				$issuers[ $issuer->issuer_id ] = $issuer->issuer_name;
			}
		}

		return $issuers;
	}

	/**
	 * Get merchant info.
	 *
	 * @param string $merchant_token
	 * @return
	 */
	public function get_merchant_info( $merchant_token ) {
		$response = $this->request( 'GET', 'merchants/' . $merchant_token );

		if ( is_object( $response ) ) {
			if ( defined( 'CM_IDIN_SIMULATE_SERVICES' ) ) {
				$response->services = (object) CM_IDIN_SIMULATE_SERVICES;
			}
		}

		return $response;
	}
}
