<?php
/**
 * WP EASY PAY
 *
 * PHP version 7
 *
 * @category Wordpress_Plugin
 * @package  WP_Easy_Pay
 * @author   Author <contact@apiexperts.io>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://wpeasypay.com/
 */

add_action( 'init', 'wpep_create_payment_forms_post_type' );
add_filter( 'manage_wp_easy_pay_posts_columns', 'wpep_modify_column_names_payment_forms' );
add_action( 'manage_wp_easy_pay_posts_custom_column', 'wpep_add_columns_data_add_form', 10, 2 );
add_action( 'init', 'wpep_create_reports_post_type' );
add_filter( 'manage_wpep_reports_posts_columns', 'wpep_modify_column_names_reports' );
add_action( 'manage_wpep_reports_posts_custom_column', 'wpep_add_columns_data_reports', 9, 2 );
add_action( 'admin_menu', 'wpep_add_submenu' );
add_action( 'post_edit_form_tag', 'wpep_post_edit_form_tag' );
add_action( 'wp_ajax_wpep_reset_donation_goal', 'wpep_reset_donation_goal' );
add_filter( 'cron_schedules', 'wpep_email_payment_summary_cron_schedules' );
add_action( 'wpep_email_payment_summary_cron_job_hook', 'wpep_email_payment_summary_cron_job' );


$post_custom = $_POST; // phpcs:ignore

/**
 * Adds the multipart encoding type to the form tag.
 *
 * This function echoes the `enctype="multipart/form-data"` attribute,
 * which allows file uploads in the form submission.
 *
 * @return void
 */
function wpep_post_edit_form_tag() {
	echo ' enctype="multipart/form-data"';
}

/**
 * Resets the donation goal achieved status for a specified form.
 *
 * Validates nonce and form ID; then updates wpep_donation_goal_achieved to 0.
 *
 * @return void Sends 'done' on success or exits with error status on failure.
 */
function wpep_reset_donation_goal() {

	// Require nonce (prevents bypass by omitting donation_goal_nonce parameter).
	if (
		! isset( $_POST['donation_goal_nonce'] ) || // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in wp_verify_nonce() below.
		! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['donation_goal_nonce'] ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by sanitize_text_field().
			'donation-goal-nonce'
		)
	) {
		wp_die( 'Invalid nonce', 403 );
	}

	$form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- Validated via absint().
	if ( 0 === $form_id ) {
		wp_die( 'Missing form ID', 400 );
	}

	update_post_meta( $form_id, 'wpep_donation_goal_achieved', 0 );

	wp_die( 'done' );
}

/**
 * Adds custom cron schedules for weekly and monthly intervals.
 *
 * This function extends the default WordPress cron schedules by adding
 * a 'weekly' interval (7 days) and a 'monthly' interval (30.44 days).
 *
 * @param array $schedules An array of existing cron schedules.
 * @return array Updated array of cron schedules including weekly and monthly intervals.
 */
function wpep_email_payment_summary_cron_schedules( $schedules ) {
	// Add a weekly interval.
	$schedules['weekly'] = array(
		'interval' => 604800, // 1 week in seconds.
		'display'  => __( 'Once Weekly' ),
	);

	// Add a monthly interval.
	$schedules['monthly'] = array(
		'interval' => 2635200, // 1 month in seconds (30.44 days).
		'display'  => __( 'Once Monthly' ),
	);

	return $schedules;
}

/**
 * Get the start date of the previous week.
 *
 * Calculates the start date of the week ending at the provided end date.
 *
 * @param string $end_date The end date in 'Y-m-d H:i:s' format.
 *
 * @return string The start date of the previous week in 'Y-m-d H:i:s' format.
 */
function get_previous_week_start_date( $end_date ) {
	// Create a DateTime object for the end date.
	$date = new DateTime( $end_date );
	// Subtract 6 days to get the start date of the week.
	$date->modify( '-1 week' );
	// Return the start date in 'Y-m-d' format.
	return $date->format( 'Y-m-d H:i:s' );
}

/**
 * Get the start date of the previous month.
 *
 * Calculates the start date of the month ending at the provided end date.
 *
 * @param string $end_date The end date in 'Y-m-d H:i:s' format.
 *
 * @return string The start date of the previous month in 'Y-m-d H:i:s' format.
 */
function get_previous_month_start_date( $end_date ) {
	// Create a DateTime object for the end date.
	$date = new DateTime( $end_date );
	// Subtract one month.
	$date->modify( '-1 month' );
	// Return the date in 'Y-m-d' format.
	return $date->format( 'Y-m-d H:i:s' );
}

/**
 * Send payment summary emails for all enabled forms.
 *
 * This function retrieves all forms of type `wp_easy_pay`, checks their payment summary
 * report settings, and sends summary emails based on the configured frequency (weekly or monthly).
 *
 * @return void
 */
function wpep_email_payment_summary_cron_job() {

	update_option( 'wpep_email_payment_summary_' . gmdate( 'Y-m-d H:i:s' ), '' );
	$args = array(
		'post_type'      => 'wp_easy_pay',  // Custom post type name.
		'posts_per_page' => -1,      // Get all posts.
		'post_status'    => 'publish', // Only get published posts.
	);

	$query = new WP_Query( $args );

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$wpep_enable_payment_summary_report        = get_post_meta( get_the_ID(), 'wpep_enable_payment_summary_report', true );
			$wpep_square_summary_email_to_field        = get_post_meta( get_the_ID(), 'wpep_square_summary_email_to_field', true );
			$wpep_square_summary_email_from_field      = get_post_meta( get_the_ID(), 'wpep_square_summary_email_from_field', true );
			$wpep_square_summary_email_subject_field   = get_post_meta( get_the_ID(), 'wpep_square_summary_email_subject_field', true );
			$wpep_square_summary_email_frequency_field = get_post_meta( get_the_ID(), 'wpep_square_summary_email_frequency_field', true );
			$end_date                                  = gmdate( 'Y-m-d H:i:s' );
			if ( isset( $wpep_square_summary_email_frequency_field ) && 'weekly' === $wpep_square_summary_email_frequency_field ) {
				$start_date = get_previous_week_start_date( $end_date );
			} elseif ( isset( $wpep_square_summary_email_frequency_field ) && 'monthly' === $wpep_square_summary_email_frequency_field ) {
				$start_date = get_previous_month_start_date( $end_date );
			}
			if ( isset( $wpep_enable_payment_summary_report ) && 'on' === $wpep_enable_payment_summary_report ) {
				require_once WPEP_ROOT_PATH . 'modules/email_notifications/wpep-payment-summary-email.php';

				wpep_send_wpep_payment_summary_email( get_the_ID(), get_the_title(), $start_date, $end_date );
			}
		}
	}
	wp_reset_postdata();
}

/**
 * Adds the main 'WP EASY PAY' menu to the WordPress admin dashboard.
 *
 * This function creates a top-level menu called 'WP EASY PAY' in the admin area,
 * with a custom icon and a specified position. It also calls `wpep_add_submenu`
 * to add additional submenu items under this main menu.
 *
 * - Menu Title: WP EASY PAY
 * - Capability: manage_options
 * - Menu Slug: wpep-dashboard
 * - Icon: Custom icon loaded from plugin assets
 * - Position: 6
 *
 * The main page of this menu renders the dashboard using `wpep_render_dashboard_page`.
 */
function wpep_add_dashboard_menu() {
	
	wpep_add_submenu();
}

if ( ! function_exists( 'wpep_add_submenu' ) ) {

	/**
	 * Adds submenu pages under the main 'wpep-dashboard' menu in the admin area.
	 *
	 * This function creates several submenu items under the custom 'wpep-dashboard' menu,
	 * each linking to different sections, such as forms, subscriptions, coupons, reports,
	 * settings, and integrations (for premium users). The submenus include:
	 *
	 * - All Forms: Links to the list of all wp_easy_pay forms.
	 * - Create Payment Form: Links to the form creation page.
	 * - Subscriptions: Links to the subscriptions post type.
	 * - Coupons: Links to the coupons post type.
	 * - Reports: Links to the reports post type.
	 * - Square Connect: Links to the settings page for Square Connect.
	 * - Integrations (premium only): Links to the integrations page.
	 * - Submit Feature Idea (premium only): Links to the roadmap page for submitting ideas.
	 */
	function wpep_add_submenu() {
		add_menu_page(
			'WP EASY PAY',               // Page title
			'WP EASY PAY',               // Menu title
			'manage_options',            // Capability
			'edit.php?post_type=wp_easy_pay', // Slug points to All Forms
			'',                          // No callback needed
			WPEP_ROOT_URL . 'assets/backend/img/Vector.png',
			6
		);
		add_submenu_page(
			'edit.php?post_type=wp_easy_pay',           // Parent slug.
			'All Forms',                    // Page title.
			'All Forms',                    // Menu title.
			'manage_options',           // Capability.
			'edit.php?post_type=wp_easy_pay'   // Menu slug (link to custom post type).
		);
		// Add custom post type as a submenu.
		add_submenu_page(
			'edit.php?post_type=wp_easy_pay',           // Parent slug.
			'Create Payment Form',                    // Page title.
			'Create Payment Form',                    // Menu title.
			'manage_options',           // Capability.
			'post-new.php?post_type=wp_easy_pay'   // Menu slug (link to custom post type).
		);
		add_submenu_page(
			'edit.php?post_type=wp_easy_pay',           // Parent slug.
			'Subscriptions',                    // Page title.
			'Subscriptions',                    // Menu title.
			'manage_options',           // Capability.
			'wpep-subscription',
			'wpep_render_subscription_page'
		);
		add_submenu_page(
			'edit.php?post_type=wp_easy_pay',           // Parent slug.
			'Coupons',                    // Page title.
			'Coupons',                    // Menu title.
			'manage_options',           // Capability.
			'wpep-coupon',
			'wpep_render_coupon_page'
		);
		add_submenu_page(
			'edit.php?post_type=wp_easy_pay',           // Parent slug.
			'Reports',                    // Page title.
			'Reports',                    // Menu title.
			'manage_options',           // Capability.
			'edit.php?post_type=wpep_reports'   // Menu slug (link to custom post type).
		);
		add_submenu_page( 'edit.php?post_type=wp_easy_pay', 'Square Connect', 'Square Connect', 'manage_options', 'wpep-settings', 'wpep_render_global_settings_page' );
		add_submenu_page( 'edit.php?post_type=wp_easy_pay', 'Integrations', 'Integrations', 'manage_options', 'wpep-integrations', 'wpep_render_integration_page' );
		add_submenu_page(
			'edit.php?post_type=wp_easy_pay',
			'Get Pro',
			'⭐ Get Pro',
			'manage_options',
			'get_pro_menu',
			'__return_false',
			999
		);
	}

}

/**
 * Registers the 'WP EASY PAY' custom post type for payment forms in the WP Easy Pay plugin.
 *
 * This function defines and registers the 'WP EASY PAY' post type, which is used to manage
 * payment forms within the plugin. The post type is public, with UI visibility, and supports
 * thumbnails. It includes custom labels and arguments, and is set up to appear in the admin
 * bar but not in the main menu.
 *
 * @return void
 */
function wpep_create_payment_forms_post_type() {
	$labels = array(
		'name'                  => _x( 'WP EASY PAY', 'Post Type General Name', 'wp_easy_pay' ),
		'singular_name'         => _x( 'WP EASY PAY', 'Post Type Singular Name', 'wp_easy_pay' ),
		'menu_name'             => __( 'WP EASY PAY', 'wp_easy_pay' ),
		'name_admin_bar'        => __( 'Post Type', 'wp_easy_pay' ),
		'archives'              => __( 'Item Archives', 'wp_easy_pay' ),
		'attributes'            => __( 'Item Attributes', 'wp_easy_pay' ),
		'parent_item_colon'     => __( 'Parent Item:', 'wp_easy_pay' ),
		'all_items'             => __( 'All Forms', 'wp_easy_pay' ),
		'add_new_item'          => __( 'Create Payment Form', 'wp_easy_pay' ),
		'add_new'               => __( 'Create Payment Form', 'wp_easy_pay' ),
		'new_item'              => __( 'New Item', 'wp_easy_pay' ),
		'edit_item'             => __( 'Edit Item', 'wp_easy_pay' ),
		'update_item'           => __( 'Update Item', 'wp_easy_pay' ),
		'view_item'             => __( 'View Item', 'wp_easy_pay' ),
		'view_items'            => __( 'View Items', 'wp_easy_pay' ),
		'search_items'          => __( 'Search Item', 'wp_easy_pay' ),
		'not_found'             => __( 'Not found', 'wp_easy_pay' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'wp_easy_pay' ),
		'featured_image'        => __( 'Featured Image (show on popup only)', 'wp_easy_pay' ),
		'set_featured_image'    => __( 'Set featured image', 'wp_easy_pay' ),
		'remove_featured_image' => __( 'Remove featured image', 'wp_easy_pay' ),
		'use_featured_image'    => __( 'Use as featured image', 'wp_easy_pay' ),
		'insert_into_item'      => __( 'Insert into item', 'wp_easy_pay' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'wp_easy_pay' ),
		'items_list'            => __( 'Items list', 'wp_easy_pay' ),
		'items_list_navigation' => __( 'Items list navigation', 'wp_easy_pay' ),
		'filter_items_list'     => __( 'Filter items list', 'wp_easy_pay' ),
	);

	$args      = array(
		'labels'              => $labels,
		'hierarchical'        => false,
		'public'              => true,
		'supports'            => array( 'thumbnail' ),
		'show_ui'             => true,
		'show_in_menu'        => false,
		'menu_position'       => null,
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
	);
	$post_type = 'wp_easy_pay';
	register_post_type( $post_type, $args );
}

/**
 * Registers the 'Reports' custom post type for the WP Easy Pay plugin.
 *
 * This function defines and registers the 'Reports' post type, allowing WP Easy Pay
 * users to manage reports within the WordPress admin interface. It sets up custom
 * labels and arguments, making the post type public with custom UI visibility.
 * Standard WordPress post supports are disabled.
 *
 * @return void
 */
function wpep_create_reports_post_type() {
	$labels = array(
		'name'                  => _x( 'Reports', 'Post Type General Name', 'wp_easy_pay' ),
		'singular_name'         => _x( 'Reports', 'Post Type Singular Name', 'wp_easy_pay' ),
		'menu_name'             => __( 'Reports', 'wp_easy_pay' ),
		'name_admin_bar'        => __( 'Post Type', 'wp_easy_pay' ),
		'archives'              => __( 'Item Archives', 'wp_easy_pay' ),
		'attributes'            => __( 'Item Attributes', 'wp_easy_pay' ),
		'parent_item_colon'     => __( 'Parent Item:', 'wp_easy_pay' ),
		'all_items'             => __( 'Reports', 'wp_easy_pay' ),
		'add_new_item'          => __( 'Build Report', 'wp_easy_pay' ),
		'add_new'               => __( 'Build Report', 'wp_easy_pay' ),
		'new_item'              => __( 'New Item', 'wp_easy_pay' ),
		'edit_item'             => __( 'Edit Item', 'wp_easy_pay' ),
		'update_item'           => __( 'Update Item', 'wp_easy_pay' ),
		'view_item'             => __( 'View Item', 'wp_easy_pay' ),
		'view_items'            => __( 'View Items', 'wp_easy_pay' ),
		'search_items'          => __( 'Search Item', 'wp_easy_pay' ),
		'not_found'             => __( 'Not found', 'wp_easy_pay' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'wp_easy_pay' ),
		'featured_image'        => __( 'Featured Image', 'wp_easy_pay' ),
		'set_featured_image'    => __( 'Set featured image', 'wp_easy_pay' ),
		'remove_featured_image' => __( 'Remove featured image', 'wp_easy_pay' ),
		'use_featured_image'    => __( 'Use as featured image', 'wp_easy_pay' ),
		'insert_into_item'      => __( 'Insert into item', 'wp_easy_pay' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'wp_easy_pay' ),
		'items_list'            => __( 'Items list', 'wp_easy_pay' ),
		'items_list_navigation' => __( 'Items list navigation', 'wp_easy_pay' ),
		'filter_items_list'     => __( 'Filter items list', 'wp_easy_pay' ),
	);

	$args = array(

		'label'               => __( 'Reports', 'wp_easy_pay' ),
		'description'         => __( 'Post Type Description', 'wp_easy_pay' ),
		'labels'              => $labels,
		'hierarchical'        => false,
		'public'              => true,
		'supports'            => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',

	);

	register_post_type( 'wpep_reports', $args );
}

/**
 * Adds a meta box for building reports in the admin area.
 *
 * This function registers a meta box titled "Build Reports" on the
 * 'wpep_reports' post type edit screen. The meta box content is rendered by
 * the `wpep_render_reports_meta_html` function.
 *
 * @return void
 */
function wpep_add_reports_metabox() {
	add_meta_box(
		'wporg_box_id',
		'Build Reports',
		'wpep_render_reports_meta_html',
		'wpep_reports'
	);
}

add_action( 'admin_init', 'wpep_add_reports_metabox' );

/**
 * Renders the reports meta box content for a specific report post.
 *
 * This function includes the 'reports_view_page.php' file, which displays
 * the details of a specific report in the admin area.
 *
 * @param WP_Post $post The current post object for the report being viewed.
 * @return void
 */
function wpep_render_reports_meta_html( $post ) { // phpcs:ignore
	require_once WPEP_ROOT_PATH . 'views/backend/reports-view-page.php';
}

/**
 * Modifies the column names for the reports in the admin list view.
 *
 * This function customizes the columns displayed in the admin list table for the
 * 'wpep_reports' post type. It removes the default 'date' and 'title' columns,
 * then adds and reorders columns to display 'ID', 'Paid By', 'Type', 'Payment Method',
 * 'Date', and 'Actions'.
 *
 * @param array $columns The existing columns in the admin list view.
 * @return array Modified array of columns for the reports list view.
 */
function wpep_modify_column_names_reports( $columns ) {
	unset( $columns['date'] );
	unset( $columns['title'] );
	$columns['post_id'] = __( 'ID' );
	$columns['paid_by'] = __( 'Paid By' );
	$columns['type']    = __( 'Type' );
	$columns['method']  = __( 'Payment Method' );
	$columns['date']    = __( 'Date' );
	$columns['actions'] = __( 'Actions' );

	return $columns;
}

/**
 * Adds custom data to specific columns in the admin list view for the reports post type.
 *
 * This function outputs data for the custom columns in the reports section. It displays
 * details such as the post ID link, transaction type, payer's name, payment method, and
 * action links. Each output is escaped to ensure security.
 *
 * @param string $column  The name of the column being displayed.
 * @param int    $post_id The ID of the current post.
 */
function wpep_add_columns_data_reports( $column, $post_id ) {

	$first_name         = get_post_meta( $post_id, 'wpep_first_name', true );
	$last_name          = get_post_meta( $post_id, 'wpep_last_name', true );
	$email              = get_post_meta( $post_id, 'wpep_email', true );
	$charge_amount      = get_post_meta( $post_id, 'wpep_square_charge_amount', true );
	$refund_id          = get_post_meta( $post_id, 'wpep_square_refund_id', false );
	$transaction_type   = get_post_meta( $post_id, 'wpep_transaction_type', true );
	$transaction_source = get_post_meta( $post_id, 'wpep_transaction_source', true );
	$transaction_id     = get_the_title( $post_id );

	switch ( $column ) {

		case 'post_id':
			echo "<a href='" . esc_url( get_edit_post_link( $post_id ) ) . "' class='wpep-blue' title='Details'>#" . esc_html( $post_id ) . '</a>';
			break;
		case 'type':
			echo "<span class='" . esc_attr( $transaction_type ) . "'>" . esc_html( str_replace( '_', ' ', $transaction_type ) ) . '</span>';
			break;
		case 'paid_by':
			echo esc_html( $first_name . ' ' . $last_name );
			break;
		case 'method':
			echo esc_html( $transaction_source );
			break;
		case 'actions':
			echo '<a href="' . esc_url( get_delete_post_link( $post_id ) ) . '" class="deleteIcon" title="Delete report"> Delete </a>';
			break;
	}
}

/**
 * Modifies the column names for the payment forms in the admin list view.
 *
 * This function customizes the columns displayed in the admin list table for the
 * wp_easy_pay post type. It removes default 'title' and 'date' columns, then
 * reorders and renames columns to include 'Form Title', 'Shortcode', 'Type',
 * 'Date', and 'Actions'.
 *
 * @param array $columns The existing columns in the admin list view.
 * @return array Modified array of columns for the payment forms list view.
 */
function wpep_modify_column_names_payment_forms( $columns ) {
	unset( $columns['title'] );
	unset( $columns['date'] );
	$columns['title']     = __( 'Form Title' );
	$columns['shortcode'] = __( 'Shortcode' );
	$columns['type']      = __( 'Type' );
	$columns['date']      = __( 'Date' );
	$columns['actions']   = __( 'Actions' );

	return $columns;
}

/**
 * Adds custom data to specific columns in the admin list view for the wp_easy_pay post type.
 *
 * This function outputs data for the 'shortcode', 'type', and 'actions' columns.
 * It provides a shortcode, payment type label, and action links for editing or deleting
 * the form. Each output is escaped for security.
 *
 * @param string $column  The name of the column being displayed.
 * @param int    $post_id The ID of the current post.
 */
function wpep_add_columns_data_add_form( $column, $post_id ) {

	switch ( $column ) {

		case 'shortcode':
			echo '<span class="wpep_tags">[wpep-form id="' . esc_attr( $post_id ) . '"]</span>';
			break;
		case 'type':
			$form_type = get_post_meta( $post_id, 'wpep_square_payment_type', true );
			echo "<span class='" . esc_attr( $form_type ) . "'>" . esc_html( str_replace( '_', ' ', $form_type ) ) . '</span>';
			break;
		case 'actions':
			$post_status = get_post_status( $post_id );
			if ( 'trash' === $post_status ) {
				// If post is in trash, show only restore button
				$restore_link = wp_nonce_url( admin_url( sprintf( 'post.php?action=untrash&post=%d', $post_id ) ), "untrash-post_{$post_id}" );
				echo '<a href="' . esc_url( $restore_link ) . '" class="restoreIcon" title="Restore form"> Restore </a>';
			} else {
				// If post is not in trash, show edit and delete buttons
				echo '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '" class="editIcon" title="Edit form"> Edit </a> <a href="' . esc_url( get_delete_post_link( $post_id ) ) . '" class="deleteIcon" title="Delete form"> Delete </a>';
			}
			break;
	}
}

/**
 * Renders the global settings page in the admin area.
 *
 * This function includes the 'global_settings_page.php' file, which displays
 * the global settings content on a dedicated admin page under the WP EASY PAY menu.
 */
function wpep_render_global_settings_page() {
	require_once 'views/backend/global-settings-page.php';
}

/**
 * Render the integration page.
 *
 * Includes the backend integration page.
 *
 * @return void
 */
function wpep_render_integration_page() {
	require_once 'views/backend/integration-page.php';
}

/**
 * Render the coupon page.
 *
 * Includes the backend coupon page.
 *
 * @return void
 */
function wpep_render_coupon_page() {
	require_once 'views/backend/coupon-page.php';
}

/**
 * Render the subscription page.
 *
 * Includes the backend subscription page.
 *
 * @return void
 */
function wpep_render_subscription_page() {
	require_once 'views/backend/subscription-page.php';
}

/**
 * Renders the roadmap page in the admin area.
 *
 * This function includes the 'roadmap_page.php' file, which displays the roadmap
 * content on a dedicated admin page.
 */
function wpep_render_road_map_page() {
	require_once 'views/backend/roadmap-page.php';
}

/**
 * Saves custom form fields and settings for a specific post in the wp_easy_pay plugin.
 *
 * This function handles saving custom meta data and uploaded files associated with the form.
 * It verifies a nonce for security, processes form fields and file uploads, and updates multiple
 * meta fields for the post. This includes setting subscription details, payment options, email
 * configurations, and additional charges for the form.
 *
 * @param int     $post_ID The ID of the post being saved.
 * @param WP_Post $post The post object containing the post data.
 * @param bool    $update Whether this is an existing post being updated.
 */
function wpep_save_add_form_fields( $post_ID, $post, $update ) {  // phpcs:ignore

	if ( isset( $_POST['wp_global_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ), 'wp_global_nonce' ) ) {
		exit;
	}

	$post_custom  = $_POST;
	$files_custom = $_FILES;

	if ( isset( $post_custom['wpep_tabular_product_hidden_image'] ) ) {

		$wpep_tabular_product_hidden_image = $post_custom['wpep_tabular_product_hidden_image'];

		if ( isset( $files_custom['wpep_tabular_products_image'] ) ) {

			$upload_overrides = array( 'test_form' => false );
			$products_url     = array();
			foreach ( $files_custom['wpep_tabular_products_image']['tmp_name'] as $key => $tmp_name ) {

				if ( ! empty( $files_custom['wpep_tabular_products_image']['name'][ $key ] ) ) {

					$file = array(
						'name'     => $files_custom['wpep_tabular_products_image']['name'][ $key ],
						'type'     => $files_custom['wpep_tabular_products_image']['type'][ $key ],
						'tmp_name' => $files_custom['wpep_tabular_products_image']['tmp_name'][ $key ],
						'error'    => $files_custom['wpep_tabular_products_image']['error'][ $key ],
						'size'     => $files_custom['wpep_tabular_products_image']['size'][ $key ],
					);

					$movefile = wp_handle_upload( $file, $upload_overrides );

					if ( $movefile && ! isset( $movefile['error'] ) ) {
						array_push( $products_url, $movefile['url'] );
					} else {
						echo esc_html( $movefile['error'] );
					}
				} elseif ( isset( $wpep_tabular_product_hidden_image[ $key ] ) ) {
						array_push( $products_url, $wpep_tabular_product_hidden_image[ $key ] );
				}
			}
		}
	}

	if ( ! empty( $post_custom ) ) {

		if ( isset( $post_custom['wpep_radio_amounts'] ) ) {
			$radio_amounts = $post_custom['wpep_radio_amounts'];
		}

		if ( isset( $post_custom['wpep_radio_amount_labels'] ) ) {
			$radio_labels = $post_custom['wpep_radio_amount_labels'];
		}

		if ( isset( $post_custom['wpep_dropdown_amounts'] ) && ! empty( $post_custom['wpep_dropdown_amounts'] ) ) {
			$dropdown_amounts = $post_custom['wpep_dropdown_amounts'];
		}

		if ( isset( $post_custom['wpep_dropdown_amount_labels'] ) && ! empty( $post_custom['wpep_dropdown_amount_labels'] ) ) {
			$dropdown_labels = $post_custom['wpep_dropdown_amount_labels'];
		}

		$radio_amounts_with_labels    = array();
		$dropdown_amounts_with_labels = array();
		$tabular_products_with_labels = array();

		if ( isset( $radio_amounts ) && is_array( $radio_amounts ) ) {
			foreach ( $radio_amounts as $key => $amount_rd ) {
				$data['amount'] = $amount_rd;
				$data['label']  = $radio_labels[ $key ];

				array_push( $radio_amounts_with_labels, $data );
			}
		}

		if ( isset( $dropdown_amounts ) && is_array( $dropdown_amounts ) ) {
			foreach ( $dropdown_amounts as $key => $amount_dd ) {

				$data['amount'] = $amount_dd;
				$data['label']  = $dropdown_labels[ $key ];

				array_push( $dropdown_amounts_with_labels, $data );
			}
		}

		if ( $post_custom['wpep_tabular_products_price'] ) {
			$tabular_product_price = $post_custom['wpep_tabular_products_price'];
		}

		if ( $post_custom['wpep_tabular_products_label'] ) {
			$tabular_product_label = $post_custom['wpep_tabular_products_label'];
		}

		if ( $post_custom['wpep_tabular_products_qty'] ) {
			$tabular_product_qty = $post_custom['wpep_tabular_products_qty'];
		}

		if ( isset( $tabular_product_price ) ) {

			foreach ( $tabular_product_price as $key => $product_price ) {

				$data['amount']       = $product_price;
				$data['label']        = $tabular_product_label[ $key ];
				$data['quantity']     = $tabular_product_qty[ $key ];
				$data['products_url'] = isset( $products_url[ $key ] ) ? $products_url[ $key ] : '';

				array_push( $tabular_products_with_labels, $data );
			}
		}

		update_post_meta( $post_ID, 'wpep_square_test_location_id', ( isset( $post_custom['wpep_square_test_location_id'] ) ? sanitize_text_field( $post_custom['wpep_square_test_location_id'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_payment_purpose', ( isset( $post_custom['wpep_square_payment_purpose'] ) ? sanitize_text_field( $post_custom['wpep_square_payment_purpose'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_payment_success_url', ( isset( $post_custom['wpep_square_payment_success_url'] ) ? sanitize_text_field( $post_custom['wpep_square_payment_success_url'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_payment_success_msg', ( isset( $post_custom['wpep_payment_success_msg'] ) ? sanitize_text_field( $post_custom['wpep_payment_success_msg'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_payment_success_label', ( isset( $post_custom['wpep_square_payment_success_label'] ) ? sanitize_text_field( $post_custom['wpep_square_payment_success_label'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_payment_box_1', ( isset( $post_custom['wpep_square_payment_box_1'] ) ? sanitize_text_field( $post_custom['wpep_square_payment_box_1'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_payment_box_2', ( isset( $post_custom['wpep_square_payment_box_2'] ) ? sanitize_text_field( $post_custom['wpep_square_payment_box_2'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_payment_box_3', ( isset( $post_custom['wpep_square_payment_box_3'] ) ? sanitize_text_field( $post_custom['wpep_square_payment_box_3'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_payment_box_4', ( isset( $post_custom['wpep_square_payment_box_4'] ) ? sanitize_text_field( $post_custom['wpep_square_payment_box_4'] ) : '' ) );
		update_post_meta( $post_ID, 'defaultPriceSelected', ( isset( $post_custom['defaultPriceSelected'] ) ? sanitize_text_field( $post_custom['defaultPriceSelected'] ) : '' ) );
		update_post_meta( $post_ID, 'currencySymbolType', ( isset( $post_custom['currencySymbolType'] ) ? sanitize_text_field( $post_custom['currencySymbolType'] ) : 'code' ) );
		update_post_meta( $post_ID, 'PriceSelected', ( isset( $post_custom['PriceSelected'] ) ? sanitize_text_field( $post_custom['PriceSelected'] ) : '1' ) );
		
		// Set default form builder fields if empty
		$form_builder_fields = isset( $post_custom['wpep_square_form_builder_fields'] ) ? sanitize_text_field( $post_custom['wpep_square_form_builder_fields'] ) : '';
		
		// If form_builder_fields is empty, set default fields
		if ( empty( $form_builder_fields ) ) {
			$form_builder_fields = '[ { "type": "text", "required": true, "label": "First Name", "className": "form-control", "name": "wpep-first-name-field", "subtype": "text", "hideLabel": "yes" }, { "type": "text", "required": true, "label": "Last Name", "className": "form-control", "name": "wpep-last-name-field", "subtype": "text", "hideLabel": "yes" }, { "type": "text", "subtype": "email", "required": true, "label": "Email", "className": "form-control", "name": "wpep-email-field", "hideLabel": "yes" } ]';
		}
		
		update_post_meta( $post_ID, 'wpep_square_form_builder_fields', $form_builder_fields );

		update_post_meta( $post_ID, 'wpep_square_user_defined_amount', ( isset( $post_custom['wpep_square_user_defined_amount'] ) ? sanitize_text_field( $post_custom['wpep_square_user_defined_amount'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_transaction_notes_box', ( isset( $post_custom['wpep_transaction_notes_box'] ) ? sanitize_text_field( $post_custom['wpep_transaction_notes_box'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_admin_email_to_field', ( isset( $post_custom['wpep_square_admin_email_to_field'] ) ? sanitize_text_field( $post_custom['wpep_square_admin_email_to_field'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_admin_email_cc_field', ( isset( $post_custom['wpep_square_admin_email_cc_field'] ) ? sanitize_text_field( $post_custom['wpep_square_admin_email_cc_field'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_admin_email_bcc_field', ( isset( $post_custom['wpep_square_admin_email_bcc_field'] ) ? sanitize_text_field( $post_custom['wpep_square_admin_email_bcc_field'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_admin_email_from_field', ( isset( $post_custom['wpep_square_admin_email_from_field'] ) ? sanitize_text_field( $post_custom['wpep_square_admin_email_from_field'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_admin_email_subject_field', ( isset( $post_custom['wpep_square_admin_email_subject_field'] ) ? sanitize_text_field( $post_custom['wpep_square_admin_email_subject_field'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_admin_email_content_field', isset( $post_custom['wpep_square_admin_email_content_field'] ) ? $post_custom['wpep_square_admin_email_content_field'] : '' );
		update_post_meta( $post_ID, 'wpep_square_admin_email_exclude_blank_tags_lines', ( isset( $post_custom['wpep_square_admin_email_exclude_blank_tags_lines'] ) ? sanitize_text_field( $post_custom['wpep_square_admin_email_exclude_blank_tags_lines'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_admin_email_content_type_html', ( isset( $post_custom['wpep_square_admin_email_content_type_html'] ) ? sanitize_text_field( $post_custom['wpep_square_admin_email_content_type_html'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_save_card', ( isset( $post_custom['wpep_save_card'] ) ? sanitize_text_field( $post_custom['wpep_save_card'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_prods_without_images', ( isset( $post_custom['wpep_prods_without_images'] ) ? sanitize_text_field( $post_custom['wpep_prods_without_images'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_user_email_to_field', ( isset( $post_custom['wpep_square_user_email_to_field'] ) ? sanitize_text_field( $post_custom['wpep_square_user_email_to_field'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_square_user_email_cc_field', ( isset( $post_custom['wpep_square_user_email_cc_field'] ) ? sanitize_text_field( $post_custom['wpep_square_user_email_cc_field'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_square_user_email_bcc_field', ( isset( $post_custom['wpep_square_user_email_bcc_field'] ) ? sanitize_text_field( $post_custom['wpep_square_user_email_bcc_field'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_square_user_email_from_field', ( isset( $post_custom['wpep_square_user_email_from_field'] ) ? sanitize_text_field( $post_custom['wpep_square_user_email_from_field'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_square_user_email_subject_field', ( isset( $post_custom['wpep_square_user_email_subject_field'] ) ? sanitize_text_field( $post_custom['wpep_square_user_email_subject_field'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_square_user_email_content_field', isset( $post_custom['wpep_square_user_email_content_field'] ) ? $post_custom['wpep_square_user_email_content_field'] : '' );
		update_post_meta( $post_ID, 'wpep_square_user_email_exclude_blank_tags_lines', ( isset( $post_custom['wpep_square_user_email_exclude_blank_tags_lines'] ) ? sanitize_text_field( $post_custom['wpep_square_user_email_exclude_blank_tags_lines'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_square_user_email_content_type_html', ( isset( $post_custom['wpep_square_user_email_content_type_html'] ) ? sanitize_text_field( $post_custom['wpep_square_user_email_content_type_html'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_enable_payment_summary_report', isset( $post_custom['wpep_enable_payment_summary_report'] ) ? sanitize_text_field( wp_unslash( $post_custom['wpep_enable_payment_summary_report'] ) ) : '' );
		update_post_meta( $post_ID, 'wpep_square_summary_email_to_field', isset( $post_custom['wpep_square_summary_email_to_field'] ) ? sanitize_text_field( wp_unslash( $post_custom['wpep_square_summary_email_to_field'] ) ) : '' );
		update_post_meta( $post_ID, 'wpep_square_summary_email_from_field', isset( $post_custom['wpep_square_summary_email_from_field'] ) ? sanitize_text_field( wp_unslash( $post_custom['wpep_square_summary_email_from_field'] ) ) : '' );
		update_post_meta( $post_ID, 'wpep_square_summary_email_subject_field', isset( $post_custom['wpep_square_summary_email_subject_field'] ) ? sanitize_text_field( wp_unslash( $post_custom['wpep_square_summary_email_subject_field'] ) ) : '' );
		update_post_meta( $post_ID, 'wpep_square_summary_email_frequency_field', isset( $post_custom['wpep_square_summary_email_frequency_field'] ) ? sanitize_text_field( wp_unslash( $post_custom['wpep_square_summary_email_frequency_field'] ) ) : '' );

		update_post_meta( $post_ID, 'wpep_button_title', ( isset( $post_custom['wpep_button_title'] ) ? sanitize_text_field( $post_custom['wpep_button_title'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_square_location_id', ( isset( $post_custom['wpep_square_location_id'] ) ? sanitize_text_field( $post_custom['wpep_square_location_id'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_amount_type', ( isset( $post_custom['wpep_square_amount_type'] ) ? sanitize_text_field( $post_custom['wpep_square_amount_type'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_open_in_popup', ( isset( $post_custom['wpep_open_in_popup'] ) ? sanitize_text_field( $post_custom['wpep_open_in_popup'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_payment_mode', ( isset( $post_custom['wpep_payment_mode'] ) ? sanitize_text_field( $post_custom['wpep_payment_mode'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_square_google_pay', ( isset( $post_custom['wpep_square_google_pay'] ) ? sanitize_text_field( $post_custom['wpep_square_google_pay'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_after_pay', ( isset( $post_custom['wpep_square_after_pay'] ) ? sanitize_text_field( $post_custom['wpep_square_after_pay'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_apple_pay', ( isset( $post_custom['wpep_square_apple_pay'] ) ? sanitize_text_field( $post_custom['wpep_square_apple_pay'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_cash_app', ( isset( $post_custom['wpep_square_cash_app'] ) ? sanitize_text_field( $post_custom['wpep_square_cash_app'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_giftcard', ( isset( $post_custom['wpep_square_giftcard'] ) ? sanitize_text_field( $post_custom['wpep_square_giftcard'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_ach_debit', ( isset( $post_custom['wpep_square_ach_debit'] ) ? sanitize_text_field( $post_custom['wpep_square_ach_debit'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_square_google_pay_live', ( isset( $post_custom['wpep_square_google_pay_live'] ) ? sanitize_text_field( $post_custom['wpep_square_google_pay_live'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_after_pay_live', ( isset( $post_custom['wpep_square_after_pay_live'] ) ? sanitize_text_field( $post_custom['wpep_square_after_pay_live'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_apple_pay_live', ( isset( $post_custom['wpep_square_apple_pay_live'] ) ? sanitize_text_field( $post_custom['wpep_square_apple_pay_live'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_cash_app_live', ( isset( $post_custom['wpep_square_cash_app_live'] ) ? sanitize_text_field( $post_custom['wpep_square_cash_app_live'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_giftcard_live', ( isset( $post_custom['wpep_square_giftcard_live'] ) ? sanitize_text_field( $post_custom['wpep_square_giftcard_live'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_ach_debit_live', ( isset( $post_custom['wpep_square_ach_debit_live'] ) ? sanitize_text_field( $post_custom['wpep_square_ach_debit_live'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_terminal', ( isset( $post_custom['wpep_square_terminal'] ) ? sanitize_text_field( $post_custom['wpep_square_terminal'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_device_code', ( isset( $post_custom['wpep_device_code'] ) ? sanitize_text_field( $post_custom['wpep_device_code'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_device_id', ( isset( $post_custom['wpep_device_id'] ) ? sanitize_text_field( $post_custom['wpep_device_id'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_individual_form_global', 'on' );

		update_post_meta( $post_ID, 'wpep_square_payment_type', ( isset( $post_custom['wpep_square_payment_type'] ) ? sanitize_text_field( $post_custom['wpep_square_payment_type'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_subscription_cycle_interval', ( isset( $post_custom['wpep_subscription_cycle_interval'] ) ? sanitize_text_field( $post_custom['wpep_subscription_cycle_interval'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_subscription_cycle', ( isset( $post_custom['wpep_subscription_cycle'] ) ? sanitize_text_field( $post_custom['wpep_subscription_cycle'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_subscription_length', ( isset( $post_custom['wpep_subscription_length'] ) ? sanitize_text_field( $post_custom['wpep_subscription_length'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_organization_name', ( isset( $post_custom['wpep_organization_name'] ) ? sanitize_text_field( $post_custom['wpep_organization_name'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_donation_goal_switch', ( isset( $post_custom['wpep_donation_goal_switch'] ) ? sanitize_text_field( $post_custom['wpep_donation_goal_switch'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_donation_goal_amount', ( isset( $post_custom['wpep_donation_goal_amount'] ) ? sanitize_text_field( $post_custom['wpep_donation_goal_amount'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_donation_goal_message_switch', ( isset( $post_custom['wpep_donation_goal_message_switch'] ) ? sanitize_text_field( $post_custom['wpep_donation_goal_message_switch'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_donation_goal_message', ( isset( $post_custom['wpep_donation_goal_message'] ) ? sanitize_text_field( $post_custom['wpep_donation_goal_message'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_donation_goal_form_close', ( isset( $post_custom['wpep_donation_goal_form_close'] ) ? sanitize_text_field( $post_custom['wpep_donation_goal_form_close'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_mailchimp_audience', ( isset( $post_custom['wpep_mailchimp_audience'] ) ? sanitize_text_field( $post_custom['wpep_mailchimp_audience'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_dropdown_amounts', isset( $dropdown_amounts_with_labels ) ? $dropdown_amounts_with_labels : '' );
		if ( isset( $radio_amounts ) && is_array( $radio_amounts ) ) {
			update_post_meta( $post_ID, 'wpep_radio_amounts', isset( $radio_amounts_with_labels ) ? $radio_amounts_with_labels : '' );
		}
		update_post_meta( $post_ID, 'wpep_products_with_labels', isset( $tabular_products_with_labels ) ? $tabular_products_with_labels : '' );

		update_post_meta( $post_ID, 'wpep_square_payment_min', ( isset( $post_custom['wpep_square_payment_min'] ) ? sanitize_text_field( $post_custom['wpep_square_payment_min'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_square_payment_max', ( isset( $post_custom['wpep_square_payment_max'] ) ? sanitize_text_field( $post_custom['wpep_square_payment_max'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_show_wizard', ( isset( $post_custom['wpep_show_wizard'] ) ? sanitize_text_field( $post_custom['wpep_show_wizard'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_show_shadow', ( isset( $post_custom['wpep_show_shadow'] ) ? sanitize_text_field( $post_custom['wpep_show_shadow'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_btn_theme', ( isset( $post_custom['wpep_btn_theme'] ) ? sanitize_text_field( $post_custom['wpep_btn_theme'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_form_theme_color', ( isset( $post_custom['wpep_form_theme_color'] ) ? sanitize_text_field( $post_custom['wpep_form_theme_color'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_payment_btn_label', ( isset( $post_custom['wpep_payment_btn_label'] ) ? sanitize_text_field( $post_custom['wpep_payment_btn_label'] ) : '' ) );

		/* adding redirection values */
		update_post_meta( $post_ID, 'wantRedirection', ( isset( $post_custom['wantRedirection'] ) ? sanitize_text_field( $post_custom['wantRedirection'] ) : 'No' ) );
		update_post_meta( $post_ID, 'redirectionDelay', ( isset( $post_custom['redirectionDelay'] ) ? sanitize_text_field( $post_custom['redirectionDelay'] ) : 5 ) );

		/*term & condition Check */
		update_post_meta( $post_ID, 'enableTermsCondition', ( isset( $post_custom['enableTermsCondition'] ) ? sanitize_text_field( $post_custom['enableTermsCondition'] ) : '' ) );
		update_post_meta( $post_ID, 'termsLabel', ( isset( $post_custom['termsLabel'] ) ? sanitize_text_field( $post_custom['termsLabel'] ) : '' ) );
		update_post_meta( $post_ID, 'termsLink', ( isset( $post_custom['termsLink'] ) ? sanitize_text_field( $post_custom['termsLink'] ) : '' ) );

		update_post_meta( $post_ID, 'enableQuantity', ( isset( $post_custom['enableQuantity'] ) ? sanitize_text_field( $post_custom['enableQuantity'] ) : '' ) );
		update_post_meta( $post_ID, 'enableCoupon', ( isset( $post_custom['enableCoupon'] ) ? sanitize_text_field( $post_custom['enableCoupon'] ) : '' ) );
		update_post_meta( $post_ID, 'enableSquareProductSync', ( isset( $post_custom['enableSquareProductSync'] ) ? sanitize_text_field( $post_custom['enableSquareProductSync'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_subscription_trial_days', ( isset( $post_custom['wpep_subscription_trial_days'] ) ? sanitize_text_field( $post_custom['wpep_subscription_trial_days'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_subscription_trial', ( isset( $post_custom['wpep_subscription_trial'] ) ? sanitize_text_field( $post_custom['wpep_subscription_trial'] ) : '' ) );

		update_post_meta( $post_ID, 'wpep_signup_fees_label', ( isset( $post_custom['wpep_signup_fees_label'] ) ? sanitize_text_field( $post_custom['wpep_signup_fees_label'] ) : '' ) );
		update_post_meta( $post_ID, 'wpep_signup_fees_amount', ( isset( $post_custom['wpep_signup_fees_amount'] ) ? sanitize_text_field( $post_custom['wpep_signup_fees_amount'] ) : '' ) );
		if ( 'simple' === $post_custom['wpep_square_payment_type'] || 'donation' === $post_custom['wpep_square_payment_type'] ) {
			update_post_meta( $post_ID, 'wpep_enable_signup_fees', '' );
		} else {
			update_post_meta( $post_ID, 'wpep_enable_signup_fees', ( isset( $post_custom['wpep_enable_signup_fees'] ) ? sanitize_text_field( $post_custom['wpep_enable_signup_fees'] ) : '' ) );
		}

		// saving addtional charges.
		if ( isset( $post_custom['wpep_service_fees_name'] ) && ! empty( $post_custom['wpep_service_fees_name'] ) ) {

			$fees_data = array();

			foreach ( $post_custom['wpep_service_fees_name'] as $key => $name ) {

				$fees_data['check'][ $key ] = isset( $post_custom['wpep_service_fees_check'][ $key ] ) ? $post_custom['wpep_service_fees_check'][ $key ] : 'no';
				$fees_data['name'][ $key ]  = isset( $post_custom['wpep_service_fees_name'][ $key ] ) ? $post_custom['wpep_service_fees_name'][ $key ] : '';
				$fees_data['type'][ $key ]  = isset( $post_custom['wpep_service_charge_type'][ $key ] ) ? $post_custom['wpep_service_charge_type'][ $key ] : '';
				$fees_data['value'][ $key ] = isset( $post_custom['wpep_fees_value'][ $key ] ) ? $post_custom['wpep_fees_value'][ $key ] : '';
			}

			update_post_meta( $post_ID, 'fees_data', $fees_data );

		}

		global $wpdb;
		if ( 'wp_easy_pay' === get_post_type( $post_ID ) ) {

			$title = sanitize_text_field( $post_custom['post_title'] );

			$post_name = rawurlencode( $post_custom['post_title'] );

			if ( isset( $post_custom['post_content'] ) ) {
				$post_content = sanitize_text_field( $post_custom['post_content'] );
			}
			$update = 'update';
			$where  = array( 'ID' => $post_ID );
			$wpdb->$update( $wpdb->posts, array( 'post_title' => $title ), $where );
			$wpdb->$update( $wpdb->posts, array( 'post_content' => $post_content ), $where );

		}
	}
}

add_action( 'save_post_wp_easy_pay', 'wpep_save_add_form_fields', 10, 3 );

/**
 * Creates a connection URL for either an individual form or a global setting in the admin area.
 *
 * This function generates a URL to connect to a specific form or global configuration
 * within the admin panel, based on the origin parameter. It adds relevant query parameters
 * to the URL, such as the page post ID and a flag for connection preparation.
 *
 * @param string $origin Specifies the origin of the request, either 'individual_form' or 'global'.
 * @return string $connection_url The generated connection URL for either an individual form or global settings.
 */
function wpep_create_connect_url( $origin ) {

	$get = $_GET; // phpcs:ignore

	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$uri_requested = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	}

	/* Fetch GET parameters from URI */
	$parts = wp_parse_url( $uri_requested );
	parse_str( $parts['query'], $url_identifiers );

	/* Fetch Admin URL */
	$slash_exploded = explode( '/', $uri_requested );

	$question_mark_exploded                          = explode( '?', $slash_exploded[2] );
	$url_identifiers['wpep_admin_url']               = $question_mark_exploded[0];
	$url_identifiers['wpep_prepare_connection_call'] = true;

	if ( 'individual_form' === $origin ) {

		if ( isset( $get['post'] ) && ! empty( $get['post'] ) ) {

			$url_identifiers['wpep_page_post'] = $get['post'];

		}
	}

	if ( 'global' === $origin ) {

		$url_identifiers['wpep_page_post'] = 'global';
		$connection_url                    = add_query_arg( $url_identifiers, admin_url( 'admin.php' ) );

	} else {

		$connection_url = add_query_arg( $url_identifiers, admin_url( 'post.php' ) );
	}

	return $connection_url;
}

/**
 * Creates a connection URL for the sandbox environment in the admin area.
 *
 * This function builds a URL to connect to a sandbox environment, either for an individual form or globally.
 * It adds specific URL parameters to the admin URL, allowing different settings based on the origin type.
 *
 * @param string $origin Specifies the origin of the request. Accepts 'individual_form' or 'global'.
 * @return string $connection_url The generated sandbox connection URL.
 */
function wpep_create_connect_sandbox_url( $origin ) {

	$get = $_GET;  // phpcs:ignore

	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$uri_requested = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	}

	/* Fetch GET parameters from URI */
	$parts = wp_parse_url( $uri_requested );
	parse_str( $parts['query'], $url_identifiers );

	/* Fetch Admin URL */
	$slash_exploded = explode( '/', $uri_requested );

	$question_mark_exploded                          = explode( '?', $slash_exploded[2] );
	$url_identifiers['wpep_admin_url']               = $question_mark_exploded[0];
	$url_identifiers['wpep_prepare_connection_call'] = true;

	if ( 'individual_form' === $origin ) {

		if ( isset( $get['post'] ) && ! empty( $get['post'] ) ) {

			$url_identifiers['wpep_page_post'] = $get['post'];
			$url_identifiers['wpep_sandbox']   = 'yes';

		}

		$connection_url = add_query_arg( $url_identifiers, admin_url( 'post.php' ) );
	}

	if ( 'global' === $origin ) {

		$url_identifiers['wpep_page_post'] = 'global';
		$url_identifiers['wpep_sandbox']   = 'yes';

		$connection_url = add_query_arg( $url_identifiers, admin_url( 'admin.php' ) );
	}

	return $connection_url;
}

/**
 * Adds meta boxes for shortcode and form style settings in the form builder.
 *
 * This function registers two meta boxes on the 'wp_easy_pay' post type:
 * - "Shortcode": Displays shortcode options, rendered by `wpep_render_form_shortcode_meta_html`.
 * - "Form Style": Displays form style settings, rendered by `wpep_render_form_style_meta_html`.
 *
 * Both meta boxes are added to the 'side' section, with the "Shortcode" box given high priority.
 */
function wpep_add_form_shortcode_metabox() {

	add_meta_box(
		'wpep_form_shortcode_metabox',
		'Shortcode',
		'wpep_render_form_shortcode_meta_html',
		'wp_easy_pay',
		'side',
		'high'
	);

	add_meta_box(
		'wpep_form_style_box',
		'Form Style',
		'wpep_render_form_style_meta_html',
		'wp_easy_pay',
		'side'
	);
}

add_action( 'admin_init', 'wpep_add_form_shortcode_metabox' );

/**
 * Renders the HTML for the shortcode meta box in the form builder.
 *
 * This function includes the template file that displays the shortcode options
 * in the backend form builder settings page.
 *
 * @param WP_Post $post The current post object for the form being edited.
 */
function wpep_render_form_shortcode_meta_html( $post ) { // phpcs:ignore
	require_once WPEP_ROOT_PATH . 'views/backend/form_builder_settings/form-shortocde-metabox.php';
}

/**
 * Adds custom options to the publish meta box for a specific post type.
 *
 * This function checks if the current post is of the specified post type ('wp_easy_pay').
 * If so, it retrieves a meta value ('check_meta') associated with the post and performs
 * an action (in this case, outputs `1`). This can be modified to display or manipulate
 * other data as needed.
 *
 * @param WP_Post $post_obj The post object containing the details of the current post.
 */
function add_publish_meta_options( $post_obj ) {

	global $post;
	$post_type = 'wp_easy_pay'; // If you want a specific post type.
	$value     = get_post_meta( $post_obj->ID, 'check_meta', true ); // If saving value to post_meta.

	if ( $post_type === $post->post_type ) {
		echo 1;
	}
}

add_action( 'post_submitbox_misc_actions', 'add_publish_meta_options' );

/**
 * Renders the HTML for form style settings in the form builder.
 *
 * This function includes the template file responsible for displaying
 * form style options in the backend form builder settings page.
 *
 * @param WP_Post $post The current post object for the form being edited.
 */
function wpep_render_form_style_meta_html( $post ) { // phpcs:ignore
	require_once WPEP_ROOT_PATH . 'views/backend/form_builder_settings/wpep-render-form-style-meta-html.php';
}

/**
 * Adds a meta box for changing the currency symbol in the form settings.
 *
 * This function registers a meta box titled "Change Currency Symbol"
 * in the 'side' section with high priority on the 'wp_easy_pay' post type.
 * The meta box content is rendered by the `wpep_render_form_change_currency_show_type_html` function.
 */
function wpep_add_form_currency_show_type_metabox() {
	add_meta_box(
		'wpep_form_currency_show_type_metabox',
		'Change Currency Symbol',
		'wpep_render_form_change_currency_show_type_html',
		'wp_easy_pay',
		'side',
		'high'
	);
}

add_action( 'admin_init', 'wpep_add_form_currency_show_type_metabox' );

/**
 * Renders the HTML for the currency show type settings in the form builder.
 *
 * This function loads and displays the currency show type options in the form builder settings page.
 *
 * @param WP_Post $post The current post object for the form being edited.
 */
function wpep_render_form_change_currency_show_type_html( $post ) { // phpcs:ignore
	require_once WPEP_ROOT_PATH . 'views/backend/form_builder_settings/form-currency-show-type-metabox.php';
}

/**
 * Retrieve the currency for a given form.
 *
 * This function determines the currency to be used based on the global or individual payment mode
 * settings of the form. If the currency symbol type is set to 'symbol', it converts the currency
 * code to its corresponding symbol.
 *
 * @param int $wpep_current_form_id The ID of the current form.
 *
 * @return string The determined currency or currency symbol.
 */
function wpep_get_form_currency( $wpep_current_form_id ) {
	$form_payment_global = get_post_meta( $wpep_current_form_id, 'wpep_individual_form_global', true );

	if ( 'on' === $form_payment_global ) {
		$global_payment_mode = get_option( 'wpep_square_payment_mode_global', true );

		$square_currency = ( 'on' === $global_payment_mode )
			? get_option( 'wpep_square_currency_new' )
			: get_option( 'wpep_square_currency_test' );
	} else {
		$individual_payment_mode = get_post_meta( $wpep_current_form_id, 'wpep_payment_mode', true );

		$square_currency = ( 'on' === $individual_payment_mode )
			? get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_new', true )
			: get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_test', true );
	}
	$currency_symbol_type = ! empty( get_post_meta( $wpep_current_form_id, 'currencySymbolType', true ) ) ? get_post_meta( $wpep_current_form_id, 'currencySymbolType', true ) : 'code';
	if ( 'symbol' === $currency_symbol_type ) {
		if ( 'USD' === $square_currency ) {
			$square_currency = '$';
		} elseif ( 'CAD' === $square_currency ) {
			$square_currency = 'C$';
		} elseif ( 'AUD' === $square_currency ) {
			$square_currency = 'A$';
		} elseif ( 'JPY' === $square_currency ) {
			$square_currency = '¥';
		} elseif ( 'GBP' === $square_currency ) {
			$square_currency = '£';
		} elseif ( 'EUR' === $square_currency ) {
			$square_currency = '€';
		}
	}
	return $square_currency;
}

/* Plugin Activation Processing */
