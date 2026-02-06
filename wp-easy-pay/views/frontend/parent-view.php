<?php
/**
 * WP Easy Pay Payment Processing and Display Script
 *
 * This file is responsible for rendering and processing the payment form using the WP Easy Pay plugin.
 * It includes logic for loading payment gateways, handling payment success redirections,
 * displaying terms and conditions, and supporting multiple payment types (e.g., Google Pay, Apple Pay).
 *
 * @package WP_Easy_Pay
 * @since 1.0.0
 * @version 1.0.0
 *
 * License: [License information, e.g., GPL-2.0+]
 */

$_SESSION['URL'] = false;

global $post;
$wpep_open_in_popup           = get_post_meta( $wpep_current_form_id, 'wpep_open_in_popup', true );
$wpep_show_wizard             = get_post_meta( $wpep_current_form_id, 'wpep_show_wizard', true );
$wpep_show_shadow             = get_post_meta( $wpep_current_form_id, 'wpep_show_shadow', true );
$wpep_btn_theme               = get_post_meta( $wpep_current_form_id, 'wpep_btn_theme', true );
$form_content                 = get_post( $wpep_current_form_id );
$wpep_button_title            = empty( get_post_meta( $wpep_current_form_id, 'wpep_button_title', true ) ) ? 'Pay' : get_post_meta( $wpep_current_form_id, 'wpep_button_title', true );
$square_application_id_in_use = null;
$square_location_id_in_use    = null;
$currency_symbol_type         = ! empty( get_post_meta( $wpep_current_form_id, 'currencySymbolType', true ) ) ? get_post_meta( $wpep_current_form_id, 'currencySymbolType', true ) : 'code';
$wpep_payment_success_url     = ! empty( get_post_meta( $wpep_current_form_id, 'wpep_square_payment_success_url', true ) ) ? get_post_meta( $wpep_current_form_id, 'wpep_square_payment_success_url', true ) : '';
$wpep_payment_success_label   = ! empty( get_post_meta( $wpep_current_form_id, 'wpep_square_payment_success_label', true ) ) ? get_post_meta( $wpep_current_form_id, 'wpep_square_payment_success_label', true ) : '';
$wpep_payment_success_msg     = ! empty( get_post_meta( $wpep_current_form_id, 'wpep_payment_success_msg', true ) ) ? get_post_meta( $wpep_current_form_id, 'wpep_payment_success_msg', true ) : '';
$want_redirection             = ! empty( get_post_meta( $wpep_current_form_id, 'wantRedirection', true ) ) ? get_post_meta( $wpep_current_form_id, 'wantRedirection', true ) : 'No';
$redirection_delay            = ! empty( get_post_meta( $wpep_current_form_id, 'redirectionDelay', true ) ) ? get_post_meta( $wpep_current_form_id, 'redirectionDelay', true ) : 5;

$enable_terms_condition = get_post_meta( $wpep_current_form_id, 'enableTermsCondition', true );
$terms_label            = ! empty( get_post_meta( $wpep_current_form_id, 'termsLabel', true ) ) ? get_post_meta( $wpep_current_form_id, 'termsLabel', true ) : 'no';
$terms_link             = ! empty( get_post_meta( $wpep_current_form_id, 'termsLink', true ) ) ? get_post_meta( $wpep_current_form_id, 'termsLink', true ) : 'no';
$postal_ph              = ! empty( get_post_meta( $wpep_current_form_id, 'postalPh', true ) ) ? get_post_meta( $wpep_current_form_id, 'postalPh', true ) : 'Postal';

if ( is_user_logged_in() ) {
	$wpep_current_user = wp_get_current_user();
	$wpep_user_email   = $wpep_current_user->user_email;
} else {
	$wpep_user_email = '';
}

$fees_data = get_post_meta( $wpep_current_form_id, 'fees_data', false );

$wpep_donation_goal_switch         = get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_switch', true );
$wpep_donation_goal_amount         = get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_amount', true );
$wpep_donation_goal_message_switch = get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_message_switch', true );
$wpep_donation_goal_message        = get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_message', true );
$wpep_donation_goal_form_close     = get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_form_close', true );
$wpep_donation_goal_achieved       = ! empty( get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_achieved', true ) ) ? get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_achieved', true ) : 0;

$form_payment_global = get_post_meta( $wpep_current_form_id, 'wpep_individual_form_global', true );


if ( 'on' === $form_payment_global ) {

	$global_payment_mode = get_option( 'wpep_square_payment_mode_global', true );


	if ( 'on' === $global_payment_mode ) {
		/* If Global Form Live Mode */

		$square_currency = get_option( 'wpep_square_currency_new' );

	}

	if ( 'on' !== $global_payment_mode ) {

		/* If Global Form Test Mode */

		$square_currency = get_option( 'wpep_square_currency_test' );

	}
}

if ( 'on' !== $form_payment_global ) {


	$individual_payment_mode = get_post_meta( $wpep_current_form_id, 'wpep_payment_mode', true );

	if ( 'on' === $individual_payment_mode ) {

		/* If Individual Form Live Mode */
		$square_application_id_in_use = WPEP_SQUARE_APP_ID;
		$square_location_id_in_use    = get_post_meta( $wpep_current_form_id, 'wpep_square_location_id', true );
		$square_currency              = get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_new', true );

	}

	if ( 'on' !== $individual_payment_mode ) {


		/* If Individual Form Test Mode */
		$square_application_id_in_use = get_post_meta( $wpep_current_form_id, 'wpep_square_test_app_id', true );
		$square_location_id_in_use    = get_post_meta( $wpep_current_form_id, 'wpep_square_test_location_id', true );
		$square_currency              = get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_test', true );

	}
}

	$wpep_square_google_pay_individual = get_post_meta( $wpep_current_form_id, 'wpep_square_google_pay', true );
	$wpep_square_customer_cof          = get_user_meta( get_current_user_id(), 'wpep_square_customer_cof', true );
	$wpep_save_card                    = get_post_meta( $wpep_current_form_id, 'wpep_save_card', true );
	$enable_coupon                     = get_post_meta( $wpep_current_form_id, 'enableCoupon', true );
	$wpep_individual_form_global       = get_post_meta( $wpep_current_form_id, 'wpep_individual_form_global', true );

require_once WPEP_ROOT_PATH . 'modules/render_forms/form-helper-functions.php';
$wpep_amount_layout_type         = get_post_meta( $wpep_current_form_id, 'wpep_square_amount_type', true );
$wpep_square_form_builder_fields = get_post_meta( $wpep_current_form_id, 'wpep_square_form_builder_fields', true );
$json_form                       = $wpep_square_form_builder_fields;
$open_form_json                  = json_decode( $json_form );

if ( 'on' === $wpep_show_shadow ) {
	$shadow_class = 'wpep_form_shadow';
} else {
	$shadow_class = '';
}
if ( 'on' === $wpep_btn_theme ) {
	$theme_color     = esc_attr( get_post_meta( $wpep_current_form_id, 'wpep_form_theme_color', true ) );
	$btn_theme_class = 'wpep-btn wpep-btn-primary wpep-popup-btn';
	$btn_theme_style = sprintf( 'background-color:#%s', $theme_color );
} else {
	$btn_theme_class = 'wpep-popup-btn';
	$btn_theme_style = '';
}
?>

<?php
if ( 'on' === $wpep_open_in_popup ) {



	$_SESSION['form_ids'][] = $wpep_current_form_id;

	$wpep_button_title = get_option( 'wpep_button_title', false );

	if ( false === $wpep_button_title ) {

		$wpep_button_title = 'Open Form';
	}
	$get_btn_val = get_post_meta( $wpep_current_form_id, 'wpep_button_title', true );
	$button_txt  = ! empty( $get_btn_val ) ? $get_btn_val : esc_html__( 'Open Form' );
	?>

	<div style="position:relative">
	<?php
	if ( 'donation' === $payment_type && 'checked' === $wpep_donation_goal_switch && ! empty( trim( $wpep_donation_goal_amount ) ) ) {
		$wpep_donation_goal_amount   = floatval( $wpep_donation_goal_amount );
		$wpep_donation_goal_achieved = floatval( $wpep_donation_goal_achieved );
		if ( $wpep_donation_goal_achieved >= $wpep_donation_goal_amount ) {
			if ( 'checked' === $wpep_donation_goal_form_close ) {

				if ( 'checked' === $wpep_donation_goal_message_switch && ! empty( trim( $wpep_donation_goal_message ) ) ) {
					?>
					<p class="doantionGoalAchieved"><?php echo esc_html( $wpep_donation_goal_message ); ?></p>
					<?php
				}
			}
		}
	}
	?>

	<button type="button" class="<?php echo esc_attr( $btn_theme_class ); ?>" style="<?php echo esc_attr( $btn_theme_style ); ?>"
			data-btn-id="<?php echo esc_attr( $wpep_current_form_id ); ?>"><?php echo esc_html( $button_txt ); ?></button>
	</div>
	<?php
	require_once WPEP_ROOT_PATH . 'views/frontend/popup-form.php';

} else {
	?>
	<style>

		.wizard-form-checkbox input[type="checkbox"],
		.wizard-form-radio input[type="radio"] {
			-webkit-appearance: none;
			-moz-appearance: none;
			-ms-appearance: none;
			-o-appearance: none;
			appearance: none;
		}

		.wizard-<?php echo esc_html( $wpep_current_form_id ); ?> {
			--parent-loader-color: #<?php echo esc_html( get_post_meta( $wpep_current_form_id, 'wpep_form_theme_color', true ) ); ?>;
		}

		.parent-loader {
			position: absolute;
			left: 20px;
			right: 20px;
			top: 20px;
			bottom: 20px;
			width: auto;
			height: auto;
			font-size: 16px;
			color: #000;
			/* background: rgb(255, 255, 255, 0.70); */
			background: rgba(253, 253, 253, 0.98);
			border-radius: 4px;
			border: 1px solid #fbfbfb;
			z-index: 9999;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.parent-loader .initial-load-animation .payment-image .icon-pay {
			height: 48px;
			width: 48px;
		}

		.parent-loader .initial-load-animation .payment-image .icon-pay {
			fill: var(--parent-loader-color);
		}

		.parent-loader .initial-load-animation .loading-bar .blue-bar {
			background-color: var(--parent-loader-color);
		}
	</style>
	<div class="wizard-<?php echo esc_attr( $wpep_current_form_id ); ?>
	<?php
	if ( 'on' !== $wpep_show_wizard ) {
		echo 'singlepage';
	} else {
		echo 'multipage';
	}
	?>
	" style="position:relative">
		<div class="wpepLoader parent-loader">
			<div class="initial-load-animation">
				<div class="loading-bar">
					<div class="blue-bar"></div>
				</div>
			</div>
		</div>
		<section class="wizard-section <?php echo esc_html( $shadow_class ); ?>" style="visibility:hidden">
			<div class="form-wizard">
				<?php
				if ( 'donation' === $payment_type && 'checked' === $wpep_donation_goal_switch && ! empty( trim( $wpep_donation_goal_amount ) ) ) {
					$wpep_donation_goal_amount   = floatval( $wpep_donation_goal_amount );
					$wpep_donation_goal_achieved = floatval( $wpep_donation_goal_achieved );
					if ( $wpep_donation_goal_achieved >= $wpep_donation_goal_amount ) {
						if ( 'checked' === $wpep_donation_goal_form_close ) {

							if ( 'checked' === $wpep_donation_goal_message_switch && ! empty( trim( $wpep_donation_goal_message ) ) ) {
								?>
								<p class="doantionGoalAchieved"><?php echo esc_html( $wpep_donation_goal_message ); ?></p>
								<?php
							} else {
								echo '<p class="doantionGoalAchieved"></p>';
							}
						}
					}
				}
				?>
				<form action="" method="post" role="form" class="wpep_payment_form"
						data-id="<?php echo esc_attr( $wpep_current_form_id ); ?>"
						id="theForm-<?php echo esc_attr( $wpep_current_form_id ); ?>" autocomplete="off"
						data-currency="<?php echo esc_attr( $square_currency ); ?>"
						data-currency-type="<?php echo esc_attr( $currency_symbol_type ); ?>" data-redirection="<?php echo esc_url( $want_redirection ); ?>"
						data-delay="<?php echo esc_attr( $redirection_delay ); ?>"
						data-user-email="<?php echo esc_html( $wpep_user_email ); ?>"
						data-redirectionurl="<?php echo esc_url( $wpep_payment_success_url ); ?>">
					<style>
						:root {
							--wpep-theme-color: '';
							--wpep-currency: '';
						}
						<?php
							$form_payment_global = get_post_meta( $wpep_current_form_id, 'wpep_individual_form_global', true );

						if ( 'on' === $form_payment_global ) {

							$global_payment_mode = get_option( 'wpep_square_payment_mode_global', true );

							if ( 'on' === $global_payment_mode ) {

								/* If Global Form Live Mode */
								$wpep_square_currency = get_option( 'wpep_square_currency_new' );

							}

							if ( 'on' !== $global_payment_mode ) {

								/* If Global Form Test Mode */
								$wpep_square_currency = get_option( 'wpep_square_currency_test' );

							}
						}

						if ( 'on' !== $form_payment_global ) {

							$individual_payment_mode = get_post_meta( $wpep_current_form_id, 'wpep_payment_mode', true );

							if ( 'on' === $individual_payment_mode ) {

								/* If Individual Form Live Mode */
								$wpep_square_currency = get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_new', true );

							}

							if ( 'on' !== $individual_payment_mode ) {
								/* If Individual Form Test Mode */
								$wpep_square_currency = get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_test', true );

							}
						}
						?>

						#theForm-<?php echo esc_html( $wpep_current_form_id ); ?> {
							--wpep-theme-color: #<?php echo esc_html( get_post_meta( $wpep_current_form_id, 'wpep_form_theme_color', true ) ); ?>;

						<?php
						if ( 'code' === $currency_symbol_type ) {
							?>
							--wpep-currency: '<?php echo esc_html( $wpep_square_currency ); ?>';

							<?php
						} else {
							?>
							<?php
							if ( 'USD' === $wpep_square_currency ) :
								?>
	--wpep-currency: '$';
													<?php endif; ?>
													<?php
													if ( 'CAD' === $wpep_square_currency ) :
														?>
							--wpep-currency: 'C$';
														<?php endif; ?>
														<?php
														if ( 'AUD' === $wpep_square_currency ) :
															?>
							--wpep-currency: 'A$';
														<?php endif; ?>
														<?php
														if ( 'JPY' === $wpep_square_currency ) :
															?>
							--wpep-currency: '¥';
														<?php endif; ?>
														<?php
														if ( 'GBP' === $wpep_square_currency ) :
															?>
							--wpep-currency: '£';
														<?php endif; ?> <?php } ?>

						}
					</style>
					<input type="hidden" name="is_extra_fee" class="is_extra_fee" value="<?php echo ( ! empty( $fees_data[0]['check'] ) && in_array( 'yes', $fees_data[0]['check'], true ) ) ? 1 : 0; ?>" />
					<div class="wizardWrap clearfix">
						<div class="form-wizard-header">
							<ul class="list-unstyled form-wizard-steps clearfix">
								<li class="active">
									<span></span>
									<small>Basic Info</small>
								</li>
								<li>
									<span></span>
									<small>Payment</small>
								</li>
								<li>
									<span></span>
									<small>Confirm</small>
								</li>
							</ul>
						</div>

						<?php
						if ( 'simple' === $payment_type ) {

							require WPEP_ROOT_PATH . 'views/frontend/simple-payment-form.php';
						}

						if ( 'donation' === $payment_type ) {

							require WPEP_ROOT_PATH . 'views/frontend/donation-payment-form.php';

						}
						if ( wepp_fs()->is__premium_only() ) {
							if ( 'subscription' === $payment_type ) {

								require WPEP_ROOT_PATH . 'views/frontend/subscription-payment-form.php';

							}


							if ( 'donation_recurring' === $payment_type ) {

								require WPEP_ROOT_PATH . 'views/frontend/subscription-payment-form.php';

							}
						}
						?>

						<input type="hidden" name="wpep_payments" id="wpep_payments"/>
						<?php if ( wepp_fs()->is__premium_only() ) { ?>
							<div class="wpep_saved_cards" style="display:none">
								<?php require WPEP_ROOT_PATH . 'views/frontend/saved-cards.php'; ?>
							</div>
						<?php } ?>
					</div>
				</form>
			</div>
		</section>
	</div>
	<?php
}
