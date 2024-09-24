<?php
/**
 * Filename: ssl-notice.php
 * Description: silence is golden.
 *
 * @package WP_Easy_Pay
 */

/**
 * Displays an admin notice for SSL certificate.
 */
function wpep_ssl_admin_notice() {
	if ( ! empty( $_SERVER['HTTPS'] ) && 'off' === $_SERVER['HTTPS'] ) {
		?>

	<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e( 'Seems like you do not have a valid SSL certificate installed or you are not accessing the website using HTTPS protocol.', 'wp-easy-pay' ); ?></p>
	</div>

		<?php
	}
}

add_action( 'admin_notices', 'wpep_ssl_admin_notice' );
