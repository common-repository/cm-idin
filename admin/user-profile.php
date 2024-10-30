<?php

/**
 * User profile.
 *
 * @since 1.1.6
 * @see https://github.com/WordPress/WordPress/blob/4.5.2/wp-admin/user-edit.php#L578-L600
 */

$data = array(
	'cm_idin_status'              => __( 'Status', 'cm-idin' ),
	'cm_idin_bin'                 => __( 'Bin', 'cm-idin' ),
	'cm_idin_gender'              => __( 'Gender', 'cm-idin' ),
	'cm_idin_initials'            => __( 'Initials', 'cm-idin' ),
	'cm_idin_first_name'          => __( 'First Name', 'cm-idin' ),
	'cm_idin_last_name'           => __( 'Last Name', 'cm-idin' ),
	'cm_idin_last_name_prefix'    => __( 'Last Name Prefix', 'cm-idin' ),
	'cm_idin_street'              => __( 'Street', 'cm-idin' ),
	'cm_idin_house_number'        => __( 'House Number', 'cm-idin' ),
	'cm_idin_house_number_suffix' => __( 'House Number Suffix', 'cm-idin' ),
	'cm_idin_postal_code'         => __( 'Postal Code', 'cm-idin' ),
	'cm_idin_city'                => __( 'City', 'cm-idin' ),
	'cm_idin_country'             => __( 'Country', 'cm-idin' ),
	'cm_idin_date_of_birth'       => __( 'Date of Birth', 'cm-idin' ),
	'cm_idin_18y_or_older'        => __( '18 Years or Older', 'cm-idin' ),
	'cm_idin_telephone_number'    => __( 'Telephone Number', 'cm-idin' ),
	'cm_idin_email_address'       => __( 'Email', 'cm-idin' ),
);

?>
<h2><?php esc_html_e( 'CM iDIN', 'cm-idin' ); ?></h2>

<table class="form-table">

	<?php foreach ( $data as $key => $label ) : ?>

		<tr>
			<th>
				<label for="<?php echo esc_attr( $key ); ?>">
					<?php echo esc_html( $label ); ?>
				</label>
			</th>
			<td>
				<input id="?php echo esc_attr( $key ); ?>" name="?php echo esc_attr( $key ); ?>" type="text" value="<?php echo esc_attr( get_user_meta( $user->ID, $key, true ) ); ?>" class="regular-text" readonly="readonly" />
			</td>
		</tr>

	<?php endforeach; ?>

</table>
