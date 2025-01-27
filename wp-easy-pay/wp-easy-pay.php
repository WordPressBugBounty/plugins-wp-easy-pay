<?php
 /*
 * Plugin Name:       WP EASY PAY
 * Plugin URI:        https://wpeasypay.com/demo/
 * Description:       Easily collect payments for Simple Payment or donations online without coding it yourself or hiring a developer. Skip setting up a complex shopping cart system.
 * Version:           4.2.9
 * Requires at least: 4.5.0
 * Requires PHP:      7.0
 * Author:            WP Easy Pay
 * Author URI:        https://wpeasypay.com/
 * License:           GPL v2 or later
 * License URI:       https://opensource.org/licenses/MIT MIT License
 * Text Domain:       wp_easy_pay
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPEP_ROOT_URL', plugin_dir_url( __FILE__ ) );
define( 'WPEP_ROOT_PATH', plugin_dir_path( __FILE__ ) );
$get_results = 'get_results';
global $wpdb;
$result = $wpdb->$get_results( "SELECT * FROM `{$wpdb->prefix}options` WHERE (`option_name` LIKE '%wpep_%' OR `option_value` LIKE '%wpep_%' OR `autoload` LIKE '%wpep_%')" );

if ( ( count( $result ) === 0 ) ) {
	update_option( 'wpep_stn', 'true' );
}
update_option( 'wpep_stn', 'true' );
if ( ! function_exists( 'wepp_fs' ) ) {
	/**
	 * Create a helper function for easy SDK access.
	 */
	function wepp_fs() {
		$switch_to_new = get_option( 'wpep_stn' );
		if ( 'true' === $switch_to_new ) {
			$settings_url = 'edit.php?post_type=wp_easy_pay&page=wpep-settings';
		} else {
			$settings_url = 'admin.php?page=wpep-settings';
		}

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

$switch_to_new = get_option( 'wpep_stn' );
if ( 'true' === $switch_to_new ) {

	if ( ! function_exists( 'add_viewport_meta_tag' ) ) {
		/**
		 * Adds the viewport meta tag to the HTML head.
		 */
		function add_viewport_meta_tag() {
			echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />';
		}
	}
	add_action( 'wp_head', 'add_viewport_meta_tag', '1' );
	register_activation_hook( __FILE__, 'wpep_plugin_activation' );

	/**
	 * Handles the activation of the WPEP plugin.
	 */
	function wpep_plugin_activation() {
		wpep_create_example_form();
	}
	/**
	 * Creates an example form for demonstration purposes.
	 */
	function wpep_create_example_form() {
		$post_id = post_exists( 'Example Form' );

		if ( 0 === $post_id ) {
			$my_post = array(
				'post_title'   => 'Example Form',
				'post_content' => 'This is to demnstrate how a form is created. Do not forget to connect your Square account in Square connect menu.',
				'post_status'  => 'publish',
				'post_type'    => 'wp_easy_pay',
			);
			// Insert the post into the database.
			$post_ID = wp_insert_post( $my_post );
			update_post_meta( $post_ID, 'wpep_individual_form_global', 'on' );
			update_post_meta( $post_ID, 'wpep_square_payment_box_1', '100' );
			update_post_meta( $post_ID, 'wpep_square_payment_box_2', '200' );
			update_post_meta( $post_ID, 'wpep_square_payment_box_3', '300' );
			update_post_meta( $post_ID, 'wpep_square_payment_box_4', '400' );
			update_post_meta( $post_ID, 'wpep_square_payment_type', 'simple' );
			update_post_meta( $post_ID, 'wpep_square_form_builder_fields', '[ { "type": "text", "required": true, "label": "First Name", "className": "form-control", "name": "wpep-first-name-field", "subtype": "text", "hideLabel": "yes" }, { "type": "text", "required": true, "label": "Last Name", "className": "form-control", "name": "wpep-last-name-field", "subtype": "text", "hideLabel": "yes" }, { "type": "text", "subtype": "email", "required": true, "label": "Email", "className": "form-control", "name": "wpep-email-field", "hideLabel": "yes" } ]' );
			update_post_meta( $post_ID, 'wpep_payment_success_msg', 'The example payment form has been submitted successfully' );
		}
	}

	require_once WPEP_ROOT_PATH . 'wpep-setup.php';
	require_once WPEP_ROOT_PATH . 'modules/vendor/autoload.php';
	require_once WPEP_ROOT_PATH . 'modules/payments/square-authorization.php';
	require_once WPEP_ROOT_PATH . 'modules/payments/square-payments.php';
	require_once WPEP_ROOT_PATH . 'modules/render_forms/form-render-shortcode.php';
	require_once WPEP_ROOT_PATH . 'modules/admin_notices/ssl-notice.php';
	require_once WPEP_ROOT_PATH . 'modules/admin_notices/square-oauth-notice.php';
	
	function redirect_to_custom_url() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'get_pro_menu' ) {
			header( 'Location: https://wpeasypay.com/pricing/?utm_source=plugin&utm_medium=get_pro_menu' );
			exit;
		}
	}

	add_action( 'admin_init', 'redirect_to_custom_url' );
	add_action(
		'plugins_loaded',
		'wpep_set_refresh_token_cron',
		10,
		2
	);
	add_action(
		'wpep_weekly_refresh_tokens',
		'wpep_weekly_refresh_tokens',
		10,
		2
	);
	$data = isset( $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : '';
	parse_str( $data, $query_params );

	$current_post_type = '';
	if ( isset( $query_params['post'] ) ) {
		$id_post           = absint( $query_params['post'] );
		$current_post_type = get_post_type( $id_post );
	}

	if ( isset( $query_params['post_type'] ) ) {
		$current_post_type = sanitize_key( $query_params['post_type'] );
	}
	if ( isset( $current_post_type ) ) {
		if ( 'wp_easy_pay' === $current_post_type ) {
			add_action( 'edit_form_after_editor', 'wpep_render_add_form_ui' );
			add_action( 'admin_enqueue_scripts', 'wpep_include_scripts_easy_pay_type_only' );
			add_action( 'admin_enqueue_scripts', 'wpep_include_stylesheets' );
		}
		if ( 'wpep_reports' === $current_post_type ) {
			add_action( 'admin_enqueue_scripts', 'wpep_include_stylesheets' );
			add_action( 'admin_enqueue_scripts', 'wpep_include_reports_scripts' );
		}
	}
	/**
	 * Sets the refresh token cron job.
	 */
	function wpep_set_refresh_token_cron() {
		if ( ! wp_next_scheduled( 'wpep_weekly_refresh_tokens' ) ) {
			wp_schedule_event( time(), 'weekly', 'wpep_weekly_refresh_tokens' );
		}
	}

	/**
	 * Includes the stylesheets required for the plugin.
	 */
	function wpep_include_stylesheets() {
		wp_enqueue_style(
			'wpep_backend_style',
			WPEP_ROOT_URL . 'assets/backend/css/wpep_backend_styles.css',
			array(),
			'1.0.0'
		);
	}
	/**
	 * Includes the scripts required for Easy Pay type only.
	 */
	function wpep_include_scripts_easy_pay_type_only() {
		wp_enqueue_script(
			'wpep_form-builder',
			WPEP_ROOT_URL . 'assets/backend/js/form-builder.min.js',
			array(),
			'3.0.0',
			true
		);
		wp_enqueue_script(
			'ckeditor',
			'https://cdn.ckeditor.com/ckeditor5/27.1.0/classic/ckeditor.js',
			array(),
			'1.0.0',
			true
		);
		wp_enqueue_script(
			'wpep_backend_scripts_multiinput',
			WPEP_ROOT_URL . 'assets/backend/js/wpep_backend_scripts_multiinput.js',
			array(),
			'3.0.0',
			true
		);
		wp_enqueue_script(
			'wpep_backend_script',
			WPEP_ROOT_URL . 'assets/backend/js/wpep_backend_scripts.js',
			array(),
			'3.0.0',
			true
		);
		$current_post_type = get_post_type( get_the_ID() );
		if ( 'wp_easy_pay' === $current_post_type ) {
			wp_localize_script(
				'wpep_backend_script',
				'wpep_hide_elements',
				array(
					'ajax_url'          => admin_url( 'admin-ajax.php' ),
					'hide_publish_meta' => 'true',
					'wpep_site_url'     => WPEP_ROOT_URL,
				)
			);
		}
		wp_enqueue_script(
			'wpep_jscolor_script',
			WPEP_ROOT_URL . 'assets/backend/js/jscolor.js',
			array(),
			'1.0',
			true
		);
	}


	/**
	 * Renders the UI for adding a form.
	 */
	function wpep_render_add_form_ui() {
		require_once 'views/backend/form_builder_settings/add-payment-form-custom-fields.php';
	}

	define( 'WPEP_SQUARE_PLUGIN_NAME', 'WP_EASY_PAY' );
	define( 'WPEP_SQUARE_APP_NAME', 'WP_EASY_PAY_SQUARE_APP' );
	define( 'WPEP_MIDDLE_SERVER_URL', 'https://connect.apiexperts.io' );
	define( 'WPEP_SQUARE_APP_ID', 'sq0idp-k0r5c0MNIBIkTd5pXmV-tg' );
	define( 'WPEP_SQUARE_TEST_APP_ID', 'sandbox-sq0idb-H_7j0M8Q7PoDNmMq_YCHKQ' );
	add_action( 'init', 'wpep_register_gutenberg_blocks' );
	/**
	 * Registers the Gutenberg blocks for the plugin.
	 */
	function wpep_register_gutenberg_blocks() {
		$args               = array(
			'numberposts' => 10,
			'post_type'   => 'wp_easy_pay',
		);
		$latest_books       = get_posts( $args );
		$wpep_payment_forms = array();
		$count              = 0;
		foreach ( $latest_books as $value ) {
			$wpep_payment_forms[ $count ]['ID']    = $value->ID;
			$wpep_payment_forms[ $count ]['title'] = $value->post_title;
			++$count;
		}
		wp_enqueue_script( 'wpep_shortcode_block', WPEP_ROOT_URL . 'assets/backend/js/gutenberg_shortcode_block/build/index.js', array( 'wp-blocks' ), '1.0.0', true );
		wp_enqueue_script( 'wpep_shortcode_block' );
		$wpep_forms = array(
			'forms' => $wpep_payment_forms,
		);
		wp_localize_script( 'wpep_shortcode_block', 'wpep_forms', $wpep_forms );
	}

	register_block_type(
		'wpep/shortcode',
		array(
			'editor_script'   => 'wpep_shortcode_block',
			'render_callback' => 'custom_gutenberg_render_html',
		)
	);
	/**
	Renders the HTML content for the custom Gutenberg block.
	 *
	@param array $attributes The attributes of the block.
	@return string The HTML content.
	 */
	function custom_gutenberg_render_html( $attributes ) {
		$shortcode = '[wpep-form id="' . $attributes['type'] . '"]';
		return $shortcode;
	}
	
	    
    function wpep_include_reports_scripts()
    {
        $data = array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wp_global_nonce' ),
        );
        wp_enqueue_script(
            'wpep_reports_scripts',
            WPEP_ROOT_URL . 'assets/backend/js/reports_scripts.js',
            array(),
            '3.0.0',
            true
        );
        wp_localize_script( 'wpep_reports_scripts', 'wpep_reports_data', $data );
    }
}

