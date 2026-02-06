<?php
/**
 * Filename: form_render_shortcode.php
 * Description: form render shortcode.
 *
 * @package WP_Easy_Pay
 */

/**
 * Renders the WP EasyPay payment form.
 *
 * This function renders the payment form based on the provided shortcode attributes.
 * It checks if SSL is enabled and validates the form ID. If the form does not exist,
 * or the Square token is not set up, appropriate fallback views are rendered.
 *
 * @param array $atts Shortcode attributes containing the form ID.
 * @return string The rendered form HTML or an error message if the form cannot be displayed.
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
 * Registers the WP EasyPay premium payment form shortcode.
 *
 * This function registers the `[wpep-form]` shortcode, which renders the WP EasyPay payment form.
 * The shortcode is associated with the `wpep_render_payment_form` function.
 *
 * @return void
 */
function wpep_register_premium_shortcode() {

	add_shortcode( 'wpep-form', 'wpep_render_payment_form' );
}

/**
 * Retrieve the Square access token for the specified form.
 *
 * This function fetches the appropriate Square access token based on
 * whether the form is set to use individual or global settings and whether
 * it is in live or test mode.
 *
 * @param int $wpep_current_form_id The ID of the current form.
 * @return string|null The Square access token if available, or null if not set.
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

/**
 * Enqueues frontend styles and scripts for the payment form in WordPress.
 *
 * This function checks if the current post contains a WPEP form shortcode and enqueues
 * the necessary CSS and JavaScript based on form settings, payment mode, and additional settings.
 *
 * The function handles both individual and global forms and includes Square, reCAPTCHA,
 * and popup form configurations as needed.
 *
 * @return void
 */
function wpep_form_backend_parent_scripts() {
	if ( 'checkout' === basename( get_permalink() ) ) {
		return true;
	}

	if ( class_exists( 'WooCommerce' ) ) {
		if ( is_checkout() ) {
			return true;
		}
	}
	$form_post = get_post();

	$current_form_content = $form_post->post_content;
	if ( empty( $current_form_content ) ) {
		$banner_content = get_post_meta( $form_post->ID, 'banner_content', true );
		if ( false !== strpos( $banner_content, 'wpep' ) ) {
			$current_form_content = $banner_content;
		}
	}

	$widget_blocks = get_option( 'widget_block' );

	foreach ( $widget_blocks as $block ) {
		if ( isset( $block['content'] ) && is_string( $block['content'] ) && strpos( $block['content'], 'wpep' ) !== false ) {
			$current_form_content = $block['content'];
		}
	}

	$shortcode_attr       = explode( 'wpep-form', $current_form_content );
	$current_form_code    = explode( '"', $shortcode_attr[1] );
	$current_form_code_id = $current_form_code[1];
	if ( empty( $current_form_code_id ) ) {
		$current_form_code_id = get_transient( 'wpep_guten_id' );
	}

	if ( empty( $current_form_code_id ) ) {
		$blocks = parse_blocks( $form_post->post_content );
		if ( ! empty( $blocks ) && isset( $blocks[0]['attrs']['type'] ) ) {
			$current_form_code_id = $blocks[0]['attrs']['type'];
		}
	}
	// checking from bricks builder.
	$getpostmeta = get_post_meta( $form_post->ID, '_bricks_page_content_2', true );
	// Di gayi string.
	if ( isset( $getpostmeta ) && ! empty( $getpostmeta ) ) {
		$string = $getpostmeta[2]['settings']['shortcode'];
		// Pattern daryaft karne ke liye regular expression.
		$pattern = '/\[wpep-form id="(\d+)"\]/';
		// Match karna.
		if ( preg_match( $pattern, $string, $matches ) ) {
			// $matches[0] mein pura match hoga.
			// $matches[1] mein sirf 260 hoga.
			$current_form_code_id = $matches[1];
		}
	}

	$global_form = get_post_meta( $current_form_code_id, 'wpep_individual_form_global', true );

	$global_payment_mode      = get_option( 'wpep_square_payment_mode_global', true );
	$individual_payment_mode  = get_post_meta( $current_form_code_id, 'wpep_payment_mode', true );
	$wpep_show_wizard         = get_post_meta( $current_form_code_id, 'wpep_show_wizard', true );
	$enable_recaptcha         = get_option( 'wpep_enable_recaptcha' );
	$recaptcha_version        = get_option( 'wpep_recaptcha_version' );
	$wpep_open_in_popup       = get_post_meta( $current_form_code_id, 'wpep_open_in_popup', true );
	$wpep_payment_success_url = ! empty( get_post_meta( $current_form_code_id, 'wpep_square_payment_success_url', true ) ) ? get_post_meta( $current_form_code_id, 'wpep_square_payment_success_url', true ) : '';
	wp_enqueue_style( 'wpep_wizard_form_style', WPEP_ROOT_URL . 'assets/frontend/css/multi_wizard.css', array(), WPEP_VERSION );
	wp_enqueue_style( 'wpep_single_form_style', WPEP_ROOT_URL . 'assets/frontend/css/single_page.css', array(), WPEP_VERSION );
	$fees_data = get_post_meta( $current_form_code_id, 'fees_data', true );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'wpep_wizard_script', WPEP_ROOT_URL . 'assets/frontend/js/script_single.js', array(), WPEP_VERSION, true );
	if ( 'on' === $wpep_show_wizard ) {
		wp_enqueue_script( 'wpep_multi_wizard_script', WPEP_ROOT_URL . 'assets/frontend/js/script_wizard.js', array(), WPEP_VERSION, true );
	}

	if ( empty( $global_form ) ) {
			/* For single form*/
		if ( empty( $individual_payment_mode ) ) {// For test individual.
			// Condition works on individual form further enhanced to mode setup.
			wp_enqueue_script( 'square_payment_form_external', 'https://sandbox.web.squarecdn.com/v1/square.js', array(), WPEP_VERSION, true );
			$square_application_id_in_use = get_post_meta( $current_form_code_id, 'wpep_square_test_app_id', true );
			$square_location_id_in_use    = get_post_meta( $current_form_code_id, 'wpep_square_test_location_id', true );
			$square_currency              = get_post_meta( $current_form_code_id, 'wpep_post_square_currency_test', true );
			$gpay                         = get_post_meta( $current_form_code_id, 'wpep_square_google_pay', true );
			$afterpay                     = get_post_meta( $current_form_code_id, 'wpep_square_after_pay', true );
			$applepay                     = get_post_meta( $current_form_code_id, 'wpep_square_apple_pay', true );
			$cashapp                      = get_post_meta( $current_form_code_id, 'wpep_square_cash_app', true );
			$giftcard                     = get_post_meta( $current_form_code_id, 'wpep_square_giftcard', true );
			$ach_debit                    = get_post_meta( $current_form_code_id, 'wpep_square_ach_debit', true );

		} else { // For Live individual.
			wp_enqueue_script( 'square_payment_form_external', 'https://web.squarecdn.com/v1/square.js', array(), WPEP_VERSION, true );
			$square_application_id_in_use = WPEP_SQUARE_APP_ID;
			$square_location_id_in_use    = get_post_meta( $current_form_code_id, 'wpep_square_location_id', true );
			$square_currency              = get_post_meta( $current_form_code_id, 'wpep_post_square_currency_new', true );
			$gpay                         = get_post_meta( $current_form_code_id, 'wpep_square_google_pay_live', true );
			$afterpay                     = get_post_meta( $current_form_code_id, 'wpep_square_after_pay_live', true );
			$applepay                     = get_post_meta( $current_form_code_id, 'wpep_square_apple_pay_live', true );
			$cashapp                      = get_post_meta( $current_form_code_id, 'wpep_square_cash_app_live', true );
			$giftcard                     = get_post_meta( $current_form_code_id, 'wpep_square_giftcard_live', true );
			$ach_debit                    = get_post_meta( $current_form_code_id, 'wpep_square_ach_debit_live', true );
		}
	} else { // phpcs:ignore
		/* For main form*/
		if ( 'on' !== $global_payment_mode ) {// For test Global.
			wp_enqueue_script( 'square_payment_form_external', 'https://sandbox.web.squarecdn.com/v1/square.js', array(), WPEP_VERSION, true );
			$square_application_id_in_use = get_option( 'wpep_square_test_app_id_global', true );
			$square_location_id_in_use    = get_option( 'wpep_square_test_location_id_global', true );
			$square_currency              = get_option( 'wpep_square_currency_test' );
			$gpay                         = get_option( 'wpep_square_test_google_pay_global', true );
			$applepay                     = get_option( 'wpep_square_test_apple_pay', true );
			$afterpay                     = get_option( 'wpep_square_test_after_pay', true );
			$cashapp                      = get_option( 'wpep_square_test_cash_app', true );
			$giftcard                     = get_option( 'wpep_square_test_giftcard', true );
			$ach_debit                    = get_option( 'wpep_square_test_ach_debit', true );

		} else { // For Live Global.
			wp_enqueue_script( 'square_payment_form_external', 'https://web.squarecdn.com/v1/square.js', array(), WPEP_VERSION, true );
			$square_application_id_in_use = WPEP_SQUARE_APP_ID;
			$square_location_id_in_use    = get_option( 'wpep_square_location_id', true );
			$square_currency              = get_option( 'wpep_square_currency_new' );
			$gpay                         = get_option( 'wpep_square_google_pay', true );
			$applepay                     = get_option( 'wpep_square_apple_pay', true );
			$afterpay                     = get_option( 'wpep_square_after_pay', true );
			$cashapp                      = get_option( 'wpep_square_cash_app', true );
			$giftcard                     = get_option( 'wpep_square_giftcard', true );
			$ach_debit                    = get_option( 'wpep_square_ach_debit', true );
		}
	}
	if ( isset( $cashapp ) && 'on' === $cashapp ) {
		$cashapp_available = is_available( 'cashapp', $square_currency );
		$cashapp_available = isset( $cashapp_available ) ? $cashapp_available : false;
	} else {
		$cashapp_available = false;
	}
	if ( isset( $giftcard ) && 'on' === $giftcard ) {
		$giftcard_available = is_available( 'giftcard', $square_currency );
		$giftcard_available = isset( $giftcard_available ) ? $giftcard_available : false;
	} else {
		$giftcard_available = false;
	}
	if ( isset( $gpay ) && 'on' === $gpay ) {
		$gpay_available = is_available( 'gpay', $square_currency );
		$gpay_available = isset( $gpay_available ) ? $gpay_available : false;
	} else {
		$gpay_available = false;
	}
	if ( isset( $afterpay ) && 'on' === $afterpay ) {
		$afterpay_available = is_available( 'afterpay', $square_currency );
		$afterpay_available = isset( $afterpay_available ) ? $afterpay_available : false;
	} else {
		$afterpay_available = false;
	}
	if ( isset( $applepay ) && 'on' === $applepay ) {
		$applepay_available = is_available( 'applepay', $square_currency );
		$applepay_available = isset( $applepay_available ) ? $applepay_available : false;
	} else {
		$applepay_available = false;
	}
	if ( isset( $ach_debit ) && 'on' === $ach_debit ) {
		$ach_debit_available = is_available( 'achDebit', $square_currency );
		$ach_debit_available = isset( $ach_debit_available ) ? $ach_debit_available : false;
	} else {
		$ach_debit_available = false;
	}

	if ( 'on' === $enable_recaptcha && 'v3' === $recaptcha_version ) {
		$recaptcha_site_key = get_option( 'wpep_recaptcha_site_key_v3' );

		wp_enqueue_script( 'wpep_recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . $recaptcha_site_key, array(), WPEP_VERSION, true );
	}

	if ( 'on' === $enable_recaptcha && 'v2' === $recaptcha_version ) {
		$recaptcha_site_key = get_option( 'wpep_recaptcha_site_key_v2' );

		wp_enqueue_script( 'wpep_recaptcha', 'https://www.google.com/recaptcha/api.js', array(), WPEP_VERSION, true );
	}
	if ( 'on' === $wpep_open_in_popup ) {

		wp_enqueue_style( 'wpep_popup_form_style', WPEP_ROOT_URL . 'assets/frontend/css/wpep_popup.css', array(), WPEP_VERSION );
		wp_enqueue_script( 'wpep_frontend_scripts', WPEP_ROOT_URL . 'assets/frontend/js/wpep_scripts.js', array(), WPEP_VERSION, true );
	}
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		$user_email   = $current_user->user_email;
	} else {
		$user_email = '';
	}

	wp_enqueue_script( 'square_payment_form_internal', WPEP_ROOT_URL . 'assets/frontend/js/wpep_paymentform.js?rand=' . wp_rand(), array(), WPEP_VERSION, true );
	wp_localize_script(
		'square_payment_form_internal',
		'wpep_local_vars',
		array(
			'ajax_url'                        => admin_url( 'admin-ajax.php' ),
			'square_application_id'           => $square_application_id_in_use,
			'square_location_id_in_use'       => $square_location_id_in_use,
			'wpep_square_currency_new'        => $square_currency,
			'wpep_currency_symbol'            => wpep_currency_symbol( $square_currency ),
			'current_form_id'                 => $current_form_code_id,
			'currencySymbolType'              => ! empty( get_post_meta( $current_form_code_id, 'currencySymbolType', true ) ) ? get_post_meta( $current_form_code_id, 'currencySymbolType', true ) : 'code',
			'wpep_form_theme_color'           => get_post_meta( $current_form_code_id, 'wpep_form_theme_color', true ),
			'front_img_url'                   => WPEP_ROOT_URL . 'assets/frontend/img',
			'wpep_payment_success_url'        => $wpep_payment_success_url,
			'logged_in_user_email'            => $user_email,
			'first_name'                      => get_user_meta( get_current_user_id(), 'first_name', true ),
			'last_name'                       => get_user_meta( get_current_user_id(), 'last_name', true ),
			'extra_fees'                      => ( ! empty( $fees_data[0]['check'] ) && in_array( 'yes', $fees_data[0]['check'], true ) ),
			'gpay'                            => isset( $gpay_available ) && true === $gpay_available ? $gpay : '',
			'afterpay'                        => isset( $afterpay_available ) && true === $afterpay_available ? $afterpay : '',
			'applepay'                        => isset( $applepay_available ) && true === $applepay_available ? $applepay : '',
			'cashapp'                         => isset( $cashapp_available ) && true === $cashapp_available ? $cashapp : '',
			'giftcard'                        => isset( $giftcard_available ) && true === $giftcard_available ? $giftcard : '',
			'achDebit'                        => isset( $ach_debit_available ) && true === $ach_debit_available ? $ach_debit : '',
			'wpep_square_user_defined_amount' => get_post_meta( $current_form_code_id, 'wpep_square_user_defined_amount', true ),
			'wp_payment_nonce'                => wp_create_nonce( 'payment_nonce' ),
			'recaptcha_version'               => $recaptcha_version,
			'enable_recaptcha'                => $enable_recaptcha,
			'wpep_square_amount_type'         => get_post_meta( $current_form_code_id, 'wpep_square_amount_type', true ),
			'recaptcha_site_key_v3'           => get_option( 'wpep_recaptcha_site_key_v3' ),
			'wpep_show_wizard'                => get_post_meta( $current_form_code_id, 'wpep_show_wizard', true ),

		)
	);
}
add_action( 'wp_enqueue_scripts', 'wpep_form_backend_parent_scripts' );


/**
 * Check if a payment method is available for a given currency.
 *
 * This function checks whether a specific payment method is available
 * based on the provided currency. Supported methods include Cash App,
 * Afterpay, and ACH Debit.
 *
 * @param string $method  The payment method (e.g., 'cashapp', 'afterpay', 'achDebit').
 * @param string $currency The currency code (e.g., 'USD', 'CAD', 'AUD', 'GBP').
 * @return bool True if the payment method is available for the currency, false otherwise.
 */
function is_available( $method, $currency ) {
	$is_available = true;
	if ( 'cashapp' === $method ) {
		if ( 'USD' !== $currency ) {
			$is_available = false;
		}
	} elseif ( 'afterpay' === $method ) {
		if ( 'USD' !== $currency &&
			'CAD' !== $currency &&
			'AUD' !== $currency &&
			'GBP' !== $currency ) {
			$is_available = false;
		}
	} elseif ( 'achDebit' === $method ) {
		if ( 'USD' !== $currency ) {
			$is_available = false;
		}
	}
	return $is_available;
}

/**
 * Get the currency symbol for a given currency code.
 *
 * This function returns the corresponding symbol for the provided currency code.
 * Supported currency codes include USD, EUR, CAD, JPY, AUD, and GBP.
 *
 * @param string $currency The currency code (e.g., 'USD', 'EUR', 'CAD').
 * @return string|null The currency symbol if found, or null if the currency is not supported.
 */
function wpep_currency_symbol( $currency ) {
	$symbol = '';
	if ( 'USD' === $currency ) {
		$symbol = '$';
	} elseif ( 'EUR' === $currency ) {
		$symbol = '€';
	} elseif ( 'CAD' === $currency ) {
		$symbol = 'C$';
	} elseif ( 'JPY' === $currency ) {
		$symbol = '¥';
	} elseif ( 'AUD' === $currency ) {
		$symbol = 'A$';
	} elseif ( 'GBP' === $currency ) {
		$symbol = '£';
	}
	return $symbol;
}
