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

add_action( 'admin_init', 'wpep_authorize_with_square' );
add_action( 'admin_init', 'wpep_square_callback_success' );
add_action( 'admin_init', 'wpep_square_disconnect' );

/**
 * Authorizes the plugin with Square and retrieves the access token.
 */
function wpep_authorize_with_square() {

	if ( isset( $_POST['wp_global_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ), 'wp_global_nonce' ) ) {
		exit;
	}
	unset( $_REQUEST['cp_admin_page_nonce'] );
	$request = $_REQUEST;

	if ( ! empty( $_GET['wpep_prepare_connection_call'] ) ) {

		$url_identifiers                  = $request;
		$url_identifiers['oauth_version'] = 2;
		$url_identifiers['wp_nonce']      = esc_attr( wp_create_nonce( wp_rand( 10, 100 ) ) );
		unset( $url_identifiers['wpep_prepare_connection_call'] );
		$redirect_url = add_query_arg( $url_identifiers, admin_url( $url_identifiers['wpep_admin_url'] ) );

		$redirect_url       = wp_nonce_url( $redirect_url, 'connect_wpep_square', 'wpep_square_token_nonce' );
		$usf_state          = substr( str_shuffle( 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' ), 1, 10 );
		$middle_server_data = array(

			'redirect'      => rawurlencode( $redirect_url ),
			'scope'         => rawurlencode( 'MERCHANT_PROFILE_READ PAYMENTS_READ PAYMENTS_WRITE CUSTOMERS_READ CUSTOMERS_WRITE ORDERS_WRITE ITEMS_WRITE INVOICES_WRITE SUBSCRIPTIONS_WRITE ITEMS_READ SUBSCRIPTIONS_READ INVOICES_READ DEVICE_CREDENTIAL_MANAGEMENT INVENTORY_READ INVENTORY_WRITE' ),
			'plug'          => WPEP_SQUARE_PLUGIN_NAME,
			'app_name'      => WPEP_SQUARE_APP_NAME,
			'oauth_version' => 2,
			'request_type'  => 'authorization',
			'usf_state'     => $usf_state,

		);

		update_option( 'wpep_usf_state', $usf_state );

		if ( isset( $url_identifiers['wpep_sandbox'] ) ) {

			$middle_server_data['sandbox_enabled'] = 'yes';

		}

		$middle_server_url = add_query_arg( $middle_server_data, WPEP_MIDDLE_SERVER_URL );

		$query_arg = array(

			'app_name'               => WPEP_SQUARE_APP_NAME,
			'wpep_disconnect_square' => 1,
			'wpep_disconnect_global' => 'true',

		);

		if ( isset( $request['wpep_page_post'] ) && ! empty( $request['wpep_page_post'] ) && 'global' === $request['wpep_page_post'] ) {

			$query_arg['wpep_disconnect_global'] = 'true';
			if ( isset( $url_identifiers['wpep_sandbox'] ) ) {
				$query_arg['wpep_disconnect_sandbox_global'] = $url_identifiers['wpep_sandbox'];
			}

			$query_arg      = array_merge( $url_identifiers, $query_arg );
			$disconnect_url = admin_url( $url_identifiers['wpep_admin_url'] );
			$disconnect_url = add_query_arg( $query_arg, $disconnect_url );

			if ( isset( $url_identifiers['wpep_sandbox'] ) ) {

				update_option( 'wpep_square_test_disconnect_url', $disconnect_url );

			} else {

				update_option( 'wpep_square_disconnect_url', $disconnect_url );
			}
		}

		if ( isset( $request['wpep_page_post'] ) && ! empty( $request['wpep_page_post'] ) && 'global' !== $request['wpep_page_post'] ) {

			$query_arg['wpep_disconnect_global'] = 'false';
			$query_arg['wpep_form_id']           = $request['wpep_page_post'];

			$query_arg      = array_merge( $url_identifiers, $query_arg );
			$disconnect_url = admin_url( $url_identifiers['wpep_admin_url'] );
			$disconnect_url = add_query_arg( $query_arg, $disconnect_url );

			update_post_meta( $query_arg['wpep_form_id'], 'wpep_square_disconnect_url', $disconnect_url );

		}

		wp_redirect( $middle_server_url ); // phpcs:ignore
		exit;

	}
}

/**
 * Callback function for successful Square authorization.
 */
function wpep_square_callback_success() {

	if ( isset( $_POST['wp_global_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ), 'wp_global_nonce' ) ) {
		exit;
	}

	$request = $_REQUEST;

	if ( ! empty( $request['access_token'] ) && ! empty( $request['token_type'] ) && ! empty( $request['wpep_square_token_nonce'] ) && 'bearer' === $request['token_type'] ) {

		if ( function_exists( 'wp_verify_nonce' ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $request['wpep_square_token_nonce'] ) ), 'connect_wpep_square' ) ) {
			wp_die( 'Looks like the URL is malformed!' );
		}

		$usf_state = get_option( 'wpep_usf_state' );

		if ( $usf_state !== $request['usf_state'] ) {
			wp_die( 'The request is not coming back from the same origin it was sent to. Try Later' );
		}

		$initial_page = 0;

		if ( isset( $request['wpep_sandbox'] ) ) {
			$wpep_sandbox = $request['wpep_sandbox'];
		}

		if ( 'yes' === $wpep_sandbox ) {
			$api_url = 'https://connect.squareupsandbox.com/v2/locations';
		} else {
			$api_url = 'https://connect.squareup.com/v2/locations';
		}

			$headers = array(
				'Square-Version' => '2024-02-22',
				'Authorization'  => 'Bearer ' . $request['access_token'],
				'Content-Type'   => 'application/json',
			);

			$response = wp_remote_get( $api_url, array( 'headers' => $headers ) );

			// Response information.
			$response_code = wp_remote_retrieve_response_code( $response );
			$locations     = json_decode( wp_remote_retrieve_body( $response ) );
			$locations     = $locations->locations;

			// Now you can handle the response as needed.

			$all_locations = array();

			foreach ( $locations as $key => $location ) {

				$one_location = array(

					'location_id'   => $location->id,
					'location_name' => $location->name,
					'currency'      => $location->currency,

				);

				array_push( $all_locations, $one_location );

			}

			// getting currency from square account dynamically.
			update_option( 'wpep_square_currency_new', $all_locations[0]['currency'] );

			if ( isset( $request['wpep_page_post'] ) && ! empty( $request['wpep_page_post'] ) && 'global' !== $request['wpep_page_post'] ) {
				$current_post_id = $request['wpep_page_post'];

				if ( 'yes' === $wpep_sandbox ) {

					update_post_meta( $current_post_id, 'wpep_test_token_details_upgraded', $_REQUEST );
					update_post_meta( $current_post_id, 'wpep_test_location_data', $all_locations );
					update_post_meta( $current_post_id, 'wpep_square_test_app_id', WPEP_SQUARE_TEST_APP_ID );
					update_post_meta( $current_post_id, 'wpep_square_test_token', sanitize_text_field( $request['access_token'] ) );
					update_post_meta( $current_post_id, 'wpep_test_refresh_token', $request['refresh_token'] );
					update_post_meta( $current_post_id, 'wpep_token_test_expires_at', $request['expires_at'] );
					update_post_meta( $current_post_id, 'wpep_post_square_currency_test', $all_locations[0]['currency'] );

				} else {

					update_post_meta( $current_post_id, 'wpep_live_token_details_upgraded', $_REQUEST );
					update_post_meta( $current_post_id, 'wpep_live_location_data', $all_locations );
					update_post_meta( $current_post_id, 'wpep_live_token_upgraded', sanitize_text_field( $request['access_token'] ) );
					update_post_meta( $current_post_id, 'wpep_square_btn_auth', 'true' );
					update_post_meta( $current_post_id, 'wpep_refresh_token', $request['refresh_token'] );
					update_post_meta( $current_post_id, 'wpep_token_expires_at', $request['expires_at'] );
					update_post_meta( $current_post_id, 'wpep_live_square_app_id', WPEP_SQUARE_APP_ID );
					update_post_meta( $current_post_id, 'wpep_post_square_currency_new', $all_locations[0]['currency'] );
				}

				$query_args = array(
					'post'   => $request['post'],
					'action' => $request['action'],
				);

				$initial_page = add_query_arg( $query_args, admin_url( 'post.php' ) );
			}

			if ( isset( $request['wpep_page_post'] ) && ! empty( $request['wpep_page_post'] ) && 'global' === $request['wpep_page_post'] ) {

				if ( 'yes' === $wpep_sandbox ) {

					update_option( 'wpep_test_location_data', $all_locations );
					update_option( 'wpep_square_test_token_global', $request['access_token'] );
					update_option( 'wpep_square_test_btn_auth', 'true' );
					update_option( 'wpep_refresh_test_token', $request['refresh_token'] );
					update_option( 'wpep_token_test_expires_at', $request['expires_at'] );
					update_option( 'wpep_square_test_app_id_global', WPEP_SQUARE_TEST_APP_ID );
					update_option( 'wpep_square_currency_test', $all_locations[0]['currency'] );

				} else {

					update_option( 'wpep_live_location_data', $all_locations );
					update_option( 'wpep_live_token_upgraded', sanitize_text_field( $request['access_token'] ) );
					update_option( 'wpep_square_btn_auth', 'true' );
					update_option( 'wpep_refresh_token', $request['refresh_token'] );
					update_option( 'wpep_token_expires_at', $request['expires_at'] );
					update_option( 'wpep_live_square_app_id', WPEP_SQUARE_APP_ID );

				}

				$query_args = array(

					'page' => $request['page'],

				);
				$initial_page = add_query_arg( $query_args, admin_url( 'admin.php' ) );
			}
			wp_safe_redirect( $initial_page );
			exit;

	}
}

/**
 * Disconnects the Square integration and revokes the access token.
 */
function wpep_square_disconnect() {

	if ( isset( $_POST['wp_global_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ), 'wp_global_nonce' ) ) {
		exit;
	}

	$request = $_REQUEST;

	if ( ! empty( $request['wpep_disconnect_square'] ) ) {

		if ( isset( $request['wpep_disconnect_global'] ) ) {

			if ( 'true' === $request['wpep_disconnect_global'] ) {

				if ( isset( $request['wpep_sandbox'] ) && 'yes' === $request['wpep_sandbox'] ) {

					$access_token = get_option( 'wpep_square_test_token_global', false );
					wpep_revoke_access_token( $access_token, 'yes' );

					delete_option( 'wpep_test_location_data' );
					delete_option( 'wpep_square_test_token_global' );
					delete_option( 'wpep_square_test_btn_auth' );
					delete_option( 'wpep_refresh_test_token' );
					delete_option( 'wpep_token_test_expires_at' );

				} else {

					$access_token = get_option( 'wpep_live_token_upgraded', false );
					wpep_revoke_access_token( $access_token, 'live' );

					delete_option( 'wpep_live_token_details_upgraded' );
					delete_option( 'wpep_live_token_upgraded' );
					delete_option( 'wpep_square_btn_auth' );
					delete_option( 'wpep_refresh_token' );
					delete_option( 'wpep_token_expires_at' );
					delete_option( 'wpep_live_location_data' );
					delete_option( 'wpep_square_currency_new' );
				}

				$query_args = array(

					'page' => $request['page'],

				);

				$initial_page = add_query_arg( $query_args, admin_url( 'admin.php' ) );

			}

			if ( 'false' === $request['wpep_disconnect_global'] ) {

				$form_id = $request['wpep_form_id'];

				if ( isset( $request['wpep_sandbox'] ) && 'yes' === $request['wpep_sandbox'] ) {

					$access_token = get_post_meta( $form_id, 'wpep_square_test_token', true );
					wpep_revoke_access_token( $access_token, 'yes' );

					delete_post_meta( $form_id, 'wpep_test_token_details_upgraded' );
					delete_post_meta( $form_id, 'wpep_test_location_data' );
					delete_post_meta( $form_id, 'wpep_square_test_app_id' );
					delete_post_meta( $form_id, 'wpep_square_test_token' );
					delete_post_meta( $form_id, 'wpep_post_square_currency_test' );
					delete_post_meta( $form_id, 'wpep_test_refresh_token' );
					delete_post_meta( $form_id, 'wpep_token_test_expires_at' );

				} else {

					$access_token = get_post_meta( $form_id, 'wpep_live_token_upgraded', true );
					wpep_revoke_access_token( $access_token, 'live' );

					delete_post_meta( $form_id, 'wpep_live_token_details_upgraded' );
					delete_post_meta( $form_id, 'wpep_live_location_data' );
					delete_post_meta( $form_id, 'wpep_live_token_upgraded' );
					delete_post_meta( $form_id, 'wpep_square_btn_auth' );
					delete_post_meta( $form_id, 'wpep_refresh_token' );
					delete_post_meta( $form_id, 'wpep_token_expires_at' );
					delete_post_meta( $form_id, 'wpep_live_square_app_id' );

				}

				$query_args = array(

					'post'   => $request['post'],
					'action' => $request['action'],

				);

				$initial_page = add_query_arg( $query_args, admin_url( 'post.php' ) );
			}
		}

		wp_safe_redirect( $initial_page );
		exit;

	}
}

/**
 * Revokes the given access token from Square.
 *
 * @param string $access_token The access token to revoke.
 * @param bool   $sandbox      Whether to revoke the token in the sandbox environment or not.
 */
function wpep_revoke_access_token( $access_token, $sandbox ) {

	if ( isset( $_POST['wp_global_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ), 'wp_global_nonce' ) ) {
		exit;
	}

	$api_url    = 'https://connect.apiexperts.io/';
	$oauth_data = array(
		'oauth_version'   => 2,
		'request_type'    => 'revoke_token',
		'app_name'        => WPEP_SQUARE_APP_NAME,
		'sandbox_enabled' => $sandbox,
		'access_token'    => $access_token,
	);

	$response = wp_remote_post(
		$api_url,
		array(
			'body'    => $oauth_data,
			'timeout' => 15,
		)
	);

	// Response information.
	$response_code = wp_remote_retrieve_response_code( $response );
	$response_body = wp_remote_retrieve_body( $response );
}
