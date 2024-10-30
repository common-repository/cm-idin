<?php

class CM_IDIN_TransactionsListTable extends WP_List_Table {
	/**
	 * Get the columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'date'           => __( 'Date', 'cm-idin' ),
			'transaction_id' => __( 'Transaction ID', 'cm-idin' ),
			'type'           => __( 'Type', 'cm-idin' ),
			'issuer'         => __( 'Issuer', 'cm-idin' ),
			'status'         => __( 'Status', 'cm-idin' ),
			'name'           => __( 'Name', 'cm-idin' ),
			'address'        => __( 'Address', 'cm-idin' ),
			'age'            => __( 'Age', 'cm-idin' ),
			'other'          => __( 'Other', 'cm-idin' ),
			'user'           => __( 'User', 'cm-idin' ),
		);
	}

	/**
	 * Column default.
	 *
	 * @param stdClass $item
	 * @param string $column_name
	 */
	protected function column_default( $item, $column_name ) {
		$plugin = cm_idin_plugin();

		$status_response = json_decode( $item->status_response );

		switch ( $column_name ) {
			case 'date' :
				echo esc_html( mysql2date( __( 'D j F Y H:i:s', 'cm-idin' ), $item->created ) );

				break;
			case 'type' :
				echo esc_html( $plugin->get_type_label( $item->type ) );

				break;
			case 'transaction_id' :
				$url = add_query_arg( array(
					'page' => 'cm-idin-transactions',
					'id'   => $item->id,
				), admin_url( 'admin.php' ) );

				printf(
					'<a href="%s">%s</a>',
					esc_url( $url ),
					esc_html( $item->transaction_id )
				);

				break;
			case 'issuer' :
				echo esc_html( $item->issuer_name );

				break;
			case 'status' :
				echo esc_html( $plugin->get_status_label( $item->status ) );

				break;
			case 'name' :
				echo esc_html( $item->get_name() );

				break;
			case 'address' :
				if ( is_object( $status_response ) ) {
					$parts = array();

					if ( isset( $status_response->address ) ) {
						$address = $status_response->address;

						if ( isset( $address->street ) ) {
							$parts[] = $address->street;
						}

						if ( isset( $address->house_number ) ) {
							$parts[] = $address->house_number;
						}

						if ( isset( $address->house_number_suffix ) ) {
							$parts[] = $address->house_number_suffix;
						}
					}

					echo esc_html( trim( implode( ' ', $parts ) ) );

					echo '<br />';

					$parts = array();

					if ( isset( $status_response->address ) ) {
						$address = $status_response->address;

						if ( isset( $address->postal_code ) ) {
							$parts[] = $address->postal_code;
						}

						if ( isset( $address->city ) ) {
							$parts[] = $address->city;
						}
					}

					echo esc_html( trim( implode( ' ', $parts ) ) );

					echo '<br />';

					$parts = array();

					if ( isset( $status_response->address ) ) {
						$address = $status_response->address;

						if ( isset( $address->country ) ) {
							$parts[] = $address->country;
						}
					}

					echo esc_html( trim( implode( ' ', $parts ) ) );
				}

				break;
			case 'age' :
				if ( is_object( $status_response ) && isset( $status_response->age ) ) {
					$age = $status_response->age;

					if ( isset( $age->date_of_birth ) && ! empty( $age->date_of_birth ) ) {
						echo esc_html( mysql2date( __( 'j F Y', 'cm-idin' ), $status_response->age->date_of_birth ) );

						echo '<br />';

						$date_of_birth = new DateTime( $status_response->age->date_of_birth );

						$interval = $date_of_birth->diff( new DateTime() );

						printf(
							/* translators: %s: age */
							esc_html__( '%s year', 'cm-idin' ),
							esc_html( $interval->y )
						);

						echo '<br />';
					}

					if ( isset( $age->{'18y_or_older'} ) ) {
						if ( $age->{'18y_or_older'} ) {
							esc_html_e( 'Yes, 18+ verified.', 'cm-idin' );
						} else {
							esc_html_e( 'No, not 18+ verified.', 'cm-idin' );
						}
					}
				}

				break;
			case 'other' :
				if ( is_object( $status_response ) ) {
					if ( isset( $status_response->email_address ) && ! empty( $status_response->email_address ) ) {
						printf(
							'<a href="%s">%s</a>',
							esc_attr( 'mailto:' . $status_response->email_address ),
							esc_html( $status_response->email_address )
						);
					}

					echo '<br />';

					if ( isset( $status_response->telephone_number ) && ! empty( $status_response->telephone_number ) ) {
						printf(
							'<a href="%s">%s</a>',
							esc_attr( 'tel:' . $status_response->telephone_number ),
							esc_html( $status_response->telephone_number )
						);
					}
				}

				break;
			case 'user' :
				if ( ! empty( $item->user_id ) ) {
					printf(
						'<a href="%s">%s</a>',
						esc_url( get_edit_user_link( $item->user_id ) ),
						esc_html( $item->display_name )
					);
				}
		}
	}

	/**
	 * Prepare items.
	 */
	public function prepare_items() {
		$plugin = cm_idin_plugin();

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$total_items = $plugin->transactions->count_transactions();
		$per_page    = 20;

		$this->items = $plugin->transactions->get_transactions( array(
			'per_page' => $per_page,
			'page'     => $this->get_pagenum(),
		) );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}
}
