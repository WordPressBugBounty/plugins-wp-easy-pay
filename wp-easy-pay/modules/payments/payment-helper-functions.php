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

	require_once WPEP_ROOT_PATH . 'modules/email_notifications/new-user-email.php';
	wpep_new_user_email_notification( $username, $password, $email );

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
function wpep_create_square_customer_card( $api_client, $square_customer_id, $nonce, $first_name, $last_name, $verification_token ) {

	$api_instance     = new SquareConnect\Api\CustomersApi( $api_client );
	$card_holder_name = $first_name . ' ' . $last_name;

	$body = new \SquareConnect\Model\CreateCustomerCardRequest();
	$body->setCardNonce( $nonce );
	$body->setCardholderName( $card_holder_name );
	$body->setVerificationToken( $verification_token );

	try {

		$result = $api_instance->createCustomerCard( $square_customer_id, $body );
		return $result->getCard()->getId();

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
		'timeout' => 45,
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

	$live_mode = get_option( 'wpep_square_payment_mode_global', true );
	if ( 'on' === $live_mode ) {
		$sandbox_enabled = 'no';
	} else {
		$sandbox_enabled = 'yes';
	}

	if ( 'expired' === $expiry_status ) {

		$oauth_connect_url = WPEP_MIDDLE_SERVER_URL;

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

	$expires_at = strtotime( $date_time[0] . ' ' . $date_time[1] );
	$today      = strtotime( 'now' );

	if ( $today >= ( $expires_at - 300 ) ) {
		return 'expired';
	} else {

		$creds        = wpep_get_creds( $current_form_id );
		$access_token = $creds['access_token'];

		$request_args = array(
			'headers' => array(
				'Square-Version' => '2023-01-19',
				'Authorization'  => 'Bearer ' . $access_token,
				'Content-Type'   => 'application/json',
			),
		);

		$response = wp_remote_get( $creds['url'] . '/oauth2/token/status', $request_args );

		if ( is_wp_error( $response ) ) {
			echo 'Error: ' . esc_html( $response->get_error_message() );
		} else {
			$body    = wp_remote_retrieve_body( $response );
			$decoded = json_decode( $body );

			if ( 'UNAUTHORIZED' === $decoded && $decoded->type ) {
				return 'expired';
			} else {
				return 'active';
			}
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
	 * Retrieve the cards associated with a Square customer.
	 *
	 * @param object $api_client             The Square API client object.
	 * @param string $square_customer_id    The ID of the Square customer.
	 * @return array|null                   An array of customer cards or null if no cards found.
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
	 * Update the cards on file for a Square customer and associate them with a WordPress user.
	 *
	 * @param object $api_client             The Square API client object.
	 * @param string $square_customer_id    The ID of the Square customer.
	 * @param int    $wp_user_id            The ID of the WordPress user.
	 */
function wpep_update_cards_on_file( $api_client, $square_customer_id, $wp_user_id ) {

	$square_cards_on_file = wpep_retrieve_customer_cards( $api_client, $square_customer_id );

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

	update_user_meta( $wp_user_id, 'wpep_square_customer_cof', $card_on_files_to_store_locally );
}





