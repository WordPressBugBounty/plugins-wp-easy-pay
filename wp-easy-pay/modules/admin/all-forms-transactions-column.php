<?php
/**
 * All Forms: Transactions column and deep-link filter on the reports list.
 *
 * @package WP_Easy_Pay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WPEP_REPORTS_FILTER_FORM_QUERY_ARG' ) ) {
	define( 'WPEP_REPORTS_FILTER_FORM_QUERY_ARG', 'wpep_transactions_form' );
}

/**
 * Inserts the Transactions column after Form title.
 *
 * @param array $columns Columns from manage_wp_easy_pay_posts_columns.
 * @return array
 */
function wpep_all_forms_transactions_insert_column( $columns ) {
	if ( isset( $columns['transactions'] ) ) {
		return $columns;
	}

	$out      = array();
	$inserted = false;
	foreach ( $columns as $key => $label ) {
		$out[ $key ] = $label;
		if ( 'formsTitle' === $key ) {
			$out['transactions'] = __( 'Transactions' );
			$inserted = true;
		}
	}

	if ( ! $inserted ) {
		if ( isset( $out['actions'] ) ) {
			$rebuilt = array();
			foreach ( $out as $k => $v ) {
				if ( 'actions' === $k ) {
					$rebuilt['transactions'] = __( 'Transactions' );
				}
				$rebuilt[ $k ] = $v;
			}
			return $rebuilt;
		}
		$out['transactions'] = __( 'Transactions' );
	}

	return $out;
}

/**
 * Renders the Transactions cell (count + link to filtered reports).
 *
 * @param string $column  Column name.
 * @param int    $post_id Form post ID.
 * @return void
 */
function wpep_all_forms_transactions_render_column( $column, $post_id ) {
	if ( 'transactions' !== $column ) {
		return;
	}

	$counts = ( isset( $GLOBALS['wpep_transaction_counts_by_form'] ) && is_array( $GLOBALS['wpep_transaction_counts_by_form'] ) )
		? $GLOBALS['wpep_transaction_counts_by_form']
		: null;
	$count  = ( is_array( $counts ) && array_key_exists( $post_id, $counts ) )
		? (int) $counts[ $post_id ]
		: wpep_get_single_form_transaction_count( $post_id );
	$reports_url = add_query_arg(
		WPEP_REPORTS_FILTER_FORM_QUERY_ARG,
		$post_id,
		admin_url( 'edit.php?post_type=wpep_reports' )
	);
	echo '<a href="' . esc_url( $reports_url ) . '" class="wpep_tags">' . esc_html( (string) $count ) . '</a>';
}

/**
 * Counts wpep_reports rows per form id (batch).
 *
 * @param int[] $form_ids Payment form post IDs.
 * @return array<int,int> Map of form post ID => transaction count.
 */
function wpep_get_transaction_counts_for_form_ids( array $form_ids ) {
	$form_ids = array_values( array_unique( array_filter( array_map( 'absint', $form_ids ) ) ) );
	if ( empty( $form_ids ) ) {
		return array();
	}

	$map = array_fill_keys( $form_ids, 0 );
	$public_statuses = array_values( get_post_stati( array( 'internal' => false ), 'names' ) );
	if ( empty( $public_statuses ) ) {
		$public_statuses = array( 'publish' );
	}

	$report_ids = get_posts(
		array(
			'post_type'              => 'wpep_reports',
			'post_status'            => $public_statuses,
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'     => 'wpep_form_id',
					'value'   => $form_ids,
					'compare' => 'IN',
					'type'    => 'NUMERIC',
				),
			),
		)
	);

	if ( ! empty( $report_ids ) ) {
		foreach ( $report_ids as $report_id ) {
			$fid = (int) get_post_meta( $report_id, 'wpep_form_id', true );
			if ( isset( $map[ $fid ] ) ) {
				++$map[ $fid ];
			}
		}
	}

	return $map;
}

/**
 * Fallback count for one form when batch map was not primed.
 *
 * @param int $form_id Payment form post ID.
 * @return int
 */
function wpep_get_single_form_transaction_count( $form_id ) {
	global $wpdb;

	$form_id = absint( $form_id );
	if ( ! $form_id ) {
		return 0;
	}

	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'wpep_form_id' AND pm.meta_value = %s
			WHERE p.post_type = 'wpep_reports'
			AND p.post_status != 'trash'
			AND p.post_status != 'auto-draft'",
			(string) $form_id
		)
	);
}

/**
 * Primes transaction counts for forms on the current All Forms list page (avoids per-row queries).
 *
 * @param WP_Post[] $posts Posts for the current query.
 * @param WP_Query  $query Query instance.
 * @return WP_Post[]
 */
function wpep_prime_transaction_counts_for_forms_list( $posts, $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return $posts;
	}

	$post_type = $query->get( 'post_type' );
	if ( 'wp_easy_pay' !== $post_type ) {
		return $posts;
	}

	if ( empty( $posts ) ) {
		$GLOBALS['wpep_transaction_counts_by_form'] = array();
	} else {
		$GLOBALS['wpep_transaction_counts_by_form'] = wpep_get_transaction_counts_for_form_ids( wp_list_pluck( $posts, 'ID' ) );
	}

	return $posts;
}

/**
 * Restricts the reports list to one payment form when opened from All Forms.
 *
 * @param WP_Query $query Main query.
 * @return void
 */
function wpep_reports_filter_by_form_pre_get_posts( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( 'wpep_reports' !== $query->get( 'post_type' ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter for list view.
	if ( empty( $_GET[ WPEP_REPORTS_FILTER_FORM_QUERY_ARG ] ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$form_id = absint( wp_unslash( $_GET[ WPEP_REPORTS_FILTER_FORM_QUERY_ARG ] ) );
	if ( ! $form_id || 'wp_easy_pay' !== get_post_type( $form_id ) ) {
		return;
	}

	$post_type_object = get_post_type_object( 'wpep_reports' );
	if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->edit_posts ) ) {
		return;
	}

	$meta_query   = $query->get( 'meta_query' );
	$meta_query   = is_array( $meta_query ) ? $meta_query : array();
	$meta_query[] = array(
		'key'     => 'wpep_form_id',
		'value'   => $form_id,
		'compare' => '=',
		'type'    => 'NUMERIC',
	);

	$query->set( 'meta_query', $meta_query );
}

/**
 * Shows which form filter is active on the reports (transactions) list.
 *
 * @return void
 */
function wpep_reports_form_filter_admin_notice() {
	if ( ! is_admin() ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-wpep_reports' !== $screen->id ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( empty( $_GET[ WPEP_REPORTS_FILTER_FORM_QUERY_ARG ] ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$form_id = absint( wp_unslash( $_GET[ WPEP_REPORTS_FILTER_FORM_QUERY_ARG ] ) );
	if ( ! $form_id || 'wp_easy_pay' !== get_post_type( $form_id ) ) {
		return;
	}

	$post_type_object = get_post_type_object( 'wpep_reports' );
	if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->edit_posts ) ) {
		return;
	}

	$form_title = get_the_title( $form_id );
	if ( '' === $form_title ) {
		$form_title = sprintf( /* translators: %d: form post ID */ __( 'Form ID %d', 'wp_easy_pay' ), $form_id );
	}

	$clear_url = remove_query_arg( WPEP_REPORTS_FILTER_FORM_QUERY_ARG );

	printf(
		'<div class="notice notice-info"><p><strong>%1$s</strong> %2$s <span class="description">(%3$s)</span> &nbsp;<a href="%4$s">%5$s</a></p></div>',
		esc_html__( 'Filtered by form:', 'wp_easy_pay' ),
		esc_html( $form_title ),
		esc_html( sprintf( /* translators: %d: numeric form ID */ __( 'ID %d', 'wp_easy_pay' ), $form_id ) ),
		esc_url( $clear_url ),
		esc_html__( 'Show all transactions', 'wp_easy_pay' )
	);
}

add_filter( 'manage_wp_easy_pay_posts_columns', 'wpep_all_forms_transactions_insert_column', 20 );
add_action( 'manage_wp_easy_pay_posts_custom_column', 'wpep_all_forms_transactions_render_column', 10, 2 );
add_filter( 'posts_results', 'wpep_prime_transaction_counts_for_forms_list', 10, 2 );
add_action( 'pre_get_posts', 'wpep_reports_filter_by_form_pre_get_posts', 5 );
add_action( 'admin_notices', 'wpep_reports_form_filter_admin_notice' );
