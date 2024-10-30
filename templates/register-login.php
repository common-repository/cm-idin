<?php

$directory = $this->service->get_directory();

$message_id = filter_input( INPUT_GET, 'cm-idin-message', FILTER_SANITIZE_STRING );

if ( 'check-email' === $message_id ) {
	return;
}

?>
<div class="cm-idin-container">
	<div class="cm-idin-item cm-idin-media">
		<img class="cm-idin-logo" src="<?php echo esc_url( plugins_url( 'images/idin-logo.svg', cm_idin_plugin()->file ) ); ?>" alt="" />

		<div class="cm-idin-media-body">
			<?php esc_html_e( 'iDIN is a secure way of identifying yourself with the merchant. It enables quick registration and login through your bank.', 'cm-idin' ); ?>
			<br />
			<a href="<?php echo esc_url( __( 'https://www.idin.nl/consumenten/', 'cm-idin' ) ); ?>" target="_blank"><?php esc_html_e( 'What is iDIN?', 'cm-idin' ); ?></a>
		</div>
	</div>

	<div class="cm-idin-item">
		<div class="cm-idin-form-group">
			<label for="cm-idin-issuer"><?php esc_html_e( 'Select your bank', 'cm-idin' ); ?></label>

			<select class="cm-idin-form-control" id="cm-idin-issuer" name="cm_idin_issuer" required>
				<option value=""><?php esc_html_e( '— Select your bank —', 'cm-idin' ); ?></option>

				<?php

				foreach ( $directory as $item ) {
					printf(
						'<optgroup label="%s">',
						esc_attr( $item->country )
					);

					foreach ( $item->issuers as $issuer ) {
						printf(
							'<option value="%s">%s</option>',
							esc_attr( $issuer->issuer_id ),
							esc_html( $issuer->issuer_name )
						);
					}

					echo '</optgroup>';
				}

				?>
			</select>
		</div>
	</div>

	<div class="cm-idin-item">
		<?php if ( $this->plugin->is_registration_active() ) : ?>

			<button name="cm_idin_register" type="submit" class="cm-idin-btn cm-idin-btn-primary"><?php esc_html_e( 'Register with iDIN', 'cm-idin' ); ?></button>

		<?php endif; ?>

		<?php if ( $this->plugin->is_login_active() ) : ?>
		
			<button name="cm_idin_login" type="submit" class="cm-idin-btn cm-idin-btn-secondary"><?php esc_html_e( 'Login with iDIN', 'cm-idin' ); ?></button>

		<?php endif; ?>
	</div>
</div>
