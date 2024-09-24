<?php
	/**
	 * Filename: simple-payment-form-free.php
	 * Description: This file contains the code related to the Basic Info section of the WP Easy Pay plugin.
	 *
	 * @package WP_Easy_Pay
	 */

?>
<?php

require_once WPEP_ROOT_PATH . '/modules/render_forms/form-helper-functions.php';


$predefined_amount           = get_option( 'wpep_free_amount', true );
$free_form_type              = get_option( 'wpep_free_form_type', true );
$user_defined_amount         = get_option( 'wpep_free_user_set_amount', true );
$global_payment_mode         = get_option( 'wpep_square_payment_mode_global', true );
$wpep_free_success_url       = ! empty( get_option( 'wpep_free_success_url', true ) ) ? get_option( 'wpep_free_success_url', true ) : '';
$wpep_form_theme_color       = ! empty( get_option( 'wpep_form_theme_color' ) ) ? get_option( 'wpep_form_theme_color' ) : '#5d97ff';
$wpep_show_shadow            = get_option( 'wpep_show_shadow', false );
$wpep_free_form_display_type = get_option( 'wpep_free_form_display_type', false );

if ( 'on' === $global_payment_mode || 1 === $global_payment_mode ) {

		/* If Global Form Live Mode */
		wp_enqueue_script( 'square_payment_form_external', '//js.squareup.com/v2/paymentform', array(), '3', true );
		$square_application_id_in_use = get_option( 'wpep_live_square_app_id', true );
		$square_location_id_in_use    = get_option( 'wpep_square_location_id', true );
		$wpep_free_form_currency      = get_option( 'wpep_square_currency_new' );

} elseif ( 'on' !== $global_payment_mode ) {

	/* If Global Form Test Mode */
	wp_enqueue_script( 'square_payment_form_external', '//js.squareupsandbox.com/v2/paymentform', array(), '3', true );

	$square_application_id_in_use = get_option( 'wpep_square_test_app_id_global', true );
	$square_location_id_in_use    = get_option( 'wpep_square_test_location_id_global', true );
	$wpep_free_form_currency      = get_option( 'wpep_square_currency_test' );
}
	$ajax_nonce = wp_create_nonce( 'check_nonce_success' );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'square_payment_form_free_internal', WPEP_ROOT_URL . 'assets/frontend/js/wpep_free_payment_form.js', array(), '3', true );
	wp_enqueue_style( 'wpep_free_form_style', WPEP_ROOT_URL . 'assets/frontend/css/free_payment_form.css', array(), '1.0.0' );
	wp_localize_script(
		'square_payment_form_free_internal',
		'wpep_local_vars',
		array(
			'ajax_url'                    => admin_url( 'admin-ajax.php' ),
			'square_application_id'       => $square_application_id_in_use,
			'square_location_id_in_use'   => $square_location_id_in_use,
			'form_user_defined_amount'    => $predefined_amount,
			'form_type'                   => $free_form_type,
			'user_defined_amount'         => $user_defined_amount,
			'wpep_free_form_currency'     => $wpep_free_form_currency,
			'front_img_url'               => WPEP_ROOT_URL . 'assets/frontend/img',
			'wpep_payment_success_url'    => $wpep_free_success_url,
			'wpep_free_form_display_type' => $wpep_free_form_display_type,
			'wpep_redirection_on_success' => get_option( 'wpep_redirection_on_success', false ),
			'wpep_redirection_in_secs'    => get_option( 'wpep_free_redirection_in_secs', false ),
			'ajax_nonce'                  => $ajax_nonce,
		)
	);


	if ( ! isset( $wpep_current_form_id ) ) {
		$wpep_current_form_id = 1; // free form.
	}
	if ( 'on' === $wpep_show_shadow ) {
		$shadow_class = 'wpep_form_shadow';
	} else {
		$shadow_class = '';
	}

	?>
<div class="freepage">
<section class="free_form_section <?php echo esc_attr( $shadow_class ); ?>">

	<div class="free_form_page" style="display:none">

		<form action="" method="post" role="form" class="wpep_payment_form" data-id="<?php echo esc_attr( $wpep_current_form_id ); ?>" id="theForm-<?php echo esc_attr( $wpep_current_form_id ); ?>">
		<style>
			:root {
				--wpep-currency: '<?php echo esc_attr( $wpep_free_form_currency ); ?>';
				--wpep-theme-color: #<?php echo esc_attr( $wpep_form_theme_color ); ?>;
			}
		</style>

			<!-- wizard header -->
			<div class="wizardWrap clearfix">


				<?php
				$wpep_free_form_display_type = get_option( 'wpep_free_form_display_type', false );

				if ( false !== $wpep_free_form_display_type && 'on_page' === $wpep_free_form_display_type ) {

					$payment_type = get_option( 'wpep_free_form_type' );

					if ( 'simple' === $payment_type ) {



						require plugin_dir_path( __FILE__ ) . 'simple-payment-form-free.php';
					}

					if ( 'donation' === $payment_type ) {
						require plugin_dir_path( __FILE__ ) . 'donation-payment-form-free.php';
					}
				}

				if ( false !== $wpep_free_form_display_type && 'popup' === $wpep_free_form_display_type ) {

					require plugin_dir_path( __FILE__ ) . 'popup-form-free.php';
				}

				?>

			</div>
			<!-- wizard partials -->

		</form>
		<!-- end form -->

	</div>
	<!-- end form wizard -->

</section>
<!-- end wizard section -->
</div>
