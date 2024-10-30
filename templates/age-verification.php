<?php

$directory = $this->service->get_directory();

?>
<div class="cm-idin-container">
	<div class="cm-idin-item cm-idin-media">
		<img class="cm-idin-logo" src="<?php echo esc_url( plugins_url( 'images/idin-logo.svg', cm_idin_plugin()->file ) ); ?>" alt="" />

		<div class="cm-idin-media-body">
			<?php esc_html_e( 'In order to continue, you need to verify your age through iDIN.', 'cm-idin' ); ?>
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

		<button name="cm_idin_verify_age" type="submit" class="cm-idin-btn cm-idin-btn-primary"><?php esc_html_e( 'Verify age with iDIN', 'cm-idin' ); ?></button>
	</div>
</div>
