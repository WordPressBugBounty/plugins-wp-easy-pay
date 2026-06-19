<?php
/**
 * Transactions list: add Form Name column between ID and Paid by.
 *
 * @package WP_Easy_Pay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inserts the Form Name column right after ID.
 *
 * @param array $columns Existing columns for wpep_reports list table.
 * @return array
 */
function wpep_transactions_insert_form_name_column( $columns ) {
	if ( isset( $columns['form_name'] ) ) {
		return $columns;
	}

	$rebuilt  = array();
	$inserted = false;
	foreach ( $columns as $key => $label ) {
		$rebuilt[ $key ] = $label;
		if ( 'post_id' === $key ) {
			$rebuilt['form_name'] = __( 'Form Name', 'wp_easy_pay' );
			$inserted             = true;
		}
	}

	if ( ! $inserted ) {
		$rebuilt['form_name'] = __( 'Form Name', 'wp_easy_pay' );
	}

	return $rebuilt;
}

/**
 * Renders the Form Name column value for each transaction row.
 *
 * @param string $column  Column name.
 * @param int    $post_id Current transaction/report post ID.
 * @return void
 */
function wpep_transactions_render_form_name_column( $column, $post_id ) {
	if ( 'form_name' !== $column ) {
		return;
	}

	$form_id = absint( get_post_meta( $post_id, 'wpep_form_id', true ) );
	if ( ! $form_id ) {
		echo '&ndash;';
		return;
	}

	$form_title = get_the_title( $form_id );
	if ( '' === $form_title ) {
		/* translators: %d: payment form post ID */
		$form_title = sprintf( __( 'Form ID %d', 'wp_easy_pay' ), $form_id );
	}

	if ( 'wp_easy_pay' === get_post_type( $form_id ) ) {
		$form_url = get_edit_post_link( $form_id );
		if ( $form_url ) {
			echo '<a href="' . esc_url( $form_url ) . '" class="wpep-blue" title="' . esc_attr__( 'View form', 'wp_easy_pay' ) . '">' . esc_html( $form_title ) . '</a>';
			return;
		}
	}

	echo esc_html( $form_title );
}

add_filter( 'manage_wpep_reports_posts_columns', 'wpep_transactions_insert_form_name_column', 20 );
add_action( 'manage_wpep_reports_posts_custom_column', 'wpep_transactions_render_form_name_column', 10, 2 );
