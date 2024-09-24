<?php
/**
 * Filename: error-logging.php
 * Description: silence is golden.
 *
 * @package WP_Easy_Pay
 */

/**
 * Writes a log message to the error log when WP_DEBUG is true.
 *
 * @param mixed $log The data to be logged. It can be a string, array, or object.
 * @return void
 */
function wpep_write_log( $log ) {
	$error_log = 'error_log';
	$print_r   = 'print_r';
	if ( true === WP_DEBUG ) {

		if ( is_array( $log ) || is_object( $log ) ) {
			$error_log( $print_r( $log, true ) );
		} else {
			$error_log( $log );
		}
	}
}
