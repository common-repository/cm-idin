<div class="cm-idin-container">
	<div class="cm-idin-item cm-idin-media">
		<img class="cm-idin-logo" src="<?php echo esc_url( plugins_url( 'images/idin-logo.svg', cm_idin_plugin()->file ) ); ?>" alt="" />

		<div class="cm-idin-media-body">
			<?php esc_html_e( 'Registration with iDIN is almost complete. Please enter your email address in order to finish your registration.', 'cm-idin' ); ?>
			<br />
			<a href="<?php echo esc_url( __( 'https://www.idin.nl/consumenten/', 'cm-idin' ) ); ?>" target="_blank"><?php esc_html_e( 'What is iDIN?', 'cm-idin' ); ?></a>
		</div>
	</div>

	<div class="cm-idin-item">
		<div class="cm-idin-form-group">
			<label for="email_address"><?php esc_html_e( 'Email', 'cm-idin' ); ?></label><br />

			<input class="cm-idin-form-control" type="email" name="email" id="email_address" placeholder="<?php esc_attr_e( 'Please enter your email address', 'cm-idin' ); ?>" title="<?php esc_attr_e( 'Email', 'cm-idin' ); ?>" required />
		</div>
	</div>

	<div class="cm-idin-item">
		<input type="hidden" name="cm-idin-trxid" value="<?php echo esc_attr( $transaction->transaction_id ); ?>" />
		<input type="hidden" name="cm-idin-ec" value="<?php echo esc_attr( $transaction->entrance_code ); ?>" />

		<button name="cm_idin_register_2" type="submit" class="cm-idin-btn cm-idin-btn-primary"><?php esc_html_e( 'Complete Registration', 'cm-idin' ); ?></button><br />

		<button name="cm_idin_cancel" type="submit" class="cm-idin-btn cm-idin-btn-secondary"><?php esc_html_e( 'Cancel Registration', 'cm-idin' ); ?></button>
	</div>
</div>
