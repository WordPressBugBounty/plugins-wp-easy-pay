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

	$api_instance = new SquareConnect\Api\CustomersApi( $api_client );
	try {
		$result = $api_instance->retrieveCustomer( $square_customer_id );
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

	$api_instance     = new SquareConnect\Api\CustomersApi( $api_client );
	$card_holder_name = $first_name . ' ' . $last_name;

	$body = new \SquareConnect\Model\CreateCustomerCardRequest();
	$body->setCardNonce( $nonce );
	$body->setCardholderName( $card_holder_name );

	try {

		$result = $api_instance->createCustomerCard( $square_customer_id, $body );
		return $result->getCard()->getId();

	} catch ( Exception $e ) {
		wp_die( wp_json_encode( $e->getResponseBody()->errors[0] ) );
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

	if ( isset( $post['wp_payment_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $post['wp_payment_nonce'] ) ), 'payment_nonce' ) ) {
		$error = array(
			'status' => 'failed',
			'code'   => '',
			'detail' => 'Sorry! Your request cannot be completed.',
		);

		wp_die( wp_json_encode( $error ) );
	}

	$post = $_POST;

	$api_instance = new SquareConnect\Api\CustomersApi( $api_client );
	$body         = new \SquareConnect\Model\CreateCustomerRequest();
	$unique_key   = uniqid() . 'wpexperts';

	$body->setIdempotencyKey( $unique_key );
	$body->setGivenName( $post['first_name'] );
	$body->setFamilyName( $post['last_name'] );
	$body->setEmailAddress( $post['email'] );
	$body->setReferenceId( $unique_key );

	try {

		$result = $api_instance->createCustomer( $body );
		return $result->getCustomer()->getId();

	} catch ( Exception $e ) {
		wp_die( wp_json_encode( $e->getResponseBody()->errors[0] ) );
	}
}

/**
 * Perform weekly refresh of tokens.
 *
 * @return void
 */
function wpep_weekly_refresh_tokens() {

	$oauth_connect_url    = WPEP_MIDDLE_SERVER_URL;
	$refresh_access_token = get_option( 'wpep_refresh_token' );

	$args_renew = array(

		'body'    => array(

			'request_type'  => 'renew_token',
			'refresh_token' => $refresh_access_token,
			'oauth_version' => 2,
			'app_name'      => WPEP_SQUARE_APP_NAME,

		),
		'timeout' => 0.01,
	);

	$oauth_response      = wp_remote_post( $oauth_connect_url, $args_renew );
	$oauth_response_body = json_decode( $oauth_response['body'] );

	update_option( 'wpep_live_token_upgraded', sanitize_text_field( $oauth_response_body->access_token ) );
	update_option( 'wpep_refresh_token', $oauth_response_body->refresh_token );
	update_option( 'wpep_token_expires_at', $oauth_response_body->expires_at );
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

	$date_time    = explode( 'T', $expires_at );
	$date_time[1] = str_replace( 'Z', '', $date_time[1] );
	$expires_at   = strtotime( $date_time[0] . ' ' . $date_time[1] );
	$today        = strtotime( 'now' );

	if ( $today >= $expires_at ) {

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

		$api_instance = new SquareConnect\Api\CustomersApi( $api_client );
		$result       = $api_instance->retrieveCustomer( $square_customer_id );
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

		$api_instance = new SquareConnect\Api\CustomersApi( $api_client );
		$result       = $api_instance->retrieveCustomer( $square_customer_id );
		return $result;

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

		$api_instance = new SquareConnect\Api\CustomersApi( $api_client );
		$result       = $api_instance->retrieveCustomer( $square_customer_id );
		return $result->getCustomer()->getCards();

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

	$current_user = wp_get_current_user();

	update_user_meta( $current_user->ID, 'wpep_square_customer_cof', $card_on_files_to_store_locally );
}

/**
 * Deletes a customer's card on file from Square and updates the cards on file.
 *
 * @return void Outputs a success or error message based on the operation result.
 */
function wpep_delete_cof() {
	if ( isset( $_POST['wp_global_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ), 'wp_global_nonce' ) ) {
		exit;
	}
	$post = $_POST;

	$square_customer_id = $post['customer_id'];
	$card_on_file       = str_replace( 'doc:', 'ccof:', $post['card_on_file'] );
	$current_form_id    = $post['current_form_id'];
	$api_client         = wpep_setup_square_configuration_by_form_id( $current_form_id );
	$api_instance       = new SquareConnect\Api\CustomersApi( $api_client );

	try {

		$result = $api_instance->deleteCustomerCard( $square_customer_id, $card_on_file );
		wpep_update_cards_on_file( $api_client, $square_customer_id, get_current_user_id() );
		echo 'success';
		wp_die();

	} catch ( Exception $e ) {
		wpep_update_cards_on_file( $api_client, $square_customer_id, get_current_user_id() );
		wp_die( wp_json_encode( $e->getResponseBody()->errors[0] ) );
	}
}

add_action( 'wp_ajax_wpep_delete_cof', 'wpep_delete_cof' );
add_action( 'wp_ajax_nopriv_wpep_delete_cof', 'wpep_delete_cof' );

/**
 * Applies a coupon to the total amount and returns the result in JSON format.
 *
 * @return void Outputs a JSON response with the coupon application status and updated total.
 */
function wpep_apply_coupon() {

	if ( isset( $_POST['wp_global_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ), 'wp_global_nonce' ) ) {
		exit;
	}

	$post = $_POST;

	if ( isset( $post['cp_submit'] ) && 'Apply' === sanitize_text_field( $post['cp_submit'] ) ) {

		if ( isset( $post['coupon_code'] ) && isset( $post['total_amount'] ) && ! empty( $post['coupon_code'] ) && ! empty( $_POST['total_amount'] ) ) {

			$today        = gmdate( 'Y-m-d' );
			$total_amount = sanitize_text_field( $post['total_amount'] );
			$currency     = sanitize_text_field( $post['currency'] );
			$code         = sanitize_text_field( $post['coupon_code'] );
			$form_id      = sanitize_text_field( $post['current_form_id'] );
			$flag         = false;
			$result       = array(
				'status'          => '',
				'message_success' => '',
				'message_failed'  => '',
			);

			// check if coupon already applied on the cart.
			if ( isset( $post['discounted'] ) && 'yes' === $post['discounted'] ) {
				$result['status']         = 'failed';
				$result['message_failed'] = 'Coupon already applied!';
				echo( wp_json_encode( $result ) );
				wp_die();
			}

			// get post id by post meta key & value.
			$args = array(
				'post_type'   => 'wpep_coupons',
				'post_status' => array( 'publish' ),
				'meta_query'  => array( // phpcs:ignore
					array(
						'value' => $code,
					),
				),
			);

			$coupon = new WP_Query( $args );

			if ( isset( $coupon->posts[0] ) ) {

				$post_ID = $coupon->posts[0]->ID;

				$wpep_coupons_discount_type = get_post_meta( $post_ID, 'wpep_coupons_discount_type', true );
				$wpep_coupons_amount        = get_post_meta( $post_ID, 'wpep_coupons_amount', true );
				$wpep_coupons_expiry        = get_post_meta( $post_ID, 'wpep_coupons_expiry', true );
				$wpep_coupons_form_include  = get_post_meta( $post_ID, 'wpep_coupons_form_include', true );
				$wpep_coupons_form_exclude  = get_post_meta( $post_ID, 'wpep_coupons_form_exclude', true );

				if ( ! empty( $wpep_coupons_discount_type ) && ! empty( $wpep_coupons_amount ) && ! empty( $wpep_coupons_expiry ) ) {

					$wpep_coupons_expiry = gmdate( 'Y-m-d', strtotime( $wpep_coupons_expiry ) );

					if ( ! empty( $wpep_coupons_form_exclude ) ) {

						if ( in_array( $form_id, $wpep_coupons_form_exclude, true ) ) {
							$flag = false;
						} else {
							$flag = true;
						}
					} else {

						$flag = true;

					}

					if ( ! empty( $wpep_coupons_form_include ) ) {

						if ( in_array( $form_id, $wpep_coupons_form_include, true ) ) {
							$flag = true;
						} else {
							$flag = false;
						}
					} else {

						$flag = true;

					}

					if ( true === $flag ) {

						if ( $today > $wpep_coupons_expiry ) {

							$result['status']         = 'failed';
							$result['message_failed'] = 'Sorry, Your coupon has been expired!';

						} else {

							$total_amount = explode( ' ', $total_amount );
							$subtotal     = floatval( $total_amount[0] );
							$currency     = $currency;

							if ( 'percentage' === $wpep_coupons_discount_type ) {

								$discount = ( $wpep_coupons_amount / 100 ) * $subtotal;
								$total    = $subtotal - $discount;
							}

							if ( 'fixed' === $wpep_coupons_discount_type ) {

								$discount = $wpep_coupons_amount;
								$total    = $subtotal - $discount;

							}

							if ( $total < 0 ) {
								$total = 0;
							}

							$result['status']          = 'success';
							$result['message_success'] = 'Congratulation! Coupon applied successfully.';
							$result['currency']        = $post['currency'];
							$result['discount']        = $discount;
							$result['total']           = $total;

						}
					} else {
						$result['status']         = 'failed';
						$result['message_failed'] = 'Sorry, coupon does\'nt exist for this form!';
					}
				}
			} else {
				$result['status']         = 'failed';
				$result['message_failed'] = 'Sorry, coupon does\'nt exist!';
			}

			echo( wp_json_encode( $result ) );
			wp_die();

		}
	}
}
add_action( 'wp_ajax_wpep_apply_coupon', 'wpep_apply_coupon' );
add_action( 'wp_ajax_nopriv_wpep_apply_coupon', 'wpep_apply_coupon' );

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

	if ( isset( $_POST['wp_global_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ), 'wp_global_nonce' ) ) {
		exit;
	}

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
}

add_action( 'wp_ajax_wpep_calculate_fee_data', 'wpep_calculate_fee_data' );
add_action( 'wp_ajax_nopriv_wpep_calculate_fee_data', 'wpep_calculate_fee_data' );
