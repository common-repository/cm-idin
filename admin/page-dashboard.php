<style type="text/css">
	.cm-idin-table {
		margin: 1em 0;

		width: 100%;
	}

	.cm-idin-table th {
		text-align: left;

		width: 25%;
	}
</style>

<div class="wrap">
	<div class="about-wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<div class="about-text">
			<?php esc_html_e( 'This plugin is being developed by Pronamic for CM. Depending on your question you should contact Pronamic or CM.', 'cm-idin' ); ?>
		</div>
	</div>

	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">
			<div id="postbox-container-1" class="postbox-container">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">

					<div class="postbox">
						<h2 class="hndle"><span><?php esc_html_e( 'CM', 'cm-idin' ); ?></span></h2>

						<div class="inside">
							<p>
								<?php esc_html_e( 'If you need help by filling out your account details or setting up common settings, please contact CM.', 'cm-idin' ); ?>
							</p>

							<table class="cm-idin-table">
								<tr>
									<th scope="row"><?php esc_html_e( 'Phone', 'cm-idin' ); ?></th>
									<td><?php esc_html_e( '+31 (0) 76 - 572 70 00', 'cm-idin' ); ?></td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Email', 'cm-idin' ); ?></th>
									<td><?php printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr__( 'info@cm.nl', 'cm-idin' ) ); ?></td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Website', 'cm-idin' ); ?></th>
									<td>
										<?php

										$url = __( 'https://www.cm.com/', 'cm-idin' );

										printf(
											'<a href="%s">%s</a>',
											esc_url( $url ),
											esc_html( $url )
										);

										?>
								</tr>
							</table>
						</div>
					</div>

					<div class="postbox">
						<h2 class="hndle"><span><?php esc_html_e( 'Merchant Info', 'cm-idin' ); ?></span></h2>

						<div class="inside">
							<?php

							$merchant_info  = $this->plugin->get_merchant_info();

							if ( false === $merchant_info ) : ?>

								<p>
									<?php esc_html_e( 'Please enter a valid CM iDIN merchant token.', 'cm-idin' ); ?>
								</p>

							<?php else : ?>

								<h3><?php esc_html_e( 'General', 'cm-idin' ); ?></h3>

								<table class="cm-idin-table">
									<tr>
										<th scope="row"><?php esc_html_e( 'Name', 'cm-idin' ); ?></th>
										<td><?php echo esc_html( $merchant_info->name ); ?></td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Status', 'cm-idin' ); ?></th>
										<td><?php echo esc_html( $merchant_info->status ); ?></td>
									</tr>
								</table>

								<h3><?php esc_html_e( 'Balance', 'cm-idin' ); ?></h3>

								<table class="cm-idin-table">
									<tr>
										<th scope="row"><?php esc_html_e( 'Used', 'cm-idin' ); ?></th>
										<td><?php echo esc_html( $merchant_info->balance->used ); ?></td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Available', 'cm-idin' ); ?></th>
										<td><?php echo esc_html( $merchant_info->balance->available ); ?></td>
									</tr>
								</table>

								<h3><?php esc_html_e( 'Contact', 'cm-idin' ); ?></h3>

								<table class="cm-idin-table">
									<tr>
										<th scope="row"><?php esc_html_e( 'Name', 'cm-idin' ); ?></th>
										<td><?php echo esc_html( $merchant_info->contact->name ); ?></td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Email', 'cm-idin' ); ?></th>
										<td><?php echo esc_html( $merchant_info->contact->email ); ?></td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Phone', 'cm-idin' ); ?></th>
										<td><?php echo esc_html( $merchant_info->contact->phone ); ?></td>
									</tr>
								</table>

							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<div id="postbox-container-2" class="postbox-container">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h2 class="hndle"><span><?php esc_html_e( 'Pronamic', 'cm-idin' ); ?></span></h2>

						<div class="inside">
							<p>
								<?php esc_html_e( 'If you’re experiencing issues or need help with advanced topics, please contact Pronamic.', 'cm-idin' ); ?>
							</p>

							<table class="cm-idin-table">
								<tr>
									<th scope="row"><?php esc_html_e( 'Support', 'cm-idin' ); ?></th>
									<td>
										<?php

										$url = __( 'https://www.pronamic.eu/support/', 'cm-idin' );

										printf(
											'<a href="%s">%s</a>',
											esc_url( $url ),
											esc_html( $url )
										);

										?>
									</td>
								</tr>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Email', 'cm-idin' ); ?></th>
									<td><?php printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr__( 'support@pronamic.eu', 'cm-idin' ) ); ?></td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>

			<div class="clear"></div>
		</div>
	</div>
</div>
