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

if ( isset( $_POST['wp_global_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ), 'wp_global_nonce' ) ) {
	exit;
}

$get = array_map( 'sanitize_text_field', wp_unslash( $_GET ) );

if ( isset( $_POST ) && ! empty( $_POST ) ) {
	$post_custom            = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$payment_mode           = 0;
	$wpep_square_google_pay = 0;
	$wpep_square_apple_pay  = 0;
	$wpep_square_after_pay  = 0;

	$wpep_square_test_google_pay_global = 0;
	$location_id_test                   = null;

	if ( isset( $post_custom['wpep_square_test_location_id_global'] ) ) {
		$location_id_test = sanitize_text_field( $post_custom['wpep_square_test_location_id_global'] );
	}

	$wpep_email_notification = sanitize_text_field( $post_custom['wpep_email_notification'] );
	if ( wepp_fs()->is__premium_only() ) {
		$wpep_device_id   = isset( $post_custom['wpep_device_id'] ) ? sanitize_text_field( $post_custom['wpep_device_id'] ) : '';
		$wpep_device_code = isset( $post_custom['wpep_device_code'] ) ? sanitize_text_field( $post_custom['wpep_device_code'] ) : '';

		if ( isset( $post_custom['wpep_square_google_pay'] ) ) {
			$wpep_square_google_pay = sanitize_text_field( $post_custom['wpep_square_google_pay'] );
		} else {
			$wpep_square_google_pay = 'off';
		}
		if ( isset( $post_custom['wpep_square_test_apple_pay'] ) ) {
			$wpep_square_test_apple_pay = sanitize_text_field( $post_custom['wpep_square_test_apple_pay'] );
		} else {
			$wpep_square_test_apple_pay = 'off';
		}
	}

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

	if ( isset( $post_custom['wpep_square_test_giftcard'] ) ) {
		$wpep_square_test_giftcard = sanitize_text_field( $post_custom['wpep_square_test_giftcard'] );
	} else {
		$wpep_square_test_giftcard = 'off';
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

	if ( isset( $post_custom['wpep_square_giftcard'] ) ) {
		$wpep_square_giftcard = sanitize_text_field( $post_custom['wpep_square_giftcard'] );
	} else {
		$wpep_square_giftcard = 'off';
	}

	if ( isset( $post_custom['wpep_square_ach_debit'] ) ) {
		$wpep_square_ach_debit = sanitize_text_field( $post_custom['wpep_square_ach_debit'] );
	} else {
		$wpep_square_ach_debit = 'off';
	}

	if ( wepp_fs()->is__premium_only() ) {
		if ( isset( $post_custom['wpep_square_terminal'] ) ) {
			$wpep_square_terminal = sanitize_text_field( $post_custom['wpep_square_terminal'] );
		} else {
			$wpep_square_terminal = 'off';
		}
		if ( isset( $post_custom['wpep_square_test_google_pay_global'] ) ) {
			$wpep_square_test_google_pay_global = sanitize_text_field( $post_custom['wpep_square_test_google_pay_global'] );
		}

		if ( isset( $post_custom['wpep_square_apple_pay'] ) ) {
			$wpep_square_apple_pay = sanitize_text_field( $post_custom['wpep_square_apple_pay'] );
		}
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
	update_option( 'wpep_square_giftcard', $wpep_square_giftcard );
	update_option( 'wpep_email_notification', $wpep_email_notification );

	if ( wepp_fs()->is__premium_only() ) {
		update_option( 'wpep_square_test_google_pay_global', $wpep_square_test_google_pay_global );
		update_option( 'wpep_square_google_pay', $wpep_square_google_pay );
		update_option( 'wpep_square_apple_pay', $wpep_square_apple_pay );
		update_option( 'wpep_device_code', $wpep_device_code );
		update_option( 'wpep_device_id', $wpep_device_id );

		if ( isset( $wpep_square_test_apple_pay ) ) {
			update_option( 'wpep_square_test_apple_pay', $wpep_square_test_apple_pay );
		}
		if ( isset( $wpep_square_terminal ) ) {
			update_option( 'wpep_square_terminal', $wpep_square_terminal );
		}
	}
	if ( isset( $wpep_square_test_after_pay ) ) {
		update_option( 'wpep_square_test_after_pay', $wpep_square_test_after_pay );
	}

	if ( isset( $wpep_square_test_cash_app ) ) {
		update_option( 'wpep_square_test_cash_app', $wpep_square_test_cash_app );
	}

	if ( isset( $wpep_square_test_giftcard ) ) {
		update_option( 'wpep_square_test_giftcard', $wpep_square_test_giftcard );
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

if ( wepp_fs()->is__premium_only() ) {
	$wpep_square_google_pay             = get_option( 'wpep_square_google_pay', true );
	$wpep_square_apple_pay              = get_option( 'wpep_square_apple_pay', true );
	$wpep_square_test_google_pay_global = get_option( 'wpep_square_test_google_pay_global', true );
	$wpep_square_terminal               = get_option( 'wpep_square_terminal', false );
	$wpep_device_id                     = get_option( 'wpep_device_id', false );
	$wpep_device_code                   = get_option( 'wpep_device_code', false );
	$wpep_square_test_apple_pay         = get_option( 'wpep_square_test_apple_pay', false );
}

$wpep_square_payment_mode_global = get_option( 'wpep_square_payment_mode_global', true );
$wpep_square_after_pay           = get_option( 'wpep_square_after_pay', true );
$wpep_square_cash_app            = get_option( 'wpep_square_cash_app', true );
$wpep_square_giftcard            = get_option( 'wpep_square_giftcard', true );
$wpep_square_ach_debit           = get_option( 'wpep_square_ach_debit', false );
$wpep_email_notification         = get_option( 'wpep_email_notification', false );
$wpep_square_test_after_pay      = get_option( 'wpep_square_test_after_pay', false );
$wpep_square_test_cash_app       = get_option( 'wpep_square_test_cash_app', false );
$wpep_square_test_giftcard       = get_option( 'wpep_square_test_giftcard', false );
$wpep_square_test_ach_debit      = get_option( 'wpep_square_test_ach_debit', false );



if ( empty( $wpep_email_notification ) || false === $wpep_email_notification ) {

	$current_user_custom     = wp_get_current_user();
	$wpep_email_notification = $current_user_custom->user_email;

}

$wpep_square_connect_url         = wpep_create_connect_url( 'global' );
$wpep_create_connect_sandbox_url = wpep_create_connect_sandbox_url( 'global' );


$live_token = get_option( 'wpep_live_token_upgraded' );
if ( isset( $live_token ) && ! empty( $live_token ) ) {
	$wpep_sandbox = false;

	$info = array(

		'access_token' => $live_token,
		'client_id'    => WPEP_SQUARE_APP_ID,

	);

	$revoked = 'false';
	if ( 'yes' === $wpep_sandbox ) {

		$url = 'https://connect.squareupsandbox.com/v2/locations';

	} else {

		$url = 'https://connect.squareup.com/v2/locations';

	}
	// remote request.

	$headers = array(
		'Square-Version' => '2021-03-17',
		'Authorization'  => 'Bearer ' . $live_token,
		'Content-Type'   => 'application/json',
	);

	$response = wp_remote_get(
		$url,
		array(
			'headers' => $headers,
		)
	);

	$response_body = json_decode( wp_remote_retrieve_body( $response ) );
	if ( isset( $response['response']['code'] ) && 200 !== $response['response']['code'] ) {
		// Handle non-200 response code.
		$revoked = 'true';
	} elseif ( isset( $response_body ) && is_object( $response_body ) ) {
		// Ensure $response_body is not null and is an object.
		if ( isset( $response_body->errors ) && is_array( $response_body->errors ) && isset( $response_body->errors[0]->code ) ) {
			if ( 'ACCESS_TOKEN_REVOKED' === $response_body->errors[0]->code || 'UNAUTHORIZED' === $response_body->errors[0]->code ) {
				$revoked = 'true';
			}
		}
	}
}
$wpep_square_test_token = get_option( 'wpep_square_test_token_global' );
$wpep_square_live_token = get_option( 'wpep_live_token_upgraded' );

?>

<form class="wpeasyPay-form" method="post" action="#">
	<div class="contentWrapGlobal wpeasyPay">
	<div class="contentHeaderGlobal">
		<h3 class="blocktitle">Square Connect</h3>
		<?php
		$wpep_square_test_token = get_option( 'wpep_square_test_token_global' );
		$wpep_square_live_token = get_option( 'wpep_live_token_upgraded' );
		
		if ( ( 'on' === $wpep_square_payment_mode_global && ! empty( $wpep_square_live_token ) ) || 
			( 'on' !== $wpep_square_payment_mode_global && ! empty( $wpep_square_test_token ) ) ) {
			if ( 'on' === $wpep_square_payment_mode_global ) {
				$disconnect_url = get_option( 'wpep_square_disconnect_url', false );
			} else {
				$disconnect_url = get_option( 'wpep_square_test_disconnect_url', false );
			}
			if ( $disconnect_url ) {
				?>
				<div class="disconnect-btn-header">
					<a href="<?php echo esc_url( $disconnect_url ); ?>" class="btn btnDisconnect btn-sm">Disconnect Square</a>
				</div>
				<?php
			}
		}
		?>
		
		<div class="swtichWrap">
		<input type="checkbox" id="on-off" name="wpep_square_payment_mode_global" class="switch-input" 
		<?php
		checked( $wpep_square_payment_mode_global, 'on', 1 );
		?>
		/>
		<label for="on-off" class="switch-label">
			<span class="toggle--on toggle--option wpep_global_mode_switch" data-mode="live">Live Payment</span>
			<span class="toggle--off toggle--option wpep_global_mode_switch" data-mode="test">Test Payment</span>
		</label>
	</div>
</div>
	<div class="contentBlockGlobal">
		<div class="squareSettingsGlobal">
		<div class="settingBlockGlobal">
			<label class="emailNotificationLabel">Notifications email</label>
			<input type="text" class="squareEmailFieldGlobal" name="wpep_email_notification" value="<?php echo esc_attr( $wpep_email_notification ); ?>" placeholder="abc@domain.com">
		</div>
		</div>

		<div class="testPayment paymentView" id="wpep_spmgt">
		<?php
			

		if ( false === $wpep_square_test_token ) {
			$disconnect_btn = false;
			?>
			<div class="squareConnect">
				<div class="squareConnectwrap">
				<h2>Connect your square (sandbox) account now!</h2>
				
				<?php
				if ( isset( $get['type'] ) && 'bad_request.missing_parameter' === $get['type'] ) {
					?>

					<p style="color: red;"> You have denied WP EASY PAY the permission to access your Square account. Please connect again to and click allow to complete OAuth. </p>

					<?php
				}
				?>
				<div class="noteInSquareConnectGlobal">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/danger.png' ); ?>" class="dangerIconNotes" alt="Warning Icon">
					<div class="noteText">
						Make sure to launch 
						<a class="wpep-highlight" href="https://developer.squareup.com/console/en/sandbox-test-accounts">seller test account</a> 
						in the same browser from developer dashboard before connecting your Square Sandbox account.
					</div>
				</div>
				<a href="<?php echo esc_url( $wpep_create_connect_sandbox_url ); ?>" class="btn btn-primary connectSquareBtn">
					<div class="SquareConnectIndivi">
						<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/square-btn-icon.png' ); ?>" class="SquareIconConnect" alt="Warning Icon"> 
						Connect Square (Sandbox)
					</div>
				</a>

				<div> 
					<p class="sandboxAuthMsg">
						The sandbox OAuth is for testing purpose by connecting and activating this you 
						will be able to make test transactions and to see how your form will work for the customers.
					</p>  
				</div>

				</div>
			</div>

			<?php
		} else {
			?>

			<div class="squareConnected">
				<h3 class="titleSquare">Square is connected <i class="fa fa-check-square" aria-hidden="true"></i></h3>
				<div class="wpeasyPay__body">

			<?php
			if ( false !== get_option( 'wpep_square_currency_test', false ) ) {
				?>
				<div class="form-group">
					<label class="locationHeading">Country currency</label>
					<select name="wpep_square_test_currency_new" class="form-control countryCurrencySquare" disabled="disabled">
						<option value="USD" 
						<?php
						if ( ! empty( get_option( 'wpep_square_currency_test' ) ) && 'USD' === get_option( 'wpep_square_currency_test' ) ) :
							echo 'selected="selected"';
endif;
						?>
						>USD</option>
						<option value="CAD" 
						<?php
						if ( ! empty( get_option( 'wpep_square_currency_test' ) ) && 'CAD' === get_option( 'wpep_square_currency_test' ) ) :
							echo 'selected="selected"';
endif;
						?>
						>CAD</option>
						<option value="AUD" 
						<?php
						if ( ! empty( get_option( 'wpep_square_currency_test' ) ) && 'AUD' === get_option( 'wpep_square_currency_test' ) ) :
							echo 'selected="selected"';
endif;
						?>
						>AUD</option>
						<option value="JPY" 
						<?php
						if ( ! empty( get_option( 'wpep_square_currency_test' ) ) && 'JPY' === get_option( 'wpep_square_currency_test' ) ) :
							echo 'selected="selected"';
endif;
						?>
						>JPY</option>
						<option value="GBP" 
						<?php
						if ( ! empty( get_option( 'wpep_square_currency_test' ) ) && 'GBP' === get_option( 'wpep_square_currency_test' ) ) :
							echo 'selected="selected"';
endif;
						?>
						>GBP</option>
						<option value="EUR" 
						<?php
						if ( ! empty( get_option( 'wpep_square_currency_test' ) ) && 'EUR' === get_option( 'wpep_square_currency_test' ) ) :
							echo 'selected="selected"';
endif;
						?>
						>EUR</option>
					</select>
				</div>
				<?php } ?>

				<?php $all_locations = get_option( 'wpep_test_location_data', false ); ?>
				<div class="form-group">
					<label class="locationHeading">Location:</label>
					<select class="form-control" name="wpep_square_test_location_id_global">
					<option>Select location</option>
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
							$selected          = '';
							if ( false !== $saved_location_id ) {
								if ( $saved_location_id === $location_id ) {
									$selected = 'selected';
								}
							}
							echo "<option value='" . esc_attr( $location_id ) . "' " . esc_attr( $selected ) . '>' . esc_html( $location_name ) . '</option>';
						}
					}

					?>
					</select>
				</div>
				</div>

				<div class="paymentint">
				<label class="title">Other payment options</label>
				<?php if ( wepp_fs()->is__premium_only() ) { ?>
				<div class="wizard-form-checkbox-square-connect">
				<input id="googlePayTest" name="wpep_square_test_google_pay_global" value="on" type="checkbox"
					<?php
					if ( 'on' === $wpep_square_test_google_pay_global ) {
						echo 'checked';
					}
					?>
					>
					<label for="googlePayTest">Google Pay</label>

				</div>
				<div class="wizard-form-checkbox-square-connect">
					<div class="apple_pay_verification">
						<input id="applePayTest" class="applePayEnableTest" name="wpep_square_test_apple_pay" value="on" type="checkbox" 
						<?php
						if ( 'on' === $wpep_square_test_apple_pay ) {
							echo 'checked';
						}
						?>
						>
						<label for="applePayTest">Apple Pay</label>
						<button class="apple_verify_domain_test" style="display:none;">Verify domain</button>
						<p class="apple_domain_error_test"></p>
						<input type="hidden" id="apple_domain_verification" name="apple_domain_verification" value="<?php echo esc_attr( wp_create_nonce( 'apple-domain-verification-nonce' ) ); ?>" />
					</div>
				</div>
				<?php } ?>
				<div class="wizard-form-checkbox-square-connect">
					<input id="afterPayTest" name="wpep_square_test_after_pay" value="on" type="checkbox"
					<?php
					if ( 'on' === $wpep_square_test_after_pay ) {
						echo 'checked';
					}
					?>
					>
					<label for="afterPayTest">After Pay</label>
				</div>
				<div class="wizard-form-checkbox-square-connect">
					<input id="cashAppTest" name="wpep_square_test_cash_app" value="on" type="checkbox" 
					<?php
					if ( 'on' === $wpep_square_test_cash_app ) {
						echo 'checked';
					}
					?>
					>
					<label for="cashAppTest">Cash App</label>
				</div>
				<?php if ( wepp_fs()->is__premium_only() ) { ?>
				<div class="wizard-form-checkbox-square-connect">
					<input id="giftcard-test" name="wpep_square_test_giftcard" value="on" type="checkbox" 
					<?php
					if ( 'on' === $wpep_square_test_giftcard ) {
						echo 'checked';
					}
					?>
					>
					<label for="giftcard-test">Square Gift Card</label>
				</div>
				<?php } ?>
				<div class="wizard-form-checkbox-square-connect">
					<input id="achDebitTest" name="wpep_square_test_ach_debit" value="on" type="checkbox" 
					<?php
					if ( 'on' === $wpep_square_test_ach_debit ) {
						echo 'checked';
					}
					?>
					>
					<label for="achDebitTest">ACH Debit</label>
				</div>
				<?php if ( ! wepp_fs()->is__premium_only() ) { ?>
					<div class="wizard-form-checkbox-square-connect extraSpacing">
						<input id="googlePayTest" name="wpep_square_test_google_pay" value="on" type="checkbox" checked disabled>
						<label class="googlePayTest" for="googlePayTest">Google Pay</label>
						<span class="pro_tag" id="pro_tag">Pro</span>
					</div>
					<div class="wizard-form-checkbox-square-connect extraSpacing">
						<input id="applePayTest" name="wpep_square_test_apple_pay" value="on" type="checkbox" checked disabled>
						<label class="applePayTest" for="applePayTest">Apple Pay</label>
						<span class="pro_tag" id="pro_tag">Pro</span>
					</div>
					<div class="wizard-form-checkbox-square-connect extraSpacing">	
						<input id="giftcardTest" name="wpep_square_test_giftcard" value="on" type="checkbox" checked disabled>
						<label class="giftcard" for="giftcard">Square Gift Card</label>
						<span class="pro_tag" id="pro_tag">Pro</span>
					</div>
				<?php } ?>
				</div>
				<div class="squareSubscriptionNoticeGlobal">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/danger.png' ); ?>" class="dangerIconNotes" alt="Warning Icon">
					<div class="noteText">
						Note: Disconnecting from square and reconnecting with another account can stop your subscription payments. 
					</div>
				</div>
				<div class="btnFooter d-btn">
					<button type="submit" class="btn btn-primary saveSettingSquareConnectionBtn"> Save Settings </button>
					<a href="<?php echo esc_url( get_option( 'wpep_square_test_disconnect_url', false ) ); ?>" class="btn btnDisconnect">Disconnect
					Square</a>
				</div>
			</div>
			<?php
		}
		?>

		</div>
		<div class="livePayment paymentView liveActive" id="wpep_spmgl">
		<?php
		$wpep_square_live_token = get_option( 'wpep_live_token_upgraded' );
		if ( empty( $wpep_square_live_token ) || false === $wpep_square_live_token ) {
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

			<a href="<?php echo esc_url( $wpep_square_connect_url ); ?>" class="btn btn-primary connectSquareBtn">
				<div class="SquareConnectIndivi">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/square-btn-icon.png' ); ?>" class="SquareIconConnect" alt="Warning Icon"> 
					Connect Square
				</div>
			</a>

			<a class="connectSquarePop" href="https://wpeasypay.com/documentation/#global-settings-live-mode" target="_blank" rel="noopener noreferrer">

			How to connect your live square account.

			</a>

			</div>
		</div>

			<?php
		} else {
			?>

		<div class="squareConnected">
			<h3 class="titleSquare">Square is connected <i class="fa fa-check-square" aria-hidden="true"></i></h3>
			<div class="wpeasyPay__body">

			<?php
			if ( '' !== get_option( 'wpep_square_currency_new' ) ) {
				?>
			<div class="form-group">
				<label class="locationHeading">Country currency</label>
				<select name="wpep_square_currency_new" class="form-control countryCurrencySquare" disabled="disabled">
					<option value="USD" 
					<?php
					if ( ! empty( get_option( 'wpep_square_currency_new' ) ) && 'USD' === get_option( 'wpep_square_currency_new' ) ) :
						echo 'selected="selected"';
endif;
					?>
					>USD</option>
					<option value="CAD" 
					<?php
					if ( ! empty( get_option( 'wpep_square_currency_new' ) ) && 'CAD' === get_option( 'wpep_square_currency_new' ) ) :
						echo 'selected="selected"';
endif;
					?>
					>CAD</option>
					<option value="AUD" 
					<?php
					if ( ! empty( get_option( 'wpep_square_currency_new' ) ) && 'AUD' === get_option( 'wpep_square_currency_new' ) ) :
						echo 'selected="selected"';
endif;
					?>
					>AUD</option>
					<option value="JPY" 
					<?php
					if ( ! empty( get_option( 'wpep_square_currency_new' ) ) && 'JPY' === get_option( 'wpep_square_currency_new' ) ) :
						echo 'selected="selected"';
endif;
					?>
					>JPY</option>
					<option value="GBP" 
					<?php
					if ( ! empty( get_option( 'wpep_square_currency_new' ) ) && 'GBP' === get_option( 'wpep_square_currency_new' ) ) :
						echo 'selected="selected"';
endif;
					?>
					>GBP</option>

					<option value="EUR" 
					<?php
					if ( ! empty( get_option( 'wpep_square_currency_new' ) ) && 'EUR' === get_option( 'wpep_square_currency_new' ) ) :
						echo 'selected="selected"';
endif;
					?>
					>EUR</option>
				</select>
			</div>
			<?php } ?>

				<?php $all_locations = get_option( 'wpep_live_location_data', false ); ?>
			<div class="form-group">
				<label class="locationHeading">Location:</label>
				<select class="form-control" name="wpep_square_location_id">
				<option>Select location</option>

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
							$saved_location_id = get_option( 'wpep_square_location_id', false );
							$selected          = '';
							if ( false !== $saved_location_id ) {

								if ( $saved_location_id === $location_id ) {
									$selected = 'selected';
								} else {
									$selected = '';
								}
							}
									echo "<option value='" . esc_attr( $location_id ) . "' " . esc_attr( $selected ) . '>' . esc_html( $location_name ) . '</option>';
						}
					}

					?>

				</select>
			</div>
			</div>


		<div class="paymentint">
			<label class="title">Other payment options</label>
			<?php if ( wepp_fs()->is__premium_only() ) { ?>
			<div class="wizard-form-checkbox-square-connect">
				<input id="googlePayLive" name="wpep_square_google_pay" value="on" type="checkbox"
					<?php
					if ( 'on' === $wpep_square_google_pay ) {
						echo 'checked';
					}
					?>
					>
				<label for="googlePayLive">Google Pay</label>
			</div>
			<div class="wizard-form-checkbox-square-connect">
				<div class="apple_pay_verification">
					<input id="applePayLive" class="applePayEnable" name="wpep_square_apple_pay" value="on" type="checkbox"
					
						<?php
						if ( 'on' === $wpep_square_apple_pay ) {
							echo 'checked';
						}
						?>
					
					>
					<label for="applePayLive">Apple Pay</label>
					<button class="apple_verify_domain" style="display:none;">Verify domain</button>
					<p class="apple_domain_error"></p>
					<input type="hidden" id="apple_domain_verification" name="apple_domain_verification" value="<?php echo esc_attr( wp_create_nonce( 'apple-domain-verification-nonce' ) ); ?>" />
				</div>
			</div>
			<?php } ?>

			<div class="wizard-form-checkbox-square-connect">
			<input id="afterPayLive" name="wpep_square_after_pay" value="on" type="checkbox"
			
			<?php
			if ( 'on' === $wpep_square_after_pay ) {
				echo 'checked';
			}
			?>
			>
			<label for="afterPayLive">After Pay</label>
			</div>
			<div class="wizard-form-checkbox-square-connect">
			<input id="cashAppLive" name="wpep_square_cash_app" value="on" type="checkbox"
			
			<?php
			if ( 'on' === $wpep_square_cash_app ) {
				echo 'checked';
			}
			?>
			
			>
			<label for="cashAppLive">Cash App</label>
			</div>
			<?php if ( wepp_fs()->is__premium_only() ) { ?>
			<div class="wizard-form-checkbox-square-connect">
				<input id="giftcard-live" name="wpep_square_giftcard" value="on" type="checkbox"
				
				<?php
				if ( 'on' === $wpep_square_giftcard ) {
					echo 'checked';
				}
				?>
				
				>
				<label for="giftcard-live">Square Gift Card</label>
			</div>
			<?php } ?>
			<div class="wizard-form-checkbox-square-connect">
			<input id="achDebitLive" name="wpep_square_ach_debit" value="on" type="checkbox"
			<?php
			if ( 'on' === $wpep_square_ach_debit ) {
				echo 'checked';
			}
			?>
			>
			<label for="achDebitLive">ACH Debit</label>
			</div>
			<?php if ( ! wepp_fs()->is__premium_only() ) { ?>
				<div class="wizard-form-checkbox-square-connect extraSpacing">
					<input id="googlePayTest" name="wpep_square_test_google_pay" value="on" type="checkbox" checked disabled>
					<label class="googlePayTest" for="googlePayTest">Google Pay</label>
					<span class="pro_tag" id="pro_tag">Pro</span>
				</div>
				<div class="wizard-form-checkbox-square-connect extraSpacing">
					<input id="applePayTest" name="wpep_square_test_apple_pay" value="on" type="checkbox" checked disabled>
					<label class="applePayTest" for="applePayTest">Apple Pay</label>
					<span class="pro_tag" id="pro_tag">Pro</span>
				</div>
				<div class="wizard-form-checkbox-square-connect extraSpacing">	
					<input id="giftcardTest" name="wpep_square_test_giftcard" value="on" type="checkbox" checked disabled>
					<label class="giftcard" for="giftcard">Square Gift Card</label>
					<span class="pro_tag" id="pro_tag">Pro</span>
				</div>
			<?php } else { ?>
			<div class="wizard-form-checkbox-square-connect">
			<input id="TerminalLive" name="wpep_square_terminal" value="on" type="checkbox"
				<?php
				if ( 'on' === $wpep_square_terminal ) {
					echo 'checked';
				}
				?>
			>
			<label for="TerminalLive">Terminal Pay</label>
			<br>
			<span id="terminalform">
			<label for="btn_wpep_gen_code" style="margin:0!important"  class="locationHeading">
				Generate device code
				</label>
				<br>
				<input type="text" name="wpep_device_code" id="wpep_device_code" value="<?php echo esc_attr( $wpep_device_code ); ?>"  style="padding: 5px; border: 1px solid #ccc; border-radius: 4px;" >
				<button id="btn_wpep_gen_code" style="padding: 5px 10px; background-color: #2065e0; color: white; border: none; border-radius: 4px; cursor: pointer;">
				Get code
				</button>
				<br>
			 
				<input type="hidden" name="wpep_device_id" id="wpep_device_id" value="<?php echo esc_attr( $wpep_device_id ); ?>" style="padding: 5px; border: 1px solid #ccc; border-radius: 4px;" >
				</span>
			</div> 
		<?php } ?>

		</div>

			<?php if ( 'true' === $revoked ) { ?>
		<p style="color: red;"> Seems like your OAuth token is revoked by Square. Please disconnect your account and reconnect to resolve the issue or contact support.  </p>
		<?php } ?>

			<?php if ( 'true' !== $revoked ) { ?>
		<div class="squareSubscriptionNoticeGlobal">
			<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/danger.png' ); ?>" class="dangerIconNotes" alt="Warning Icon">
			<div class="noteText">
				Note: Disconnecting from square and reconnecting with another account can stop your subscription payments. 
			</div>
		</div>
		<?php } ?>
		<div class="btnFooter d-btn">
			<button type="submit" class="btn btn-primary saveSettingSquareConnectionBtn"> Save Settings </button>
			<a href="<?php echo esc_url( get_option( 'wpep_square_disconnect_url', false ) ); ?>" class="btn btnDisconnect">Disconnect
			Square</a>
		</div>
			
			<?php
		}
		?>
		</div>

	</div>
</form>
</div>
<div id="pre-popupModal" class="pre-modal">
	<div class="pre-modal-content">
		<span class="pre-close">&times;</span>
		<div class="premium_popup_content">
			<div class="wp_easypay_logo">
				<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Logo_white.png' ); ?>" class="wpep_logo">
			</div>
			<h3 class="proPopHeading">Enhance Your Square Payment Forms With Premium Features.</h3>
			<div class="featuresListPopup">
				<div class="row">
					<div class="col-6">
						<ul>
							<li><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-tick.png' ); ?>" class="" /> 
								<p>5+ Digital Wallets</p>
							</li>
							<li><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-tick.png' ); ?>" class="" /> 
								<p>Square Product Sync</p>
							</li>
						</ul>
					</div>
					<div class="col-6">
						<ul>	
							<li><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-tick.png' ); ?>" class="" /> 
								<p>Square Gift Card</p>
							</li>
							<li><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-tick.png' ); ?>" class="" /> 
								<p>Manage Subscriptions</p>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="integration_button_div">
				<a href="https://wpeasypay.com/pricing?utm_source=plugin&utm_medium=payment_options" target="_blank" rel="noopener noreferrer" class="wpep-no-save-popup">
					<button type="button" class="upgradeBtn">
						Upgrade Now <img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/upgrade-btn-arrow.png' ); ?>" class="" /> 
					</button>
				</a>
			</div>
		</div>
	</div>
</div>
