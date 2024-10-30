<?php

/**
 * Transaction.
 */
class CM_IDIN_Transaction {
	public $transaction_id;
	public $entrance_code;
	public $status;
	public $message;

	/**
	 * Construct.
	 */
	public function __construct( $data ) {
		foreach ( $data as $key => $value ) {
			$this->$key = $value;
		}

		if ( isset( $this->status_response ) ) {
			$extra = json_decode( $this->status_response );

			foreach ( $extra as $key => $value ) {
				$this->$key = $value;
			}
		}
	}

	public function get_first_name() {
		if ( isset( $this->name, $this->name->first_name ) ) {
			return $this->name->first_name;
		}
	}

	public function get_last_name() {
		$parts = array();

		if ( isset( $this->name ) ) {
			if ( isset( $this->name->last_name_prefix ) ) {
				$parts[] = $this->name->last_name_prefix;
			}

			if ( isset( $this->name->last_name ) ) {
				$parts[] = $this->name->last_name;
			}
		}

		return trim( implode( ' ', $parts ) );
	}

	public function get_name() {
		$parts = array();

		if ( isset( $this->name ) ) {
			$name = $this->name;

			if ( isset( $name->first_name ) ) {
				$parts[] = $name->first_name;
			}

			if ( isset( $name->last_name_prefix ) ) {
				$parts[] = $name->last_name_prefix;
			}

			if ( isset( $name->last_name ) ) {
				$parts[] = $name->last_name;
			}
		}

		return trim( implode( ' ', $parts ) );
	}
}
