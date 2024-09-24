<?php
/**
 * Filename: form_render_shortcode.php
 * Description: form render shortcode.
 *
 * @package WP_Easy_Pay
 */

/**
 * Render the payment form.
 *
 * This function is responsible for rendering the payment form on the frontend.
 * It may process the provided attributes (if any) and return the HTML content of the form.
 *
 * @param array $atts An array of attributes (if used in the shortcode) for the payment form.
 */
function wpep_render_payment_form( $atts ) {

	if ( isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ) {

		if ( ! is_admin() ) {

			if ( isset( $atts['id'] ) ) {

				$form_post            = get_post( $atts['id'] );
				$wpep_current_form_id = $atts['id'];

				if ( null !== $form_post && 'trash' === $form_post->post_status ) {

					return 'This form has been trashed by the admin';
				}

				if ( null === $form_post ) {

					return 'Form does not exist';
				}

				$square_token = wpep_get_square_token( $wpep_current_form_id );

				if ( ! isset( $square_token ) || empty( $square_token ) ) {

					ob_start();

					require WPEP_ROOT_PATH . 'views/frontend/no-square-setup.php';
					return ob_get_clean();

				}

				ob_start();
				$payment_type = get_post_meta( $wpep_current_form_id, 'wpep_square_payment_type', true );
				require WPEP_ROOT_PATH . 'views/frontend/parent-view.php';

				return ob_get_clean();

			} else {

				return "Please provide 'id' in shortcode to display the respective form";

			}
		}
	}

	if ( ! isset( $_SERVER['HTTPS'] ) || 'on' !== $_SERVER['HTTPS'] ) {

		ob_start();

		require WPEP_ROOT_PATH . 'views/frontend/no-ssl.php';
		return ob_get_clean();

	}
}

add_action( 'init', 'wpep_register_premium_shortcode' );
/**
 * Register the premium payment form shortcode.
 *
 * This function registers the shortcode for the premium payment form.
 * It is executed on the 'init' action hook to make the shortcode available for use.
 */
function wpep_register_premium_shortcode() {

	add_shortcode( 'wpep-form', 'wpep_render_payment_form' );
}

/**
 * Get the Square token for a form.
 *
 * This function retrieves the Square token associated with a specific form.
 *
 * @param int $wpep_current_form_id The ID of the form to get the Square token for.
 */
function wpep_get_square_token( $wpep_current_form_id ) {

	$form_payment_global = get_post_meta( $wpep_current_form_id, 'wpep_individual_form_global', true );

	if ( 'on' === $form_payment_global ) {

		$global_payment_mode = get_option( 'wpep_square_payment_mode_global', true );

		if ( 'on' === $global_payment_mode ) {

			/* If Global Form Live Mode */
			$access_token = get_option( 'wpep_live_token_upgraded', true );

		}

		if ( 'on' !== $global_payment_mode ) {

			/* If Global Form Test Mode */
			$access_token = get_option( 'wpep_square_test_token_global', true );

		}
	}

	if ( 'on' !== $form_payment_global ) {

		$individual_payment_mode = get_post_meta( $wpep_current_form_id, 'wpep_payment_mode', true );

		if ( 'on' === $individual_payment_mode ) {

			/* If Individual Form Live Mode */
			$access_token = get_post_meta( $wpep_current_form_id, 'wpep_live_token_upgraded', true );

		}

		if ( 'on' !== $individual_payment_mode ) {

			/* If Individual Form Test Mode */
			$access_token = get_post_meta( $wpep_current_form_id, 'wpep_square_test_token', true );

		}
	}

	return $access_token;
}

function is_available( $method, $currency ) {
	$is_available = true;
	if ( $method == 'cashapp' ) {
		if ( 'USD' != $currency ) {
			$is_available = false;
		}
	} elseif ( $method == 'afterpay' ) {
		if ( 'USD' != $currency && 
			 'CAD' != $currency &&  
			 'AUD' != $currency && 
			 'GBP' != $currency ) {
			$is_available = false;
		}
	} elseif ( $method == 'ach_debit' ) {
		if ( 'USD' != $currency ) {
			$is_available = false;
		}
	}
	return $is_available;
}
function wpep_currency_symbol( $currency ) {
	if( 'USD' === $currency ) {
		$symbol = '$';
	} elseif( 'EUR' === $currency ) {
		$symbol = '€';
	} elseif( 'CAD' === $currency ) {
		$symbol = 'C$';
	} elseif( 'JPY' === $currency ) {
		$symbol = '¥';
	} elseif( 'AUD' === $currency ) {
		$symbol = 'A$';
	} elseif( 'GBP' === $currency ) {
		$symbol = '£';
	}
	return $symbol;
}



