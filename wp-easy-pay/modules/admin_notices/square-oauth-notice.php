<?php
/**
 * File: square-oauth-notice.php
 * Description: Silence is golden.
 *
 * @package WP_Easy_Pay
 */

/**
 * Display an admin notice to connect Square account.
 *
 * This function checks if the Square account has not been connected yet and displays
 * an admin notice with a link to the Square connection page in WP Easy Pay settings.
 */
function wpep_square_oauth_admin_notice() {
	$wpep_live_token_upgraded = get_option( 'wpep_live_token_upgraded', false );
	if ( ! $wpep_live_token_upgraded ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p>
		<?php
			// translators: Link to connect Square account in WP Easy Pay settings: %s is a placeholder for Connect Square URL.
			printf(
				wp_kses_post( 'Seems like you have not connected your Square account yet. <a href="%s" class="btn btn-primary btn-square"> Connect Square </a>', 'wp-easy-pay' ),
				esc_url( 'edit.php?page=wpep-settings&post_type=wp_easy_pay&wpep_admin_url=edit.php&wpep_post_type=wp_easy_pay&wpep_prepare_connection_call=1&wpep_page_post=global' )
			);
		?>
				</p>
		</div>
		<?php
	}
}


	add_action( 'admin_notices', 'wpep_square_oauth_admin_notice' );
