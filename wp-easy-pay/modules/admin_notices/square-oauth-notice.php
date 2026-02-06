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
	$wpep_square_test_token   = get_option( 'wpep_square_test_token_global', false );

	// Example: retrieve global Square payment mode option
	// $global_payment_mode = get_option( 'wpep_square_payment_mode_global', true );
	
	if ( empty( $wpep_live_token_upgraded ) && empty( $wpep_square_test_token ) ) {

		?>

	<div class="notice notice-success is-dismissible">
		<p>
			<?php
			// translators: Link to connect Square account in WP Easy Pay settings: %s is a placeholder for Connect Square URL.
			printf(
				wp_kses_post( 'Seems like you have not connected your Square account yet. <a href="%s" class="btn btn-primary btn-square"> Connect Square </a>', 'wp-easy-pay' ),
				esc_url( 'admin.php?page=wpep-settings&wpep_admin_url=admin.php&wpep_prepare_connection_call=1&wpep_page_post=global' )
			);
			?>
		</p>
	</div>

		<?php
	}
}

add_action( 'admin_notices', 'wpep_square_oauth_admin_notice' );
