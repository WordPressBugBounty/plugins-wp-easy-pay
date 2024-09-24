<?php
/**
 * WP EASY PAY
 *
 * PHP version 7
 *
 * Category: Wordpress_Plugin
 *
 * @package  WP_Easy_Pay
 * Author:   Author <contact@apiexperts.io>
 * License: https://opensource.org/licenses/MIT MIT License
 * @link     http://wpeasypay.com/
 */

add_action( 'admin_init', 'wpep_authorize_with_square' );
add_action( 'admin_init', 'wpep_square_callback_success' );
add_action( 'admin_init', 'wpep_square_disconnect' );

/**
 * Authorizes the plugin with Square and retrieves the access token.
 */
function wpep_authorize_with_square() {
	if ( ! empty( $_GET['wpep_prepare_connection_call'] ) ) {
		if ( isset( $_POST['wp_global_nonce'] ) ) {
			check_admin_referer( 'wp_global_nonce', 'wp_global_nonce' );
		}
		$url_identifiers                  = $_REQUEST;
		$url_identifiers['oauth_version'] = 2;
		$url_identifiers['wpep_disconnect_nonce']      = esc_attr( wp_create_nonce( 'wpep_disconnect_square' ) );
		unset( $url_identifiers['wpep_prepare_connection_call'] );
		$redirect_url = add_query_arg( $url_identifiers, admin_url( $url_identifiers['wpep_admin_url'] ) );

		$redirect_url       = wp_nonce_url( $redirect_url, 'connect_wpep_square', 'wpep_square_token_nonce' );
		$usf_state          = substr( str_shuffle( 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' ), 1, 10 );
		$middle_server_data = array(

			'redirect'      => rawurlencode( $redirect_url ),
			'scope'         => rawurlencode( 'MERCHANT_PROFILE_READ PAYMENTS_READ PAYMENTS_WRITE CUSTOMERS_READ CUSTOMERS_WRITE ORDERS_WRITE' ),
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

		if ( isset( $_REQUEST['wpep_page_post'] ) && ! empty( $_REQUEST['wpep_page_post'] ) && 'global' === sanitize_text_field( wp_unslash( $_REQUEST['wpep_page_post'] ) ) ) {
			if ( isset( $_POST['wp_global_nonce'] ) ) {
				check_admin_referer( 'wp_global_nonce', 'wp_global_nonce' );
			}
			$query_arg['wpep_disconnect_global']         = 'true';
			$query_arg['wpep_disconnect_sandbox_global'] = $url_identifiers['wpep_sandbox'];

			$query_arg      = array_merge( $url_identifiers, $query_arg );
			$disconnect_url = admin_url( $url_identifiers['wpep_admin_url'] );
			$disconnect_url = add_query_arg( $query_arg, $disconnect_url );

			if ( isset( $url_identifiers['wpep_sandbox'] ) ) {

				update_option( 'wpep_square_test_disconnect_url', $disconnect_url );

			} else {

				update_option( 'wpep_square_disconnect_url', $disconnect_url );
			}
		}

		if ( isset( $_REQUEST['wpep_page_post'] ) && ! empty( $_REQUEST['wpep_page_post'] ) && 'global' !== sanitize_text_field( wp_unslash( $_REQUEST['wpep_page_post'] ) ) ) {
			if ( isset( $_POST['wp_global_nonce'] ) ) {
				check_admin_referer( 'wp_global_nonce', 'wp_global_nonce' );
			}
			$query_arg['wpep_disconnect_global'] = 'false';
			$query_arg['wpep_form_id']           = isset( $_REQUEST['wpep_page_post'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpep_page_post'] ) ) : '';
			$query_arg                           = array_merge( $url_identifiers, $query_arg );
			$disconnect_url                      = admin_url( $url_identifiers['wpep_admin_url'] );
			$disconnect_url                      = add_query_arg( $query_arg, $disconnect_url );

			update_post_meta( $query_arg['wpep_form_id'], 'wpep_square_disconnect_url', $disconnect_url );

		}

		$wp_redirect = 'wp_redirect';
		$wp_redirect( $middle_server_url );

	}
}

/**
 * Callback function for successful Square authorization.
 */
function wpep_square_callback_success() {

	if (
		isset( $_REQUEST['access_token'] ) &&
		null !== sanitize_text_field( wp_unslash( $_REQUEST['access_token'] ) ) &&
		isset( $_REQUEST['token_type'] ) &&
		null !== sanitize_text_field( wp_unslash( $_REQUEST['token_type'] ) ) &&
		isset( $_REQUEST['wpep_square_token_nonce'] ) &&
		null !== sanitize_text_field( wp_unslash( $_REQUEST['wpep_square_token_nonce'] ) ) &&
		'bearer' === sanitize_text_field( wp_unslash( $_REQUEST['token_type'] ) )
	) {
		if ( function_exists( 'wp_verify_nonce' ) && ! wp_verify_nonce( sanitize_key( $_REQUEST['wpep_square_token_nonce'] ), 'connect_wpep_square' ) ) {
			wp_die( 'Looks like the URL is malformed!' );
		}
		$usf_state = get_option( 'wpep_usf_state' );

		if ( isset( $_REQUEST['usf_state'] ) && $usf_state !== $_REQUEST['usf_state'] ) {
			wp_die( 'The request is not coming back from the same origin it was sent to. Try Later' );
		}

		$initial_page  = 0;
		$wpep_sandbox  = isset( $_REQUEST['wpep_sandbox'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpep_sandbox'] ) ) : '';
		$access_token  = isset( $_REQUEST['access_token'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['access_token'] ) ) : '';
		// $api_client    = wpep_setup_square_with_access_token( $access_token, $wpep_sandbox );
		// $locations_api = new \SquareConnect\Api\LocationsApi( $api_client );
		// $locations     = $locations_api->listLocations()->getLocations();
		$all_locations = array();
		
		if ( 'yes' == $wpep_sandbox ) {

			$url = 'https://connect.squareupsandbox.com/v2/locations';

		} else {

			$url = 'https://connect.squareup.com/v2/locations';

		}
		//remote request
		
		$headers = array(
			'Square-Version' => '2021-03-17',
			'Authorization'  => 'Bearer ' . $access_token,
			'Content-Type'   => 'application/json'
		);
		
		$response = wp_remote_get($url, array(
			'headers'  =>  $headers
			)
		);
		
		$locations = json_decode(wp_remote_retrieve_body($response));
				
		if ( $response['response']['code'] === 200 && isset ( $locations ) ) {
			
			foreach ( $locations->locations as $key => $location ) {

				$one_location = array(

					'location_id'   => $location->id,
					'location_name' => $location->name,
					'currency'      => $location->currency,

				);

				array_push( $all_locations, $one_location );

			}
		}

		// getting currency from square account dynamically.
		update_option( 'wpep_square_currency_new', $all_locations[0]['currency'] );

		$wpep_page_post = isset( $_REQUEST['wpep_page_post'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpep_page_post'] ) ) : '';

		if ( ! empty( $wpep_page_post ) && 'global' === sanitize_text_field( $wpep_page_post ) ) {

			$expires_at    = isset( $_REQUEST['expires_at'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['expires_at'] ) ) : '';
			$refresh_token = isset( $_REQUEST['refresh_token'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['refresh_token'] ) ) : '';
			$access_token  = isset( $_REQUEST['access_token'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['access_token'] ) ) : '';
			if ( 'yes' === $wpep_sandbox ) {

				update_option( 'wpep_test_location_data', $all_locations );
				update_option( 'wpep_square_test_token_global', sanitize_text_field( $access_token ) );
				update_option( 'wpep_square_test_btn_auth', 'true' );
				update_option( 'wpep_refresh_test_token', sanitize_text_field( $refresh_token ) );
				update_option( 'wpep_token_test_expires_at', sanitize_text_field( $expires_at ) );
				update_option( 'wpep_square_test_app_id_global', WPEP_SQUARE_TEST_APP_ID );
				update_option( 'wpep_square_currency_test', $all_locations[0]['currency'] );

			} else {

				update_option( 'wpep_live_location_data', $all_locations );
				update_option( 'wpep_live_token_upgraded', sanitize_text_field( $access_token ) );
				update_option( 'wpep_square_btn_auth', 'true' );
				update_option( 'wpep_refresh_token', sanitize_text_field( $refresh_token ) );
				update_option( 'wpep_token_expires_at', sanitize_text_field( $expires_at ) );
				update_option( 'wpep_live_square_app_id', WPEP_SQUARE_APP_ID );

			}

			$query_args   = array(
				'page'      => isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '',
				'post_type' => isset( $_REQUEST['wpep_post_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpep_post_type'] ) ) : '',
			);
			$initial_page = add_query_arg( $query_args, admin_url( 'edit.php' ) );

		}

		$wp_redirect = 'wp_redirect';
		$wp_redirect( $initial_page );

	}
}

/**
 * Disconnects the Square integration and revokes the access token.
 */
function wpep_square_disconnect() {
	
	if ( current_user_can( 'manage_options' ) ) {
	
		if ( ! empty( $_REQUEST['wpep_disconnect_square'] ) ) {

			if ( ! isset( $_REQUEST['wpep_disconnect_nonce'] ) || ( isset($_REQUEST['wpep_disconnect_nonce']) && !wp_verify_nonce( sanitize_text_field( $_REQUEST['wpep_disconnect_nonce'] ), 'wpep_disconnect_square' ) ) ) {
				exit( 'Not Authorized' );
			}
			if ( isset( $_REQUEST['wpep_disconnect_global'] ) ) {

				if ( 'true' === $_REQUEST['wpep_disconnect_global'] ) {

					if ( isset( $_REQUEST['wpep_sandbox'] ) && 'yes' === $_REQUEST['wpep_sandbox'] ) {

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
						'page'      => isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '',
						'post_type' => isset( $_REQUEST['wpep_post_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpep_post_type'] ) ) : '',
					);

					$initial_page = add_query_arg( $query_args, admin_url( 'edit.php' ) );

				}
			}
			$wp_redirect = 'wp_redirect';
			$wp_redirect( $initial_page );

		}
	}
}
/**
 * Revokes the given access token from Square.
 *
 * @param string $access_token The access token to revoke.
 * @param bool   $sandbox      Whether to revoke the token in the sandbox environment or not.
 */
function wpep_revoke_access_token( $access_token, $sandbox ) {

	$curl_init   = 'curl_init';
	$curl_setopt = 'curl_setopt';
	$curl_exec   = 'curl_exec';
	$curl_close  = 'curl_close';
	$ch          = $curl_init();

	$curl_setopt( $ch, CURLOPT_URL, 'https://connect.apiexperts.io/' );
	$curl_setopt( $ch, CURLOPT_POST, 1 );
	$curl_setopt( $ch, CURLOPT_POSTFIELDS, 'oauth_version=2&request_type=revoke_token&app_name=' . WPEP_SQUARE_APP_NAME . '&sandbox_enabled=' . $sandbox . '&access_token=' . $access_token );
	$curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

	$server_output = $curl_exec( $ch );

	$curl_close( $ch );
}
