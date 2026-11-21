<?php
/**
 * Filename: payment-helper-functions.php
 * Description: payment function file.
 *
 * @package WP_Easy_Pay
 */

/**
 * Require square-configuration.php
 */
require_once WPEP_ROOT_PATH . 'modules/payments/square-configuration.php';

if ( ! function_exists( 'wpep_get_subscription_cycle_label' ) ) {
	function wpep_get_subscription_cycle_label( $form_id ) {
		$wpep_subscription_cycle = get_post_meta( $form_id, 'wpep_subscription_cycle', true );

		if ( 'DAILY' === $wpep_subscription_cycle ) {
			return __( 'daily', 'wp_easy_pay' );
		} elseif ( 'WEEKLY' === $wpep_subscription_cycle ) {
			return __( 'weekly', 'wp_easy_pay' );
		} elseif ( 'EVERY_TWO_WEEKS' === $wpep_subscription_cycle ) {
			return __( 'every two weeks', 'wp_easy_pay' );
		} elseif ( 'MONTHLY' === $wpep_subscription_cycle ) {
			return __( 'monthly', 'wp_easy_pay' );
		} elseif ( 'EVERY_TWO_MONTHS' === $wpep_subscription_cycle ) {
			return __( 'every two months', 'wp_easy_pay' );
		} elseif ( 'QUARTERLY' === $wpep_subscription_cycle ) {
			return __( 'quarterly', 'wp_easy_pay' );
		} elseif ( 'EVERY_FOUR_MONTHS' === $wpep_subscription_cycle ) {
			return __( 'every four months', 'wp_easy_pay' );
		} elseif ( 'EVERY_SIX_MONTHS' === $wpep_subscription_cycle ) {
			return __( 'every six months', 'wp_easy_pay' );
		} elseif ( 'ANNUAL' === $wpep_subscription_cycle ) {
			return __( 'annually', 'wp_easy_pay' );
		} elseif ( 'EVERY_TWO_YEARS' === $wpep_subscription_cycle ) {
			return __( 'every two years', 'wp_easy_pay' );
		}

		return strtolower( str_replace( '_', ' ', (string) $wpep_subscription_cycle ) );
	}
}

if ( ! function_exists( 'wpep_get_transaction_type_label' ) ) {
	function wpep_get_transaction_type_label( $transaction_type ) {
		$labels = array(
			'simple'             => __( 'Simple', 'wp_easy_pay' ),
			'donation'           => __( 'Donation', 'wp_easy_pay' ),
			'subscription'       => __( 'Subscription', 'wp_easy_pay' ),
			'donation_recurring' => __( 'Donation Recurring', 'wp_easy_pay' ),
			'one_time'           => __( 'One Time', 'wp_easy_pay' ),
			'one_time_recurring' => __( 'One Time Recurring', 'wp_easy_pay' ),
		);

		if ( isset( $labels[ $transaction_type ] ) ) {
			return $labels[ $transaction_type ];
		}

		return ucwords( str_replace( '_', ' ', (string) $transaction_type ) );
	}
}

if ( ! function_exists( 'wpep_parse_payment_amount' ) ) {
	/**
	 * Normalize a payment amount that may include currency symbols or codes.
	 *
	 * @param mixed $amount Raw amount from POST or display text.
	 * @return float
	 */
	function wpep_parse_payment_amount( $amount ) {
		if ( is_array( $amount ) ) {
			return 0.0;
		}

		if ( is_numeric( $amount ) ) {
			return floatval( $amount );
		}

		$amount = preg_replace( '/[^0-9.]/', '', str_replace( ',', '', (string) $amount ) );

		return '' === $amount ? 0.0 : floatval( $amount );
	}
}

if ( ! function_exists( 'wpep_upload_url_to_trusted_path' ) ) {
	/**
	 * Map an uploads URL to a readable file path under wp_upload_dir(), or false.
	 *
	 * @param string $url Upload URL.
	 * @return string|false Absolute filesystem path.
	 */
	function wpep_upload_url_to_trusted_path( $url ) {
		$url = esc_url_raw( trim( (string) $url ) );
		if ( '' === $url ) {
			return false;
		}

		$upload_dir = wp_upload_dir();
		if ( ! empty( $upload_dir['error'] ) ) {
			return false;
		}

		$baseurl = untrailingslashit( $upload_dir['baseurl'] );
		if ( 0 !== strpos( $url, $baseurl ) ) {
			return false;
		}

		$url_path   = wp_parse_url( $url, PHP_URL_PATH );
		$base_path  = wp_parse_url( $baseurl, PHP_URL_PATH );
		if ( ! is_string( $url_path ) || ! is_string( $base_path ) || 0 !== strpos( $url_path, $base_path ) ) {
			return false;
		}

		$relative = ltrim( substr( $url_path, strlen( $base_path ) ), '/' );
		if ( '' === $relative || false !== strpos( $relative, '..' ) ) {
			return false;
		}

		$candidate = wp_normalize_path( $upload_dir['basedir'] . '/' . $relative );
		$real      = realpath( $candidate );
		$real_base = realpath( $upload_dir['basedir'] );
		if ( false === $real || false === $real_base || ! is_file( $real ) || ! is_readable( $real ) ) {
			return false;
		}

		$real_norm      = trailingslashit( wp_normalize_path( $real_base ) );
		$real_file_norm = wp_normalize_path( $real );
		if ( 0 !== strpos( $real_file_norm, $real_norm ) ) {
			return false;
		}

		return $real;
	}
}

if ( ! function_exists( 'wpep_sanitize_trusted_upload_url_value' ) ) {
	/**
	 * Keep only upload URLs that resolve inside the uploads directory.
	 *
	 * @param mixed $value Single URL or comma-separated URLs.
	 * @return string Sanitized value or empty string.
	 */
	function wpep_sanitize_trusted_upload_url_value( $value ) {
		if ( is_array( $value ) ) {
			$parts = $value;
		} else {
			$parts = preg_split( '/\s*,\s*/', (string) $value );
		}

		$trusted = array();
		foreach ( (array) $parts as $part ) {
			$part = trim( (string) $part );
			if ( '' === $part ) {
				continue;
			}
			if ( wpep_upload_url_to_trusted_path( $part ) ) {
				$trusted[] = esc_url_raw( $part );
			}
		}

		return implode( ', ', array_unique( $trusted ) );
	}
}

if ( ! function_exists( 'wpep_form_has_file_field' ) ) {
	/**
	 * Whether the form defines a file upload field.
	 *
	 * @param int $form_id Form post ID.
	 * @return bool
	 */
	function wpep_form_has_file_field( $form_id ) {
		$form_fields_json = json_decode( get_post_meta( $form_id, 'wpep_square_form_builder_fields', true ) );
		if ( empty( $form_fields_json ) || ! is_array( $form_fields_json ) ) {
			return false;
		}
		foreach ( $form_fields_json as $field ) {
			if ( isset( $field->type ) && 'file' === $field->type ) {
				return true;
			}
		}
		return false;
	}
}

if ( ! function_exists( 'wpep_sanitize_payment_form_values' ) ) {
	/**
	 * Allow only configured form labels; validate upload URLs.
	 *
	 * @param int   $form_id     Form post ID.
	 * @param array $form_values Raw submitted values.
	 * @return array
	 */
	function wpep_sanitize_payment_form_values( $form_id, $form_values ) {
		if ( ! is_array( $form_values ) ) {
			return array();
		}

		$allowed = array(
			'First Name' => true,
			'Last Name'  => true,
			'Email'      => true,
			'Line Items' => true,
			'quantity'   => true,
			'total_amount' => true,
		);

		$form_fields_json = json_decode( get_post_meta( $form_id, 'wpep_square_form_builder_fields', true ) );
		if ( is_array( $form_fields_json ) ) {
			foreach ( $form_fields_json as $field ) {
				if ( ! empty( $field->label ) ) {
					$allowed[ (string) $field->label ] = true;
				}
			}
		}

		$has_file_field = wpep_form_has_file_field( $form_id );
		$sanitized      = array();

		foreach ( $form_values as $row ) {
			if ( ! is_array( $row ) || ! isset( $row['label'], $row['value'] ) ) {
				continue;
			}

			$label = (string) $row['label'];

			if ( in_array( $label, array( 'Uploaded URL', 'Uploaded File URL' ), true ) ) {
				if ( ! $has_file_field ) {
					continue;
				}
				$value = wpep_sanitize_trusted_upload_url_value( $row['value'] );
				if ( '' === $value ) {
					continue;
				}
				$sanitized[] = array(
					'label' => $label,
					'value' => $value,
				);
				continue;
			}

			if ( empty( $allowed[ $label ] ) ) {
				continue;
			}

			$value = $row['value'];
			if ( is_string( $value ) ) {
				$value = str_replace( array( '[file_upload]', '[uploaded_url]' ), '', $value );
			}

			$sanitized[] = array(
				'label' => $label,
				'value' => $value,
			);
		}

		return $sanitized;
	}
}

/**
 * Creates a WordPress user.
 *
 * @param string $first_name The first_name for the user.
 * @param string $last_name The last_name for the user.
 * @param string $email    The email address for the user.
 * @return int|WP_Error The user ID on success, or WP_Error object on failure.
 */
function wpep_create_wordpress_user( $first_name, $last_name, $email ) {
	$username = strtolower( $email );
	$password = wpep_generate_random_password();
	$user_id  = wp_create_user( $username, $password, $email );

	return $user_id;
}

/**
 * Generates a random password.
 *
 * @return string The generated random password.
 */
function wpep_generate_random_password() {
	$alphabet     = 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789';
	$pass         = array();
	$alpha_length = strlen( $alphabet ) - 1;
	for ( $i = 0; $i < 8; $i++ ) {
		$n      = wp_rand( 0, $alpha_length );
		$pass[] = $alphabet[ $n ];
	}
	return implode( $pass );
}

/**
 * Retrieves a Square customer for verification.
 *
 * @param object $api_client       The Square API client.
 * @param string $square_customer_id The ID of the Square customer.
 * @return array|WP_Error          The retrieved Square customer data or WP_Error on failure.
 */
function wpep_retrieve_square_customer_to_verify( $api_client, $square_customer_id ) {

	try {
		$result = $api_client->customers->get(
			new \Square\Customers\Requests\GetCustomersRequest(
				array(
					'customerId' => $square_customer_id,
				)
			)
		);
		return $result->getCustomer()->getId();
	} catch ( Exception $e ) {
		return false;
	}
}

/**
 * Create a Square customer card.
 *
 * @param object $api_client The Square API client object.
 * @param string $square_customer_id The Square customer ID.
 * @param string $nonce The payment method nonce.
 * @param string $first_name The first name of the customer.
 * @param string $last_name The last name of the customer.
 * @param string $verification_token The verification token.
 * @return object|WP_Error The Square customer card object on success, WP_Error object on failure.
 */
function wpep_create_square_customer_card( $api_client, $square_customer_id, $nonce, $first_name, $last_name, $verification_token ) { // phpcs:ignore

	$card_holder_name = $first_name . ' ' . $last_name;

	$request_values = array(
		'customerId'     => $square_customer_id,
		'cardNonce'      => $nonce,
		'cardholderName' => $card_holder_name,
	);

	if ( ! empty( $verification_token ) ) {
		$request_values['verificationToken'] = $verification_token;
	}

	$body = new \Square\Customers\Cards\Requests\CreateCustomerCardRequest( $request_values );

	try {

		$result = $api_client->customers->cards->create( $body );
		return $result->getCard()->getId();

	} catch ( Exception $e ) {
		wp_die( wp_json_encode( wpep_square_exception_error( $e ) ) );
	}
}

/**
 * Creates a new Square customer using the provided API client and customer data.
 *
 * @param object $api_client The Square API client instance.
 *
 * @return string|void Returns the customer ID on success, or terminates with an error message on failure.
 */
function wpep_create_square_customer( $api_client ) {

	$post = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in wpep_payment_request().

	$unique_key = uniqid() . 'wpexperts';
	$body       = new \Square\Customers\Requests\CreateCustomerRequest(
		array(
			'idempotencyKey' => $unique_key,
			'givenName'      => $post['first_name'],
			'familyName'     => $post['last_name'],
			'emailAddress'   => $post['email'],
			'referenceId'    => $unique_key,
		)
	);

	try {

		$result = $api_client->customers->create( $body );
		return $result->getCustomer()->getId();

	} catch ( Exception $e ) {
		wp_die( wp_json_encode( wpep_square_exception_error( $e ) ) );
	}
}

/**
 * Perform weekly refresh of tokens.
 *
 * @return void
 */
function wpep_weekly_refresh_tokens() {

	$oauth_connect_url = WPEP_MIDDLE_SERVER_URL;

	// Check if global connection exists (either live or sandbox)
	$global_live_refresh_token    = get_option( 'wpep_refresh_token' );
	$global_sandbox_refresh_token = get_option( 'wpep_refresh_test_token' );

	// Only process global token refresh if at least one global connection exists
	if ( ! empty( $global_live_refresh_token ) || ! empty( $global_sandbox_refresh_token ) ) {

		// Get the global payment mode setting to determine which mode is active
		$global_payment_mode = get_option( 'wpep_square_payment_mode_global', true );
		
		// Determine which mode is active based on the setting, not just token existence
		$is_global_sandbox = ( 'on' !== $global_payment_mode );
		
		// Use the appropriate refresh token based on the active mode
		if ( $is_global_sandbox ) {
			$refresh_access_token = $global_sandbox_refresh_token;
		} else {
			$refresh_access_token = $global_live_refresh_token;
		}
		
		// Only proceed if we have a valid refresh token for the active mode
		if ( empty( $refresh_access_token ) ) {
			return;
		}

		$args_renew = array(
			'body'    => array(
				'request_type'    => 'renew_token',
				'refresh_token'   => $refresh_access_token,
				'oauth_version'   => 2,
				'app_name'        => WPEP_SQUARE_APP_NAME,
				'sandbox_enabled' => $is_global_sandbox ? 'yes' : 'no',
			),
			'timeout' => 10,
		);

		$oauth_response      = wp_remote_post( $oauth_connect_url, $args_renew );
		$oauth_response_body = is_wp_error( $oauth_response ) ? null : json_decode( wp_remote_retrieve_body( $oauth_response ) );

		if ( ! is_wp_error( $oauth_response ) && ! empty( $oauth_response_body->access_token ) ) {
			if ( $is_global_sandbox ) {
				update_option( 'wpep_square_test_token_global', sanitize_text_field( $oauth_response_body->access_token ) );
				update_option( 'wpep_refresh_test_token', $oauth_response_body->refresh_token );
				update_option( 'wpep_token_test_expires_at', $oauth_response_body->expires_at );
			} else {
				update_option( 'wpep_live_token_upgraded', sanitize_text_field( $oauth_response_body->access_token ) );
				update_option( 'wpep_refresh_token', $oauth_response_body->refresh_token );
				update_option( 'wpep_token_expires_at', $oauth_response_body->expires_at );
			}
		}

		$mode     = $is_global_sandbox ? 'Sandbox' : 'Live';
		$request  = $args_renew['body'];
		$response = is_wp_error( $oauth_response ) ? $oauth_response->get_error_message() : wp_remote_retrieve_body( $oauth_response );

		// Use smart status determination based on response
		$status = wpep_determine_connection_status( $oauth_response, $oauth_response_body );

		wpep_insert_connection_log( $mode, $request, $response, $status, null );
	}
	// NEW: refresh tokens for individual forms and log with form_id
	$forms = get_posts(
		array(
			'post_type'      => 'wp_easy_pay',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	foreach ( $forms as $form_id ) {

		// Skip forms that use Global Settings
		$uses_global = get_post_meta( $form_id, 'wpep_individual_form_global', true );
		if ( 'on' === $uses_global ) {
			continue;
		}

		// Decide live/test mode for this form
		$form_mode  = get_post_meta( $form_id, 'wpep_payment_mode', true ); // 'on' => live, else sandbox
		$is_sandbox = ( 'on' === $form_mode ) ? false : true;

		// Pick the right refresh token meta for the form
		$form_refresh_token = $is_sandbox
			? get_post_meta( $form_id, 'wpep_test_refresh_token', true )
			: get_post_meta( $form_id, 'wpep_refresh_token', true );

		if ( empty( $form_refresh_token ) ) {
			continue;
		}

		$form_args = array(
			'body'    => array(
				'request_type'    => 'renew_token',
				'refresh_token'   => $form_refresh_token,
				'oauth_version'   => 2,
				'app_name'        => WPEP_SQUARE_APP_NAME,
				'sandbox_enabled' => $is_sandbox ? 'yes' : 'no',
			),
			'timeout' => 10,
		);

		$form_resp      = wp_remote_post( $oauth_connect_url, $form_args );
		$form_resp_body = is_wp_error( $form_resp ) ? null : json_decode( wp_remote_retrieve_body( $form_resp ) );

		if ( ! is_wp_error( $form_resp ) && ! empty( $form_resp_body->access_token ) ) {
			if ( $is_sandbox ) {
				update_post_meta( $form_id, 'wpep_square_test_token', sanitize_text_field( $form_resp_body->access_token ) );
				update_post_meta( $form_id, 'wpep_test_refresh_token', $form_resp_body->refresh_token );
				update_post_meta( $form_id, 'wpep_token_test_expires_at', $form_resp_body->expires_at );
			} else {
				update_post_meta( $form_id, 'wpep_live_token_upgraded', sanitize_text_field( $form_resp_body->access_token ) );
				update_post_meta( $form_id, 'wpep_refresh_token', $form_resp_body->refresh_token );
				update_post_meta( $form_id, 'wpep_token_expires_at', $form_resp_body->expires_at );
			}
		}

		$form_mode_label = $is_sandbox ? 'Individual - Sandbox' : 'Individual - Live';
		$form_response   = is_wp_error( $form_resp ) ? $form_resp->get_error_message() : wp_remote_retrieve_body( $form_resp );

		// Use smart status determination based on response
		$form_status = wpep_determine_connection_status( $form_resp, $form_resp_body );

		wpep_insert_connection_log(
			$form_mode_label,
			$form_args['body'],
			$form_response,
			$form_status,
			$form_id
		);
	}
}

/**
 * Send email for log alerts.
 *
 * @return void
 */
function wpep_send_log_email_alert( $log_data ) {

	require_once WPEP_ROOT_PATH . 'modules/square-logs/logs-formatting.php';

	$alerts_enabled = get_option( 'logAlertEmail', 0 );
	if ( ! $alerts_enabled ) {
		return;
	}

	$recipient = get_option( 'my_alert_email' );

	if ( empty( $recipient ) ) {
		$recipient = get_bloginfo( 'admin_email' );
	}
	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
	);

	// Determine if connection is successful or failed
	$is_success = ( isset( $log_data['status'] ) && 'Success' === $log_data['status'] );

	// Determine Form ID or Global
	$form_display = 'Global';
	if ( ! empty( $log_data['form_id'] ) && is_numeric( $log_data['form_id'] ) ) {
		$form_display = esc_html( $log_data['form_id'] );
	}

	// Set subject and colors based on status
	if ( $is_success ) {
		$subject      = 'Square Connection Alert - Connection Successful';
		$status_color = '#F4FCF7';
		$status_text  = 'Connection Successful';
		$message_text = '<span style="font-weight: 600;">Your Square API connection has been established </span><br>The connection has been verified and is now active.';
		$header_bg    = '#47B388';
		$border_color = '#F4FCF7';
	} else {
		$subject      = 'Square Connection Alert - Connection Failed';
		$status_color = '#FEF8F3';
		$status_text  = 'Connection Failed';
		$message_text = '<span style="font-weight: 600;">Unable to establish connection with Square API </span><br>The connection attempt failed. please review the details below.';
		$header_bg    = '#FC605B';
		$border_color = '#FEF8F3';
	}

	// Email body template - Success
	if ( $is_success ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		$request_details = print_r( wpep_format_request_body_for_display( $log_data['request'] ), true );
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		$response_details = print_r( wpep_format_response_body_for_display( $log_data['response'] ), true );
		
		// Check if user has active valid license (Pro version)
		// Only show marketing if user doesn't have valid license (Free version)
		$marketing_html = '';
		if ( ! function_exists( 'wepp_fs' ) || ! wepp_fs()->has_active_valid_license() ) {
			// phpcs:disable Generic.Formatting.MultipleStatementAlignment -- HTML string with style attributes, not PHP assignments
			$marketing_html = '
					<div style="margin-top:20px;padding: 20px;background: #B8D3FF4D;border-radius:8px;font-size:13px;border: 1px solid #B8D3FF4D;display: flex;">
    					<div style="width:50%;">
							<h2 style="color: #000645;font-weight: 600;">Upgrade to WP EasyPay Pro — Unlock 20+ Powerful Features</h2>
							<ul style="padding: 0px 0px;list-style: none;margin: 0px;color: #000645;">
								<li style="padding: 0px 0px 6px 0px;line-height: 20px;">
									<img src="' . esc_url( WPEP_ROOT_URL . 'assets/backend/img/upgrade-pin.png' ) . '" alt="TickIcon" style="max-width:21px;margin:0px 3px -5px 0px;">	
									Real-time Square Product Sync
								</li>
								<li style="padding: 0px 0px 6px 0px;line-height: 20px;">
									<img src="' . esc_url( WPEP_ROOT_URL . 'assets/backend/img/upgrade-pin.png' ) . '" alt="TickIcon" style="max-width:21px;margin:0px 3px -5px 0px;">	
									Square Gift Cards Redemption
								</li>
								<li style="padding: 0px 0px 6px 0px;line-height: 20px;">
									<img src="' . esc_url( WPEP_ROOT_URL . 'assets/backend/img/upgrade-pin.png' ) . '" alt="TickIcon" style="max-width:21px;margin:0px 3px -5px 0px;">	
									Accept Google Pay, Apply Pay, Cash App, and more.
								</li>
							</ul>
							<p style="color: #000645;font-size: 12px;margin: 0px;padding: 0px 0px 6px 0px;">
								All that for less than your monthly coffee budget. 🙂
							</p>
							<a href="https://wpeasypay.com/pricing?utm_source=plugin&utm_medium=payment_options" target="_blank" rel="noopener noreferrer">
								<button type="button" style="background-color: #000645;text-decoration: none;padding: 15px 30px;margin: 6px 0px;color: white;border-radius: 25px;">
									Upgrade Now <img src="' . esc_url( WPEP_ROOT_URL . 'assets/backend/img/arrow-icon.png' ) . '" alt="TickIcon" style="max-width:23px;margin:0px -5px -2px 5px;">
								</button>
							</a>
						</div>
						<div style="width:50%;text-align: right;">
							<img src="' . esc_url( WPEP_ROOT_URL . 'assets/backend/img/upgrade-icon.png' ) . '" alt="TickIcon" style="max-width:90%;margin-top:12px;">
						</div>
					</div>';
			// phpcs:enable
		}
		
		// phpcs:disable Generic.Formatting.MultipleStatementAlignment
		$body = '
			<!DOCTYPE html>
		<html>
		<head>
			<style>
				@import url(\'https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap\');

				@font-face {
				font-family: \'Sora\';
				src: url(data:font/woff2;base64,AAA....) format(\'woff2\');
				font-weight:400;
				font-style: normal;
				}
			</style>
		</head>
		<body>
			<div style="max-width:750px;margin:0 auto;font-family: \'Sora\', Arial, Helvetica, sans-serif;background:#f5f7fa;padding:20px;">
				
				<div style="background:white;border-radius:10px;padding:25px;border:2px solid ' . $border_color . ';">
					
					<div style="padding-bottom:20px;margin-bottom:0px;background:' . $header_bg . ';padding:20px;border-radius:8px 8px 0 0;margin:-25px -25px 20px -25px;">
						<table style="width:100%;font-size:14px;margin-top:10px;border-collapse:collapse;">
							<tr>
								<td style="color:#666;padding:8px 0;">
									<div style="display: flex">
										<div>
											<img src="' . esc_url( WPEP_ROOT_URL . 'assets/backend/img/header-white-icon.png' ) . '" alt="TickIcon" style="max-width: 40px;margin: 5px 10px;">
										</div>
										<div>
											<h2 style="color:' . $status_color . ';margin:0;font-size:18px;font-weight:600;">Square Connection Alert</h2>
											<p style="color:' . $status_color . ';margin:0px 0px;font-size:15px;font-weight:500;">' . $status_text . '</p>
										</div>
									</div>
								</td>
								<td style="color:#000;font-weight:600;padding:8px 0;">
								<img src="' . esc_url( WPEP_ROOT_URL . 'assets/backend/img/white-logo.png' ) . '" alt="WP EasyPay" style="filter: brightness(999);max-width:140px;margin-bottom:10px;">
								</td>
							</tr>
						</table>
					</div>
					<div style="padding: 10px 10px;background: #F4FCF7;">
						<p style="font-size:14px;color:#444;line-height:1.6">
												' . $message_text . '
						</p>
					</div>
					<h3 style="font-size:16px;margin-top:25px;color:#222;">Connection Details</h3>

					<table style="width:100%;font-size:14px;margin-top:10px;border-collapse:collapse;">
						<tr>
							<td style="color:#666;padding:8px 0;">Square Mode:</td>
							<td style="color:#000;font-weight:600;padding:8px 0;text-align:center;">
								<span style="border: 1px solid black;padding: 7px 15px;border-radius: 30px;font-size: 13px;">
								' . esc_html( $log_data['mode'] ) . '
								</span>
							</td>
						</tr>
						<tr>
							<td style="color:#666;padding:8px 0;">Form ID:</td>
							<td style="color:#000;font-weight:600;padding:8px 0;text-align:center;">' . $form_display . '</td>
						</tr>
						<tr>
							<td style="color:#666;padding:8px 0;">Date & Time:</td>
							<td style="color:#000;font-weight:600;padding:8px 0;text-align:center;">' . esc_html( $log_data['datetime'] ) . '</td>
						</tr>
						<tr>
							<td style="color:#666;padding:8px 0;">Connection Status:</td>
							<td style="color:' . $header_bg . ';font-weight:600;padding:8px 0;text-align: center;">
							<img src="' . esc_url( WPEP_ROOT_URL . 'assets/backend/img/green-icon.png' ) . '" alt="TickIcon" style="max-width: 23px; margin: 0px 3px -3px 0px;"> Connected
							</td>
						</tr>
					</table>

					' . $marketing_html . '
				</div>
			</div>
			</body>
			</html>
		';
		// phpcs:enable
	} else {
		// Email body template - Failed
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		$request_details = print_r( wpep_format_request_body_for_display( $log_data['request'] ), true );
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		$response_details = print_r( wpep_format_response_body_for_display( $log_data['response'] ), true );
		// phpcs:disable Generic.Formatting.MultipleStatementAlignment
		$body = '
			<!DOCTYPE html>
		<html>
		<head>
			<style>
				@import url(\'https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap\');

				@font-face {
				font-family: \'Sora\';
				src: url(data:font/woff2;base64,AAA....) format(\'woff2\');
				font-weight:400;
				font-style: normal;
				}
			</style>
		</head>
		<body>
			<div style="max-width:750px;margin:0 auto;font-family: \'Sora\', Arial, Helvetica, sans-serif;background:#f5f7fa;padding:20px;">
				
				<div style="background:white;border-radius:10px;padding:25px;border:2px solid ' . $border_color . ';">
					
					<div style="padding-bottom:20px;margin-bottom:0px;background:' . $header_bg . ';padding:20px;border-radius:8px 8px 0 0;margin:-25px -25px 20px -25px;">
						<table style="width:100%;font-size:14px;margin-top:10px;border-collapse:collapse;">
							<tr>
								<td style="color:#666;padding:8px 0;">
									<div style="display: flex">
										<div>
											<img src="' . esc_url( WPEP_ROOT_URL . 'assets/backend/img/header-white-icon.png' ) . '" alt="TickIcon" style="max-width: 40px;margin: 5px 10px;">
										</div>
										<div>
											<h2 style="color:' . $status_color . ';margin:0;font-size:18px;font-weight:600;">Square Connection Alert</h2>
											<p style="color:' . $status_color . ';margin:0px 0px;font-size:15px;font-weight:500;">' . $status_text . '</p>
										</div>
									</div>
								</td>
								<td style="color:#000;font-weight:600;padding:8px 0;">
								<img src="' . esc_url( WPEP_ROOT_URL . 'assets/backend/img/white-logo.png' ) . '" alt="WP EasyPay" style="filter: brightness(999);max-width:140px;margin-bottom:10px;">
								</td>
							</tr>
						</table>
					</div>
					<div style="padding: 10px 10px;background: #FEF8F3;">
						<p style="font-size:14px;color:#444;line-height:1.6">
												' . $message_text . '
						</p>
					</div>
					<h3 style="font-size:16px;margin-top:25px;color:#222;">Connection Details</h3>

					<table style="width:100%;font-size:14px;margin-top:10px;border-collapse:collapse;">
						<tr>
							<td style="color:#666;padding:8px 0;">Square Mode:</td>
							<td style="color:#000;font-weight:600;padding:8px 0;text-align:center;">
								<span style="border: 1px solid black;padding: 7px 15px;border-radius: 30px;font-size: 13px;">
								' . esc_html( $log_data['mode'] ) . '
								</span>
							</td>
						</tr>
						<tr>
							<td style="color:#666;padding:8px 0;">Form ID:</td>
							<td style="color:#000;font-weight:600;padding:8px 0;text-align:center;">' . $form_display . '</td>
						</tr>
						<tr>
							<td style="color:#666;padding:8px 0;">Date & Time:</td>
							<td style="color:#000;font-weight:600;padding:8px 0;text-align:center;">' . esc_html( $log_data['datetime'] ) . '</td>
						</tr>
						<tr>
							<td style="color:#666;padding:8px 0;">Connection Status:</td>
							<td style="color:' . $header_bg . ';font-weight:600;padding:8px 0;text-align: center;">
							<img src="' . esc_url( WPEP_ROOT_URL . 'assets/backend/img/cross-red.png' ) . '" alt="TickIcon" style="max-width: 21px;margin: 0px 3px -5px 0px;"> Disconnected
							</td>
						</tr>
					</table>

					<div style="margin-top:20px;padding: 20px;background: #FFF0EA;border-radius:8px;font-size:13px;">
						<ul style="padding: 0px 0px;list-style: none;margin: 0px;color: #000645;">
							<li style="padding: 0px 0px 6px 0px;line-height: 20px;">
								<div style="display: flex;">
									<img src="' . esc_url( WPEP_ROOT_URL . 'assets/backend/img/action-icon.png' ) . '" alt="ActionIcon" style="max-width: 30px;max-height: 30px;margin: 0px 5px 0px 0px;">
									<h2 style="color: #3E4348;font-weight: 600;margin: 5px 5px;">Action Required</h2>	
								</div>
								<ul>
									<li style="padding: 10px 0px 5px 0px;font-size: 15px;">Verify your Square API Credentials</li>
									<li style="padding: 5px 0px;font-size: 15px;">
										Try reconnecting Square. 
										<a href="https://wpeasypay.com/documentation/free-version-documentation/connect-square-account/" target="_blank" style="color:#A61B1B;text-decoration: underline;">How to reconnect Square with WP Easy Pay</a>
									</li>
								</ul>
							</li>
						</ul>
					</div>

				</div>
			</div>
			</body>
			</html>
		';
		// phpcs:enable
	}

	wp_mail( $recipient, $subject, $body, $headers );
}

/**
 * Inserts Connection log in table.
 *
 * @return void
 */
function wpep_insert_connection_log( $mode, $request, $response, $status, $form_id = null ) {

	$log_data = array(
		'mode'     => $mode,
		'datetime' => current_time( 'mysql' ),
		'status'   => $status,
		'request'  => $request,
		'response' => $response,
		'form_id'  => $form_id,
	);

	wpep_send_log_email_alert( $log_data );

	global $wpdb;
	$wpep_log_table_name = $wpdb->prefix . 'wpep_square_logs';

	$wpdb->insert(
		$wpep_log_table_name,
		array(
			'mode'     => $mode,
			'datetime' => current_time( 'mysql' ),
			'request'  => maybe_serialize( $request ),
			'response' => maybe_serialize( $response ),
			'status'   => $status,
			'form_id'  => $form_id,
		),
		array( '%s', '%s', '%s', '%s', '%s' )
	);
}

/**
 * Smartly determine connection status based on API response
 *
 * @param mixed $response WP_Error or array response from wp_remote_post
 * @param mixed $response_body Decoded JSON response body (object or array)
 * @return string 'Success' or 'Failed'
 */
function wpep_determine_connection_status( $response, $response_body = null ) {

	// Check for WP_Error first
	if ( is_wp_error( $response ) ) {
		return 'Failed';
	}

	// Get HTTP response code
	$response_code = wp_remote_retrieve_response_code( $response );

	// Check for HTTP error codes (4xx, 5xx are failures)
	if ( $response_code >= 400 ) {
		return 'Failed';
	}

	// If response body is not provided, try to decode it
	if ( is_null( $response_body ) ) {
		$response_raw = wp_remote_retrieve_body( $response );
		// Check if response is empty
		if ( empty( $response_raw ) ) {
			return 'Failed';
		}
		$response_body = json_decode( $response_raw );
		// Check if JSON decode failed
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return 'Failed';
		}
	}

	// If response_body is still null after decoding, it's a failure
	if ( is_null( $response_body ) ) {
		return 'Failed';
	}

	// List of error codes that indicate disconnection
	$error_codes = array(
		'UNAUTHORIZED',
		'ACCESS_TOKEN_REVOKED',
		'INVALID_ACCESS_TOKEN',
		'EXPIRED_TOKEN',
		'INVALID_REFRESH_TOKEN',
		'REFRESH_TOKEN_EXPIRED',
		'BAD_REQUEST',
		'FORBIDDEN',
		'NOT_FOUND',
	);

	// Check if response body has errors (handle both object and array formats)
	if ( is_object( $response_body ) ) {
		// Check for errors array in object - ANY error means failure
		if ( isset( $response_body->errors ) && is_array( $response_body->errors ) && ! empty( $response_body->errors ) ) {
			foreach ( $response_body->errors as $error ) {
				if ( is_object( $error ) && isset( $error->code ) ) {
					// If it's a known error code, definitely failed
					if ( in_array( $error->code, $error_codes, true ) ) {
						return 'Failed';
					}
					// Even if not in our list, if there's an error code, it's likely a failure
					if ( ! empty( $error->code ) ) {
						return 'Failed';
					}
				} elseif ( is_array( $error ) && isset( $error['code'] ) ) {
					if ( in_array( $error['code'], $error_codes, true ) ) {
						return 'Failed';
					}
					if ( ! empty( $error['code'] ) ) {
						return 'Failed';
					}
				}
			}
		}

		// Check for direct error code in object
		if ( isset( $response_body->code ) && ! empty( $response_body->code ) ) {
			if ( in_array( $response_body->code, $error_codes, true ) ) {
				return 'Failed';
			}
			// Any error code means failure
			return 'Failed';
		}

		// Check for error message (indicates failure)
		if ( isset( $response_body->error ) || isset( $response_body->message ) ) {
			// If there's an error or message field and no access_token, it's likely a failure
			if ( ! isset( $response_body->access_token ) ) {
				return 'Failed';
			}
		}

		// Check if access_token exists (successful token refresh)
		if ( isset( $response_body->access_token ) && ! empty( $response_body->access_token ) ) {
			return 'Success';
		}
	} elseif ( is_array( $response_body ) ) {
		// Check for errors array - ANY error means failure
		if ( isset( $response_body['errors'] ) && is_array( $response_body['errors'] ) && ! empty( $response_body['errors'] ) ) {
			foreach ( $response_body['errors'] as $error ) {
				if ( isset( $error['code'] ) ) {
					if ( in_array( $error['code'], $error_codes, true ) ) {
						return 'Failed';
					}
					// Even if not in our list, if there's an error code, it's likely a failure
					if ( ! empty( $error['code'] ) ) {
						return 'Failed';
					}
				}
			}
		}

		// Check for direct error code
		if ( isset( $response_body['code'] ) && ! empty( $response_body['code'] ) ) {
			if ( in_array( $response_body['code'], $error_codes, true ) ) {
				return 'Failed';
			}
			// Any error code means failure
			return 'Failed';
		}

		// Check for error message (indicates failure)
		if ( isset( $response_body['error'] ) || isset( $response_body['message'] ) ) {
			// If there's an error or message field and no access_token, it's likely a failure
			if ( ! isset( $response_body['access_token'] ) ) {
				return 'Failed';
			}
		}

		// Check if access_token exists (successful token refresh)
		if ( isset( $response_body['access_token'] ) && ! empty( $response_body['access_token'] ) ) {
			return 'Success';
		}
	}

	// If we got here and response code is 200, check if we have access_token
	// If no access_token in 200 response, it might still be a failure
	if ( 200 === $response_code ) {
		// If response is empty or doesn't have access_token, it's suspicious
		if ( empty( $response_body ) ) {
			return 'Failed';
		}
		// If we have a 200 but no access_token, default to success (might be other API responses)
		return 'Success';
	}

	// Default to failed if we can't determine
	return 'Failed';
}

/**
 * Refresh Square access token.
 *
 * @param string $expires_at            The expiration timestamp of the access token.
 * @param string $refresh_access_token  The refresh access token.
 * @param string $type                  The type of token to refresh.
 * @param int    $current_form_id       The ID of the current form.
 * @return void
 */
function wpep_square_refresh_token( $expires_at, $refresh_access_token, $type, $current_form_id ) {

	$expiry_status = wpep_check_give_square_expiry( $expires_at, $current_form_id );
	$creds         = wpep_get_creds( $current_form_id );

	if ( 'expired' === $expiry_status ) {

		$oauth_connect_url = WPEP_MIDDLE_SERVER_URL;
		$live_mode         = $creds['_payment_mode'];
		if ( 'on' === $creds['_payment_mode'] ) {
			$sandbox_enabled = 'no';
		} else {
			$sandbox_enabled = 'yes';
		}
		$args_renew = array(

			'body'    => array(

				'request_type'    => 'renew_token',
				'refresh_token'   => $refresh_access_token,
				'oauth_version'   => 2,
				'app_name'        => WPEP_SQUARE_APP_NAME,
				'sandbox_enabled' => $sandbox_enabled,
			),
			'timeout' => 45,
		);

		$oauth_response      = wp_remote_post( $oauth_connect_url, $args_renew );
		$oauth_response_body = json_decode( $oauth_response['body'] );

		if ( 'global' === $type ) {
			if ( 'on' === $live_mode ) {
				update_option( 'wpep_live_token_upgraded', sanitize_text_field( $oauth_response_body->access_token ) );
				update_option( 'wpep_refresh_token', $oauth_response_body->refresh_token );
				update_option( 'wpep_token_expires_at', $oauth_response_body->expires_at );

			} else {
				update_option( 'wpep_square_test_token_global', sanitize_text_field( $oauth_response_body->access_token ) );
				update_option( 'wpep_refresh_test_token', $oauth_response_body->refresh_token );
				update_option( 'wpep_token_test_expires_at', $oauth_response_body->expires_at );
			}
		}

		if ( 'specific' === $type ) {
			if ( 'on' === $live_mode ) {
				update_post_meta( $current_form_id, 'wpep_live_token_upgraded', sanitize_text_field( $oauth_response_body->access_token ) );
				update_post_meta( $current_form_id, 'wpep_refresh_token', $oauth_response_body->refresh_token );
				update_post_meta( $current_form_id, 'wpep_token_expires_at', $oauth_response_body->expires_at );
			} else {
				update_post_meta( $current_form_id, 'wpep_square_test_token', sanitize_text_field( $oauth_response_body->access_token ) );
				update_post_meta( $current_form_id, 'wpep_test_refresh_token', $oauth_response_body->refresh_token );
				update_post_meta( $current_form_id, 'wpep_token_test_expires_at', $oauth_response_body->expires_at );
			}
		}
	}
}


/**
 * Check the expiration of the Square access token.
 *
 * @param string $expires_at        The expiration timestamp of the access token.
 * @param int    $current_form_id   The ID of the current form.
 * @return string check status.
 */
function wpep_check_give_square_expiry( $expires_at, $current_form_id ) {

	if ( empty( $expires_at ) ) {
		return 'expired';
	}

	if ( is_numeric( $expires_at ) ) {
		$expires_timestamp = (int) $expires_at;
	} else {
		$date_time = explode( 'T', (string) $expires_at, 2 );

		if ( isset( $date_time[1] ) ) {
			$time_part         = str_replace( 'Z', '', $date_time[1] );
			$expires_timestamp = strtotime( $date_time[0] . ' ' . $time_part );
		} else {
			$expires_timestamp = strtotime( $date_time[0] );
		}
	}

	if ( false === $expires_timestamp ) {
		return 'expired';
	}

	$today = strtotime( 'now' );

	if ( $today >= $expires_timestamp ) {

		return 'expired';

	} else {

		$creds        = wpep_get_creds( $current_form_id );
		$access_token = $creds['access_token'];

		$api_url = $creds['url'] . '/oauth2/token/status';
		$headers = array(
			'Square-Version' => '2023-01-19',
			'Authorization'  => 'Bearer ' . $access_token,
			'Content-Type'   => 'application/json',
		);

		$response = wp_remote_post(
			$api_url,
			array(
				'headers' => $headers,
			)
		);

		// Response information.
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		// Check for errors.
		if ( is_wp_error( $response ) ) {
			echo esc_html( 'Error: ' . $response->get_error_message() );
		}

		// Now you can handle the response as needed.
		$result = ( $response_body );

		if ( isset( json_decode( $result )->code ) && 'UNAUTHORIZED' === json_decode( $result )->code ) {
			return 'expired';
		} else {
			return 'active';
		}
	}
}

/**
 * Retrieve a Square customer by customer ID.
 *
 * @param object $api_client             The Square API client object.
 * @param string $square_customer_id    The ID of the Square customer.
 * @return object|null                  The Square customer object or null if not found.
 */
function wpep_retrieve_square_customer( $api_client, $square_customer_id ) {

	try {

		$result = $api_client->customers->get(
			new \Square\Customers\Requests\GetCustomersRequest(
				array(
					'customerId' => $square_customer_id,
				)
			)
		);
		return $result->getCustomer()->getId();

	} catch ( Exception $e ) {

		return false;
	}
}

/**
 * Retrieve the result of a Square customer retrieval request.
 *
 * @param object $api_client             The Square API client object.
 * @param string $square_customer_id    The ID of the Square customer.
 * @return object|null                  The result object of the customer retrieval request or null if not found.
 */
function wpep_retrieve_square_customer_result( $api_client, $square_customer_id ) {

	try {

		return $api_client->customers->get(
			new \Square\Customers\Requests\GetCustomersRequest(
				array(
					'customerId' => $square_customer_id,
				)
			)
		);

	} catch ( Exception $e ) {

		return false;
	}
}

/**
 * Retrieves the list of cards on file for a given Square customer.
 *
 * @param object $api_client          The Square API client instance.
 * @param string $square_customer_id  The Square customer ID.
 *
 * @return array|false An array of card objects on success, or `false` on failure.
 */
function wpep_retrieve_customer_cards( $api_client, $square_customer_id ) {
	try {

		$cards = array();
		foreach (
			$api_client->cards->list(
				new \Square\Cards\Requests\ListCardsRequest(
					array(
						'customerId' => $square_customer_id,
					)
				)
			) as $card
		) {
			$cards[] = $card;
		}

		return $cards;

	} catch ( Exception $e ) {
		return false;
	}
}

/**
 * Updates the stored cards on file for the current user in WordPress.
 *
 * @param object $api_client          The Square API client instance.
 * @param string $square_customer_id  The Square customer ID.
 * @param int    $wp_user_id          The WordPress user ID.
 *
 * @return void Updates the user meta with the customer's card information.
 */
function wpep_update_cards_on_file( $api_client, $square_customer_id, $wp_user_id ) { // phpcs:ignore

	$square_cards_on_file           = wpep_retrieve_customer_cards( $api_client, $square_customer_id );
	$card_on_files_to_store_locally = array();

	if ( ! empty( $square_cards_on_file ) && is_array( $square_cards_on_file ) ) {
		foreach ( $square_cards_on_file as $card ) {

			$card_container                     = array();
			$card_container['card_customer_id'] = $square_customer_id;
			$card_container['card_id']          = $card->getId();
			$card_container['card_holder_name'] = $card->getCardholderName();
			$card_container['card_brand']       = $card->getCardBrand();
			$card_container['card_last_4']      = $card->getLast4();
			$card_container['card_exp_month']   = $card->getExpMonth();
			$card_container['card_exp_year']    = $card->getExpYear();

			array_push( $card_on_files_to_store_locally, $card_container );

		}
	}

	$current_user = wp_get_current_user();

	update_user_meta( $current_user->ID, 'wpep_square_customer_cof', $card_on_files_to_store_locally );
}

/**
 * Deletes a customer's card on file from Square and updates the cards on file.
 *
 * @return void Outputs a success or error message based on the operation result.
 */
function wpep_delete_cof() {
	wpep_require_payment_post_nonce();
	$post = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing

	$square_customer_id = $post['customer_id'];
	$card_on_file       = str_replace( 'doc:', 'ccof:', $post['card_on_file'] );
	$current_form_id    = $post['current_form_id'];
	$api_client         = wpep_setup_square_configuration_by_form_id( $current_form_id );

	try {

		$api_client->customers->cards->delete(
			new \Square\Customers\Cards\Requests\DeleteCardsRequest(
				array(
					'customerId' => $square_customer_id,
					'cardId'     => $card_on_file,
				)
			)
		);
		wpep_update_cards_on_file( $api_client, $square_customer_id, get_current_user_id() );
		echo 'success';
		wp_die();

	} catch ( Exception $e ) {
		wpep_update_cards_on_file( $api_client, $square_customer_id, get_current_user_id() );
		wp_die( wp_json_encode( wpep_square_exception_error( $e ) ) );
	}
}

add_action( 'wp_ajax_wpep_delete_cof', 'wpep_delete_cof' );
add_action( 'wp_ajax_nopriv_wpep_delete_cof', 'wpep_delete_cof' );

/**
 * Calculates and displays the fee data for a payment form.
 *
 * This function processes the total amount, any discounts, and calculates additional fees (e.g., signup fees, percentage-based fees, etc.)
 * for a specific form. It outputs an unordered list of the fees and the total amount, including the subtotals, discounts, and any extra fees
 * that may be applied based on the form's configuration.
 *
 * @return void Outputs an HTML list of the fee data.
 */
function wpep_calculate_fee_data() {

	wpep_require_payment_post_nonce();

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified above.
	if ( isset( $_POST['current_form_id'] ) && isset( $_POST['total_amount'] ) && ! empty( $_POST['current_form_id'] ) && ! empty( $_POST['total_amount'] ) ) {

		$sub_total_amount = floatval( $_POST['total_amount'] );
		$total_amount     = $sub_total_amount;

		if ( isset( $_POST['discount'] ) ) {
			$discount = floatval( $_POST['discount'] );
		}

		$fees_data               = get_post_meta( sanitize_text_field( wp_unslash( $_POST['current_form_id'] ) ), 'fees_data', true );
		$wpep_enable_signup_fees = get_post_meta( sanitize_text_field( wp_unslash( $_POST['current_form_id'] ) ), 'wpep_enable_signup_fees', true );
		$currency                = isset( $_POST['currency'] ) ? sanitize_text_field( wp_unslash( $_POST['currency'] ) ) : '$';
		if ( ! empty( $fees_data[0]['check'] ) ) {
			?>
			<ul>				
			<?php
			if ( $discount > 0 ) {
				?>
				<li class="wpep-fee-subtotal">
					<span class="fee_name"><?php echo esc_html__( 'Subtotal', 'wp_easy_pay' ); ?></span>					
					<span class="fee_value"><?php echo esc_html( number_format( $sub_total_amount, 2 ) + number_format( $discount, 2 ) ) . ' ' . esc_attr( $currency ); ?></span>					
				</li>
				<li class="wpep-fee-discount">
					<span class="fee_name"><?php echo esc_html__( 'Discount', 'wp_easy_pay' ); ?></span>
					<span class="fee_value"><?php echo '-' . esc_attr( number_format( $discount, 2 ) ) . ' ' . esc_attr( $currency ); ?></span>
				</li>
				<?php
			} else {
				?>
				<li class="wpep-fee-subtotal">
					<span class="fee_name"><?php echo esc_html__( 'Subtotal', 'wp_easy_pay' ); ?></span>					
					<span class="fee_value"><?php echo esc_html( number_format( $sub_total_amount, 2 ) ) . ' ' . esc_attr( $currency ); ?></span>					
				</li>
				<?php
			}
			if ( 'yes' === $wpep_enable_signup_fees ) :
							$wpep_signup_fees_value = get_post_meta( sanitize_text_field( wp_unslash( $_POST['current_form_id'] ) ), 'wpep_signup_fees_amount', true );
				?>
						<li class="wpep-fee-onetime">
							<span class="fee_name"><?php echo esc_html__( 'Signup Fee', 'wp_easy_pay' ); ?></span>
							<span class="fee_value"><?php echo esc_html( number_format( $wpep_signup_fees_value, 2 ) ) . ' ' . esc_attr( $currency ); ?></span>
							<input type='hidden' value='<?php echo esc_attr( number_format( $wpep_signup_fees_value, 2 ) ); ?>' name='wpep-signup-amount'>
						</li>
						<?php
							$total_amount = $total_amount + $wpep_signup_fees_value;
			endif;
			foreach ( $fees_data[0]['check'] as $key => $fees ) :
				if ( 'yes' === $fees ) :

					if ( 'percentage' === $fees_data[0]['type'][ $key ] ) {
						$tax = $sub_total_amount * ( $fees_data[0]['value'][ $key ] / 100 );
					} else {
						$tax = $fees_data[0]['value'][ $key ];
					}

					$total_amount = $total_amount + $tax;
					?>
					<li>
						<span class="fee_name"><?php echo esc_html( $fees_data[0]['name'][ $key ] ); ?></span>
						<span class="fee_value"><?php echo esc_html( number_format( $tax, 2 ) ) . ' ' . esc_attr( $currency ); ?></span>
					</li>
					<?php
				endif;
			endforeach;
			?>

				<li class="wpep-fee-total">
					<span class="fee_name"><?php echo esc_html__( 'Total', 'wp_easy_pay' ); ?></span>
					<span class="fee_value"><?php echo esc_html( number_format( $total_amount, 2 ) ) . ' ' . esc_attr( $currency ); ?></span>
				</li>
			</ul>
			<?php
		}

		wp_die();
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing
}

add_action( 'wp_ajax_wpep_calculate_fee_data', 'wpep_calculate_fee_data' );
add_action( 'wp_ajax_nopriv_wpep_calculate_fee_data', 'wpep_calculate_fee_data' );
