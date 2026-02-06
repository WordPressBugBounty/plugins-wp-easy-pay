<?php
/**
 * WP Easy Pay Plugin Payment Script
 *
 * This script handles the session initialization, script enqueuing, and form rendering for the WP Easy Pay plugin.
 * It includes functions to:
 * - Start sessions for managing form IDs.
 * - Enqueue the appropriate Square payment form script (sandbox or live) based on payment mode settings.
 * - Render the payment form as a popup in the footer with various options like simple payment, donation, and subscription.
 *
 * @package    WP_Easy_Pay
 * @version    1.0.0
 * @since      1.0.0
 * @license    GPL-2.0-or-later
 */

/**
 * Enqueues the appropriate Square payment form script based on payment mode settings.
 *
 * This function determines the correct Square payment form script (sandbox or production)
 * based on global and individual form payment mode settings. It also handles the form ID retrieval,
 * whether embedded in the page content or stored as a transient.
 *
 * @since 1.0.0
 * @return void
 */
function wpep_popup_enque_scripts() {
	if ( 'checkout' === basename( get_permalink() ) ) {
		return;
	}
	$form_post            = get_post();
	$current_form_content = $form_post->post_content;
	if ( isset( $current_form_content ) ) {
		$shortcode_attr       = explode( 'wpep-form', $current_form_content );
		$current_form_code    = explode( '"', $shortcode_attr[1] );
		$current_form_code_id = $current_form_code[1];
	}
	if ( empty( $current_form_code_id ) ) {
		$current_form_code_id = get_transient( 'wpep_guten_id' );
	}
	$global_form             = get_post_meta( $current_form_code_id, 'wpep_individual_form_global', true );
	$global_payment_mode     = get_option( 'wpep_square_payment_mode_global', true );
	$individual_payment_mode = get_post_meta( $current_form_code_id, 'wpep_payment_mode', true );
	if ( 0 === $global_payment_mode || empty( $individual_payment_mode ) ) {
		wp_enqueue_script( 'square_payment_form_external', 'https://sandbox.web.squarecdn.com/v1/square.js', array(), '1.0', true );
	} else {
		wp_enqueue_script( 'square_payment_form_external', 'https://web.squarecdn.com/v1/square.js', array(), '1.0', true );
	}
}
if ( wepp_fs()->is__premium_only() ) {
	add_action( 'wp_enqueue_scripts', 'wpep_popup_enque_scripts' );
}
add_action( 'wp_footer', 'wpep_popup_into_footer' );

/**
 * Renders the WP Easy Pay form as a popup on the footer.
 *
 * This function checks for the presence of session form IDs and retrieves the
 * appropriate settings and form details for each form ID. It determines the payment
 * mode (sandbox or live), form structure, and payment type, and renders the form as a
 * popup with options for various payment methods, including simple payment, donation,
 * and subscription.
 *
 * @since 1.0.0
 * @return void
 */
function wpep_popup_into_footer() {

	if ( isset( $_POST['wp_global_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ), 'wp_global_nonce' ) ) {
		exit;
	}
	if ( isset( $_SESSION['form_ids'] ) ) {
		$form_ids = array_map( 'intval', $_SESSION['form_ids'] );
		foreach ( array_unique( $form_ids ) as $ids ) {

			$wpep_current_form_id = intval( $ids );

			if ( ! empty( $wpep_current_form_id ) ) {

				global $post;
				$payment_type                 = get_post_meta( $wpep_current_form_id, 'wpep_square_payment_type', true );
				$wpep_open_in_popup           = get_post_meta( $wpep_current_form_id, 'wpep_open_in_popup', true );
				$wpep_show_wizard             = get_post_meta( $wpep_current_form_id, 'wpep_show_wizard', true );
				$wpep_show_shadow             = get_post_meta( $wpep_current_form_id, 'wpep_show_shadow', true );
				$form_content                 = get_post( $wpep_current_form_id );
				$wpep_button_title            = empty( get_post_meta( $wpep_current_form_id, 'wpep_button_title', true ) ) ? 'Pay' : get_post_meta( $wpep_current_form_id, 'wpep_button_title', true );
				$square_application_id_in_use = null;
				$square_location_id_in_use    = null;
				$wpep_payment_success_url     = ! empty( get_post_meta( $wpep_current_form_id, 'wpep_square_payment_success_url', true ) ) ? get_post_meta( $wpep_current_form_id, 'wpep_square_payment_success_url', true ) : '';
				$wpep_payment_success_label   = ! empty( get_post_meta( $wpep_current_form_id, 'wpep_square_payment_success_label', true ) ) ? get_post_meta( $wpep_current_form_id, 'wpep_square_payment_success_label', true ) : '';
				$wpep_payment_success_msg     = ! empty( get_post_meta( $wpep_current_form_id, 'wpep_payment_success_msg', true ) ) ? get_post_meta( $wpep_current_form_id, 'wpep_payment_success_msg', true ) : '';
				$currency_symbol_type         = ! empty( get_post_meta( $wpep_current_form_id, 'currencySymbolType', true ) ) ? get_post_meta( $wpep_current_form_id, 'currencySymbolType', true ) : 'code';
				$want_redirection             = ! empty( get_post_meta( $wpep_current_form_id, 'wantRedirection', true ) ) ? get_post_meta( $wpep_current_form_id, 'wantRedirection', true ) : 'No';
				$redirection_delay            = ! empty( get_post_meta( $wpep_current_form_id, 'redirectionDelay', true ) ) ? get_post_meta( $wpep_current_form_id, 'redirectionDelay', true ) : 5;
				$enable_terms_condition       = get_post_meta( $wpep_current_form_id, 'enableTermsCondition', true );
				$terms_label                  = ! empty( get_post_meta( $wpep_current_form_id, 'termsLabel', true ) ) ? get_post_meta( $wpep_current_form_id, 'termsLabel', true ) : 'no';
				$terms_link                   = ! empty( get_post_meta( $wpep_current_form_id, 'termsLink', true ) ) ? get_post_meta( $wpep_current_form_id, 'termsLink', true ) : 'no';
				$global_form                  = get_post_meta( $wpep_current_form_id, 'wpep_individual_form_global', true );
				$global_payment_mode          = get_option( 'wpep_square_payment_mode_global', true );
				$individual_payment_mode      = get_post_meta( $wpep_current_form_id, 'wpep_payment_mode', true );
				$postal_ph                    = ! empty( get_post_meta( $wpep_current_form_id, 'postalPh', true ) ) ? get_post_meta( $wpep_current_form_id, 'postalPh', true ) : 'Postal';
				if ( is_user_logged_in() ) {
					$current_user = wp_get_current_user();
					$user_email   = $current_user->user_email;
				} else {
					$user_email = '';
				}
				$fees_data                         = get_post_meta( $wpep_current_form_id, 'fees_data', true );
				$wpep_donation_goal_switch         = get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_switch', true );
				$wpep_donation_goal_amount         = get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_amount', true );
				$wpep_donation_goal_message_switch = get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_message_switch', true );
				$wpep_donation_goal_message        = get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_message', true );
				$wpep_donation_goal_form_close     = get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_form_close', true );
				$wpep_donation_goal_achieved       = ! empty( get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_achieved', true ) ) ? get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_achieved', true ) : 0;
				$wpep_amount_layout_type           = get_post_meta( $wpep_current_form_id, 'wpep_square_amount_type', true );
				$wpep_square_form_builder_fields   = get_post_meta( $wpep_current_form_id, 'wpep_square_form_builder_fields', true );
				$json_form                         = $wpep_square_form_builder_fields;
				$open_form_json                    = json_decode( $json_form );
				if ( 'on' === $wpep_show_shadow ) {
					$shadow_class = 'wpep_form_shadow';
				} else {
					$shadow_class = '';
				}
				if ( empty( $global_form ) ) {
					// For single form.
					if ( empty( $individual_payment_mode ) ) { // For test individual.
						$square_application_id_in_use = get_post_meta( $wpep_current_form_id, 'wpep_square_test_app_id', true );
						$square_location_id_in_use    = get_post_meta( $wpep_current_form_id, 'wpep_square_test_location_id', true );
						$square_currency              = get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_test', true );
						
					} elseif ( ! empty( $individual_payment_mode ) ) { // For live individual.
						$square_application_id_in_use = WPEP_SQUARE_APP_ID;
						$square_location_id_in_use    = get_post_meta( $wpep_current_form_id, 'wpep_square_location_id', true );
						$square_currency              = get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_new', true );
					}
				} elseif ( 0 === $global_payment_mode ) { // For test global.
					$square_application_id_in_use = get_option( 'wpep_square_test_app_id_global', true );
					$square_location_id_in_use    = get_option( 'wpep_square_test_location_id_global', true );
					$square_currency              = get_option( 'wpep_square_currency_test' );
				} else { // For live global.
					$square_application_id_in_use = WPEP_SQUARE_APP_ID;
					$square_location_id_in_use    = get_option( 'wpep_square_location_id', true );
					$square_currency              = get_option( 'wpep_square_currency_new' );
				}
				?>


				<div id="wpep_popup-<?php echo esc_html( $wpep_current_form_id ); ?>" class="wpep-overlay">
					<div class="wpep-popup">
						<?php $logo = get_the_post_thumbnail_url( $wpep_current_form_id ); ?>
						<?php
						if ( ! empty( $logo ) && '' !== $logo ) {
							echo '<span class="wpep-popup-logo"><img src="' . esc_url( $logo ) . '" class="wpep-popup-logo-img"></span>';
						}
						?>
						<a class="wpep-close" data-btn-id="<?php echo esc_attr( $wpep_current_form_id ); ?>" href="#wpep_popup-<?php echo esc_attr( $wpep_current_form_id ); ?>">
							<span></span>
							<span></span>
						</a>
						<div class="wpep-content">
							<div class="wizard-<?php echo esc_attr( $wpep_current_form_id ); ?>
							<?php
							if ( 'on' !== $wpep_show_wizard ) {
								echo 'singlepage';
							} else {
								echo 'multipage';
							}
							?>
							" style="position:relative">
								<section class="wizard-section <?php echo esc_attr( $shadow_class ); ?>" style="visibility:hidden">
									<div class="form-wizard">
										<form action="" method="post" role="form" class="wpep_payment_form"
												data-id="<?php echo esc_attr( $wpep_current_form_id ); ?>"
												id="theForm-<?php echo esc_attr( $wpep_current_form_id ); ?>" autocomplete="off"
												data-currency="<?php echo esc_attr( $square_currency ); ?>"
												data-currency-type="<?php echo esc_attr( $currency_symbol_type ); ?>"
												data-redirection="<?php echo esc_attr( $want_redirection ); ?>"
												data-delay="<?php echo esc_attr( $redirection_delay ); ?>"
												data-postal="<?php echo esc_attr( $postal_ph ); ?>" data-user-email="<?php echo esc_attr( $user_email ); ?>"
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

												#theForm-<?php echo esc_attr( $wpep_current_form_id ); ?> {
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
																<?php endif; ?><?php } ?>

												}

												#wpep_popup-<?php echo esc_attr( $wpep_current_form_id ); ?> {

													--wpep-theme-color: #<?php echo esc_html( get_post_meta( $wpep_current_form_id, 'wpep_form_theme_color', true ) ); ?>;
												}

											</style>
			
											<input type="hidden" name="is_extra_fee" class="is_extra_fee" value="<?php echo ( ! empty( $fees_data[0]['check'] ) && in_array( 'yes', $fees_data[0]['check'], true ) ) ? 1 : 0; ?>" />

											<?php if ( ! empty( $form_content->post_content ) ) { ?>

												<h3> <?php echo esc_html( $form_content->post_title ); ?> </h3>

												<p class="wpep-form-desc"><?php echo esc_html( $form_content->post_content ); ?></p>

											<?php } ?>

											<?php if ( 'on' !== $wpep_open_in_popup ) { ?>
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
											<?php } ?>


											<!-- wizard header -->
											<div class="wizardWrap clearfix">


												<div class="form-wizard-header 
												<?php
												if ( isset( $logo ) ) {
													echo 'form-wizard-header-logo';
												}
												?>
												">
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
											</div>
											<!-- wizard partials -->

										</form>
										<!-- end form -->

									</div>
								</section>
							</div>

						</div>
					</div>
				</div>

				<?php
			}
		}
	}
}

/**
 * Initializes a session and resets the 'form_ids' session variable.
 *
 * This function starts a PHP session, and if the 'form_ids' session variable is set,
 * it resets it to an empty array. It is designed for use with the WP Easy Pay plugin to handle form session data.
 *
 * @since 1.0.0
 * @return void
 */
function wpep_session_start() {
	session_start();
	if ( isset( $_SESSION['form_ids'] ) ) {
		$_SESSION['form_ids'] = array();
	}
	session_write_close();
}

add_action( 'init', 'wpep_session_start' );


?>
