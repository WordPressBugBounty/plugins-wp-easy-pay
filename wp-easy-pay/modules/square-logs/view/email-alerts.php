<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="logsListMain"> 
	<div class="emailAlertScreen">

		<?php echo '<h2 class="mainHeadings">' . esc_html__( 'Alerts', 'wp_easy_pay' ) . '</h2>'; ?>

		<div class="noteInEmailAlert">
			<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/blue-alert.png' ); ?>" class="alertIconNotes" alt="Alert Icon">
			<div class="noteText">
				By enabling this option, the WordPress admin email will be used as the default email to receive alerts.
			</div>
		</div>
		
		<?php
		if ( isset( $_POST['wpep_alerts_nonce'] ) && wp_verify_nonce( $_POST['wpep_alerts_nonce'], 'save_wpep_alerts' ) ) {
			// Save settings
			$square_connection = isset( $_POST['logAlertEmail'] ) ? 1 : 0;
			$alert_email       = sanitize_email( $_POST['my_alert_email'] );

			update_option( 'logAlertEmail', $square_connection );
			update_option( 'my_alert_email', $alert_email );

			echo '<div class="updated"><p>Settings saved.</p></div>';
		}

			$square_connection = get_option( 'logAlertEmail', 0 );
			$alert_email       = get_option( 'my_alert_email', '' );
		?>

		<form method="post">
			<?php wp_nonce_field( 'save_wpep_alerts', 'wpep_alerts_nonce' ); ?>
			
			<div class="wizard-form-checkbox-alert-log">
				<input name="logAlertEmail" id="logAlertEmail" value="on" type="checkbox" <?php checked( 1, $square_connection ); ?>>
				<label for="logAlertEmail">Square Connection</label>
			</div>

			<p class="emailField">
				<label for="my_alert_email">Email</label><br>
				<input type="email" id="my_alert_email" name="my_alert_email"
					value="<?php echo esc_attr( $alert_email ); ?>"
					placeholder="Enter Email" class="form-control emailInputField">
			</p>
			<p class="fieldNote">Enter separate email if you don't wish to use admin email.</p>

			<?php submit_button( 'Save Settings', 'submitAlertEmail' ); ?>
		</form>

	</div>
</div>

