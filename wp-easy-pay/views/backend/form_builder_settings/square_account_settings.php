<?php

/**
 * WP EASY PAY
 *
 * PHP version 7
 *
 * @category Wordpress_Plugin
 * @package  WP_Easy_Pay
 * @author   Author <contact@apiexperts.io>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://wpeasypay.com/
 */

$wpep_test_app_id = get_post_meta( get_the_ID(), 'wpep_square_test_app_id', true );
$wpep_test_token  = get_post_meta( get_the_ID(), 'wpep_square_test_token', true );

$wpep_test_location_id   = get_post_meta( get_the_ID(), 'wpep_square_test_location_id', true );
$wpep_test_location_data = get_post_meta( get_the_ID(), 'wpep_test_location_data', true );

$wpep_live_token_upgraded = get_post_meta( get_the_ID(), 'wpep_live_token_upgraded', true );
$wpep_refresh_token       = get_post_meta( get_the_ID(), 'wpep_refresh_token', true );
$wpep_token_expires_at    = get_post_meta( get_the_ID(), 'wpep_token_expires_at', true );
$wpep_square_btn_auth     = get_post_meta( get_the_ID(), 'wpep_square_btn_auth', true );
$wpep_live_location_data  = get_post_meta( get_the_ID(), 'wpep_live_location_data', true );

$wpep_payment_mode = get_post_meta( get_the_ID(), 'wpep_payment_mode', true );

$wpep_square_google_pay = get_post_meta( get_the_ID(), 'wpep_square_google_pay', true );
$wpep_square_after_pay  = get_post_meta( get_the_ID(), 'wpep_square_after_pay', true );
$wpep_square_apple_pay  = get_post_meta( get_the_ID(), 'wpep_square_apple_pay', true );
$wpep_square_cash_app   = get_post_meta( get_the_ID(), 'wpep_square_cash_app', true );
$wpep_square_giftcard   = get_post_meta( get_the_ID(), 'wpep_square_giftcard', true );
$wpep_square_ach_debit  = get_post_meta( get_the_ID(), 'wpep_square_ach_debit', true );


$wpep_square_google_pay_live = get_post_meta( get_the_ID(), 'wpep_square_google_pay_live', true );
$wpep_square_after_pay_live  = get_post_meta( get_the_ID(), 'wpep_square_after_pay_live', true );
$wpep_square_apple_pay_live  = get_post_meta( get_the_ID(), 'wpep_square_apple_pay_live', true );
$wpep_square_cash_app_live   = get_post_meta( get_the_ID(), 'wpep_square_cash_app_live', true );
$wpep_square_giftcard_live   = get_post_meta( get_the_ID(), 'wpep_square_giftcard_live', true );
$wpep_square_ach_debit_live  = get_post_meta( get_the_ID(), 'wpep_square_ach_debit_live', true );
$wpep_square_terminal        = get_post_meta( get_the_ID(), 'wpep_square_terminal', true );
$wpep_device_id              = get_post_meta( get_the_ID(), 'wpep_device_id', true );
$wpep_device_code            = get_post_meta( get_the_ID(), 'wpep_device_code', true );



$wpep_individual_form_global_meta = get_post_meta( get_the_ID(), 'wpep_individual_form_global', false );
$wpep_global_default_after        = (int) get_option( 'wpep_global_default_after', 0 );
$post_created_at                  = (int) get_the_date( 'U', get_the_ID() );

if ( array() === $wpep_individual_form_global_meta && $wpep_global_default_after > 0 && $post_created_at >= $wpep_global_default_after ) {
	$wpep_individual_form_global = 'on';
} else {
	$wpep_individual_form_global = get_post_meta( get_the_ID(), 'wpep_individual_form_global', true );
}
$wpep_square_location_id = get_post_meta( get_the_ID(), 'wpep_square_location_id', true );
$wpep_disconnect_url     = get_post_meta( get_the_ID(), 'wpep_square_disconnect_url', true );
$wpep_square_connect_url = wpep_create_connect_url( 'individual_form' );

// get test currency
$wpep_post_square_currency_test = get_post_meta( get_the_ID(), 'wpep_post_square_currency_test', true );

// get test currency
$wpep_post_square_currency_new = get_post_meta( get_the_ID(), 'wpep_post_square_currency_new', true );

$input_url = $wpep_disconnect_url;

// Parse the input URL to get an array of query string parameters.
$query_params = array();
$query_string = wp_parse_url( $input_url, PHP_URL_QUERY );
if ( ! empty( $query_string ) ) {
	parse_str( $query_string, $query_params );
} else {
	$query_params = array();
}

// Update the "wpep_sandbox" query parameter based on the current payment mode.
// When payment mode is "on" (live), sandbox is disabled; otherwise sandbox is enabled.
if ( 'on' === $wpep_payment_mode ) {
	$query_params['wpep_sandbox'] = 'no';
} else {
	$query_params['wpep_sandbox'] = 'yes';
}


// Create a new URL with the updated parameters
$wpep_disconnect_url = get_site_url() . '/wp-admin/post.php?' . http_build_query( $query_params );







$wpep_create_connect_sandbox_url = wpep_create_connect_sandbox_url( 'individual_form' );
?>

<form class="wpeasyPay-form">
	
	<main>
		<?php
		if ( wepp_fs()->is__premium_only() ) {
			?>
				<div id="globalSettings" style="display: none">
			<div class="globalSettingsa">
				<div class="globalSettingswrap">
					<h2>Global settings is active</h2>
				<?php $global_setting_url = admin_url( 'admin.php?page=wpep-settings', 'https' ); ?>
					<a href="<?php echo esc_url( $global_setting_url ); ?>" class="btn btn-primary btnglobal wpep-no-save-popup">Go to Square Connect
						settings</a>
				</div>
			</div>
		</div>
		<div class="globalSettings">
			<div class="wizard-form-checkbox wpep-no-save-popup">
				<input type="checkbox" name="wpep_individual_form_global" id="chkGlobal" 
				<?php

				if ( 'on' === $wpep_individual_form_global ) {

					echo 'checked';
				}

				?>
					>
				<label for="chkGlobal">Use global settings</label>
			</div>
		</div>
		
		<div id="normalSettings">
			<div class="swtichWrap">
				<input type="checkbox" id="on-off-single" name="wpep_payment_mode" class="switch-input wpep-no-save-popup"
				<?php
				if ( 'on' === $wpep_payment_mode ) {
					echo 'checked';
				}
				?>
				>
				<label for="on-off-single" class="switch-label">
					<span class="toggle--on toggle--option">Live payment</span>
					<span class="toggle--off toggle--option">Test payment</span>
				</label>
			</div>

			<div class="paymentView" id="wpep_spmst">
				<?php


				if ( empty( $wpep_test_token ) ) {

					?>

					<div class="squareConnect">
						<div class="squareConnectwrap">
							<h2>Connect your square (sandbox) account now!</h2>
							<?php
							if ( isset( $_POST['wp_global_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_POST['wp_global_nonce'] ), 'wp_global_nonce' ) ) {
								exit;
							}
								$get = $_GET;
							if ( isset( $get['type'] ) && 'bad_request.missing_parameter' === $get['type'] ) {
								?>

							<p style="color: red;"> You have denied WP EASY PAY the permission to access your Square account. Please connect again to and click allow to complete OAuth. </p>

								<?php
							}
							?>
							<div class="noteInSquareConnect">
								<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/danger.png' ); ?>" class="dangerIconNotes" alt="Warning Icon">
								<div class="noteText">
									Make sure to launch 
									<a class="wpep-highlight wpep-no-save-popup" href="https://developer.squareup.com/console/en/sandbox-test-accounts">seller test account</a> 
									in the same browser from developer dashboard before connecting your Square Sandbox account.
								</div>
							</div><br><br>
							<a href="<?php echo esc_url( $wpep_create_connect_sandbox_url ); ?>" class="btn btn-primary connectSquareBtn wpep-no-save-popup">
								<div class="SquareConnectIndivi">
									<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/square-btn-icon.png' ); ?>" class="SquareIconConnect" alt="Warning Icon"> 
								
								Connect square (Sandbox)
								</div>
							</a><br><br>
							<p><small> The sandbox OAuth is for testing purpose by connecting and activating this you will be able to make test transactions and to see how your form will work for the customers.  </small></p>

						</div>
					</div>

					<?php

				} else {
					?>

					<div class="squareConnected">
						<h3 class="titleSquare">Square is Connected <i class="fa fa-check-square" aria-hidden="true"></i></h3>
						<div class="wpeasyPay__body">

							<?php
							if ( '' !== $wpep_post_square_currency_test ) {
								?>
								<div class="form-group">
									<label class="locationHeading">Country Currency</label>
									<select class="form-control countryCurrencySquare" disabled="disabled">
										<option
											value="USD" 
											<?php
											if ( ! empty( $wpep_post_square_currency_test ) && 'USD' === $wpep_post_square_currency_test ) :
												echo "selected='selected'";
endif;
											?>
											>
											USD
										</option>
										<option
											value="CAD" 
											<?php
											if ( ! empty( $wpep_post_square_currency_test ) && 'CAD' === $wpep_post_square_currency_test ) :
												echo "selected='selected'";
endif;
											?>
											>
											CAD
										</option>
										<option
											value="AUD" 
											<?php
											if ( ! empty( $wpep_post_square_currency_test ) && 'AUD' === $wpep_post_square_currency_test ) :
												echo "selected='selected'";
endif;
											?>
											>
											AUD
										</option>
										<option
											value="JPY" 
											<?php
											if ( ! empty( $wpep_post_square_currency_test ) && 'JPY' === $wpep_post_square_currency_test ) :
												echo "selected='selected'";
endif;
											?>
											>
											JPY
										</option>
										<option
											value="GBP" 
											<?php
											if ( ! empty( $wpep_post_square_currency_test ) && 'GBP' === $wpep_post_square_currency_test ) :
												echo "selected='selected'";
											endif;
											?>
											>
											GBP
										</option>
									</select>
								</div>
							<?php } ?>

							<?php $all_locations = $wpep_test_location_data; ?>
							<div class="form-group">
								<label class="locationHeading">Location:</label>
								<select class="form-control" name="wpep_square_test_location_id">
									<option>Select Location</option>

									<?php
									foreach ( $all_locations as $location ) {
										$saved_location_id = $wpep_test_location_id;
										if ( false !== $saved_location_id ) {
											if ( $location['location_id'] === $saved_location_id ) {
												$selected = 'selected';
											} else {
												$selected = '';
											}
										}
										echo "<option value='" . esc_attr( $location['location_id'] ) . "' " . esc_html( $selected ) . '>' . esc_html( $location['location_name'] ) . '</option>';
									}
									?>

								</select>
							</div>
						</div>

						<div class="paymentint">
							<label class="title">Other Payment Options</label>
							<div class="wizard-form-checkbox-square-connect">
								<input name="wpep_square_google_pay" id="googlePay"
										type="checkbox" 
										<?php
										if ( 'on' === $wpep_square_google_pay ) {
											echo 'checked';
										}
										?>
										>
								<label for="googlePay">Google Pay</label>
							</div>
							<div class="wizard-form-checkbox-square-connect">
								<input name="wpep_square_after_pay" id="afterPay" type="checkbox"
									<?php
									if ( 'on' === $wpep_square_after_pay ) {
										echo 'checked';
									}
									?>
								>
								<label for="afterPay">After Pay</label>
							</div>
							<div class="wizard-form-checkbox-square-connect">
								<div class="apple_pay_verification">
									<input class="applePayEnableTest" name="wpep_square_apple_pay" id="applePay" type="checkbox"
										<?php
										if ( 'on' === $wpep_square_apple_pay ) {
											echo 'checked';
										}
										?>
									>
									<label for="applePay">Apple Pay</label>
									<button class="apple_verify_domain_test" style="display:none;">Verify domain</button>
									<p class="apple_domain_error_test"></p>
									<input type="hidden" id="apple_domain_verification" name="apple_domain_verification" value="<?php echo esc_attr( wp_create_nonce( 'apple-domain-verification-nonce' ) ); ?>" />
								</div>
							</div>


							<div class="wizard-form-checkbox-square-connect">
								<input name="wpep_square_cash_app" id="cashApp" type="checkbox"
									<?php
									if ( 'on' === $wpep_square_cash_app ) {
										echo 'checked';
									}
									?>
								>
								<label for="cashApp">Cash App</label>
							</div>

							<div class="wizard-form-checkbox-square-connect">
								<input name="wpep_square_giftcard" id="giftcard" type="checkbox"
									<?php
									if ( 'on' === $wpep_square_giftcard ) {
										echo 'checked';
									}
									?>
								>
								<label for="giftcard">Square Gift Card</label>
							</div>

							<div class="wizard-form-checkbox-square-connect">
								<input name="wpep_square_ach_debit" id="achDebit" type="checkbox"
									<?php
									if ( 'on' === $wpep_square_ach_debit ) {
										echo 'checked';
									}
									?>
								>
								<label for="achDebit">ACH Debit</label>
							</div>

						</div>

						<div class="squareSubscriptionNoticeIndividual">
							<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/danger.png' ); ?>" class="dangerIconNotes" alt="Warning Icon">
							<div class="noteText">
								Note: Disconnecting from square and reconnecting with another account can stop your subscription payments. 
							</div>
						</div>
						<div class="btnFooter d-btn">

							<a href="<?php echo esc_url( $wpep_disconnect_url ); ?>"
								class="btn btnDisconnect wpep-no-save-popup">Disconnect
								Square</a>

						</div>

					</div>

					<?php
				}
				?>


</div>

<!-- test block end -->

<div class="livePayment paymentView" id="wpep_spmsl">
			<?php


			if ( empty( $wpep_live_token_upgraded ) ) {

				?>

		<div class="squareConnect">
			<div class="squareConnectwrap">
				<h2>Connect your square account now!</h2>
				<?php
				if ( isset( $get['type'] ) && 'bad_request.missing_parameter' === $get['type'] ) {
					?>

				<p style="color: red;"> You have denied WP EASY PAY the permission to access your Square account. Please connect again to and click allow to complete OAuth. </p>

					<?php
				}
				?>
				<a href="<?php echo esc_url( $wpep_square_connect_url ); ?>" class="btn btn-primary connectSquareBtn wpep-no-save-popup">
					<div class="SquareConnectIndivi">
						<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/square-btn-icon.png' ); ?>" class="SquareIconConnect" alt="Warning Icon">
							Connect Square
					</div>
				</a>
				<a class="connectSquarePop wpep-no-save-popup" href="https://wpeasypay.com/documentation/#global-settings-live-mode"
					target="_blank">

					How to Connect Your Live Square Account.

				</a>

			</div>
		</div>

				<?php

			} else {
				?>

		<div class="squareConnected">
			<h3 class="titleSquare">Square is Connected <i class="fa fa-check-square" aria-hidden="true"></i></h3>
			<div class="wpeasyPay__body">

				<?php
				if ( '' !== $wpep_post_square_currency_new ) {
					?>
					<div class="form-group">
						<label class="locationHeading">Country Currency</label>
						<select name="wpep_post_square_currency_new" class="form-control countryCurrencySquare" disabled="disabled">
							<option
								value="USD" 
								<?php
								if ( ! empty( $wpep_post_square_currency_new ) && 'USD' === $wpep_post_square_currency_new ) :
									echo "selected='selected'";
endif;
								?>
								>
								USD
							</option>
							<option
								value="CAD" 
								<?php
								if ( ! empty( $wpep_post_square_currency_new ) && 'CAD' === $wpep_post_square_currency_new ) :
									echo "selected='selected'";
endif;
								?>
								>
								CAD
							</option>
							<option
								value="AUD" 
								<?php
								if ( ! empty( $wpep_post_square_currency_new ) && 'AUD' === $wpep_post_square_currency_new ) :
									echo "selected='selected'";
endif;
								?>
								>
								AUD
							</option>
							<option
								value="JPY" 
								<?php
								if ( ! empty( $wpep_post_square_currency_new ) && 'JPY' === $wpep_post_square_currency_new ) :
									echo "selected='selected'";
endif;
								?>
								>
								JPY
							</option>
							<option
								value="GBP" 
								<?php
								if ( ! empty( $wpep_post_square_currency_new ) && 'GBP' === $wpep_post_square_currency_new ) :
									echo "selected='selected'";
endif;
								?>
								>
								GBP
							</option>
						</select>
					</div>
				<?php } ?>

						<?php $all_locations = $wpep_live_location_data; ?>
				<div class="form-group">
					<label class="locationHeading">Location:</label>
					<select class="form-control" name="wpep_square_location_id">
						<option>Select Location</option>

								<?php
								foreach ( $all_locations as $location ) {
									$saved_location_id = $wpep_square_location_id;
									if ( false !== $saved_location_id ) {

										if ( $location['location_id'] === $saved_location_id ) {

											$selected = 'selected';

										} else {

											$selected = '';
										}
									}
									echo "<option value='" . esc_attr( $location['location_id'] ) . "'" . esc_html( $selected ) . '>' . esc_html( $location['location_name'] ) . '</option>';
								}
								?>

					</select>
				</div>
			</div>

			<div class="paymentint">
				<label class="title">Other Payment Options</label>
				<div class="wizard-form-checkbox-square-connect">
					<input name="wpep_square_google_pay_live" id="googlePay"
							type="checkbox" 
									<?php
									if ( 'on' === $wpep_square_google_pay_live ) {
										echo esc_html( 'checked' );
									}
									?>
							/>
					<label for="googlePay">Google Pay</label>
				</div>
				<div class="wizard-form-checkbox-square-connect">
					<input name="wpep_square_after_pay_live" id="afterPay" type="checkbox" 
							<?php
							if ( 'on' === $wpep_square_after_pay_live ) {
								echo esc_html( 'checked' );
							}
							?>
							>
					<label for="afterPay">After Pay</label>
				</div>
				<div class="wizard-form-checkbox-square-connect">
					<div class="apple_pay_verification">
						<input class="applePayEnable" name="wpep_square_apple_pay_live" id="applePay" type="checkbox" 
								<?php
								if ( 'on' === $wpep_square_apple_pay_live ) {
									echo esc_html( 'checked' );
								}
								?>
								>
						<label for="applePay">Apple Pay</label>
						<button class="apple_verify_domain" style="display:none;">Verify domain</button>
						<p class="apple_domain_error"></p>
						<input type="hidden" id="apple_domain_verification" name="apple_domain_verification" value="<?php echo esc_attr( wp_create_nonce( 'apple-domain-verification-nonce' ) ); ?>" />
					</div>
				</div>

				<div class="wizard-form-checkbox-square-connect">
					<input name="wpep_square_cash_app_live" id="cashApp" type="checkbox" 
							<?php
							if ( 'on' === $wpep_square_cash_app_live ) {
								echo esc_html( 'checked' );
							}
							?>
							>
					<label for="cashApp">Cash App</label>
				</div>

				<div class="wizard-form-checkbox-square-connect">
					<input name="wpep_square_giftcard_live" id="giftcard" type="checkbox" 
							<?php
							if ( 'on' === $wpep_square_giftcard_live ) {
								echo esc_html( 'checked' );
							}
							?>
							>
					<label for="giftcard">Square Gift Card</label>
				</div>

				<div class="wizard-form-checkbox-square-connect">
					<input name="wpep_square_ach_debit_live" id="achDebit" type="checkbox" 
							<?php
							if ( 'on' === $wpep_square_ach_debit_live ) {
								echo esc_html( 'checked' );
							}
							?>
							>
					<label for="achDebit">ACH Debit</label>
				</div>
				
				<div class="wizard-form-checkbox-square-connect">
					<input name="wpep_square_terminal" id="terminal_pay" type="checkbox" 
							<?php
							if ( 'on' === $wpep_square_terminal ) {
								echo esc_html( 'checked' );
							}
							?>
							>
					<label for="terminal_pay">Terminal Pay</label>
					<br>
					<span id="terminalform">
					<label for="btn_wpep_gen_code" style="margin:0!important">
						Generate device code
						</label>
						<br>
						<input type="text" name="wpep_device_code" id="wpep_device_code" value="<?php echo esc_attr( $wpep_device_code ); ?>"  style="padding: 5px; border: 1px solid #ccc; border-radius: 4px;" >
						<button id="btn_wpep_gen_code" data-formid="<?php echo esc_attr( get_the_ID() ); ?>"  style="padding: 5px 10px; background-color: #2065e0; color: white; border: none; border-radius: 4px; cursor: pointer;">
						Get Code
						</button>
						<br>
					  
						<input type="hidden" name="wpep_device_id" id="wpep_device_id" value="<?php echo esc_attr( $wpep_device_id ); ?>" style="padding: 5px; border: 1px solid #ccc; border-radius: 4px;" >
						</span>
				</div> 
			</div>

			<div class="squareSubscriptionNoticeIndividual">
				<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/danger.png' ); ?>" class="dangerIconNotes" alt="Warning Icon">
				<div class="noteText">
					Note: Disconnecting from square and reconnecting with another account can stop your subscription payments. 
				</div>
			</div>
			<div class="btnFooter d-btn">
				<a href="<?php echo esc_url( $wpep_disconnect_url ); ?>"
					class="btn btnDisconnect wpep-no-save-popup">Disconnect
					Square</a>
			</div>

		</div>

				<?php
			}
			?>
</div>
<!-- live block end -->
</div>

</form>
			<?php
		} else {
			?>
	<main>
		<div class="MainDiv squareAccountScreen">
			<div class="FieldProTag pro_tag">
				<h3 class="FieldHeading">Individual Square Connection </h3>
				<span class="pro_tag" id="pro_tag">Pro</span>
			</div>
			<div class="FieldImg pro_tag">
				<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/square-account-settings.png' ); ?>">
			</div>
		</div>	
	</main>
	<div id="pre-popupModal" class="pre-modal">
		<div class="pre-modal-content">
			<span class="pre-close">&times;</span>
			<div class="premium_popup_content">
				<div class="wp_easypay_logo">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Logo_white.png' ); ?>" class="wpep_logo">
				</div>
				<h3 class="proPopHeading">Enhance your square payment forms with premium features.</h3>
				<div class="featuresListPopup">
					<div class="row">
						<div class="col-6">
							<ul>
								<li><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-tick.png' ); ?>" class="" /> 
									<p>5+ digital wallets</p>
								</li>
								<li><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-tick.png' ); ?>" class="" /> 
									<p>Square product sync</p>
								</li>
							</ul>
						</div>
						<div class="col-6">
							<ul>	
								<li><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-tick.png' ); ?>" class="" /> 
									<p>Square gift card</p>
								</li>
								<li><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-tick.png' ); ?>" class="" /> 
									<p>Manage subscriptions</p>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="integration_button_div">
					<a href="https://wpeasypay.com/pricing?utm_source=plugin&utm_medium=payment_options" class="wpep-no-save-popup"  target="_blank" rel="noopener noreferrer">
						<button type="button" class="upgradeBtn">
							Upgrade now <img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/upgrade-btn-arrow.png' ); ?>" class="" /> 
						</button>
					</a>
				</div>
			</div>
		</div>
	</div>
			<?php
		}
		?>
</main>
