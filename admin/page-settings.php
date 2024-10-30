<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php

	$merchant_token = $this->plugin->get_merchant_token();
	$merchant_info  = $this->plugin->get_merchant_info();

	if ( ! empty( $merchant_token ) && false === $merchant_info ) : ?>

		<div class="error">
			<p>
				<?php esc_html_e( 'Please enter a valid CM iDIN merchant token.', 'cm-idin' ); ?>
			</p>
		</div>

	<?php endif; ?>

	<form name="form" action="options.php" method="post">
		<?php settings_fields( 'cm-idin' ); ?>

		<?php do_settings_sections( 'cm-idin' ); ?>

		<?php submit_button(); ?>
	</form>
</div>
