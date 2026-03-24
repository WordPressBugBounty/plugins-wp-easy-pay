<?php

// Mask + format request body before display
function wpep_format_request_body_for_display( $request ) {
	if ( is_serialized( $request ) ) {
		$request = maybe_unserialize( $request );
	}

	if ( is_array( $request ) ) {
		$formatted = array();

		// Show only required keys
		if ( isset( $request['request_type'] ) ) {
			$formatted['Request Type'] = $request['request_type'];
		}

		if ( isset( $request['oauth_version'] ) ) {
			$formatted['OAuth Version'] = $request['oauth_version'];
		}

		if ( isset( $request['app_name'] ) ) {
			$formatted['App Name'] = $request['app_name'];
		}

		// Mask refresh_token
		if ( isset( $request['refresh_token'] ) ) {
			$formatted['Refresh Token'] = '***';
		}

		// Convert array to readable string
		$output = '';
		foreach ( $formatted as $key => $value ) {
			$output .= $key . ': ' . $value . "\n";
		}

		return esc_html( $output );
	}

	return esc_html( print_r( $request, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
}

// Mask + format response body before display
function wpep_format_response_body_for_display( $response ) {
	$decoded = json_decode( $response, true );

	if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
		$formatted = $decoded;

		// Mask tokens
		if ( isset( $formatted['access_token'] ) ) {
			$formatted['access_token'] = '***';
		}
		if ( isset( $formatted['refresh_token'] ) ) {
			$formatted['refresh_token'] = '***';
		}
		if ( isset( $formatted['merchant_id'] ) ) {
			$formatted['merchant_id'] = '***';
		}

		// Format expires_at into readable date
		if ( isset( $formatted['expires_at'] ) ) {
			$formatted['expires_at'] = gmdate( 'd-M-Y H:i:s', strtotime( $formatted['expires_at'] ) );
		}

		// Convert array to readable string (recursive)
		$output = wpep_array_to_readable_string( $formatted );

		return esc_html( $output );
	}

	return esc_html( print_r( $response, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
}

// Helper: Recursively convert array to readable string
function wpep_array_to_readable_string( $data, $indent = 0 ) {
	$output = '';
	foreach ( $data as $key => $value ) {
		// Agar key numeric hai (jaise 0,1,2), label blank rakho
		if ( is_numeric( $key ) ) {
			$label = '';
		} else {
			$label = ucfirst( str_replace( '_', ' ', $key ) );
		}

		$prefix = str_repeat( '  ', $indent ); // indent for nested arrays

		if ( is_array( $value ) ) {
			if ( '' !== $label ) {
				$output .= $prefix . $label . ":\n";
			}
			$output .= wpep_array_to_readable_string( $value, $indent + 1 );
		} elseif ( is_bool( $value ) ) {
			$output .= $prefix . $label . ': ' . ( $value ? 'true' : 'false' ) . "\n";
		} else {
			$output .= $prefix . $label . ': ' . $value . "\n";
		}
	}
	return $output;
}
