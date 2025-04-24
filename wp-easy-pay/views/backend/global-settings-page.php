<?php
/**
 * Filename: global-settings-page.php
 * Description: global settings page backend.
 *
 * @package WP_Easy_Pay
 */

wp_enqueue_script( 'wpep_backend_js', WPEP_ROOT_URL . 'assets/backend/js/wpep_backend_scripts.js', array(), '1.0.0', true );

if ( isset( $_POST['_wpnoncewpepglobal'] ) && ! empty( $_POST ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnoncewpepglobal'] ) ), '_wpnoncewpepglobal' ) ) {
	$post_custom           = $_POST;
	$payment_mode          = 0;
	$wpep_square_after_pay = 0;
	$location_id_test      = null;

	if ( isset( $post_custom['wpep_square_test_location_id_global'] ) ) {
		$location_id_test = sanitize_text_field( $post_custom['wpep_square_test_location_id_global'] );
	}

	$wpep_email_notification = sanitize_text_field( $post_custom['wpep_email_notification'] );



	if ( isset( $post_custom['wpep_square_test_after_pay'] ) ) {
		$wpep_square_test_after_pay = sanitize_text_field( $post_custom['wpep_square_test_after_pay'] );
	} else {
		$wpep_square_test_after_pay = 'off';
	}

	if ( isset( $post_custom['wpep_square_test_cash_app'] ) ) {
		$wpep_square_test_cash_app = sanitize_text_field( $post_custom['wpep_square_test_cash_app'] );
	} else {
		$wpep_square_test_cash_app = 'off';
	}

	if ( isset( $post_custom['wpep_square_test_ach_debit'] ) ) {
		$wpep_square_test_ach_debit = sanitize_text_field( $post_custom['wpep_square_test_ach_debit'] );
	} else {
		$wpep_square_test_ach_debit = 'off';
	}

	if ( isset( $post_custom['wpep_square_cash_app'] ) ) {
		$wpep_square_cash_app = sanitize_text_field( $post_custom['wpep_square_cash_app'] );
	} else {
		$wpep_square_cash_app = 'off';
	}

	if ( isset( $post_custom['wpep_square_ach_debit'] ) ) {
		$wpep_square_ach_debit = sanitize_text_field( $post_custom['wpep_square_ach_debit'] );
	} else {
		$wpep_square_ach_debit = 'off';
	}



	if ( isset( $post_custom['wpep_square_after_pay'] ) ) {
		$wpep_square_after_pay = sanitize_text_field( $post_custom['wpep_square_after_pay'] );
	}

	if ( isset( $post_custom['wpep_square_payment_mode_global'] ) ) {
		$payment_mode = sanitize_text_field( $post_custom['wpep_square_payment_mode_global'] );
	}

	if ( isset( $post_custom['wpep_square_location_id'] ) ) {
		$location_id = sanitize_text_field( $post_custom['wpep_square_location_id'] );
		update_option( 'wpep_square_location_id', $location_id );
	}

	if ( isset( $post_custom['wpep_square_currency_test'] ) ) {
		$currency = sanitize_text_field( $post_custom['wpep_square_currency_test'] );
		update_option( 'wpep_square_currency_test', $currency );
	}

	update_option( 'wpep_square_test_location_id_global', $location_id_test );
	update_option( 'wpep_square_payment_mode_global', $payment_mode );


	update_option( 'wpep_square_after_pay', $wpep_square_after_pay );
	update_option( 'wpep_square_cash_app', $wpep_square_cash_app );
	update_option( 'wpep_email_notification', $wpep_email_notification );
	if ( isset( $wpep_square_test_after_pay ) ) {
		update_option( 'wpep_square_test_after_pay', $wpep_square_test_after_pay );
	}

	if ( isset( $wpep_square_test_cash_app ) ) {
		update_option( 'wpep_square_test_cash_app', $wpep_square_test_cash_app );
	}

	if ( isset( $wpep_square_test_ach_debit ) ) {
		update_option( 'wpep_square_test_ach_debit', $wpep_square_test_ach_debit );
	}

	if ( isset( $wpep_square_ach_debit ) ) {
		update_option( 'wpep_square_ach_debit', $wpep_square_ach_debit );
	}
} else {
	$current_user_custom     = wp_get_current_user();
	$wpep_email_notification = $current_user_custom->user_email;
}

$wpep_square_payment_mode_global = get_option( 'wpep_square_payment_mode_global', true );

$wpep_square_after_pay = get_option( 'wpep_square_after_pay', true );
$wpep_square_cash_app  = get_option( 'wpep_square_cash_app', true );
$wpep_square_ach_debit = get_option( 'wpep_square_ach_debit', false );

$wpep_email_notification    = get_option( 'wpep_email_notification', false );
$wpep_square_test_after_pay = get_option( 'wpep_square_test_after_pay', false );
$wpep_square_test_cash_app  = get_option( 'wpep_square_test_cash_app', false );
$wpep_square_test_ach_debit = get_option( 'wpep_square_test_ach_debit', false );



if ( empty( $wpep_email_notification ) || false === $wpep_email_notification ) {

	$current_user_custom     = wp_get_current_user();
	$wpep_email_notification = $current_user_custom->user_email;

}

$wpep_square_connect_url         = wpep_create_connect_url( 'global' );
$wpep_create_connect_sandbox_url = wpep_create_connect_sandbox_url( 'global' );


$live_token   = get_option( 'wpep_live_token_upgraded' );
if( isset( $live_token ) && ! empty( $live_token ) ){
	$wpep_sandbox = false;

	$info = array(

		'access_token' => $live_token,
		'client_id'    => WPEP_SQUARE_APP_ID,

	);
	$revoked = 'false';

	
	// $api_client    = wpep_setup_square_with_access_token( $live_token, $wpep_sandbox );
	// $locations_api = new \SquareConnect\Api\LocationsApi( $api_client );
	// $locations     = $locations_api->listLocations()->getLocations();
	if ( 'yes' == $wpep_sandbox ) {

		$url = 'https://connect.squareupsandbox.com/v2/locations';

	} else {

		$url = 'https://connect.squareup.com/v2/locations';

	}
	//remote request
	
	$headers = array(
		'Square-Version' => '2021-03-17',
		'Authorization'  => 'Bearer ' . $live_token,
		'Content-Type'   => 'application/json'
	);
	
	$response = wp_remote_get($url, array(
		'headers'  =>  $headers
		)
	);
	
	$response_body = json_decode(wp_remote_retrieve_body($response));
			
	if ( $response['response']['code'] != 200 || 'ACCESS_TOKEN_REVOKED' === @$response_body->errors[0]->code || 'UNAUTHORIZED' === @$response_body->errors[0]->code ) {
		$revoked = 'true';
	}
}

?>

<form class="wpeasyPay-form" method="post" action="#">
	<div class="contentWrap wpeasyPay">
	<div class="contentHeader">
		<h3 class="blocktitle">Square Connect</h3>
		<div class="swtichWrap">
		<input type="checkbox" id="on-off" name="wpep_square_payment_mode_global" class="switch-input" 
		<?php
		if ( 'on' === $wpep_square_payment_mode_global || ( isset( $_COOKIE['wpep-payment-mode'] ) && 'live' === $_COOKIE['wpep-payment-mode'] ) ) {
			echo esc_html( 'checked' );
		}
		?>
		/>
		<label for="on-off" class="switch-label">
			<span class="toggle--on toggle--option wpep_global_mode_switch" data-mode="live">Live Payment</span>
			<span class="toggle--off toggle--option wpep_global_mode_switch" data-mode="test">Test Payment</span>
		</label>
		</div>
	</div>
	<div class="contentBlock">
		<div class="squareSettings">
		<div class="settingBlock">
			<label>Notifications Email</label>
			<input type="text" class="form-control" name="wpep_email_notification" value="<?php echo esc_attr( $wpep_email_notification ); ?>" placeholder="abc@domain.com">
		</div>
		</div>

		<div class="testPayment paymentView" id="wpep_spmgt">
		<?php
			$wpep_square_test_token = get_option( 'wpep_square_test_token_global' );

		if ( empty( $wpep_square_test_token ) ) {
			?>
			<div class="squareConnect">
				<div class="squareConnectwrap">
					
					<h2>Connect your square (sandbox) account now!</h2>

					<?php
			$get = $_GET;
			if ( isset( $get['type'] ) && 'bad_request.missing_parameter' === $get['type'] ) {
					?>

					<p style="color: red;"> You have denied WP EASY PAY the permission to access your Square account. Please connect again to and click allow to complete OAuth. </p>

					<?php
				}
				?>

				<div class="center-container">
					<div class="custom-notification">
						<div class="notification-icon">
							<span class="noti-icon">&#9432;</span>
						</div>
						<div class="notification-content">
							<p class="notification-text">
								Make sure to launch <a class="wpep-highlight" href="https://developer.squareup.com/console/en/sandbox-test-accounts">seller test account</a> in the same browser from developer dashboard before connecting your Square Sandbox account.
							</p>
						</div>
					</div>
				</div>
				<a href="<?php echo esc_url( $wpep_create_connect_sandbox_url ); ?>" class="btn btn-primary btn-square">Connect Square (sandbox)</a>

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
			if ( get_option( 'wpep_square_currency_test', false ) !== false ) {
				?>
				<div class="form-group">
					<label>Country Currency</label>
					<select name="wpep_square_test_currency_new" class="form-control" disabled="disabled">
						<option value="USD" 
						<?php
						if ( ! empty( get_option( 'wpep_square_currency_test' ) ) && 'USD' === get_option( 'wpep_square_currency_test' ) ) :
							echo esc_html( "selected='selected'" );
endif;
						?>
						>USD</option>
						<option value="CAD" 
						<?php
						if ( ! empty( get_option( 'wpep_square_currency_test' ) ) && 'CAD' === get_option( 'wpep_square_currency_test' ) ) :
							echo esc_html( "selected='selected'" );
endif;
						?>
						>CAD</option>
						<option value="AUD" 
						<?php
						if ( ! empty( get_option( 'wpep_square_currency_test' ) ) && 'AUD' === get_option( 'wpep_square_currency_test' ) ) :
							echo esc_html( "selected='selected'" );
endif;
						?>
						>AUD</option>
						<option value="JPY" 
						<?php
						if ( ! empty( get_option( 'wpep_square_currency_test' ) ) && 'JPY' === get_option( 'wpep_square_currency_test' ) ) :
							echo esc_html( "selected='selected'" );
endif;
						?>
						>JPY</option>
						<option value="GBP" 
						<?php
						if ( ! empty( get_option( 'wpep_square_currency_test' ) ) && 'GBP' === get_option( 'wpep_square_currency_test' ) ) :
							echo esc_html( "selected='selected'" );
endif;
						?>
						>GBP</option>
						<option value="EUR" 
						<?php
						if ( ! empty( get_option( 'wpep_square_currency_test' ) ) && 'EUR' === get_option( 'wpep_square_currency_test' ) ) :
							echo esc_html( "selected='selected'" );
endif;
						?>
						>EUR</option>
					</select>
				</div>
				<?php } ?>

				<?php $all_locations = get_option( 'wpep_test_location_data', false ); ?>
				<div class="form-group">
					<label>Location:</label>
					<select class="form-control" name="wpep_square_test_location_id_global">
					<option>Select Location</option>
					<?php
					if ( isset( $all_locations ) && ! empty( $all_locations ) && false !== $all_locations ) {

						foreach ( $all_locations as $location ) {

							if ( is_array( $location ) ) {

								if ( isset( $location['location_id'] ) ) {

											$location_id = $location['location_id'];

								}

								if ( isset( $location['location_name'] ) ) {

										$location_name = $location['location_name'];

								}
							}

							if ( is_object( $location ) ) {

								if ( isset( $location->id ) ) {

											$location_id = $location->id;

								}

								if ( isset( $location->name ) ) {

										$location_name = $location->name;
								}
							}

							$saved_location_id = get_option( 'wpep_square_test_location_id_global', false );
							$selected = '';
							if ( false !== $saved_location_id ) {

								if ( $saved_location_id === $location_id ) {

											$selected = 'selected';

								}
							}
										echo "<option value='" . esc_attr( $location_id ) . "'" . esc_html( $selected ) . '>' . esc_html( $location_name ) . '</option>';
						}
					}

					?>
					</select>
				</div>
				</div>

				<div class="paymentint">
				<label class="title">Other Payment Options</label>
				<div class="wizard-form-checkbox ">
					<input id="afterPayTest" name="wpep_square_test_after_pay" value="on" type="checkbox"
					<?php
					if ( 'on' === $wpep_square_test_after_pay ) {
						echo 'checked';
					}
					?>
					>
					<label for="afterPayTest">After Pay</label>
				</div>

				<div class="wizard-form-checkbox">
					<input id="cashAppTest" name="wpep_square_test_cash_app" value="on" type="checkbox" 
					<?php
					if ( 'on' === $wpep_square_test_cash_app ) {
						echo 'checked';
					}
					?>
					>
					<label for="cashAppTest">Cash App</label>
				</div>

				<div class="wizard-form-checkbox">
					<input id="achDebitTest" name="wpep_square_test_ach_debit" value="on" type="checkbox" 
					<?php
					if ( 'on' === $wpep_square_test_ach_debit ) {
						echo 'checked';
					}
					?>
					>
					<label for="achDebitTest">ACH Debit</label>
				</div>

				</div>
				<p style="color: red;"> Note: Disconnecting from Square and Reconnecting with another account can stop your subscription payments. </p>
				<div class="btnFooter d-btn">
				<button type="submit" class="btn btn-primary"> Save Settings </button>
				<a href="<?php echo esc_url( get_option( 'wpep_square_test_disconnect_url', false ) ); ?>" class="btn btnDiconnect">Disconnect
					Square</a>
				</div>
			</div>
			<?php
		}
		?>

		</div>

		<div class="livePayment paymentView" id="wpep_spmgl">
		<?php
		$wpep_square_live_token = get_option( 'wpep_live_token_upgraded' );

			if ( empty( $wpep_square_live_token ) ) {
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
			<a href="<?php echo esc_url( $wpep_square_connect_url ); ?>" class="btn btn-primary btn-square">Connect Square</a>

			<a class="connectSquarePop" href="https://wpeasypay.com/documentation/#global-settings-live-mode" target="_blank">

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
			if ( '' !== get_option( 'wpep_square_currency_new' ) ) {
				?>
			<div class="form-group">
				<label>Country Currency</label>
				<select name="wpep_square_currency_new" class="form-control" disabled="disabled">
					<option value="USD" 
					<?php
					if ( ! empty( get_option( 'wpep_square_currency_new' ) ) && 'USD' === get_option( 'wpep_square_currency_new' ) ) :
						echo esc_html( "selected='selected'" );
endif;
					?>
					>USD</option>
					<option value="CAD" 
					<?php
					if ( ! empty( get_option( 'wpep_square_currency_new' ) ) && 'CAD' === get_option( 'wpep_square_currency_new' ) ) :
						echo esc_html( "selected='selected'" );
endif;
					?>
					>CAD</option>
					<option value="AUD" 
					<?php
					if ( ! empty( get_option( 'wpep_square_currency_new' ) ) && 'AUD' === get_option( 'wpep_square_currency_new' ) ) :
						echo esc_html( "selected='selected'" );
endif;
					?>
					>AUD</option>
					<option value="JPY" 
					<?php
					if ( ! empty( get_option( 'wpep_square_currency_new' ) ) && 'JPY' === get_option( 'wpep_square_currency_new' ) ) :
						echo esc_html( "selected='selected'" );
endif;
					?>
					>JPY</option>
					<option value="GBP" 
					<?php
					if ( ! empty( get_option( 'wpep_square_currency_new' ) ) && 'GBP' === get_option( 'wpep_square_currency_new' ) ) :
						echo esc_html( "selected='selected'" );
endif;
					?>
					>GBP</option>

					<option value="EUR" 
					<?php
					if ( ! empty( get_option( 'wpep_square_currency_new' ) ) && 'EUR' === get_option( 'wpep_square_currency_new' ) ) :
						echo esc_html( "selected='selected'" );
endif;
					?>
					>EUR</option>
				</select>
			</div>
			<?php } ?>

				<?php $all_locations = get_option( 'wpep_live_location_data', false ); ?>
			<div class="form-group">
				<label>Location:</label>
				<select class="form-control" name="wpep_square_location_id">
				<option>Select Location</option>

					<?php

					if ( $all_locations && ! empty( $all_locations ) && false !== $all_locations ) {

						foreach ( $all_locations as $location ) {

							if ( is_array( $location ) ) {

								if ( isset( $location['location_id'] ) ) {
									$location_id = $location['location_id'];
								}

								if ( isset( $location['location_name'] ) ) {
									$location_name = $location['location_name'];
								}
							}

							if ( is_object( $location ) ) {

								if ( isset( $location->id ) ) {
									$location_id = $location->id;
								}


								if ( isset( $location->name ) ) {
									$location_name = $location->name;
								}
							}

							$saved_location_id = get_option( 'wpep_square_location_id', false );
							if ( false !== $saved_location_id ) {

								if ( $saved_location_id === $location_id ) {
									$selected = 'selected';
								} else {
									$selected = '';
								}
							}
									echo "<option value='" . esc_attr( $location_id ) . "'" . esc_html( $selected ) . '>' . esc_html( $location_name ) . '</option>';
						}
					}

					?>

				</select>
			</div>
			</div>


		<div class="paymentint">
			<label class="title">Other Payment Options</label>
		  
			<div class="wizard-form-checkbox ">
			<input id="afterPayLive" name="wpep_square_after_pay" value="on" type="checkbox"
			
			<?php
			if ( 'on' === $wpep_square_after_pay ) {
				echo esc_html( 'checked' );
			}
			?>
			>
			<label for="afterPayLive">After Pay</label>
			</div>

			<div class="wizard-form-checkbox">
			<input id="cashAppLive" name="wpep_square_cash_app" value="on" type="checkbox"
			
			<?php
			if ( 'on' === $wpep_square_cash_app ) {
				echo esc_html( 'checked' );
			}
			?>
			
			>
			<label for="cashAppLive">Cash App</label>
			</div>

			<div class="wizard-form-checkbox">
			<input id="achDebitLive" name="wpep_square_ach_debit" value="on" type="checkbox"
			<?php
			if ( 'on' === $wpep_square_ach_debit ) {
				echo esc_html( 'checked' );
			}
			?>
			>
			<label for="achDebitLive">ACH Debit</label>
			</div>

		</div>

			<?php if ( 'true' === $revoked ) { ?>
		<p style="color: red;"> Seems like your OAuth token is revoked by Square. Please disconnect your account and reconnect to resolve the issue or contact support.  </p>
		<?php } ?>

			<?php if ( 'true' !== $revoked ) { ?>
		<p style="color: red;"> Note: Disconnecting from Square and Reconnecting with another account can stop your subscription payments. </p>
		<?php } ?>
		<div class="btnFooter d-btn">
			<button type="submit" class="btn btn-primary"> Save Settings </button>
			<a href="<?php echo esc_url( get_option( 'wpep_square_disconnect_url', false ) ); ?>" class="btn btnDiconnect">Disconnect
			Square</a>
		</div>
			
			<?php
		}

		?>
		<input type="hidden" id="_wpnoncewpepglobal" name="_wpnoncewpepglobal" value="<?php echo esc_attr( wp_create_nonce( '_wpnoncewpepglobal' ) ); ?>" />

		</div>

	</div>
	
</form>
</div>
