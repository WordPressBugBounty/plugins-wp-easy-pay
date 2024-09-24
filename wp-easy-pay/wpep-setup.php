<?php
/**
 * WP EASY PAY
 *
 * PHP version 7
 *
 * Wordpress_Plugin
 *
 * @package  WP_Easy_Pay
 * Author:  Author <contact@apiexperts.io>
 * license:
 * cle  https://opensource.org/licenses/MIT MIT License
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
add_action( 'admin_init', 'wpep_add_reports_metabox' );
add_action( 'save_post_wp_easy_pay', 'wpep_save_add_form_fields', 10, 3 );
add_action( 'admin_init', 'wpep_add_form_currency_show_type_metabox' );
add_action( 'post_submitbox_misc_actions', 'add_publish_meta_options' );
add_action( 'admin_init', 'wpep_add_form_shortcode_metabox' );

/**
 * Modifies the form tag for post editing.
 */
function wpep_post_edit_form_tag() {
	echo ' enctype="multipart/form-data"';
}



/**
 * Creates the payment forms custom post type.
 */
function wpep_create_payment_forms_post_type() {
	$labels    = array(
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
		'label'               => __( 'WP EASY PAY', 'wp_easy_pay' ),
		'description'         => __( 'Post Type Description', 'wp_easy_pay' ),
		'labels'              => $labels,
		'hierarchical'        => false,
		'public'              => true,
		'supports'            => array( 'thumbnail' ),
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'menu_icon'           => WPEP_ROOT_URL . 'assets/backend/img/square-logo.png',
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	$post_type = 'wp_easy_pay';
	register_post_type( $post_type, $args );
}

/**
 * Creates the reports custom post type.
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
	$args   = array(
		'label'               => __( 'Reports', 'wp_easy_pay' ),
		'description'         => __( 'Post Type Description', 'wp_easy_pay' ),
		'labels'              => $labels,
		'hierarchical'        => false,
		'public'              => true,
		'supports'            => false,
		'show_ui'             => true,
		'show_in_menu'        => 'edit.php?post_type=wp_easy_pay',
		'menu_position'       => 5,
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
 * Adds the reports metabox.
 */
function wpep_add_reports_metabox() {
	add_meta_box(
		'wporg_box_id',
		'Build Reports',
		'wpep_render_reports_meta_html',
		'wpep_reports'
	);
}

/**
 * Renders the HTML for the reports meta box.
 */
function wpep_render_reports_meta_html() {
	require_once WPEP_ROOT_PATH . 'views/backend/reports-view-page.php';
}

/**
 * Modifies the column names for the reports.
 *
 * @param array $columns The existing column names.
 * @return array The modified column names.
 */
function wpep_modify_column_names_reports( $columns ) {
	unset( $columns['date'] );
	unset( $columns['title'] );
	$columns['post_id'] = __( 'ID' );
	$columns['paid_by'] = __( 'Paid By' );
	$columns['type']    = __( 'Type' );
	$columns['date']    = __( 'Date' );
	$columns['actions'] = __( 'Actions' );
	return $columns;
}

/**
 * Adds custom columns data for the reports.
 *
 * @param string $column  The column name.
 * @param int    $post_id  The post ID.
 */
function wpep_add_columns_data_reports( $column, $post_id ) {
	$first_name       = get_post_meta( $post_id, 'wpep_first_name', true );
	$last_name        = get_post_meta( $post_id, 'wpep_last_name', true );
	$email            = get_post_meta( $post_id, 'wpep_email', true );
	$charge_amount    = get_post_meta( $post_id, 'wpep_square_charge_amount', true );
	$refund_id        = get_post_meta( $post_id, 'wpep_square_refund_id', false );
	$transaction_type = get_post_meta( $post_id, 'wpep_transaction_type', true );
	$transaction_id   = get_the_title( $post_id );
	switch ( $column ) {
		case 'post_id':
			echo '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '" class="wpep-blue" title="Details">#' . esc_html( $post_id ) . '</a>';
			break;
		case 'type':
			echo "<span class='" . esc_attr( $transaction_type ) . "'>" . esc_html( str_replace( '_', ' ', $transaction_type ) ) . '</span>';
			break;
		case 'paid_by':
			echo esc_html( $first_name ) . ' ' . esc_html( $last_name );
			break;
		case 'actions':
			echo '<a href="' . esc_url( get_delete_post_link( $post_id ) ) . '" class="deleteIcon" title="Delete report">' . esc_html( 'Delete' ) . '</a>';

	}
}

/**
 * Modifies the column names for the payment forms.
 *
 * @param array $columns The existing column names.
 * @return array The modified column names.
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
 * Adds custom columns data for the payment forms.
 *
 * @param string $column  The column name.
 * @param int    $post_id  The post ID.
 */
function wpep_add_columns_data_add_form( $column, $post_id ) {
	switch ( $column ) {
		case 'shortcode':
			echo '<span class="wpep_tags">[wpep-form id="' . esc_html( $post_id ) . '"]</span>';
			break;
		case 'type':
			$form_type = get_post_meta( $post_id, 'wpep_square_payment_type', true );
			echo "<span class='" . esc_attr( $form_type ) . "'>" . esc_html( str_replace( '_', ' ', $form_type ) ) . '</span>';
			break;
		case 'actions':
			echo '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '" class="editIcon" title="Edit form">' . esc_html( 'Edit' ) . '</a> <a href="' . esc_url( get_delete_post_link( $post_id ) ) . '" class="deleteIcon" title="Delete form">' . esc_html( 'Delete' ) . '</a>';
			break;
	}
}
// Continue after vacation.

/**
 * Renders the global settings page.
 */
function wpep_render_global_settings_page() {
	require_once 'views/backend/global-settings-page.php';
}


if ( ! function_exists( 'wpep_add_submenu' ) ) {
	/**
	 * Adds a submenu page to the WordPress admin menu.
	 */
	function wpep_add_submenu() {
		add_submenu_page(
			'edit.php?post_type=wp_easy_pay',
			'Square Connect',
			'Square Connect',
			'manage_options',
			'wpep-settings',
			'wpep_render_global_settings_page'
		);
		add_submenu_page(
            'edit.php?post_type=wp_easy_pay',
            'Get Pro',
            'Get Pro',
            'manage_options',
            'get_pro_menu',
            '__return_false',
			999
        );
	}
}

/**
 * Renders the road map page.
 */
function wpep_render_road_map_page() {
	require_once 'views/backend/roadmap-page.php';
}

/**
 * Saves the additional fields for adding a form.
 *
 * @param int     $post_ID The post ID.
 * @param WP_Post $post    The post object.
 * @param bool    $update  Whether this is an update or new post.
 */
function wpep_save_add_form_fields( $post_ID, $post, $update ) {
	$nonce = isset( $_POST['wpep_tabular_product_hidden_image_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wpep_tabular_product_hidden_image_nonce'] ) ) : '';
		
	if ( isset( $_POST['wpep_tabular_product_hidden_image_nonce'] ) && wp_verify_nonce( $nonce, 'wpep_tabular_product_hidden_image' ) ) {
		$wpep_tabular_product_hidden_image = isset( $_POST['wpep_tabular_product_hidden_image'] ) ? sanitize_text_field( wp_unslash( $_POST['wpep_tabular_product_hidden_image'] ) ) : '';

		if ( isset( $_FILES['wpep_tabular_products_image'] ) && isset( $_FILES['wpep_tabular_products_image']['tmp_name'] ) ) {
				$upload_overrides = array(
					'test_form' => false,
				);
				$products_url     = array();
				$tmp_name         = isset( $_FILES['wpep_tabular_products_image']['tmp_name'][ $key ] ) ? sanitize_file_name( wp_unslash( $_FILES['wpep_tabular_products_image']['tmp_name'][ $key ] ) ) : '';
				foreach ( $tmp_name as $key => $tmp_name ) {
					if ( ! empty( $_FILES['wpep_tabular_products_image']['name'][ $key ] ) && ! empty( $_FILES['wpep_tabular_products_image']['type'][ $key ] ) && ! empty( $_FILES['wpep_tabular_products_image']['tmp_name'][ $key ] ) && ! empty( $_FILES['wpep_tabular_products_image']['error'][ $key ] ) && ! empty( $_FILES['wpep_tabular_products_image']['size'][ $key ] ) ) {
						$file     = array(
							'name'     => sanitize_file_name( wp_unslash( $_FILES['wpep_tabular_products_image']['name'][ $key ] ) ),
							'type'     => sanitize_file_name( wp_unslash( $_FILES['wpep_tabular_products_image']['type'][ $key ] ) ),
							'tmp_name' => sanitize_file_name( wp_unslash( $_FILES['wpep_tabular_products_image']['tmp_name'][ $key ] ) ),
							'error'    => sanitize_file_name( wp_unslash( $_FILES['wpep_tabular_products_image']['error'][ $key ] ) ),
							'size'     => sanitize_file_name( wp_unslash( $_FILES['wpep_tabular_products_image']['size'][ $key ] ) ),
						);
						$movefile = wp_handle_upload( $file, $upload_overrides );

						if ( $movefile && ! isset( $movefile['error'] ) ) {
							array_push( $products_url, $movefile['url'] );
						} else {
							echo esc_html( $movefile['error'] );
						}
					} else {
						array_push( $products_url, $wpep_tabular_product_hidden_image[ $key ] );
					}
				}
		}
	}

	if ( ! empty( $_POST ) ) {
		if ( isset( $_POST['wpep_radio_amounts'] ) ) {
			$radio_amounts = sanitize_text_field( wp_unslash( $_POST['wpep_radio_amounts'] ) );
		}

		if ( isset( $_POST['wpep_radio_amount_labels'] ) ) {
			$radio_labels = sanitize_text_field( wp_unslash( $_POST['wpep_radio_amount_labels'] ) );
		}

		if ( isset( $_POST['wpep_dropdown_amounts'] ) && ! empty( $_POST['wpep_dropdown_amounts'] ) ) {
			$dropdown_amounts = sanitize_text_field( wp_unslash( $_POST['wpep_dropdown_amounts'] ) );
		}

		if ( isset( $_POST['wpep_dropdown_amount_labels'] ) && ! empty( $_POST['wpep_dropdown_amount_labels'] ) ) {
			$dropdown_labels = sanitize_text_field( wp_unslash( $_POST['wpep_dropdown_amount_labels'] ) );
		}
		$radio_amounts_with_labels    = array();
		$dropdown_amounts_with_labels = array();
		$tabular_products_with_labels = array();
		if ( isset( $radio_amounts ) ) {
			foreach ( $radio_amounts as $key => $amount_rd ) {
				$data['amount'] = $amount_rd;
				$data['label']  = $radio_labels[ $key ];
				array_push( $radio_amounts_with_labels, $data );
			}
		}
		if ( isset( $dropdown_amounts ) ) {
			foreach ( $dropdown_amounts as $key => $amount_dd ) {
				$data['amount'] = $amount_dd;
				$data['label']  = $dropdown_labels[ $key ];
				array_push( $dropdown_amounts_with_labels, $data );
			}
		}
		if ( isset( $_POST['wpep_tabular_products_price'] ) && ! empty( $_POST['wpep_tabular_products_price'] ) ) {
			$tabular_product_price = sanitize_text_field( wp_unslash( $_POST['wpep_tabular_products_price'] ) );
		}

		if ( isset( $_POST['wpep_tabular_products_label'] ) && ! empty( $_POST['wpep_tabular_products_label'] ) ) {
			$tabular_product_label = sanitize_text_field( wp_unslash( $_POST['wpep_tabular_products_label'] ) );
		}

		if ( isset( $_POST['wpep_tabular_products_qty'] ) && ! empty( $_POST['wpep_tabular_products_qty'] ) ) {
			$tabular_product_qty = sanitize_text_field( wp_unslash( $_POST['wpep_tabular_products_qty'] ) );
		}
		if ( isset( $tabular_product_price ) ) {
			foreach ( $tabular_product_price as $key => $product_price ) {
				$data['amount']       = $product_price;
				$data['label']        = $tabular_product_label[ $key ];
				$data['quantity']     = $tabular_product_qty[ $key ];
				$data['products_url'] = ( isset( $products_url[ $key ] ) ? $products_url[ $key ] : '' );
				array_push( $tabular_products_with_labels, $data );
			}
		}
		update_post_meta( $post_ID, 'wpep_square_test_location_id', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_test_location_id'] ) ? $_POST['wpep_square_test_location_id'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_payment_type', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_payment_type'] ) ? $_POST['wpep_square_payment_type'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_payment_purpose', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_payment_purpose'] ) ? $_POST['wpep_square_payment_purpose'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_payment_success_url', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_payment_success_url'] ) ? $_POST['wpep_square_payment_success_url'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_payment_success_msg', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_payment_success_msg'] ) ? $_POST['wpep_payment_success_msg'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_payment_success_label', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_payment_success_label'] ) ? $_POST['wpep_square_payment_success_label'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_payment_box_1', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_payment_box_1'] ) ? $_POST['wpep_square_payment_box_1'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_payment_box_2', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_payment_box_2'] ) ? $_POST['wpep_square_payment_box_2'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_payment_box_3', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_payment_box_3'] ) ? $_POST['wpep_square_payment_box_3'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_payment_box_4', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_payment_box_4'] ) ? $_POST['wpep_square_payment_box_4'] : '' ) ) ) );
		update_post_meta( $post_ID, 'defaultPriceSelected', sanitize_text_field( wp_unslash( ( isset( $_POST['defaultPriceSelected'] ) ? $_POST['defaultPriceSelected'] : '' ) ) ) );
		update_post_meta( $post_ID, 'currencySymbolType', sanitize_text_field( wp_unslash( ( isset( $_POST['currencySymbolType'] ) ? $_POST['currencySymbolType'] : 'code' ) ) ) );
		update_post_meta( $post_ID, 'PriceSelected', sanitize_text_field( wp_unslash( ( isset( $_POST['PriceSelected'] ) ? $_POST['PriceSelected'] : '1' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_form_builder_fields', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_form_builder_fields'] ) ? $_POST['wpep_square_form_builder_fields'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_user_defined_amount', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_user_defined_amount'] ) ? $_POST['wpep_square_user_defined_amount'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_transaction_notes_box', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_transaction_notes_box'] ) ? $_POST['wpep_transaction_notes_box'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_admin_email_to_field', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_admin_email_to_field'] ) ? $_POST['wpep_square_admin_email_to_field'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_admin_email_cc_field', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_admin_email_cc_field'] ) ? $_POST['wpep_square_admin_email_cc_field'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_admin_email_bcc_field', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_admin_email_bcc_field'] ) ? $_POST['wpep_square_admin_email_bcc_field'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_admin_email_from_field', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_admin_email_from_field'] ) ? $_POST['wpep_square_admin_email_from_field'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_admin_email_subject_field', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_admin_email_subject_field'] ) ? $_POST['wpep_square_admin_email_subject_field'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_admin_email_content_field', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_admin_email_content_field'] ) ? $_POST['wpep_square_admin_email_content_field'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_admin_email_exclude_blank_tags_lines', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_admin_email_exclude_blank_tags_lines'] ) ? $_POST['wpep_square_admin_email_exclude_blank_tags_lines'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_admin_email_content_type_html', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_admin_email_content_type_html'] ) ? $_POST['wpep_square_admin_email_content_type_html'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_save_card', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_save_card'] ) ? $_POST['wpep_save_card'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_prods_without_images', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_prods_without_images'] ) ? $_POST['wpep_prods_without_images'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_user_email_to_field', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_user_email_to_field'] ) ? $_POST['wpep_square_user_email_to_field'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_user_email_cc_field', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_user_email_cc_field'] ) ? $_POST['wpep_square_user_email_cc_field'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_user_email_bcc_field', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_user_email_bcc_field'] ) ? $_POST['wpep_square_user_email_bcc_field'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_user_email_from_field', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_user_email_from_field'] ) ? $_POST['wpep_square_user_email_from_field'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_user_email_subject_field', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_user_email_subject_field'] ) ? $_POST['wpep_square_user_email_subject_field'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_user_email_content_field', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_user_email_content_field'] ) ? $_POST['wpep_square_user_email_content_field'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_user_email_exclude_blank_tags_lines', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_user_email_exclude_blank_tags_lines'] ) ? $_POST['wpep_square_user_email_exclude_blank_tags_lines'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_user_email_content_type_html', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_user_email_content_type_html'] ) ? $_POST['wpep_square_user_email_content_type_html'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_button_title', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_button_title'] ) ? $_POST['wpep_button_title'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_location_id', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_location_id'] ) ? $_POST['wpep_square_location_id'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_amount_type', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_amount_type'] ) ? $_POST['wpep_square_amount_type'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_open_in_popup', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_open_in_popup'] ) ? $_POST['wpep_open_in_popup'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_payment_mode', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_payment_mode'] ) ? $_POST['wpep_payment_mode'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_master_pay', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_master_pay'] ) ? $_POST['wpep_square_master_pay'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_master_pay_live', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_master_pay_live'] ) ? $_POST['wpep_square_master_pay_live'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_individual_form_global', 'on' );
		update_post_meta( $post_ID, 'wpep_subscription_cycle_interval', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_subscription_cycle_interval'] ) ? $_POST['wpep_subscription_cycle_interval'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_subscription_cycle', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_subscription_cycle'] ) ? $_POST['wpep_subscription_cycle'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_subscription_length', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_subscription_length'] ) ? $_POST['wpep_subscription_length'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_organization_name', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_organization_name'] ) ? $_POST['wpep_organization_name'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_donation_goal_switch', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_donation_goal_switch'] ) ? $_POST['wpep_donation_goal_switch'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_donation_goal_amount', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_donation_goal_amount'] ) ? $_POST['wpep_donation_goal_amount'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_donation_goal_message_switch', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_donation_goal_message_switch'] ) ? $_POST['wpep_donation_goal_message_switch'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_donation_goal_message', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_donation_goal_message'] ) ? $_POST['wpep_donation_goal_message'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_donation_goal_form_close', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_donation_goal_form_close'] ) ? $_POST['wpep_donation_goal_form_close'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_mailchimp_audience', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_mailchimp_audience'] ) ? $_POST['wpep_mailchimp_audience'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_dropdown_amounts', ( isset( $dropdown_amounts_with_labels ) ? $dropdown_amounts_with_labels : '' ) );
		update_post_meta( $post_ID, 'wpep_radio_amounts', ( isset( $radio_amounts_with_labels ) ? $radio_amounts_with_labels : '' ) );
		update_post_meta( $post_ID, 'wpep_products_with_labels', ( isset( $tabular_products_with_labels ) ? $tabular_products_with_labels : '' ) );
		update_post_meta( $post_ID, 'wpep_square_payment_min', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_payment_min'] ) ? $_POST['wpep_square_payment_min'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_square_payment_max', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_square_payment_max'] ) ? $_POST['wpep_square_payment_max'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_show_wizard', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_show_wizard'] ) ? $_POST['wpep_show_wizard'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_show_shadow', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_show_shadow'] ) ? $_POST['wpep_show_shadow'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_btn_theme', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_btn_theme'] ) ? $_POST['wpep_btn_theme'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_form_theme_color', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_form_theme_color'] ) ? $_POST['wpep_form_theme_color'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_payment_btn_label', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_payment_btn_label'] ) ? $_POST['wpep_payment_btn_label'] : '' ) ) ) );
		/* adding redirection values */
		update_post_meta( $post_ID, 'wantRedirection', sanitize_text_field( wp_unslash( ( isset( $_POST['wantRedirection'] ) ? $_POST['wantRedirection'] : 'No' ) ) ) );
		update_post_meta( $post_ID, 'redirectionDelay', sanitize_text_field( wp_unslash( ( isset( $_POST['redirectionDelay'] ) ? $_POST['redirectionDelay'] : '' ) ) ) );
		/*term & condition Check */
		update_post_meta( $post_ID, 'enableTermsCondition', sanitize_text_field( wp_unslash( ( isset( $_POST['enableTermsCondition'] ) ? $_POST['enableTermsCondition'] : '' ) ) ) );
		update_post_meta( $post_ID, 'termsLabel', sanitize_text_field( wp_unslash( ( isset( $_POST['termsLabel'] ) ? $_POST['termsLabel'] : '' ) ) ) );
		update_post_meta( $post_ID, 'termsLink', sanitize_text_field( wp_unslash( ( isset( $_POST['termsLink'] ) ? $_POST['termsLink'] : '' ) ) ) );
		update_post_meta( $post_ID, 'enableQuantity', sanitize_text_field( wp_unslash( ( isset( $_POST['enableQuantity'] ) ? $_POST['enableQuantity'] : '' ) ) ) );
		update_post_meta( $post_ID, 'enableCoupon', sanitize_text_field( wp_unslash( ( isset( $_POST['enableCoupon'] ) ? $_POST['enableCoupon'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_subscription_trial_days', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_subscription_trial_days'] ) ? $_POST['wpep_subscription_trial_days'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_subscription_trial', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_subscription_trial'] ) ? $_POST['wpep_subscription_trial'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_signup_fees_label', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_signup_fees_label'] ) ? $_POST['wpep_signup_fees_label'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_signup_fees_amount', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_signup_fees_amount'] ) ? $_POST['wpep_signup_fees_amount'] : '' ) ) ) );
		update_post_meta( $post_ID, 'wpep_enable_signup_fees', sanitize_text_field( wp_unslash( ( isset( $_POST['wpep_enable_signup_fees'] ) ? $_POST['wpep_enable_signup_fees'] : '' ) ) ) );
		// saving addtional charges.

		if ( isset( $_POST['wpep_service_fees_name'] ) && ! empty( $_POST['wpep_service_fees_name'] ) ) {
			$fees_data              = array();
			$wpep_service_fees_name = isset( $_POST['wpep_service_fees_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wpep_service_fees_name'] ) ) : '';
			foreach ( $wpep_service_fees_name as $key => $name ) {
				$fees_data['check'][ $key ] = ( isset( $_POST['wpep_service_fees_check'][ $key ] ) ? sanitize_text_field( wp_unslash( $_POST['wpep_service_fees_check'][ $key ] ) ) : 'no' );
				$fees_data['name'][ $key ]  = ( isset( $_POST['wpep_service_fees_name'][ $key ] ) ? sanitize_text_field( wp_unslash( $_POST['wpep_service_fees_name'][ $key ] ) ) : '' );
				$fees_data['type'][ $key ]  = ( isset( $_POST['wpep_service_charge_type'][ $key ] ) ? sanitize_text_field( wp_unslash( $_POST['wpep_service_charge_type'][ $key ] ) ) : '' );
				$fees_data['value'][ $key ] = ( isset( $_POST['wpep_fees_value'][ $key ] ) ? sanitize_text_field( wp_unslash( $_POST['wpep_fees_value'][ $key ] ) ) : '' );
			}
			update_post_meta( $post_ID, 'fees_data', $fees_data );
		}

		global  $wpdb;

		if ( get_post_type( $post_ID ) === 'wp_easy_pay' ) {
			$title = '';
			if ( isset( $data['post_title'] ) ) {
				$title = sanitize_text_field( $data['post_title'] );
			}

			$post_name = '';
			if ( isset( $data['post_title'] ) ) {
				$post_name = rawurlencode( sanitize_text_field( $data['post_title'] ) );
			}
			$post_content = '';
			if ( isset( $data['post_content'] ) ) {
				$post_content = sanitize_text_field( $data['post_content'] );
			}
			/*$update = 'update';
			$where  = array(
				'ID' => $post_ID,
			);
			
			
			$wpdb->$update(
				$wpdb->posts,
				array(
					'post_title' => $title,
				),
				$where
			);
			$wpdb->$update(
				$wpdb->posts,
				array(
					'post_content' => $post_content,
				),
				$where
			);*/
		}
	}
}

/**
 * Creates the URL for connecting to Square.
 *
 * @param string $origin The origin URL.
 * @return string The Square connection URL.
 */
function wpep_create_connect_url( $origin ) {
	$uri_requested = '';
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$uri_requested = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	}
	/* Fetch GET parameters from URI */
	$parts = wp_parse_url( $uri_requested );
	parse_str( $parts['query'], $url_identifiers );
	/* Fetch Admin URL */
	$slash_exploded                                  = explode( '/', $uri_requested );
	$admin_url_index                                 = count( $slash_exploded ) >= 4 ? 3 : 2;
	$question_mark_exploded                          = explode( '?', $slash_exploded[ $admin_url_index ] );
	$url_identifiers['wpep_admin_url']               = $question_mark_exploded[0];
	$url_identifiers['wpep_post_type']               = 'wp_easy_pay';
	$url_identifiers['wpep_prepare_connection_call'] = true;
	if ( 'individual_form' === $origin ) {
		if ( filter_input( INPUT_GET, 'post' ) && ! empty( filter_input( INPUT_GET, 'post' ) ) ) {
			$url_identifiers['wpep_page_post'] = sanitize_text_field( filter_input( INPUT_GET, 'post' ) );
		}
	}
	if ( 'global' === $origin ) {
		$url_identifiers['wpep_page_post'] = 'global';
	}
	$connection_url = add_query_arg( $url_identifiers, esc_url( admin_url( 'edit.php' ) ) );
	return $connection_url;
}

/**
 * Creates the URL for connecting to Square in the sandbox mode.
 *
 * @param string $origin The origin URL.
 * @return string The Square sandbox connection URL.
 */
function wpep_create_connect_sandbox_url( $origin ) {
	$uri_requested = '';
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$uri_requested = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	}
	/* Fetch GET parameters from URI */
	$parts = wp_parse_url( $uri_requested );
	parse_str( $parts['query'], $url_identifiers );
	/* Fetch Admin URL */
	$slash_exploded                                  = explode( '/', $uri_requested );
	$admin_url_index                                 = count( $slash_exploded ) >= 4 ? 3 : 2;
	$question_mark_exploded                          = explode( '?', $slash_exploded[ $admin_url_index ] );
	$url_identifiers['wpep_admin_url']               = sanitize_text_field( $question_mark_exploded[0] );
	$url_identifiers['wpep_post_type']               = 'wp_easy_pay';
	$url_identifiers['wpep_prepare_connection_call'] = true;
	if ( 'individual_form' === $origin ) {
		if ( filter_input( INPUT_GET, 'post' ) && ! empty( filter_input( INPUT_GET, 'post' ) ) ) {
			$url_identifiers['wpep_page_post'] = sanitize_text_field( filter_input( INPUT_GET, 'post' ) );
			$url_identifiers['wpep_sandbox']   = 'yes';
		}
	}
	if ( 'global' === $origin ) {
		$url_identifiers['wpep_page_post'] = 'global';
		$url_identifiers['wpep_sandbox']   = 'yes';
	}
	$connection_url = add_query_arg( $url_identifiers, esc_url( admin_url( 'edit.php' ) ) );
	return $connection_url;
}

/**
 * Adds the shortcode metabox to the form.
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

/**
 * Renders the HTML for the shortcode meta box in the form.
 */
function wpep_render_form_shortcode_meta_html() {
	require_once WPEP_ROOT_PATH . 'views/backend/form_builder_settings/form-shortocde-metabox.php';
}

/**
 * Adds additional meta options for publishing a post.
 *
 * @param WP_Post $post_obj The post object.
 */
function add_publish_meta_options( $post_obj ) {
	global  $post;
	$post_type = 'wp_easy_pay';
	// If you want a specific post type.
	$value = get_post_meta( $post_obj->ID, 'check_meta', true );
	// If saving value to post_meta.
	if ( $post_type === $post->post_type ) {
		echo 1;
	}
}


/**
 * Renders the HTML for the form style meta box.
 */
function wpep_render_form_style_meta_html() {
	require_once WPEP_ROOT_PATH . 'views/backend/form_builder_settings/wpep-render-form-style-meta-html.php';
}

/**
 * Adds the currency show type metabox to the form.
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

/**
 * Renders the HTML for changing the currency show type in the form.
 */
function wpep_render_form_change_currency_show_type_html() {
	require_once WPEP_ROOT_PATH . 'views/backend/form_builder_settings/form-currency-show-type-metabox.php';
}
