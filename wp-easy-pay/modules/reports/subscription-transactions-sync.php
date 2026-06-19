<?php
/**
 * Sync recurring subscriptions with transactions list entries.
 *
 * @package WP_Easy_Pay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates one mirror transaction (wpep_reports) for a subscription post.
 *
 * @param int   $subscription_post_id Subscription post ID.
 * @param array $args Optional transaction overrides.
 * @return int Transaction post ID or 0 on failure.
 */
function wpep_create_subscription_transaction_mirror( $subscription_post_id, $args = array() ) {
	$subscription_post_id = absint( $subscription_post_id );
	if ( ! $subscription_post_id || 'wpep_subscriptions' !== get_post_type( $subscription_post_id ) ) {
		return 0;
	}

	$defaults = array(
		'transaction_id'     => '',
		'transaction_status' => 'COMPLETED',
		'transaction_source' => 'CARD',
	);
	$args = wp_parse_args( $args, $defaults );

	$existing = get_posts(
		array(
			'post_type'              => 'wpep_reports',
			'post_status'            => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'     => 'wpep_subscription_post_id',
					'value'   => $subscription_post_id,
					'compare' => '=',
					'type'    => 'NUMERIC',
				),
				array(
					'key'     => 'wpep_subscription_mirror',
					'value'   => '1',
					'compare' => '=',
				),
			),
		)
	);
	if ( ! empty( $existing ) ) {
		return (int) $existing[0];
	}

	$form_id = absint( get_post_meta( $subscription_post_id, 'wpep_form_id', true ) );
	if ( ! $form_id ) {
		return 0;
	}

	$transaction_id = $args['transaction_id'];
	if ( '' === $transaction_id ) {
		$transaction_id = (string) get_post_meta( $subscription_post_id, 'wpep_square_subscription_id', true );
	}
	if ( '' === $transaction_id ) {
		$transaction_id = get_the_title( $subscription_post_id );
	}
	if ( '' === trim( (string) $transaction_id ) ) {
		$transaction_id = '-';
	}

	$transaction_post_id = wp_insert_post(
		array(
			'post_title'  => $transaction_id,
			'post_type'   => 'wpep_reports',
			'post_status' => 'publish',
		)
	);
	if ( ! $transaction_post_id || is_wp_error( $transaction_post_id ) ) {
		return 0;
	}

	$meta_copy_keys = array(
		'wpep_first_name',
		'wpep_last_name',
		'wpep_email',
		'wpep_logged_email',
		'wpep_square_charge_amount',
		'wpep_square_signup_amount',
		'wpep_square_discount',
		'wpep_charge_currency',
		'wpep_square_taxes',
		'wpep_form_values',
	);
	foreach ( $meta_copy_keys as $meta_key ) {
		$value = get_post_meta( $subscription_post_id, $meta_key, true );
		if ( '' !== $value && null !== $value ) {
			update_post_meta( $transaction_post_id, $meta_key, $value );
		}
	}

	$transaction_type = get_post_meta( $form_id, 'wpep_square_payment_type', true );
	update_post_meta( $transaction_post_id, 'wpep_form_id', $form_id );
	update_post_meta( $transaction_post_id, 'wpep_transaction_type', $transaction_type );
	update_post_meta( $transaction_post_id, 'wpep_transaction_status', $args['transaction_status'] );
	update_post_meta( $transaction_post_id, 'wpep_transaction_source', $args['transaction_source'] );
	update_post_meta( $transaction_post_id, 'wpep_subscription_post_id', $subscription_post_id );
	update_post_meta( $transaction_post_id, 'wpep_subscription_mirror', '1' );

	return (int) $transaction_post_id;
}

/**
 * Backfills missing mirror transactions for legacy subscription posts.
 *
 * Runs in small admin-side batches to avoid heavy requests.
 *
 * @return void
 */
function wpep_backfill_subscription_mirror_transactions() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) || ! post_type_exists( 'wpep_subscriptions' ) ) {
		return;
	}

	$state = get_option( 'wpep_subscription_mirror_backfill_state', array() );
	if ( ! empty( $state['done'] ) ) {
		return;
	}

	$offset = isset( $state['offset'] ) ? absint( $state['offset'] ) : 0;
	$limit  = 50;

	$subscription_ids = get_posts(
		array(
			'post_type'              => 'wpep_subscriptions',
			'post_status'            => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page'         => $limit,
			'offset'                 => $offset,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( empty( $subscription_ids ) ) {
		update_option(
			'wpep_subscription_mirror_backfill_state',
			array(
				'offset' => $offset,
				'done'   => 1,
			),
			false
		);
		return;
	}

	foreach ( $subscription_ids as $subscription_id ) {
		wpep_create_subscription_transaction_mirror( (int) $subscription_id );
	}

	update_option(
		'wpep_subscription_mirror_backfill_state',
		array(
			'offset' => $offset + count( $subscription_ids ),
			'done'   => 0,
		),
		false
	);
}

/**
 * Syncs trash/delete between subscriptions and mirror transactions.
 *
 * @param int    $post_id Post ID.
 * @param string $action Action name: trash|delete.
 * @return void
 */
function wpep_sync_subscription_and_mirror_transactions( $post_id, $action ) {
	static $processing = array();

	$post_id = absint( $post_id );
	if ( ! $post_id || isset( $processing[ $post_id ] ) ) {
		return;
	}

	$post_type = get_post_type( $post_id );
	if ( 'wpep_subscriptions' !== $post_type && 'wpep_reports' !== $post_type ) {
		return;
	}

	$processing[ $post_id ] = true;

	if ( 'wpep_subscriptions' === $post_type ) {
		$linked_report_ids = get_posts(
			array(
				'post_type'              => 'wpep_reports',
				'post_status'            => 'any',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_key'               => 'wpep_subscription_post_id',
				'meta_value'             => $post_id,
			)
		);
		foreach ( $linked_report_ids as $report_id ) {
			if ( 'trash' === $action ) {
				wp_trash_post( (int) $report_id );
			} else {
				wp_delete_post( (int) $report_id, true );
			}
		}
	} else {
		$linked_subscription_id = absint( get_post_meta( $post_id, 'wpep_subscription_post_id', true ) );
		$is_mirror             = '1' === get_post_meta( $post_id, 'wpep_subscription_mirror', true );
		if ( $is_mirror && $linked_subscription_id && ! isset( $processing[ $linked_subscription_id ] ) && 'wpep_subscriptions' === get_post_type( $linked_subscription_id ) ) {
			if ( 'trash' === $action ) {
				wp_trash_post( $linked_subscription_id );
			} else {
				wp_delete_post( $linked_subscription_id, true );
			}
		}
	}

	unset( $processing[ $post_id ] );
}

/**
 * Trashed-post wrapper for sync.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function wpep_sync_subscription_and_mirror_transactions_on_trash( $post_id ) {
	wpep_sync_subscription_and_mirror_transactions( $post_id, 'trash' );
}

/**
 * Permanent-delete wrapper for sync.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function wpep_sync_subscription_and_mirror_transactions_on_delete( $post_id ) {
	wpep_sync_subscription_and_mirror_transactions( $post_id, 'delete' );
}

add_action( 'admin_init', 'wpep_backfill_subscription_mirror_transactions' );
add_action( 'wp_trash_post', 'wpep_sync_subscription_and_mirror_transactions_on_trash', 20 );
add_action( 'before_delete_post', 'wpep_sync_subscription_and_mirror_transactions_on_delete', 20 );
