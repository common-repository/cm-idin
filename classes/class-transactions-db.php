<?php

/**
 * Transaction.
 */
class CM_IDIN_TransactionsDB {
	public function count_transactions() {
		global $wpdb;

		return $wpdb->get_var( "SELECT COUNT( id ) FROM $wpdb->cm_idin_transactions;" );
	}

	public function get_transaction_by_id( $id ) {
		global $wpdb;

		$result = $wpdb->get_row( $wpdb->prepare( "
			SELECT *
			FROM $wpdb->cm_idin_transactions
			WHERE id = %d
			LIMIT 1
			;",
			$id
		) );

		if ( is_object( $result ) ) {
			return new CM_IDIN_Transaction( $result );
		}
	}

	public function get_transaction_by_trxid_ec( $id, $ec ) {
		global $wpdb;

		$result = $wpdb->get_row( $wpdb->prepare( "
			SELECT
				*
			FROM
				$wpdb->cm_idin_transactions
			WHERE
				transaction_id = %s
					AND
				entrance_code = %s
			LIMIT
				1
			;
		", $id, $ec ) );

		if ( is_object( $result ) ) {
			return new CM_IDIN_Transaction( $result );
		}
	}

	public function get_transactions( $args = array() ) {
		global $wpdb;

		$args = wp_parse_args( $args, array(
			'per_page' => 20,
			'page'     => 1,
		) );

		$per_page = $args['per_page'];
		$page     = $args['page'];

		$results = $wpdb->get_results( $wpdb->prepare( "
			SELECT transaction.*, user.display_name
			FROM $wpdb->cm_idin_transactions AS transaction
			LEFT JOIN $wpdb->users AS user ON transaction.user_id = user.ID
			ORDER BY created DESC
			LIMIT %d OFFSET %d
			;",
			$per_page,
			( $page - 1 ) * $per_page
		) );

		$transactions = array();

		if ( is_array( $results ) ) {
			foreach ( $results as $data ) {
				$transactions[] = new CM_IDIN_Transaction( $data );
			}
		}

		return $transactions;
	}
}
