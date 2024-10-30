<?php

$id = filter_input( INPUT_GET, 'cm-idin-message', FILTER_SANITIZE_STRING );

$message = null;

switch ( $id ) {
	case 'cancelled' :
		$message = __( 'The iDIN transaction has been cancelled.', 'cm-idin' );

		break;
	case 'email-empty' :
		$message = __( 'Your issuer did not supply an email adress.', 'cm-idin' );

		break;
	case 'user-not-found' :
		$message = __( 'We could not find a corresponding user for your iDIN transaction.', 'cm-idin' );

		break;
	case 'user-email-exists' :
		$message = __( 'There is already a user with your e-mail address, try to login.', 'cm-idin' );

		break;
	case 'user-bin-exists' :
		$message = __( 'There is already a user with your iDIN information, try to login.', 'cm-idin' );

		break;
	case 'check-email' :
		$message = __( 'Check your email for your login details.', 'cm-idin' );

		break;
	case 'age-verified' :
		$message = __( 'Your age is verified.', 'cm-idin' );

		break;
	case 'age-not-verified' :
		$message = __( 'Unfortunately, we were unable to verify your age.', 'cm-idin' );

		break;
	case 'logged-in' :
		$message = __( 'You are successfully logged in.', 'cm-idin' );

		break;
	case 'registered' :
		$message = __( 'You are successfully registered.', 'cm-idin' );

		break;
}

if ( empty( $message ) ) {
	return;
}

?>
<div class="cm-idin-container">
	<div class="cm-idin-item cm-idin-media">
		<img class="cm-idin-logo" src="<?php echo esc_url( plugins_url( 'images/idin-logo.svg', cm_idin_plugin()->file ) ); ?>" alt="" />

		<div class="cm-idin-media-body">
			<?php echo esc_html( $message ); ?>
		</div>
	</div>
</div>
