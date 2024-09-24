<?php
/**
 * Filename: popup-form-free.php
 * Description: This file contains the popup container and button related to the WP Easy Pay plugin.
 *
 * @package WP_Easy_Pay
 */

$wpep_btn_theme = get_option( 'wpep_btn_theme', false );

if ( 'on' === $wpep_btn_theme ) {
	$btn_theme_class = 'class= "wpep-btn wpep-btn-primary wpep-popup-btn" style="background-color:#' . get_option( 'wpep_form_theme_color', true ) . '"';
} else {
	$btn_theme_class = 'class= "wpep-popup-btn" ';
}

?>




<div class="popup-container">

	<button<?php echo esc_attr( $btn_theme_class ); ?>>  <label for="login-popup"> <?php echo esc_attr( get_option( 'wpep_free_popup_btn_text' ) ); ?></label> </button>

	<input type="checkbox" id="login-popup">
	<div class="popupin">
		<label for="login-popup" class="transparent-label"></label>
		<div class="popup-inner">

			<div class="popup-content">
				<div class="closeBtn">
					<label for="login-popup" class="popup-close-btn"><?php esc_html_e( 'X', 'wp_easy_pay' ); ?></label>
				</div>
				<?php
				$payment_type = get_option( 'wpep_free_form_type' );

				if ( 'simple' === $payment_type ) {

					require plugin_dir_path( __FILE__ ) . 'simple-payment-form-free.php';
				}

				if ( 'donation' === $payment_type ) {
					require plugin_dir_path( __FILE__ ) . 'donation-payment-form-free.php';
				}
				?>

			</div>
		</div>
	</div>
</div>
