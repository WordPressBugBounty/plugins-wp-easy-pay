<?php
/**
 * Filename: square-configuration.php,
 * Description: silence is golden.
 *
 * @package WP_Easy_Pay
 */

?>
<?php
require_once WPEP_ROOT_PATH . 'square-sdk/autoload.php';

/**
 * Set up the Square API configuration based on the payment mode (live or test).
 *
 * @return \SquareConnect\ApiClient|null An instance of the Square API client or null if there is an error.
 */
function wpep_setup_square_configuration() {
	$live_mode = get_option( 'wpep_square_payment_mode_global' );

	$api_config = new \SquareConnect\Configuration();

	if ( 'on' === $live_mode ) {

		$access_token = get_option( 'wpep_live_token_upgraded' );
		$api_config->setHost( 'https://connect.squareup.com' );
		$api_config->setAccessToken( $access_token );

	} else {

		$access_token = get_option( 'wpep_square_test_token_global' );
		$api_config->setHost( 'https://connect.squareupsandbox.com' );
		$api_config->setAccessToken( $access_token );
	}

	$api_client = new \SquareConnect\ApiClient( $api_config );

	return $api_client;
}

/**
 * Set up the Square API configuration using the provided access token and sandbox mode.
 *
 * @param string $wpep_access_token The Square access token to use for API authentication.
 * @param bool   $wpep_sandbox      Optional. Set to true to use the Square sandbox environment. Default is false.
 * @return \SquareConnect\ApiClient|null An instance of the Square API client or null if there is an error.
 */
function wpep_setup_square_with_access_token( $wpep_access_token, $wpep_sandbox = false ) {

	$api_config = new \SquareConnect\Configuration();

	if ( 'yes' === $wpep_sandbox ) {

		$api_config->setHost( 'https://connect.squareupsandbox.com' );

	} else {

		$api_config->setHost( 'https://connect.squareup.com' );

	}

	$api_config->setAccessToken( $wpep_access_token );

	$api_client = new \SquareConnect\ApiClient( $api_config );

	return $api_client;
}

/**
 * Set up the Square API configuration based on the form ID.
 *
 * @param int $current_form_id The ID of the current form.
 * @return \SquareConnect\ApiClient|null An instance of the Square API client or null if there is an error.
 */
function wpep_setup_square_configuration_by_form_id( $current_form_id ) {

	$api_config = new \SquareConnect\Configuration();

	$wpep_individual_form_global = get_post_meta( $current_form_id, 'wpep_individual_form_global', true );

	/* If form is using global settings */
	if ( 'on' === $wpep_individual_form_global ) {

		$wpep_payment_mode = get_option( 'wpep_square_payment_mode_global' );

		if ( 'on' === $wpep_payment_mode ) {
			/* if live is on */
			$access_token = get_option( 'wpep_live_token_upgraded', true );

			$api_config->setHost( 'https://connect.squareup.com' );
			$api_config->setAccessToken( $access_token );
			$square_currency = get_option( 'wpep_square_currency_new', true );

		}

		if ( 'on' !== $wpep_payment_mode ) {

			/* if test is on */
			$access_token = get_option( 'wpep_square_test_token_global', true );

			$api_config->setHost( 'https://connect.squareupsandbox.com' );
			$api_config->setAccessToken( $access_token );

			$square_currency = get_option( 'wpep_square_currency_test', true );

		}
	}

	/* If form is using its own settings */
	if ( '' === $wpep_individual_form_global ) {

		$wpep_payment_mode = get_post_meta( $current_form_id, 'wpep_payment_mode', true );

		if ( 'on' === $wpep_payment_mode ) {

			/* if live is on */
			$access_token = get_post_meta( $current_form_id, 'wpep_live_token_upgraded', true );
			$api_config->setHost( 'https://connect.squareup.com' );
			$api_config->setAccessToken( $access_token );

		}

		if ( 'on' !== $wpep_payment_mode ) {

			/* if test is on */
			$access_token = get_post_meta( $current_form_id, 'wpep_square_test_token', true );
			$api_config->setHost( 'https://connect.squareupsandbox.com' );
			$api_config->setAccessToken( $access_token );

		}
	}

	$api_client = new \SquareConnect\ApiClient( $api_config );

	return $api_client;
}

/**
 * Get the Square location ID based on the form ID.
 *
 * @param int $current_form_id The ID of the current form.
 * @return string|null The Square location ID associated with the form, or null if not found.
 */
function wpep_get_location_by_form_id( $current_form_id ) {

	$wpep_individual_form_global = get_post_meta( $current_form_id, 'wpep_individual_form_global', true );

	/* If form is using global settings */
	if ( 'on' === $wpep_individual_form_global ) {

		$wpep_payment_mode = get_option( 'wpep_square_payment_mode_global' );

		if ( 'on' === $wpep_payment_mode ) {
			/* if live is on */
			$location_id = get_option( 'wpep_square_location_id', true );

		}

		if ( 'on' !== $wpep_payment_mode ) {

			/* if test is on */
			$location_id = get_option( 'wpep_square_test_location_id_global', true );

		}
	}

	/* If form is using its own settings */
	if ( '' === $wpep_individual_form_global ) {

		$wpep_payment_mode = get_post_meta( $current_form_id, 'wpep_payment_mode', true );

		if ( 'on' === $wpep_payment_mode ) {

			/* if live is on */
			$location_id = get_post_meta( $current_form_id, 'wpep_square_location_id', true );

		}

		if ( 'on' !== $wpep_payment_mode ) {

			/* if test is on */
			$location_id = get_post_meta( $current_form_id, 'wpep_square_test_location_id', true );

		}
	}

	return $location_id;
}

/**
 * Get the currency associated with the form ID.
 *
 * @param int $wpep_current_form_id The ID of the current form.
 * @return string|null The currency code associated with the form, or null if not found.
 */
function wpep_get_currency( $wpep_current_form_id ) {

	$form_payment_global = get_post_meta( $wpep_current_form_id, 'wpep_individual_form_global', true );

	if ( 'on' === $form_payment_global ) {

		$global_payment_mode = get_option( 'wpep_square_payment_mode_global', true );

		if ( 'on' === $global_payment_mode ) {

			/* If Global Form Live Mode */

			$square_currency = get_option( 'wpep_square_currency_new' );

		}

		if ( 'on' !== $global_payment_mode ) {

			/* If Global Form Test Mode */

			$square_currency = get_option( 'wpep_square_currency_test' );

		}
	}

	if ( 'on' !== $form_payment_global ) {

		$individual_payment_mode = get_post_meta( $wpep_current_form_id, 'wpep_payment_mode', true );

		if ( 'on' === $individual_payment_mode ) {

			/* If Individual Form Live Mode */

			$square_currency = get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_new', true );

		}

		if ( 'on' !== $individual_payment_mode ) {

			/* If Individual Form Test Mode */

			$square_currency = get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_test', true );

		}
	}

	return $square_currency;
}

/**
 * Get the details of the refresh token associated with the form ID.
 *
 * @param int $wpep_current_form_id The ID of the current form.
 * @return array|null An array containing the details of the refresh token, or null if not found.
 */
function wpep_refresh_token_details( $wpep_current_form_id ) {

	$refresh_token_details = array();

	$form_payment_global = get_post_meta( $wpep_current_form_id, 'wpep_individual_form_global', true );

	if ( 'on' === $form_payment_global ) {

		$global_payment_mode = get_option( 'wpep_square_payment_mode_global', true );

		if ( 'on' === $global_payment_mode ) {
			/* If Global Form Live Mode */
			$refresh_token_details['refresh_token'] = get_option( 'wpep_refresh_token', false );
			$refresh_token_details['expires_at']    = get_option( 'wpep_token_expires_at', false );
			$refresh_token_details['type']          = 'global';

		} else {

			$refresh_token_details['refresh_token'] = get_option( 'wpep_refresh_test_token', false );
			$refresh_token_details['expires_at']    = get_option( 'wpep_token_test_expires_at', false );
			$refresh_token_details['type']          = 'global';

		}
	}

	if ( 'on' !== $form_payment_global ) {

		$individual_payment_mode = get_post_meta( $wpep_current_form_id, 'wpep_payment_mode', true );

		if ( 'on' === $individual_payment_mode ) {

			/* If Individual Form Live Mode */

			$refresh_token_details['refresh_token'] = get_post_meta( $wpep_current_form_id, 'wpep_refresh_token', true );
			$refresh_token_details['expires_at']    = get_post_meta( $wpep_current_form_id, 'wpep_token_expires_at', true );
			$refresh_token_details['type']          = 'specific';

		}
	}

	return $refresh_token_details;
}
