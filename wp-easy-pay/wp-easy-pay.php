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
 * Version: 4.3
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

// Start session safely (avoid headers already sent).
add_action(
	'init',
	function () {
		if ( function_exists( 'session_status' ) && PHP_SESSION_NONE !== session_status() ) {
			return;
		}
		if ( headers_sent() ) {
			return;
		}
		session_start();
	},
	1
);

register_activation_hook( __FILE__, 'wpep_create_square_logs_table' );
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
	$all_reports = $wpdb->get_results(
		$wpdb->prepare(
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
				$transient_data['successful_transactions'][ $report_id ] = $transaction_status; // phpcs:ignore WordPress.Arrays.ArrayKeySpacingRestrictions.NoSpacesAroundArrayKeys
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
// Fix: Check $_POST instead of undefined $post variable
if ( isset( $_POST['wp_global_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ), 'wp_global_nonce' ) ) {
	exit;
}
// $_REQUEST is used for reading query parameters, values are sanitized when accessed via $request array
$request = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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
require_once WPEP_ROOT_PATH . 'modules/blocks/gutenberg-shortcode-block.php';
require_once WPEP_ROOT_PATH . 'modules/admin_notices/square-oauth-notice.php';
// Add this after line 369 (after other AJAX handlers)
add_action( 'wp_ajax_wpep_clear_all_logs', 'wpep_clear_all_logs_handler' );

/**
 * Clear all Square connection logs from database
 *
 * @return void
 */
function wpep_clear_all_logs_handler() {
	// Verify nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpep_clear_logs_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed.' ) );
	}

	// Check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'You do not have permission to perform this action.' ) );
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'wpep_square_logs';

	// Delete all logs from the table
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
	$deleted = $wpdb->query( "DELETE FROM {$table_name}" );

	if ( false !== $deleted ) {
		wp_send_json_success( array( 'message' => 'All logs deleted successfully.', 'deleted_count' => $deleted ) );
	} else {
		wp_send_json_error( array( 'message' => 'Failed to delete logs from database.' ) );
	}
}

/**
 * Redirect to a custom URL when a specific query parameter is set.
 *
 * This function checks if the `page` query parameter in the URL is equal to `get_pro_menu`.
 * If it matches, the user is redirected to a predefined external URL.
 *
 * @return void
 */
function redirect_to_custom_url() {
	if ( isset( $_GET['page'] ) && 'get_pro_menu' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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
	$post_type_name = get_post_type( absint( $request['post'] ) );
}
if ( isset( $request['post_type'] ) ) {
	$post_type_name = sanitize_text_field( $request['post_type'] );
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
	$request_page = sanitize_text_field( $request['page'] );
	if ( 'wpep-settings' === $request_page || 'wpep-dashboard' === $request_page || 'wpep-integrations' === $request_page || 'wpep-coupon' === $request_page || 'wpep-subscription' === $request_page ) {
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
 * Create a square log table when plugin register_activation_hook 
 */
function wpep_create_square_logs_table() {
	global $wpdb;
	$wpep_log_table_name  = $wpdb->prefix . 'wpep_square_logs';
	$wpep_charset_collate = $wpdb->get_charset_collate();

	$wpep_log_sql = "CREATE TABLE $wpep_log_table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        mode varchar(50) NOT NULL,
        datetime datetime NOT NULL,
        request longtext NULL,
        response longtext NULL,
        status varchar(20) NOT NULL,
		form_id varchar(100) NULL,
        PRIMARY KEY  (id)
    ) $wpep_charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $wpep_log_sql );
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
	wp_enqueue_style(
		'figtree-admin-font',
		'https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap',
		array(),
		WPEP_VERSION
	);
	wp_enqueue_style(
		'wpep_square_logs',
		WPEP_ROOT_URL . 'assets/backend/css/square-logs.css',
		array(),
		'1.0.0'
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

	$current_post_id = get_the_ID();
	$post_type_name  = $current_post_id ? get_post_type( $current_post_id ) : '';
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
			'clear_logs_nonce'  => wp_create_nonce( 'wpep_clear_logs_nonce' ),
		)
	);

	// Add forms count data if on wp_easy_pay list page
	global $typenow, $wp_query;
	$current_post_type = isset( $post_type_name ) ? $post_type_name : '';
	if ( empty( $current_post_type ) && isset( $typenow ) ) {
		$current_post_type = $typenow;
	}
	if ( empty( $current_post_type ) && isset( $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_post_type = sanitize_text_field( wp_unslash( $_GET['post_type'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	
	if ( 'wp_easy_pay' === $current_post_type ) {
		// Get total count of all forms from database
		$total_forms = wp_count_posts( 'wp_easy_pay' );
		$total_count = 0;
		
		// Sum all statuses except auto-draft
		foreach ( $total_forms as $status => $count ) {
			// Exclude auto-draft from count
			if ( 'auto-draft' !== $status ) {
				$total_count += (int) $count;
			}
		}
		
		// Get current displayed count from the query
		$displayed_count = 0;
		if ( isset( $wp_query ) && isset( $wp_query->found_posts ) && $wp_query->found_posts > 0 ) {
			$displayed_count = (int) $wp_query->found_posts;
		} else {
			// Fallback: use total count if query not available
			$displayed_count = $total_count;
		}
		
		wp_localize_script(
			'wpep_backend_script',
			'wpep_forms_count',
			array(
				'displayed_count' => $displayed_count,
				'total_count'     => $total_count,
			)
		);
	}

	if ( ( isset( $post_type_name ) && ( 'wp_easy_pay' === $post_type_name || 'wpep_coupons' === $post_type_name ) ) || ( isset( $_REQUEST['page'] ) && 'wpep-settings' === sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		add_action( 'admin_footer', 'add_custom_dialog_box' );
		add_action( 'admin_footer', 'add_custom_delete_box' );
		add_action( 'admin_footer', 'wpep_confirmation_popup' );
		add_action( 'admin_footer', 'wpep_draft_popup' );
	}
	wp_enqueue_script(
		'wpep_jscolor_script',
		WPEP_ROOT_URL . 'assets/backend/js/jscolor.js',
		array(),
		'1.0',
		true
	);
	if ( isset( $_GET['page'] ) && 'wpep-dashboard' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

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

function load_font_awesome_in_admin() {
	// font-awesome load
	wp_enqueue_style(
		'font-awesome-easypay',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css',
		array(),
		WPEP_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'load_font_awesome_in_admin' );

// Add this new function for frontend
function load_figtree_font_in_frontend() {
	wp_enqueue_style(
		'figtree-frontend-font',
		'https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap',
		array(),
		WPEP_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'load_figtree_font_in_frontend' );

/**
 * Adds custom WP EasyPay header to all backend pages
 */
function wpep_add_custom_header_to_all_pages() {
	$screen = get_current_screen();

	$wpep_pages = array(
		'wp_easy_pay',
		'wpep_reports',
		'wpep_subscriptions',
		'wpep_coupons',
		'wpep-dashboard',
		'wpep-settings',
		'wpep-integrations',
		'wpep-coupon',
		'wpep-subscription',
	);

	$is_wpep_page = false;

	if ( isset( $screen->post_type ) && in_array( $screen->post_type, $wpep_pages, true ) ) {
		$is_wpep_page = true;
	}

	if ( isset( $screen->id ) ) {
		foreach ( $wpep_pages as $page ) {
			if ( false !== strpos( $screen->id, $page ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				$is_wpep_page = true;
				break;
			}
		}
	}

	// Sirf edit form screen par (body .post-type-wp_easy_pay.post-php)
	$is_edit_form_screen = ( isset( $screen->base ) && 'post' === $screen->base && isset( $screen->post_type ) && 'wp_easy_pay' === $screen->post_type );

	if ( $is_wpep_page ) {
		?>
		<div class="wpep-custom-header">
			<div class="wpep-header-content">
				<div class="wpep-logo-section">
					<div class="wpep-logo">
						<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/wpep-logo-pro.png' ); ?>" class="" />
					</div>
				</div>
				<div class="wpep-header-actions" id="wpep-screen-options-container">
					<?php if ( ! empty( $is_edit_form_screen ) ) { ?>
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=wp_easy_pay' ) ); ?>" 
						class="wpep-back-to-forms-btn">
							<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/arrow-left.png' ); ?>" class="backToFormArrow" /> Back to all forms
						</a>
					<?php } ?>
					<button type="button" class="wpep-screen-options-btn" id="wpep-screen-options">
						Screen Options <span class="dashicons dashicons-arrow-down-alt2"></span>
					</button>
				</div>
			</div>
		</div>

		<style>
		/* WP EasyPay Custom Header Styles */
		.backToFormArrow{
			width: 15%;
			margin-right: 8px;
		}

		.wpep-custom-header {
			background: #fff;
			border-bottom: 1px solid #e1e5e9;
			padding: 15px 20px;
			margin: 0px 0px 0px 0px;
			position: relative;
		}

		.wpep-header-content {
			display: flex;
			justify-content: space-between;
			align-items: center;
			max-width: 100%;
		}

		.wpep-logo-section {
			flex: 1;
		}

		.wpep-logo {
			padding: 8px 16px;
		}

		.wpep-logo img{
			width: 15%;
		}

		.wpep-header-actions {
			flex: 0 0 auto;
			display: inline-flex;
		}

		.wpep-screen-options-btn {
			background: none;
			border: none;
			color: #2065E0;
			font-size: 14px;
			font-weight: 500;
			cursor: pointer;
			padding: 8px 5px;
			transition: all 0.2s ease;
			display: flex;
			align-items: center;
			gap: 5px;
			border-bottom: 1px solid #2065E0;
			font-family: Figtree;
		}

		.wpep-screen-options-btn:hover {
			background: #f0f6fc;
			color: #2065E0;
		}

		.wpep-screen-options-btn .dashicons {
			font-size: 16px;
			width: 16px;
			height: 16px;
		}

		/* Hide original screen options button */
		#screen-options-link-wrap {
			display: none !important;
		}
		#screen-meta{
			width: 100% !important;
		}

		/* Responsive Design */
		@media (max-width: 768px) {
			.wpep-header-content {
				flex-direction: column;
				gap: 15px;
				align-items: flex-start;
			}

			.wpep-header-actions {
				align-self: flex-end;
			}
		}
		.wpep-back-to-forms-btn {
			display: inline-flex;
			align-items: center;
			gap: 5px;
			padding: 8px 12px;
			margin-right: 15px;
			background: none;
			border: none;
			color: #647179;
			font-size: 14px;
			font-weight: 600;
			text-decoration: none;
			cursor: pointer;
			transition: all 0.2s ease;
			border-right: 2px solid #647179;
			font-family: Figtree;
		}
		.wpep-back-to-forms-btn:hover {
			background: #f0f6fc;
			color: #2065E0;
		}
		</style>

		<script>
		jQuery(document).ready(function(jQuery) {
			// Hide Screen Options button if WP screen meta not available
				var hasWpScreenMeta = jQuery('#screen-meta-links').length > 0 && (jQuery('#screen-options-wrap').length > 0 || jQuery('#contextual-help-wrap').length > 0);
				if ( !hasWpScreenMeta ) {
					jQuery('#wpep-screen-options-container').remove();
					return; // stop binding click handlers for this page
				}

			// Function to toggle WordPress screen options
			function toggleScreenOptions() {
				var screenMeta = jQuery('#screen-meta');
				var screenOptionsWrap = jQuery('#screen-options-wrap');

				if (screenMeta.length > 0) {
					if (screenMeta.is(':visible')) {
						// Hide screen options
						screenMeta.hide();
						screenOptionsWrap.hide();
					} else {
						// Show screen options
						screenMeta.show();
						screenOptionsWrap.show();
					}
				} else {
					console.log('Screen options not found on this page');
				}
			}

			// Bind click event to our custom button
			jQuery('#wpep-screen-options').on('click', function(e) {
				e.preventDefault();
				toggleScreenOptions();
			});

			// Also handle the original WordPress screen options functionality
			jQuery(document).on('click', '#screen-options-apply', function(e) {
				// Let WordPress handle the form submission
				return true;
			});
		});
		</script>
		<?php
	}
}

// Hook the function to admin_notices with very high priority
add_action('admin_notices', 'wpep_add_custom_header_to_all_pages', -999);

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

/**
 * Displays a custom dialog box with modal overlay and actions.
 *
 * This function outputs a modal dialog box with two options: delete and draft.
 * It includes SVG icons, styled messages, and buttons for each option. The modal
 * serves as an important warning for actions on forms, with specific messages
 * describing the effects of each action.
 *
 * @return void Outputs HTML markup for the dialog box with embedded SVG icons.
 */
function add_custom_dialog_box() {
	?>
				
		<div class="modal-overlay allFormsScreen">
			<div class="modal">
			<div class="modal-header">
				<!-- <h3 class="modal-title error-message">Important!!</h3> -->
				<div class="mainModalHeader">
					<div class="child1">
						<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-action.png' ); ?>" class="" />
					</div>
					<div class="child2">
						<h4>Action Required</h4>
					</div>
				</div>
				<p class="deletePopupTxt">Choose how you'd like to proceed with this form and its associated transactions.</p>
			</div>
			<div class="modal-content">
				<div class="mainDeleteContent" id="dialog-delete">
					<div class="contentChild1">
						<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/delete-icon.png' ); ?>" class="" />
					</div>
					<div class="contentChild2">
						<h4>Delete Permanently</h4>
						<p class="deletePermenantTxt">All transactions related to this form will be permanently removed and cannot be recovered. This action cannot be undone.</p>
					</div>
				</div>
				<div class="mainDraftContent" id="dialog-draft">
					<div class="contentChild1">
						<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/draft-icon.png' ); ?>" class="" />
					</div>
					<div class="contentChild2">
						<h4>Save as Draft</h4>
						<p class="draftPermenantTxt">The form will be saved as a draft, allowing you to pause or resume all associated transactions at any time.</p>
					</div>
				</div>
			</div>
			<div class="modal-footer">
			</div>
			</div>
		</div>

	<?php
}

/**
 * Renders a custom delete confirmation dialog box.
 *
 * This function outputs HTML markup for a modal dialog that provides
 * users with a confirmation prompt when attempting to delete a form.
 * The dialog includes a warning icon, title, descriptive message,
 * and Cancel/Delete buttons.
 *
 * @return void Outputs HTML markup for the delete confirmation popup modal.
 */
function add_custom_delete_box() {
	?>
			<div id="custom-delete-dialog" class="wpep-modal">
				<div class="wpep-modal-content deleteConfirmation">
					<div class="mainModalHeader">
						<div class="child1">
							<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-action.png' ); ?>" class="" />
						</div>
						<div class="child2">
							<h4>Are you sure?</h4>
						</div>
					</div>
					<p class="confirmationDeletePopupTxt">This will permanently delete the form and all 5 active transactions. This action cannot be undone.</p>
					<br/>
					<div class="mainModalHeaderBtns">
						<div class="child1">
							<button id="confirm-delete" class="delete-button">Delete Permanently</button>
						</div>
						<div class="child2">
							<button id="confirm-cancel" class="green-button">Cancel</button>
						</div>
					</div>
				</div>
			</div>
			<?php
}

/**
 * Displays a custom confirmation popup for permanent deletion.
 *
 * This function outputs a confirmation modal dialog to inform the user that a form
 * has been permanently deleted. It includes a warning icon and a custom message.
 *
 * @return void Outputs HTML markup for the deletion confirmation popup modal.
 */
function wpep_confirmation_popup() {
	?>
		<div id="delete-modal" class="delete-wpep-message">
			<div class="modal-delete">
			<div class="warning-logo">
				<svg width="100px" height="100px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M16 8L8 16M8.00001 8L16 16" stroke="red" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="stroke-width: 2; stroke: red; font-weight: bold;" />
				</svg>
			</div>
			<h2 style="margin-top: 0;color: red;">Your form has been permanently deleted</h2>
			<!-- Additional content if needed -->
			</div>
		</div>
	<?php
}

/**
 * Displays a custom draft success popup for the plugin.
 *
 * This function outputs a success modal dialog with a draft message and a
 * custom SVG icon to indicate that a form has been successfully drafted.
 *
 * @return void Outputs HTML markup for the draft success popup modal.
 */
function wpep_draft_popup() {
	?>
		<div id="draft-modal" class="draft-wpep-message">
			<div class="modal-draft">
			<div class="warning-logo">
				<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 64 64" width="64px" height="64px"><radialGradient id="6zrDEwTjn7rjI7a4AleQXa" cx="32" cy="31.5" r="31.259" gradientUnits="userSpaceOnUse" spreadMethod="reflect"><stop offset="0" stop-color="#e9ce8e"/><stop offset="0" stop-color="#e9ce8e"/><stop offset=".241" stop-color="#f1dca9"/><stop offset=".521" stop-color="#f9e8c0"/><stop offset=".782" stop-color="#fdefcf"/><stop offset="1" stop-color="#fff2d4"/></radialGradient><path fill="url(#6zrDEwTjn7rjI7a4AleQXa)" d="M58,54c-1.105,0-2-0.895-2-2c0-1.105,0.895-2,2-2h2.5c1.925,0,3.5-1.575,3.5-3.5 S62.425,43,60.5,43H50c-1.381,0-2.5-1.119-2.5-2.5c0-1.381,1.119-2.5,2.5-2.5h8c1.65,0,3-1.35,3-3c0-1.65-1.35-3-3-3H42v-6h18 c2.335,0,4.22-2.028,3.979-4.41C63.77,19.514,61.897,18,59.811,18H58c-1.105,0-2-0.895-2-2c0-1.105,0.895-2,2-2h0.357 c1.308,0,2.499-0.941,2.63-2.242C61.137,10.261,59.966,9,58.5,9h-14C43.672,9,43,8.328,43,7.5S43.672,6,44.5,6h3.857 c1.308,0,2.499-0.941,2.63-2.242C51.137,2.261,49.966,1,48.5,1L15.643,1c-1.308,0-2.499,0.941-2.63,2.242 C12.863,4.739,14.034,6,15.5,6H19c1.105,0,2,0.895,2,2c0,1.105-0.895,2-2,2H6.189c-2.086,0-3.958,1.514-4.168,3.59 C1.78,15.972,3.665,18,6,18h2.5c1.933,0,3.5,1.567,3.5,3.5c0,1.933-1.567,3.5-3.5,3.5H5.189c-2.086,0-3.958,1.514-4.168,3.59 C0.78,30.972,2.665,33,5,33h17v11H6c-1.65,0-3,1.35-3,3c0,1.65,1.35,3,3,3h0c1.105,0,2,0.895,2,2c0,1.105-0.895,2-2,2H4.189 c-2.086,0-3.958,1.514-4.168,3.59C-0.22,59.972,1.665,62,4,62h53.811c2.086,0,3.958-1.514,4.168-3.59C62.22,56.028,60.335,54,58,54z"/><linearGradient id="6zrDEwTjn7rjI7a4AleQXb" x1="32" x2="32" y1="8.925" y2="52.925" gradientUnits="userSpaceOnUse" spreadMethod="reflect"><stop offset="0" stop-color="#42d778"/><stop offset=".428" stop-color="#3dca76"/><stop offset="1" stop-color="#34b171"/></linearGradient><path fill="url(#6zrDEwTjn7rjI7a4AleQXb)" d="M50,12H14c-2.209,0-4,1.791-4,4v36c0,2.209,1.791,4,4,4h36c2.209,0,4-1.791,4-4V16 C54,13.791,52.209,12,50,12z"/><linearGradient id="6zrDEwTjn7rjI7a4AleQXc" x1="21" x2="21" y1="3.961" y2="49.717" gradientUnits="userSpaceOnUse" spreadMethod="reflect"><stop offset="0" stop-color="#62de8f"/><stop offset=".478" stop-color="#5dd18d"/><stop offset="1" stop-color="#56be89"/></linearGradient><path fill="url(#6zrDEwTjn7rjI7a4AleQXc)" d="M32,14.5L32,14.5c0-1.381-1.119-2.5-2.5-2.5H14c-2.209,0-4,1.791-4,4v25h10 c1.657,0,3-1.343,3-3v0c0-1.657-1.343-3-3-3h-1c-1.657,0-3-1.343-3-3v0c0-1.657,1.343-3,3-3h4c1.657,0,3-1.343,3-3v0 c0-1.657-1.343-3-3-3h-3c-1.657,0-3-1.343-3-3v0c0-1.657,1.343-3,3-3h9.5C30.881,17,32,15.881,32,14.5z"/><linearGradient id="6zrDEwTjn7rjI7a4AleQXd" x1="43.5" x2="43.5" y1="50.833" y2="18" gradientUnits="userSpaceOnUse" spreadMethod="reflect"><stop offset="0" stop-color="#37ab6a"/><stop offset=".422" stop-color="#39b66f"/><stop offset="1" stop-color="#3ac074"/></linearGradient><path fill="url(#6zrDEwTjn7rjI7a4AleQXd)" d="M54,52V39c-3.083,0-10.118,0-11.872,0c-1.451,0-2.786,0.972-3.068,2.395 C38.681,43.307,40.152,45,42,45h0.5c1.381,0,2.5,1.119,2.5,2.5c0,1.381-1.119,2.5-2.5,2.5h-6.369c-1.451,0-2.789,0.972-3.071,2.395 C32.681,54.307,34.152,56,36,56h14C52.209,56,54,54.209,54,52z"/><path fill="#f1fcff" d="M43.379,24.621L29,38.995l-6.379-6.377c-0.828-0.828-2.17-0.828-2.998,0l-0.002,0.002 c-0.828,0.828-0.828,2.169,0,2.997l7.684,7.681c0.936,0.936,2.454,0.936,3.391,0L46.379,27.62c0.828-0.828,0.828-2.169,0-2.997 l-0.002-0.002C45.549,23.793,44.207,23.793,43.379,24.621z"/></svg>
			</div>
			<h2 style="margin-top: 0;color: green;">Your form is successfully drafted now</h2>
			<!-- Additional content if needed -->
			</div>
		</div>
	<?php
}

define( 'WPEP_SQUARE_PLUGIN_NAME', 'WP_EASY_PAY' );
define( 'WPEP_SQUARE_APP_NAME', 'WP_EASY_PAY_SQUARE_APP' );
define( 'WPEP_MIDDLE_SERVER_URL', 'https://connect.apiexperts.io' );
define( 'WPEP_SQUARE_APP_ID', 'sq0idp-k0r5c0MNIBIkTd5pXmV-tg' );
define( 'WPEP_SQUARE_TEST_APP_ID', 'sandbox-sq0idb-H_7j0M8Q7PoDNmMq_YCHKQ' );
session_write_close();
