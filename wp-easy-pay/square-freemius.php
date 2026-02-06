<?php
/**
 * Freemius Initialization
 *
 * @package WP_EASY_PAY
 */

if ( ! function_exists( 'wepp_fs' ) ) {
	/**
	 * Create a helper function for easy SDK access.
	 */
	function wepp_fs() {
		$settings_url = 'edit.php?post_type=wp_easy_pay';
		global  $wepp_fs;

		if ( ! isset( $wepp_fs ) ) {
			// Include Freemius SDK.
			require_once __DIR__ . '/freemius/start.php';
			$wepp_fs = fs_dynamic_init(
				array(
					'id'              => '1920',
					'slug'            => 'wp-easy-pay',
					'type'            => 'plugin',
					'public_key'      => 'pk_4c854593bf607fd795264061bbf57',
					'is_premium'      => false,
					'is_premium_only' => false,
					'has_addons'      => false,
					'has_paid_plans'  => false,
					'menu'            => array(
						'slug'       => 'edit.php?post_type=wp_easy_pay',
						'first-path' => $settings_url,
						'contact'    => false,
						'support'    => false,
						'pricing'    => false,
					),
					'is_live'         => true,
				)
			);
		}

		return $wepp_fs;
	}

	// Init Freemius.
	wepp_fs();
	// Signal that SDK was initiated.
	/**
	 * Action to indicate that the "wepp_fs" library has been loaded.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wepp_fs_loaded' );
}
