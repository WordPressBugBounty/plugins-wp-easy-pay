<?php
/**
 * Server-side payment amount validation.
 *
 * @package WP_Easy_Pay
 */

/**
 * Compare two money values at cents precision.
 *
 * @param float $left  First amount.
 * @param float $right Second amount.
 * @return bool
 */
function wpep_payment_amounts_match( $left, $right ) {
	return abs( round( (float) $left, 2 ) - round( (float) $right, 2 ) ) < 0.009;
}

/**
 * Signup fee configured on the form.
 *
 * @param int $form_id Form ID.
 * @return float
 */
function wpep_get_form_signup_fee_amount( $form_id ) {
	return ( 'yes' === get_post_meta( $form_id, 'wpep_enable_signup_fees', true ) )
		? (float) get_post_meta( $form_id, 'wpep_signup_fees_amount', true )
		: 0.0;
}

/**
 * Calculate the server-authoritative total for a trusted subtotal.
 *
 * @param int    $form_id     Form ID.
 * @param float  $subtotal    Trusted subtotal.
 * @param string $coupon_code Submitted coupon code.
 * @return array
 */
function wpep_calculate_server_payment_total( $form_id, $subtotal, $coupon_code = '' ) {
	$subtotal = round( (float) $subtotal, 2 );
	$signup   = wpep_get_form_signup_fee_amount( $form_id );
	$fees     = 0.0;
	$fees_row = get_post_meta( $form_id, 'fees_data', true );
	$fees_row = ( isset( $fees_row['check'] ) ) ? $fees_row : ( ( isset( $fees_row[0]['check'] ) ) ? $fees_row[0] : array() );

	if ( ! empty( $fees_row['check'] ) ) {
		foreach ( $fees_row['check'] as $key => $enabled ) {
			if ( 'yes' !== $enabled ) {
				continue;
			}
			$value = isset( $fees_row['value'][ $key ] ) ? (float) $fees_row['value'][ $key ] : 0;
			$fees += ( isset( $fees_row['type'][ $key ] ) && 'percentage' === $fees_row['type'][ $key ] )
				? $subtotal * ( $value / 100 )
				: $value;
		}
	}

	$gross    = $subtotal + $signup + $fees;
	$discount = 0.0;
	$coupon_code = sanitize_text_field( $coupon_code );

	if ( '' !== $coupon_code ) {
		$coupons = get_posts(
			array(
				'post_type'      => 'wpep_coupons',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'meta_key'       => 'wpep_coupons_code', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $coupon_code, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		if ( ! empty( $coupons[0] ) ) {
			$coupon_id = $coupons[0]->ID;
			$expiry    = get_post_meta( $coupon_id, 'wpep_coupons_expiry', true );
			$include   = get_post_meta( $coupon_id, 'wpep_coupons_form_include', true );
			$exclude   = get_post_meta( $coupon_id, 'wpep_coupons_form_exclude', true );
			$include   = is_array( $include ) ? $include : array();
			$exclude   = is_array( $exclude ) ? $exclude : array();
			$form_key  = (string) $form_id;
			$is_valid  = ( empty( $expiry ) || gmdate( 'Y-m-d' ) <= gmdate( 'Y-m-d', strtotime( $expiry ) ) )
				&& ( empty( $include ) || in_array( $form_key, array_map( 'strval', $include ), true ) )
				&& ( empty( $exclude ) || ! in_array( $form_key, array_map( 'strval', $exclude ), true ) );

			if ( $is_valid ) {
				$coupon_amount = (float) get_post_meta( $coupon_id, 'wpep_coupons_amount', true );
				$basis         = ( 'with_total' === get_post_meta( $coupon_id, 'wpep_coupons_discount_preference', true ) ) ? $gross : $subtotal;
				$discount      = ( 'percentage' === get_post_meta( $coupon_id, 'wpep_coupons_discount_type', true ) )
					? $basis * ( $coupon_amount / 100 )
					: $coupon_amount;
			}
		}
	}

	return array(
		'amount'        => round( max( 0, $gross - $discount ), 2 ),
		'signup_amount' => $signup,
		'discount'      => round( max( 0, $discount ), 2 ),
	);
}

/**
 * Configured selectable amounts for radio, dropdown, or custom boxes.
 *
 * @param int    $form_id     Form ID.
 * @param string $amount_type Amount layout.
 * @return float[]
 */
function wpep_get_form_preset_amounts( $form_id, $amount_type ) {
	$presets = array();

	if ( 'payment_radio' === $amount_type ) {
		$list = get_post_meta( $form_id, 'wpep_radio_amounts', true );
	} elseif ( 'payment_drop' === $amount_type ) {
		$list = get_post_meta( $form_id, 'wpep_dropdown_amounts', true );
	} else {
		$list = array();
		foreach ( array( 'wpep_square_payment_box_1', 'wpep_square_payment_box_2', 'wpep_square_payment_box_3', 'wpep_square_payment_box_4' ) as $key ) {
			$value = get_post_meta( $form_id, $key, true );
			if ( '' !== $value && null !== $value ) {
				$presets[] = wpep_parse_payment_amount( $value );
			}
		}
	}

	if ( is_array( $list ) ) {
		foreach ( $list as $row ) {
			if ( ! empty( $row['amount'] ) ) {
				$presets[] = wpep_parse_payment_amount( $row['amount'] );
			}
		}
	}

	return array_values(
		array_filter(
			$presets,
			function ( $amount ) {
				return $amount > 0;
			}
		)
	);
}

/**
 * Product-table subtotal from form meta, not client prices.
 *
 * @param int    $form_id     Form ID.
 * @param string $amount_type Amount layout.
 * @param array  $form_values Submitted form values.
 * @return float|WP_Error
 */
function wpep_get_server_tabular_subtotal( $form_id, $amount_type, $form_values ) {
	$items = array();
	foreach ( (array) $form_values as $row ) {
		if ( isset( $row['label'], $row['value'] ) && 'Line Items' === $row['label'] ) {
			$decoded = json_decode( wp_unslash( $row['value'] ), true );
			if ( is_array( $decoded ) ) {
				$items = $decoded;
			}
			break;
		}
	}

	$catalog = array();
	if ( 'tabular_layout_for_square' === $amount_type ) {
		$is_global = ( 'on' === get_post_meta( $form_id, 'wpep_individual_form_global', true ) );
		$is_live   = $is_global
			? ( 'on' === get_option( 'wpep_square_payment_mode_global' ) )
			: ( 'on' === get_post_meta( $form_id, 'wpep_payment_mode', true ) );
		$products  = get_post_meta( $form_id, 'wpep_square_products_data' . ( $is_live ? '' : '_sandbox' ), true );
		foreach ( (array) $products as $product ) {
			if ( ! empty( $product['id'] ) ) {
				$catalog[ (string) $product['id'] ] = wpep_parse_payment_amount( isset( $product['price'] ) ? $product['price'] : 0 );
			}
		}
	} else {
		$products = get_post_meta( $form_id, 'wpep_products_with_labels', true );
		foreach ( (array) $products as $product ) {
			if ( ! empty( $product['label'] ) ) {
				$catalog[ (string) $product['label'] ] = wpep_parse_payment_amount( isset( $product['amount'] ) ? $product['amount'] : 0 );
			}
		}
	}

	$subtotal = 0.0;
	foreach ( $items as $item ) {
		$qty = isset( $item['quantity'] ) ? max( 0, min( 9999, (int) $item['quantity'] ) ) : 0;
		if ( $qty < 1 ) {
			continue;
		}
		$key = ( 'tabular_layout_for_square' === $amount_type )
			? ( isset( $item['id'] ) ? (string) $item['id'] : '' )
			: ( isset( $item['label'] ) ? (string) $item['label'] : '' );
		if ( ! isset( $catalog[ $key ] ) || $catalog[ $key ] <= 0 ) {
			return new WP_Error( 'invalid_amount', __( 'Invalid payment amount.', 'wp_easy_pay' ) );
		}
		$subtotal += $catalog[ $key ] * $qty;
	}

	return ( $subtotal > 0 )
		? round( $subtotal, 2 )
		: new WP_Error( 'invalid_amount', __( 'Invalid payment amount.', 'wp_easy_pay' ) );
}

/**
 * Validate the submitted total and return the server-derived charge.
 *
 * @param int   $form_id Form ID.
 * @param array $post    Payment request.
 * @return array|WP_Error
 */
function wpep_validate_payment_amount( $form_id, $post ) {
	$form_id   = absint( $form_id );
	$submitted = isset( $post['amount'] ) ? wpep_parse_payment_amount( wp_unslash( $post['amount'] ) ) : 0;
	if ( $form_id < 1 || 'wp_easy_pay' !== get_post_type( $form_id ) || $submitted <= 0 ) {
		return new WP_Error( 'invalid_amount', __( 'Invalid payment amount.', 'wp_easy_pay' ) );
	}

	$amount_type = get_post_meta( $form_id, 'wpep_square_amount_type', true );
	$coupon_code = isset( $post['coupon_code'] ) ? sanitize_text_field( wp_unslash( $post['coupon_code'] ) ) : '';
	$form_values = isset( $post['form_values'] ) ? (array) $post['form_values'] : array();
	$presets      = wpep_get_form_preset_amounts( $form_id, $amount_type );
	$user_defined = ( 'on' === get_post_meta( $form_id, 'wpep_square_user_defined_amount', true ) )
		|| ( 'payment_custom' === $amount_type && empty( $presets ) );
	$min          = 1;
	$max          = 50000;

	if ( in_array( $amount_type, array( 'payment_tabular', 'tabular_layout_for_square' ), true ) ) {
		$subtotal = wpep_get_server_tabular_subtotal( $form_id, $amount_type, $form_values );
		if ( is_wp_error( $subtotal ) ) {
			return $subtotal;
		}
		$resolved = wpep_calculate_server_payment_total( $form_id, $subtotal, $coupon_code );
		return wpep_payment_amounts_match( $submitted, $resolved['amount'] )
			? $resolved
			: new WP_Error( 'invalid_amount', __( 'Invalid payment amount.', 'wp_easy_pay' ) );
	}

	$quantity = 1;
	if ( 'on' === get_post_meta( $form_id, 'enableQuantity', true ) && ! in_array( $amount_type, array( 'payment_custom', 'payment_tabular', 'tabular_layout_for_square' ), true ) ) {
		foreach ( $form_values as $row ) {
			if ( isset( $row['label'], $row['value'] ) && 'quantity' === $row['label'] ) {
				$quantity = max( 1, min( 9999, (int) $row['value'] ) );
				break;
			}
		}
	}

	$candidates = array();
	foreach ( $presets as $preset ) {
		$candidates[] = $preset * $quantity;
	}

	if ( $user_defined ) {
		$min = wpep_parse_payment_amount( get_post_meta( $form_id, 'wpep_square_payment_min', true ) );
		$max = wpep_parse_payment_amount( get_post_meta( $form_id, 'wpep_square_payment_max', true ) );
		$min = ( $min > 0 ) ? $min : 1;
		$max = ( $max > 0 ) ? $max : 50000;
		$base = isset( $post['base_amount'] ) ? wpep_parse_payment_amount( wp_unslash( $post['base_amount'] ) ) : 0;
		if ( $base >= $min && $base <= $max ) {
			$candidates[] = $base * $quantity;
		}
		if ( $submitted >= $min && $submitted <= $max ) {
			$candidates[] = $submitted;
		}
	}

	foreach ( array_unique( $candidates ) as $subtotal ) {
		if ( $subtotal <= 0 ) {
			continue;
		}
		$resolved = wpep_calculate_server_payment_total( $form_id, $subtotal, $coupon_code );
		if ( $resolved['amount'] > 0 && ( wpep_payment_amounts_match( $submitted, $resolved['amount'] ) || wpep_payment_amounts_match( $submitted, $subtotal ) ) ) {
			return $resolved;
		}
	}

	if ( $user_defined ) {
		$min_total = wpep_calculate_server_payment_total( $form_id, $min, $coupon_code );
		$max_total = wpep_calculate_server_payment_total( $form_id, $max, $coupon_code );
		if ( $submitted + 0.009 >= $min_total['amount'] && $submitted - 0.009 <= $max_total['amount'] ) {
			return array(
				'amount'        => round( $submitted, 2 ),
				'signup_amount' => $min_total['signup_amount'],
				'discount'      => $min_total['discount'],
			);
		}
	}

	return new WP_Error( 'invalid_amount', __( 'Invalid payment amount.', 'wp_easy_pay' ) );
}
