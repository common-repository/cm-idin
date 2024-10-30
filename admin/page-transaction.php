<?php

$status_response = json_decode( $transaction->status_response );

?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php if ( is_object( ( $transaction ) ) ) : ?>

		<table class="form-table">
			<tr>
				<th>
					<?php esc_html_e( 'Date', 'cm-idin' ); ?>
				</th>
				<td>
					<?php echo esc_html( mysql2date( __( 'D j F Y H:i:s', 'cm-idin' ), $transaction->created ) ); ?>
				</td>
			</tr>
			<tr>
				<th>
					<?php esc_html_e( 'Transaction ID', 'cm-idin' ); ?>
				</th>
				<td>
					<?php echo esc_html( $transaction->transaction_id ); ?>
				</td>
			</tr>
			<tr>
				<th>
					<?php esc_html_e( 'Type', 'cm-idin' ); ?>
				</th>
				<td>
					<?php echo esc_html( $this->plugin->get_type_label( $transaction->type ) ); ?>
				</td>
			</tr>
			<tr>
				<th>
					<?php esc_html_e( 'Issuer', 'cm-idin' ); ?>
				</th>
				<td>
					<?php echo esc_html( $transaction->issuer_name ); ?>
				</td>
			</tr>
			<tr>
				<th>
					<?php esc_html_e( 'Status', 'cm-idin' ); ?>
				</th>
				<td>
					<?php echo esc_html( $this->plugin->get_status_label( $transaction->status ) ); ?>
				</td>
			</tr>
			<tr>
				<th>
					<?php esc_html_e( 'Gender', 'cm-idin' ); ?>
				</th>
				<td>
					<?php

					if ( isset( $status_response->name, $status_response->name->gender ) ) {
						echo esc_html( $this->plugin->get_gender_label( $status_response->name->gender ) );
					}

					?>
				</td>
			</tr>
			<tr>
				<th>
					<?php esc_html_e( 'Name', 'cm-idin' ); ?>
				</th>
				<td>
					<?php echo esc_html( $transaction->get_name() ); ?>
				</td>
			</tr>
			<tr>
				<th>
					<?php esc_html_e( 'Address', 'cm-idin' ); ?>
				</th>
				<td>
					<?php

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

					?>
				</td>
			</tr>
			<tr>
				<th>
					<?php esc_html_e( 'Age', 'cm-idin' ); ?>
				</th>
				<td>
					<?php

					if ( isset( $status_response->age ) ) {
						$age = $status_response->age;

						if ( isset( $age->date_of_birth ) && ! empty( $age->date_of_birth ) ) {
							echo esc_html( mysql2date( __( 'j F Y', 'cm-idin' ), $age->date_of_birth ) );

							echo '<br />';

							$date_of_birth = new DateTime( $age->date_of_birth );

							$interval = $date_of_birth->diff( new DateTime() );

							printf(
								/* translators: %s: age */
								esc_html__( '%s year', 'cm-idin' ),
								esc_html( $interval->y )
							);
						}

						echo '<br />';

						if ( isset( $age->{'18y_or_older'} ) ) {
							if ( $age->{'18y_or_older'} ) {
								esc_html_e( 'Yes, 18+ verified.', 'cm-idin' );
							} else {
								esc_html_e( 'No, not 18+ verified.', 'cm-idin' );
							}
						}
					}

					?>
				</td>
			</tr>
			<tr>
				<th>
					<?php esc_html_e( 'Status Response', 'cm-idin' ); ?>
				</th>
				<td>
					<textarea cols="60" rows="10" readonly="readonly"><?php echo esc_textarea( $transaction->status_response ); ?></textarea>
				</td>
			</tr>
		</table>

	<?php endif; ?>

</div>
