<?php
/**
 * Filename: square-payments.php
 * Description: silence is golden.
 *
 * @package WP_Easy_Pay
 */

require_once WPEP_ROOT_PATH . 'modules/payments/square-configuration.php';
require_once WPEP_ROOT_PATH . 'modules/payments/payment-helper-functions.php';
require_once WPEP_ROOT_PATH . 'modules/error-logging.php';

add_action( 'wp_ajax_wpep_payment_request', 'wpep_payment_request' );
add_action( 'wp_ajax_nopriv_wpep_payment_request', 'wpep_payment_request' );

add_action( 'wp_ajax_wpep_file_upload', 'wpep_file_upload' );
add_action( 'wp_ajax_nopriv_wpep_file_upload', 'wpep_file_upload' );

add_action( 'wp_ajax_wpep_payment_refund', 'wpep_payment_refund' );

/**
 * Handle file upload.
 */
function wpep_file_upload() {
	if ( isset( $_POST['wp_global_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ), 'wp_global_nonce' ) ) {
		exit;
	}

	$files        = $_FILES;
	$uploadedfile = $files['file'];

	$upload_overrides = array(
		'test_form' => false,
	);
	$movefile         = wp_handle_upload( $uploadedfile, $upload_overrides );

	if ( $movefile && ! isset( $movefile['error'] ) ) {
		$return_response = array(
			'uploaded_file_url' => $movefile['url'],
		);
		wp_die( wp_json_encode( $return_response ) );
	} else {
		echo wp_json_encode( $movefile );
		wp_die();
	}
}

/**
 * Handle payment request.
 */
function wpep_payment_request() {

	if ( isset( $_POST['wp_payment_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_payment_nonce'] ) ), 'payment_nonce' ) ) {
		$error = array(
			'status' => 'failed',
			'code'   => '',
			'detail' => 'Sorry! Your request cannot be completed.',
		);

		wp_die( wp_json_encode( $error ) );
	}

	$post            = $_POST;
	$payment_type    = $post['payment_type'];
	$save_card       = $post['save_card'];
	$cof             = $post['card_on_file'];
	$first_name      = $post['first_name'];
	$last_name       = $post['last_name'];
	$email           = $post['email'];
	$nonce           = $post['nonce'];
	$current_form_id = $post['current_form_id'];
	$amount          = $post['amount'];
	$currency        = $post['currency'];

	if ( isset( $cof ) && ! empty( $cof ) && 'false' !== $cof ) {

		if ( isset( $_POST['email'] ) ) {
			$email = sanitize_text_field( wp_unslash( $_POST['email'] ) );
		}

		$cof        = str_replace( 'doc:', 'ccof:', $cof );
		$wp_user_id = email_exists( $email );
		if ( $wp_user_id ) {
			$stored_square_customer_id = get_user_meta( $wp_user_id, 'wpep_square_customer_id', true );
		}

		update_option( 'cof_to_use', $cof );
		update_option( 'customer_id_to_use', $stored_square_customer_id );
	}

	if ( 'single' === $payment_type ) {
		$single_pay = wpep_single_square_payment();

		if ( ! empty( $single_pay['errorCode'] ) ) {
			$data = array(
				'errorCode' => $single_pay['errorCode'],
				'detail'    => $single_pay['errorDetail'],
			);
			echo wp_json_encode( $data );
			wp_die();
		}
	}

	if ( 'donation_recurring' === $payment_type ) {

		wpep_subscription_square_payment();

	}
}

/**
 * Process a single payment using the Square payment gateway.
 *
 * @param string|false $square_customer_id         The Square customer ID. False if not available.
 * @param bool         $square_customer_card_on_file Whether to use the customer's card on file in Square.
 * @param int|false    $current_form_id            The ID of the current form. False if not available.
 * @param float|false  $amount                     The amount to be paid. False if not available.
 *
 * @return bool Whether the payment was successful (true) or not (false).
 */
function wpep_single_square_payment( $square_customer_id = false, $square_customer_card_on_file = false, $current_form_id = false, $amount = false ) {

	// if recaptch enable.
	if ( isset( $_POST['wp_global_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ), 'wp_global_nonce' ) ) {
		exit;
	}

	$post                   = $_POST;
	$subscription           = false;
	$scheduled_subscription = false;
	$form_values            = $post['form_values'];

	if ( 'false' !== $post['buyer_verification'] ) {
		$verification_token = $post['buyer_verification'];
	}

	$payment_type = $post['payment_type'];

	if ( false !== $square_customer_id && false !== $square_customer_card_on_file && false === $current_form_id && false === $amount ) {
		$subscription = true;
	}

	/* If $square_customer_id, $square_customer_card_on_file, $current_form_id and $amount are true it means it's scheduled subscription charge */
	if ( false !== $square_customer_id && false !== $square_customer_card_on_file && false !== $current_form_id && false !== $amount ) {
		$scheduled_subscription = true;
	}

	if ( false === $current_form_id ) {
		$current_form_id = $post['current_form_id'];
	}

	$refresh_token_details = wpep_refresh_token_details( $current_form_id );

	if ( 0 !== count( $refresh_token_details ) ) {
		$expires_at    = $refresh_token_details['expires_at'];
		$refresh_token = $refresh_token_details['refresh_token'];
		$type          = $refresh_token_details['type'];

		$response = wpep_square_refresh_token( $expires_at, $refresh_token, $type, $current_form_id );
	}

	$api_client  = wpep_setup_square_configuration_by_form_id( $current_form_id );
	$location_id = wpep_get_location_by_form_id( $current_form_id );

	$payments_api = new \SquareConnect\Api\PaymentsApi( $api_client );
	$body         = new \SquareConnect\Model\CreatePaymentRequest();

	if ( ! empty( $post ) && false === $amount && isset( $post['nonce'] ) ) {
		$nonce = $post['nonce'];
		if ( false !== strpos( $post['amount'], '$' ) ) {
			$amount = str_replace( '$', '', $post['amount'] );
			$amount = trim( $amount, ' ' );
		} else {
			$amount = $post['amount'];
		}

		$amount = str_replace( ',', '', $amount );

		$signup_amount = ( isset( $post['signup_amount'] ) ) ? floatval( str_replace( ',', '', $post['signup_amount'] ) ) : '';

		$body->setSourceId( $nonce );
	}

	if ( ! empty( $post ) && isset( $post['card_on_file'] ) && 'false' !== $post['card_on_file'] ) {
		$amount        = $amount;
		$signup_amount = $post['signup_amount'];

		if ( 'false' !== $post['save_customer_id'] ) {
				$save_customer_id = $post['save_customer_id'];
		}

		$square_customer_id           = $save_customer_id;
		$square_customer_card_on_file = $post['card_on_file'];
		$square_customer_card_on_file = str_replace( 'doc:', 'ccof:', $square_customer_card_on_file );

		$body->setCustomerId( $square_customer_id );
		$body->setSourceId( $square_customer_card_on_file );
	}

	$cof_to_use         = get_option( 'cof_to_use' );
	$customer_id_to_use = get_transient( 'customer_id_to_use' );

	if ( isset( $cof_to_use ) && ! empty( $cof_to_use ) && 'false' !== $cof_to_use ) {

		$body->setCustomerId( $customer_id_to_use );
		$body->setSourceId( $cof_to_use );
	}

	$square_currency = wpep_get_currency( $current_form_id );
	$amount_money    = new \SquareConnect\Model\Money();

	if ( 'JPY' !== $square_currency ) {
		$amount = $amount * 100;
	}

	$amount = (int) ( round( $amount ) );

	$signup_amount        = (float) $signup_amount;
	$report_amount        = $amount;
	$report_signup_amount = $signup_amount;
	$amount_money->setAmount( $amount );
	$amount_money->setCurrency( $square_currency );
	$body->setAmountMoney( $amount_money );
	$body->setLocationId( $location_id );

	if ( isset( $_POST['save_card'] ) ) {
		$save_card = sanitize_text_field( wp_unslash( $_POST['save_card'] ) );
	}

	if ( 'single' === $payment_type && 'true' !== $save_card ) {
		( isset( $verification_token ) ) ? $body->setVerificationToken( $verification_token ) : '';
	}

	$note               = get_post_meta( $current_form_id, 'wpep_transaction_notes_box', true );
	$fees_data          = get_post_meta( $current_form_id, 'fees_data', true );
	$form_values_object = (object) $form_values;

	foreach ( $form_values_object as $form_value ) {

		if ( isset( $form_value['label'] ) && isset( $form_value['value'] ) ) {

			$label = $form_value['label'];
			$value = $form_value['value'];

			if ( null !== $label ) {

				if ( 'Email' === $label ) {
					$label = 'user_email';
					$to    = $value;
				}
				$tag  = '[' . str_replace( ' ', '_', strtolower( $label ) ) . ']';
				$note = str_replace( $tag, $value, $note );

				if ( isset( $fees_data[0] ) && count( $fees_data[0] ) > 0 ) {
					foreach ( $fees_data[0]['name'] as $key => $fees ) {
						$fees_name  = isset( $fees_data[0]['name'][ $key ] ) ? $fees_data[0]['name'][ $key ] : '';
						$fees_value = isset( $fees_data[0]['value'][ $key ] ) ? $fees_data[0]['value'][ $key ] : '';
						$fees_type  = isset( $fees_data[0]['type'][ $key ] ) ? $fees_data[0]['type'][ $key ] : '';
						if ( 'percentage' === $fees_type ) {
							$fees_type = '%';
						} else {
							$fees_type = 'fixed';
						}
						$note = str_replace( '[' . $fees_name . ']', $fees_value . ' ' . $fees_type, $note );
					}
				}
			}
		}
	}

	if ( $scheduled_subscription ) {
		$body->setNote( 'Scheduled Payment' );
	} else {
		$body->setNote( $note );
	}
	$body->setIdempotencyKey( uniqid() );

	$num_of_attempts = 5;
	$attempts        = 0;

	do {

		try {

			$creds        = wpep_get_creds( $current_form_id );
			$access_token = $creds['access_token'];

			$headers = array(
				'Square-Version' => '2022-04-20',
				'Authorization'  => 'Bearer ' . $access_token,
				'Content-Type'   => 'application/json',
			);

			$data = array(
				'idempotency_key' => uniqid(),
				'order'           => array(
					'location_id' => $location_id,
					'line_items'  => array(
						array(
							'name'             => ucfirst( $payment_type ),
							'quantity'         => '1',
							'base_price_money' => array(
								'amount'   => $amount,
								'currency' => $square_currency,
							),
						),
					),
				),
			);

			$response = wp_remote_post(
				$creds['url'] . '/v2/orders',
				array(
					'headers' => $headers,
					'body'    => wp_json_encode( $data ),
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
			$result = json_decode( $response_body );

			$order_id = $result->order->id;

			$body->setOrderId( $order_id );

			$result = $payments_api->createPayment( $body );

			delete_option( 'cof_to_use' );
			delete_option( 'customer_id_to_use' );
			$transaction_id         = $result->getPayment()->getId();
			$transaction_status     = $result->getPayment()->getStatus();
			$transaction_source     = $result->getPayment()->getSourceType();
			$transaction_ach_status = $result->getPayment()->getSourceType();
			if ( isset( $transaction_id ) && 'BANK_ACCOUNT' === $transaction_ach_status ) {
				$transaction_status = 'COMPLETED';
			}
			$transaction_data = array(
				'transaction_id'     => $transaction_id,
				'transaction_status' => $transaction_status,
				'transaction_source' => $transaction_source,
			);

			if ( $scheduled_subscription ) {
				return $transaction_data;
			}

			/* Adding Single Transaction Report */
			if ( 'single' === $payment_type || 'donation' === $payment_type ) {

				foreach ( $form_values as $value ) {

					if ( isset( $value['label'] ) ) {

						if ( 'total_amount' === $value['label'] ) {
							$report_amount = $value['value'];
						}
					}
				}

				$type_of_payment = get_post_meta( $current_form_id, 'wpep_square_payment_type', true );
				if ( 'donation' === $type_of_payment ) {
					$wpep_donation_goal_switch = get_post_meta( $current_form_id, 'wpep_donation_goal_switch', true );
					$wpep_donation_goal_amount = get_post_meta( $current_form_id, 'wpep_donation_goal_amount', true );
					if ( 'checked' === $wpep_donation_goal_switch && ! empty( trim( $wpep_donation_goal_amount ) ) ) {
						$achieved_amount  = floatval( ! empty( get_post_meta( $current_form_id, 'wpep_donation_goal_achieved', true ) ) ? get_post_meta( $current_form_id, 'wpep_donation_goal_achieved', true ) : 0 );
						$paid_amount      = str_replace( ',', '', $report_amount );
						$achieving_amount = floatval( $paid_amount ) + $achieved_amount;
						update_post_meta( $current_form_id, 'wpep_donation_goal_achieved', $achieving_amount );
					}
				}

				$personal_information = array(
					'first_name'      => $post['first_name'],
					'last_name'       => $post['last_name'],
					'email'           => $post['email'],
					'amount'          => $report_amount,
					'signup_amount'   => $report_signup_amount,
					'discount'        => isset( $post['discount'] ) ? $post['discount'] : 0,
					'current_form_id' => $current_form_id,
					'form_values'     => $form_values,
					'currency'        => $square_currency,
				);

				// adding additional tax values to subscription reports.
				if ( isset( $fees_data[0] ) && count( $fees_data[0] ) > 0 ) {
					$personal_information['taxes'] = $fees_data[0];
				}

				require_once WPEP_ROOT_PATH . 'modules/reports/transaction-report.php';
				$wpep_transaction_id = wpep_single_transaction_report( $transaction_data, $current_form_id, $personal_information );

				require_once WPEP_ROOT_PATH . 'modules/email_notifications/admin-email.php';
				wpep_send_admin_email( $current_form_id, $form_values, $transaction_id, $square_currency );

				require_once WPEP_ROOT_PATH . 'modules/email_notifications/user-email.php';
				wpep_send_user_email( $current_form_id, $form_values, $transaction_id, $square_currency );

			}

			$response = array(
				'status'                => 'success',
				'transaction_report_id' => $wpep_transaction_id,
			);

			wp_die( wp_json_encode( $response ) );

		} catch ( \SquareConnect\ApiException $e ) {

			wpep_write_log( wp_json_encode( $e->getResponseBody()->errors[0] ) );

			if ( 'too_many_requests' === $e->getResponseBody()->errors[0]->code ) {
				++$attempts;
				sleep( 5 );
				continue;
			}

			$error = array(
				'status' => 'failed',
				'code'   => $e->getResponseBody()->errors[0]->code,
				'detail' => $e->getResponseBody()->errors[0]->detail,
			);

			wp_die( wp_json_encode( $error ) );

		}

		break;

	} while ( $attempts < $num_of_attempts );
}

/**
 * Get credentials associated with the form ID.
 *
 * @param int $wpep_current_form_id The ID of the current form.
 * @return array|null An array containing the credentials or null if not found.
 */
function wpep_get_creds( $wpep_current_form_id ) {

	if ( isset( $_POST['wp_global_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ), 'wp_global_nonce' ) ) {
		exit;
	}
	$post = $_POST;

	if ( empty( $wpep_current_form_id ) ) {
		$form_payment_global = 'on';
	} else {
		$form_payment_global = get_post_meta( $wpep_current_form_id, 'wpep_individual_form_global', true );
	}
	if ( 'on' === $form_payment_global ) {

		$global_payment_mode = get_option( 'wpep_square_payment_mode_global', true );

		if ( 'on' === $global_payment_mode ) {

			/* If Global Form Live Mode */
			$access_token = get_option( 'wpep_live_token_upgraded', true );

			$creds['access_token']       = $access_token;
			$creds['url']                = 'https://connect.squareup.com';
			$creds['_payment_mode']      = $global_payment_mode;
			$creds['_location_id']       = get_option( 'wpep_square_location_id', true );
			$creds['wpep_refresh_token'] = get_option( 'wpep_refresh_token', true );

		}

		if ( 'on' !== $global_payment_mode ) {

			/* If Global Form Test Mode */
			$access_token = get_option( 'wpep_square_test_token_global', true );

			$creds['access_token']  = $access_token;
			$creds['url']           = 'https://connect.squareupsandbox.com';
			$creds['_payment_mode'] = $global_payment_mode;
			$creds['_location_id']  = get_option( 'wpep_square_test_location_id_global', false );

		}
	}

	if ( 'on' !== $form_payment_global ) {

		$individual_payment_mode = get_post_meta( $wpep_current_form_id, 'wpep_payment_mode', true );

		if ( 'on' === $individual_payment_mode ) {

			/* If Individual Form Live Mode */
			$access_token = get_post_meta( $wpep_current_form_id, 'wpep_live_token_upgraded', true );

			$creds['access_token']       = $access_token;
			$creds['url']                = 'https://connect.squareup.com';
			$creds['_payment_mode']      = $individual_payment_mode;
			$creds['_location_id']       = get_post_meta( $wpep_current_form_id, 'wpep_square_location_id', true );
			$creds['wpep_refresh_token'] = get_post_meta( $wpep_current_form_id, 'wpep_refresh_token', true );

		}

		if ( 'on' !== $individual_payment_mode ) {

			/* If Individual Form Test Mode */
			$access_token = get_post_meta( $wpep_current_form_id, 'wpep_square_test_token', true );

			$creds['access_token']  = $access_token;
			$creds['url']           = 'https://connect.squareupsandbox.com';
			$creds['_payment_mode'] = $individual_payment_mode;
			$creds['_location_id']  = get_post_meta( $wpep_current_form_id, 'wpep_square_test_location_id', true );

		}
	}
	$creds['_payment_global'] = $form_payment_global;
	return $creds;
}
