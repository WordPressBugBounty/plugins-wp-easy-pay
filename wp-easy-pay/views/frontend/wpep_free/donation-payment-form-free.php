<fieldset class="wizard-fieldset show">
	<div class="s_ft noMulti">
		<?php
			/**
			 * Filename: donation-payment-form-free.php
			 * Description: This file contains the code related to donation form.
			 *
			 * @package WP_Easy_Pay
			 */

		?>
		<h2><?php esc_html_e( 'Basic Info', 'wp_easy_pay' ); ?></h2>
	</div>
	<div id="wpep_personal_information" class="fieldMainWrapper <?= basename(__FILE__, '.p') ?>">
		<div class="text-field form-group wpep-required">
			<label class="wizard-form-text-label" data-label-show="yes"> <?php esc_html_e( 'First Name', 'wp_easy_pay' ); ?> </label>
			<input type="text" class="form-control" data-label="First Name" name="wpep-first-name-field" required="true">
		</div>

		<div class="text-field form-group wpep-required">
			<label class="wizard-form-text-label" data-label-show="yes"> <?php esc_html_e( 'Last Name ', 'wp_easy_pay' ); ?></label>
			<input type="text" class="form-control" data-label="Last Name" name="wpep-last-name-field" required="true">
		</div>

		<div class="text-field form-group wpep-required">
			<label class="wizard-form-text-label" data-label-show="yes"> <?php esc_html_e( 'Email', 'wp_easy_pay' ); ?> </label>
			<input type="email" class="form-control" data-label="Email" name="wpep-email-field" required="true">
		</div>	
	</div>
</fieldset>

<fieldset class="wizard-fieldset">
	<div class="s_ft noMulti">
		<h2><?php esc_html_e( 'Donation Payment', 'wp_easy_pay' ); ?></h2>
	</div>   
	<div id="creditCard" class="tab-content current">
		<h3><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/creditcard.svg' ); ?>" alt="Avatar" width="25"
			class="credicon" alt="Credit Card"> <?php esc_html_e( 'Credit Card', 'wp_easy_pay' ); ?></h3>
			<?php
			wpep_print_credit_card_fields_free();
			?>
	</div>
</fieldset>

<?php
$payment_success_message = get_option( 'wpep_free_payment_success_message', false );
$redirection_on_success  = get_option( 'wpep_redirection_on_success', false );
$success_btn_label       = get_option( 'wpep_free_payment_success_btn_label', false );
?>
<fieldset class="wizard-fieldset orderCompleted blockIfSingle">
	<div class="confIfSingleTop">
		<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/order-done.svg' ); ?>" alt="Avatar" width="70"
			class="doneorder">
		<h2><?php esc_html_e( 'Payment Successful', 'wp_easy_pay' ); ?> </h2>
	</div>
	<p><?php echo esc_attr( $payment_success_message ); ?></p>

	<?php if ( '' !== $wpep_free_success_url && 'yes' === $redirection_on_success ) { ?>
		<a href="<?php echo esc_url( $wpep_free_success_url ); ?>" class="form-wizard-submit float-right"><?php esc_html_e( 'Your Success Button Label', 'wp_easy_pay' ); ?></a><br><br>
		<small><?php esc_html_e( 'Page will be redirected in', 'wp_easy_pay' ); ?> <span id="counter"><?php echo esc_html( __( '5', 'wp_easy_pay' ) ); ?></span> <?php esc_html_e( 'seconds.', 'wp_easy_pay' ); ?></small>
	<?php } ?>


</fieldset>
