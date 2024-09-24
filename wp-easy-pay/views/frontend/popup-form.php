<?php
/**
 * Filename: popup-form.php
 * Description: popup form in frontend.
 *
 * @package WP_Easy_Pay
 */

?>
<?php

add_action( 'wp_footer', 'wpep_popup_into_footer' );
/**
 * Outputs the necessary JavaScript and HTML for the popup into the footer.
 */
function wpep_popup_into_footer() {

	if ( isset( $_SESSION['form_ids'] ) ) {

		foreach ( array_unique( $_SESSION['form_ids'] ) as $ids ) {

			$wpep_current_form_id = $ids;

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

				$wpep_payment_success_url   = ! empty( get_post_meta( $wpep_current_form_id, 'wpep_square_payment_success_url', true ) ) ? get_post_meta( $wpep_current_form_id, 'wpep_square_payment_success_url', true ) : '';
				$wpep_payment_success_label = ! empty( get_post_meta( $wpep_current_form_id, 'wpep_square_payment_success_label', true ) ) ? get_post_meta( $wpep_current_form_id, 'wpep_square_payment_success_label', true ) : '';
				$wpep_payment_success_msg   = ! empty( get_post_meta( $wpep_current_form_id, 'wpep_payment_success_msg', true ) ) ? get_post_meta( $wpep_current_form_id, 'wpep_payment_success_msg', true ) : '';

				$currency_symbol_type = ! empty( get_post_meta( $wpep_current_form_id, 'currencySymbolType', true ) ) ? get_post_meta( $wpep_current_form_id, 'currencySymbolType', true ) : 'code';

				$want_redirection  = ! empty( get_post_meta( $wpep_current_form_id, 'wantRedirection', true ) ) ? get_post_meta( $wpep_current_form_id, 'wantRedirection', true ) : 'No';
				$redirection_delay = ! empty( get_post_meta( $wpep_current_form_id, 'redirectionDelay', true ) ) ? get_post_meta( $wpep_current_form_id, 'redirectionDelay', true ) : '';

				$enable_terms_condition = get_post_meta( $wpep_current_form_id, 'enableTermsCondition', true );
				$terms_label            = ! empty( get_post_meta( $wpep_current_form_id, 'termsLabel', true ) ) ? get_post_meta( $wpep_current_form_id, 'termsLabel', true ) : 'no';
				$terms_link             = ! empty( get_post_meta( $wpep_current_form_id, 'termsLink', true ) ) ? get_post_meta( $wpep_current_form_id, 'termsLink', true ) : 'no';

				if ( is_user_logged_in() ) {
					$current_user = wp_get_current_user();
					$user_email   = $current_user->user_email;
				} else {
					$user_email = '';
				}

				$fees_data = get_post_meta( $wpep_current_form_id, 'fees_data' );

				$wpep_donation_goal_switch         = get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_switch', true );
				$wpep_donation_goal_amount         = get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_amount', true );
				$wpep_donation_goal_message_switch = get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_message_switch', true );
				$wpep_donation_goal_message        = get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_message', true );
				$wpep_donation_goal_form_close     = get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_form_close', true );
				$wpep_donation_goal_achieved       = ! empty( get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_achieved', true ) ) ? get_post_meta( $wpep_current_form_id, 'wpep_donation_goal_achieved', true ) : 0;

				$form_payment_global = get_post_meta( $wpep_current_form_id, 'wpep_individual_form_global', true );

				$wpep_amount_layout_type         = get_post_meta( $wpep_current_form_id, 'wpep_square_amount_type', true );
				$wpep_square_form_builder_fields = get_post_meta( $wpep_current_form_id, 'wpep_square_form_builder_fields', true );
				$json_form                       = $wpep_square_form_builder_fields;
				$open_form_json                  = json_decode( $json_form );
				if ( 'on' === $wpep_show_shadow ) {
					$shadow_class = 'wpep_form_shadow';

				} else {

					$shadow_class = '';

				}

				if ( 'on' === $form_payment_global ) {

					$global_payment_mode = get_option( 'wpep_square_payment_mode_global', true );

					if ( 'on' === $global_payment_mode ) {

						/* If Global Form Live Mode */

						wp_enqueue_script( 'square_payment_form_external', 'https://sandbox.web.squarecdn.com/v1/square.js', array(), '3', true );

						$square_application_id_in_use = WPEP_SQUARE_APP_ID;
						$square_location_id_in_use    = get_option( 'wpep_square_location_id', true );
						$square_currency              = get_option( 'wpep_square_currency_new' );

					}

					if ( 'on' !== $global_payment_mode ) {

						/* If Global Form Test Mode */

						wp_enqueue_script( 'square_payment_form_external', 'https://sandbox.web.squarecdn.com/v1/square.js', array(), '3', true );

						$square_application_id_in_use = get_option( 'wpep_square_test_app_id_global', true );
						$square_location_id_in_use    = get_option( 'wpep_square_test_location_id_global', true );
						$square_currency              = get_option( 'wpep_square_currency_test' );

					}
				}

				if ( 'on' !== $form_payment_global ) {

					$individual_payment_mode = get_post_meta( $wpep_current_form_id, 'wpep_payment_mode', true );

					if ( 'on' === $individual_payment_mode ) {

						/* If Individual Form Live Mode */

						wp_enqueue_script( 'square_payment_form_external', 'https://sandbox.web.squarecdn.com/v1/square.js', array(), '3', true );

						$square_application_id_in_use = WPEP_SQUARE_APP_ID;
						$square_location_id_in_use    = get_post_meta( $wpep_current_form_id, 'wpep_square_location_id', true );
						$square_currency              = get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_new', true );

					}

					if ( 'on' !== $individual_payment_mode ) {

						/* If Individual Form Test Mode */

						wp_enqueue_script( 'square_payment_form_external', 'https://sandbox.web.squarecdn.com/v1/square.js', array(), '3', true );

						$square_application_id_in_use = get_post_meta( $wpep_current_form_id, 'wpep_square_test_app_id', true );
						$square_location_id_in_use    = get_post_meta( $wpep_current_form_id, 'wpep_square_test_location_id', true );
						$square_currency              = get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_test', true );

					}
				}
				?>


				<div id="wpep_popup-<?php echo esc_html( $wpep_current_form_id ); ?>" class="wpep-overlay">
					<div class="wpep-popup">
						<?php $logo = get_the_post_thumbnail_url( $wpep_current_form_id ); ?>
						<?php
						if ( isset( $logo ) && '' !== $logo ) {
							echo '<span class="wpep-popup-logo"><img src="' . esc_url( $logo ) . '" class="wpep-popup-logo-img"></span>';
						}
						?>
						<a class="wpep-close" data-btn-id="<?php echo esc_attr( $wpep_current_form_id ); ?>" href="#wpep_popup-<?php echo esc_attr( $wpep_current_form_id ); ?>">
							<span></span>
							<span></span>
						</a>
						<div class="wpep-content">
							<div class="wizard-<?php echo esc_attr( $wpep_current_form_id ); ?> <?php
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
												data-delay="<?php echo esc_attr( $redirection_delay ); ?>"data-user-email="<?php echo esc_attr( $user_email ); ?>"
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

												if ( 'subscription' === $payment_type ) {

													require WPEP_ROOT_PATH . 'views/frontend/subscription-payment-form.php';

												}

												if ( 'donation_recurring' === $payment_type ) {

													require WPEP_ROOT_PATH . 'views/frontend/subscription-payment-form.php';

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

add_action( 'init', 'wpep_session_start' );
/**
 * Start the session and initialize the 'form_ids' session variable.
 */
function wpep_session_start() {
	session_start();
	if ( isset( $_SESSION['form_ids'] ) ) {
		$_SESSION['form_ids'] = array();
	}
}


?>
