<?php
/**
 * WP EASY PAY
 * PHP version 7
 * Plugin Name: WP Easy Pay (Free)
 * Plugin URI: https://wpeasypay.com/demo/
 * Description: Easily collect payments for Simple Payment or donations online
 * without coding it yourself or hiring a developer. Skip setting up a complex shopping cart system.
 * Author: WP Easy Pay
 * Author URI: https://wpeasypay.com/
 * Version: 4.2.11
 * Text Domain: wp_easy_pay
 * License: GPLv2 or later
 *
 * Category: Wordpress_Plugin
 *
 * @package  WP_Easy_Pay
 * Author:   Author <contact@apiexperts.io>
 * license:  https://opensource.org/licenses/MIT MIT License
 * @link     http://wpeasypay.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'square-freemius.php';

if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$plugin_file = __FILE__;
$plugin_data = get_file_data(
	$plugin_file,
	array(
		'Name'        => 'Plugin Name',
		'Version'     => 'Version',
		'Description' => 'Description',
		'Author'      => 'Author',
		'Plugin URI'  => 'Plugin URI',
	)
);
define( 'WPEP_VERSION', $plugin_data['Version'] );
define( 'WPEP_ROOT_URL', plugin_dir_url( __FILE__ ) );
define( 'WPEP_ROOT_PATH', plugin_dir_path( __FILE__ ) );
function wpep_init_session() {
	if ( ! session_id() && ! headers_sent() ) {
		session_start();
	}
}
add_action( 'init', 'wpep_init_session', 1 );

register_activation_hook( __FILE__, 'wpep_plugin_activation' );
register_deactivation_hook( __FILE__, 'wpep_plugin_deactivation' );

/**
 * Activates the WPEP plugin by creating an example form and setting initial dashboard transient data.
 *
 * This function is triggered upon plugin activation to create a default example form for demonstration purposes
 * and to initialize the dashboard transient data for tracking transactions and related metrics.
 */
function wpep_plugin_activation() {
	wpep_create_example_form();
	wpep_set_dashboard_transient_data();
}

require_once WPEP_ROOT_PATH . 'wpep-setup.php';

/**
 * Handles the data of dashboard..
 */
function wpep_set_dashboard_transient_data() {
	if ( ! wp_next_scheduled( 'wpep_fetch_dashboard_data' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'wpep_fetch_dashboard_data' );
	}
}

/**
 * Handles the deactivation of the WPEP plugin.
 */
function wpep_plugin_deactivation() {
	$timestamp = wp_next_scheduled( 'wpep_fetch_dashboard_data' );
	wp_unschedule_event( $timestamp, 'wpep_fetch_dashboard_data' );
	delete_transient( 'last_processed_report_id' );
	delete_transient( 'existing_report_ids' );
	delete_transient( 'dashboard_transient_data' );
}

add_action( 'wpep_fetch_dashboard_data', 'wpep_fetch_dashboard_data_in_transient' );

/**
 * Fetches and updates dashboard data in a transient.
 *
 * This function retrieves the latest transaction data for reports and subscriptions,
 * processes transaction details, and updates a transient for efficient data storage.
 * It handles both new transactions and re-fetches data if existing reports are missing.
 *
 * @global wpdb $wpdb WordPress database access abstraction object.
 */
function wpep_fetch_dashboard_data_in_transient() {
	global $wpdb;

	// Retrieve or initialize transient values.
	$last_processed_report_id = get_transient( 'last_processed_report_id' ) ? get_transient( 'last_processed_report_id' ) : 0;
	$existing_report_ids      = get_transient( 'existing_report_ids' ) ? get_transient( 'existing_report_ids' ) : array();

	// Query all relevant report IDs in a single query.
	$prepare     = 'prepare';
	$get_results = 'get_results';
	$all_reports = $wpdb->$get_results(
		$wpdb->$prepare(
			"SELECT ID FROM `{$wpdb->prefix}posts` WHERE post_type IN (%s, %s)",
			'wpep_reports',
			'wpep_subscriptions'
		)
	);

	// Extract IDs for processing.
	$all_report_ids = wp_list_pluck( $all_reports, 'ID' );
	$new_report_ids = array_filter(
		$all_report_ids,
		function ( $id ) use ( $last_processed_report_id ) {
			return $id > $last_processed_report_id;
		}
	);
	// Initialize transient data with a helper function.
	$initialize_transient_data = function () {
		return array(
			'total_transactions_count' => 0,
			'simple_payment_total'     => 0,
			'donation_payment_total'   => 0,
			'successful_transactions'  => array(),
			'total_customers'          => array(),
			'total_amount'             => 0,
		);
	};

	$transient_data = get_transient( 'dashboard_transient_data' );
	if ( ! $transient_data ) {
		$transient_data = $initialize_transient_data();
	}

	// Reset data if missing report IDs are detected.
	if ( array_diff( $existing_report_ids, $all_report_ids ) ) {
		$last_processed_report_id = 0;
		$new_report_ids           = $all_report_ids;
		$transient_data           = $initialize_transient_data();
	}

	if ( ! empty( $new_report_ids ) ) {
		foreach ( $new_report_ids as $report_id ) {
			$transaction_type   = get_post_meta( $report_id, 'wpep_transaction_type', true );
			$transaction_status = get_post_meta( $report_id, 'wpep_transaction_status', true );
			$email              = get_post_meta( $report_id, 'wpep_email', true );

			// Add unique customers.
			if ( ! in_array( $email, $transient_data['total_customers'], true ) ) {
				$transient_data['total_customers'][] = $email;
			}

			// Track successful transactions.
			if ( 'COMPLETED' === $transaction_status ) {
				$transient_data['successful_transactions'][ $report_id ] = $transaction_status;
			}

			// Update totals based on transaction type.
			if ( 'simple' === $transaction_type ) {
				$transient_data['simple_payment_total'] += (float) get_post_meta( $report_id, 'wpep_square_charge_amount', true );
			} elseif ( 'donation' === $transaction_type ) {
				$transient_data['donation_payment_total'] += (float) get_post_meta( $report_id, 'wpep_square_charge_amount', true );
			}
		}
	}

	// Update total counts.
	$transient_data['total_transactions_count']     += count( $new_report_ids );
	$transient_data['total_customers_count']         = count( $transient_data['total_customers'] );
	$transient_data['total_successful_transactions'] = count( $transient_data['successful_transactions'] );
	$transient_data['total_amount']                  = $transient_data['simple_payment_total'] + $transient_data['donation_payment_total'];

	// Save updated transient data.
	set_transient( 'dashboard_transient_data', $transient_data, 12 * HOUR_IN_SECONDS );
	set_transient(
		'last_processed_report_id',
		end( $new_report_ids ) ? end( $new_report_ids ) : $last_processed_report_id,
		12 * HOUR_IN_SECONDS
	);
	set_transient( 'existing_report_ids', $all_report_ids, 12 * HOUR_IN_SECONDS );
}

/**
 * Creates an example payment form if it does not already exist.
 *
 * This function checks for a post with the title 'Example Form'. If it does not exist,
 * it creates a new form with predefined settings and meta data, including form fields
 * and payment options.
 *
 * @since 1.0.0
 * @return void
 */
function wpep_create_example_form() {

	$post_id = post_exists( 'Example Form' );

	if ( 0 === $post_id ) {

		$my_post = array(
			'post_title'   => 'Example Form',
			'post_content' => 'This is to demonstrate how a form is created. Do not forget to connect your Square account in Square connect menu.',
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
		update_post_meta( $post_ID, 'wpep_square_amount_type', 'payment_custom' );
		update_post_meta( $post_ID, 'wpep_form_theme_color', '5d97ff' );
		update_post_meta( $post_ID, 'wpep_square_form_builder_fields', '[ { "type": "text", "required": true, "label": "First Name", "className": "form-control", "name": "wpep-first-name-field", "subtype": "text", "hideLabel": "yes" }, { "type": "text", "required": true, "label": "Last Name", "className": "form-control", "name": "wpep-last-name-field", "subtype": "text", "hideLabel": "yes" }, { "type": "text", "subtype": "email", "required": true, "label": "Email", "className": "form-control", "name": "wpep-email-field", "hideLabel": "yes" } ]' );
		update_post_meta( $post_ID, 'wpep_payment_success_msg', 'The example payment form has been submitted successfully' );

	}
}
if ( isset( $post['wp_global_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $post['wp_global_nonce'] ), 'wp_global_nonce' ) ) {
	exit;
}
$request = $_REQUEST;
if ( ! function_exists( 'add_viewport_meta_tag' ) ) {

	/**
	 * Adds a viewport meta tag to the header for responsive design.
	 *
	 * This meta tag ensures that the website scales appropriately on different devices,
	 * preventing the user from zooming out.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function add_viewport_meta_tag() {
		echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />';
	}
}
add_action( 'wp_head', 'add_viewport_meta_tag', '1' );
require_once WPEP_ROOT_PATH . 'modules/vendor/autoload.php';
require_once WPEP_ROOT_PATH . 'modules/payments/square-authorization.php';
require_once WPEP_ROOT_PATH . 'modules/payments/square-payments.php';
require_once WPEP_ROOT_PATH . 'modules/render_forms/form-render-shortcode.php';
require_once WPEP_ROOT_PATH . 'modules/admin_notices/ssl-notice.php';
require_once WPEP_ROOT_PATH . 'modules/admin_notices/square-oauth-notice.php';

/**
 * Redirect to a custom URL when a specific query parameter is set.
 *
 * This function checks if the `page` query parameter in the URL is equal to `get_pro_menu`.
 * If it matches, the user is redirected to a predefined external URL.
 *
 * @return void
 */
function redirect_to_custom_url() {
	if ( isset( $_GET['page'] ) && 'get_pro_menu' === $_GET['page'] ) { // phpcs:ignore
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
if ( isset( $request['post'] ) ) {
	$post_type_name = get_post_type( $request['post'] );
}
if ( isset( $request['post_type'] ) ) {
	$post_type_name = $request['post_type'];
}

if ( isset( $post_type_name ) ) {

	if ( 'wp_easy_pay' === $post_type_name ) {
		add_action( 'edit_form_after_editor', 'wpep_render_add_form_ui' );
		add_action( 'admin_enqueue_scripts', 'wpep_include_scripts_easy_pay_type_only' );
		add_action( 'admin_enqueue_scripts', 'wpep_include_stylesheets' );
	}


	if ( 'wpep_reports' === $post_type_name ) {
		add_action( 'admin_enqueue_scripts', 'wpep_include_stylesheets' );
		add_action( 'admin_enqueue_scripts', 'wpep_include_reports_scripts' );
	}
}

if ( isset( $request['page'] ) ) {
	if ( 'wpep-settings' === $request['page'] || 'wpep-dashboard' === $request['page'] || 'wpep-integrations' === $request['page'] || 'wpep-coupon' === $request['page'] || 'wpep-subscription' === $request['page'] ) {
		add_action( 'edit_form_after_editor', 'wpep_render_add_form_ui' );
		add_action( 'admin_enqueue_scripts', 'wpep_include_scripts_easy_pay_type_only' );
		add_action( 'admin_enqueue_scripts', 'wpep_include_stylesheets' );
		add_action( 'admin_enqueue_scripts', 'wpep_include_reports_scripts' );
	}
}

/**
 * Sets a weekly cron event to refresh tokens.
 *
 * This function schedules a weekly cron job named 'wpep_weekly_refresh_tokens'
 * if it is not already scheduled. This cron job can be used to refresh tokens
 * at regular intervals.
 *
 * @since 1.0.0
 */
function wpep_set_refresh_token_cron() {
	if ( ! wp_next_scheduled( 'wpep_weekly_refresh_tokens' ) ) {
		wp_schedule_event( time(), 'weekly', 'wpep_weekly_refresh_tokens' );
	}
}

/**
 * Enqueues and localizes scripts for the Reports page in WP Easy Pay.
 *
 * This function loads the necessary JavaScript file for handling reports and provides
 * localized data including the AJAX URL and nonce for secure AJAX requests.
 *
 * @since 3.0.0
 */
function wpep_include_reports_scripts() {
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

/**
 * Enqueues backend stylesheets for WP Easy Pay.
 *
 * This function registers and enqueues custom backend stylesheets, including Google Fonts.
 *
 * @since 1.0.0
 */
function wpep_include_stylesheets() {
	wp_enqueue_style(
		'wpep_backend_style',
		WPEP_ROOT_URL . 'assets/backend/css/wpep_backend_styles.css',
		array(),
		'1.0.0'
	);
	wp_enqueue_style(
		'google-fonts-poppins',
		'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap',
		array(),
		'1.0'
	);
}

/**
 * Enqueues scripts and styles for WP Easy Pay plugin based on post type and page conditions.
 *
 * This function includes specific scripts and styles for the WP Easy Pay plugin
 * for use in form building, date pickers, multiselect fields, and dashboard analytics.
 * Additionally, it sets up localized data and conditional dialog boxes.
 *
 * @return void
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

	$post_type_name = get_post_type( get_the_ID() );
	if ( 'wpep_coupons' === $post_type_name ) {
		wp_enqueue_style(
			'wpep_multiselect_style',
			WPEP_ROOT_URL . 'assets/backend/css/wpep_multiselect.css',
			array(),
			'3.0.0'
		);
		wp_enqueue_style(
			'wpep_jquery-ui_style',
			'//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',
			array(),
			'3.0.0'
		);
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script(
			'wpep_multiselect_script',
			WPEP_ROOT_URL . 'assets/backend/js/wpep_multiselect.min.js',
			array(),
			'3.0.0',
			true
		);
		wp_enqueue_script(
			'wpep_backend_coupon_script',
			WPEP_ROOT_URL . 'assets/backend/js/wpep_backend_coupon_script.js',
			array(),
			'3.0.0',
			true
		);
	}

	wp_enqueue_script(
		'wpep_backend_script',
		WPEP_ROOT_URL . 'assets/backend/js/wpep_backend_scripts.js',
		array(),
		'3.0.0',
		true
	);
	wp_localize_script(
		'wpep_backend_script',
		'wpep_hide_elements',
		array(
			'ajax_url'          => admin_url( 'admin-ajax.php' ),
			'hide_publish_meta' => 'true',
			'wpep_site_url'     => WPEP_ROOT_URL,
			'nonce'             => wp_create_nonce( 'wp_global_nonce' ),
		)
	);

	wp_enqueue_script(
		'wpep_jscolor_script',
		WPEP_ROOT_URL . 'assets/backend/js/jscolor.js',
		array(),
		'1.0',
		true
	);
	if ( isset( $_GET['page'] ) && 'wpep-dashboard' === $_GET['page'] ) { // phpcs:ignore

		wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '1.0.0', true );
		wp_enqueue_script( 'chartjs-plugin-zoom', 'https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1', array( 'chart-js' ), '1.2.1', true );

		wp_enqueue_style( 'daterangepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css', array(), '3.1' );
		wp_enqueue_script( 'daterangepicker-js', 'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js', array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'daterangepicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', array( 'jquery', 'daterangepicker-js' ), '1.0.0', true );
		wp_enqueue_style( 'jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', array(), '1.12.1' );
		// Custom JS.
		$transient_data = get_transient( 'dashboard_transient_data' );
		wp_register_script( 'wpep-dashboard-js', WPEP_ROOT_URL . 'assets/backend/js/wpep-dashboard.js', array( 'jquery', 'daterangepicker', 'chart-js', 'chartjs-plugin-zoom' ), '1.0.0', true );
		wp_localize_script(
			'wpep-dashboard-js',
			'wpep_dashboard_params',
			array(
				'wpep_gross_total' => $transient_data['total_amount'],
			)
		);
		wp_enqueue_script( 'wpep-dashboard-js' );
	}
}

/**
 * Renders the UI for adding a new payment form.
 *
 * This function includes the required PHP file for displaying the UI
 * components for the "Add Payment Form" section within the backend.
 * It loads custom fields and settings for configuring a new payment form.
 *
 * @return void Outputs HTML markup for the "Add Payment Form" UI.
 */
function wpep_render_add_form_ui() {
	require_once 'views/backend/form_builder_settings/add-payment-form-custom-fields.php';
}

define( 'WPEP_SQUARE_PLUGIN_NAME', 'WP_EASY_PAY' );
define( 'WPEP_SQUARE_APP_NAME', 'WP_EASY_PAY_SQUARE_APP' );
define( 'WPEP_MIDDLE_SERVER_URL', 'https://connect.apiexperts.io' );
define( 'WPEP_SQUARE_APP_ID', 'sq0idp-k0r5c0MNIBIkTd5pXmV-tg' );
define( 'WPEP_SQUARE_TEST_APP_ID', 'sandbox-sq0idb-H_7j0M8Q7PoDNmMq_YCHKQ' );
session_write_close();
