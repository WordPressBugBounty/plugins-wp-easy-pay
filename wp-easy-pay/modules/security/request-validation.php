<?php
/**
 * Security helpers for WP Easy Pay admin and AJAX requests.
 *
 * @package WP_Easy_Pay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current user can manage plugin settings.
 *
 * @return bool
 */
function wpep_current_user_can_manage() {
	return current_user_can( 'manage_options' );
}

/**
 * Require manage_options capability or terminate the request.
 *
 * @return void
 */
function wpep_require_manage_options() {
	if ( ! wpep_current_user_can_manage() ) {
		wp_die(
			esc_html__( 'You do not have permission to perform this action.', 'wp_easy_pay' ),
			esc_html__( 'Forbidden', 'wp_easy_pay' ),
			array( 'response' => 403 )
		);
	}
}

/**
 * Verify a request nonce from GET/POST.
 *
 * @param string $action    Nonce action name.
 * @param string $query_arg Request parameter that carries the nonce.
 * @return bool
 */
function wpep_verify_request_nonce( $action, $query_arg = '_wpnonce' ) {
	$nonce = '';

	if ( isset( $_REQUEST[ $query_arg ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$nonce = sanitize_text_field( wp_unslash( $_REQUEST[ $query_arg ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	} elseif ( 'wp_global_nonce' === $action && isset( $_POST['wp_global_nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$nonce = sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	if ( '' === $nonce ) {
		return false;
	}

	return (bool) wp_verify_nonce( $nonce, $action );
}

/**
 * Require a valid request nonce or terminate the request.
 *
 * @param string $action    Nonce action name.
 * @param string $query_arg Request parameter that carries the nonce.
 * @return void
 */
function wpep_require_request_nonce( $action, $query_arg = '_wpnonce' ) {
	if ( ! wpep_verify_request_nonce( $action, $query_arg ) ) {
		wp_die(
			esc_html__( 'Security check failed.', 'wp_easy_pay' ),
			esc_html__( 'Forbidden', 'wp_easy_pay' ),
			array( 'response' => 403 )
		);
	}
}

/**
 * Require a valid wp_global_nonce for POST requests.
 *
 * @return void
 */
function wpep_require_global_post_nonce() {
	if ( ! isset( $_POST['wp_global_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ), 'wp_global_nonce' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		wp_die(
			esc_html__( 'Security check failed.', 'wp_easy_pay' ),
			esc_html__( 'Forbidden', 'wp_easy_pay' ),
			array( 'response' => 403 )
		);
	}
}

/**
 * Read the payment AJAX nonce from POST (supports legacy field name).
 *
 * @return string
 */
function wpep_get_payment_post_nonce_value() {
	if ( isset( $_POST['wpep_payment_nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return sanitize_text_field( wp_unslash( $_POST['wpep_payment_nonce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	if ( isset( $_POST['wp_payment_nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return sanitize_text_field( wp_unslash( $_POST['wp_payment_nonce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	return '';
}

/**
 * Verify payment AJAX nonce.
 *
 * @return bool
 */
function wpep_verify_payment_post_nonce() {
	$nonce = trim( wpep_get_payment_post_nonce_value() );

	if ( '' === $nonce ) {
		return false;
	}

	return (bool) wp_verify_nonce( $nonce, 'payment_nonce' );
}

/**
 * Require a valid payment_nonce for public AJAX POST requests.
 *
 * @return void
 */
function wpep_require_payment_post_nonce() {
	if ( ! wpep_verify_payment_post_nonce() ) {
		wp_die(
			esc_html__( 'Security check failed.', 'wp_easy_pay' ),
			esc_html__( 'Forbidden', 'wp_easy_pay' ),
			array( 'response' => 403 )
		);
	}
}

/**
 * Build a nonce-protected Square disconnect URL.
 *
 * @param string $disconnect_url Stored disconnect URL.
 * @return string
 */
function wpep_get_disconnect_url( $disconnect_url ) {
	if ( empty( $disconnect_url ) ) {
		return '';
	}

	return wp_nonce_url( $disconnect_url, 'wpep_disconnect_square', '_wpnonce' );
}

/**
 * Whether the logged-in user owns a subscription record.
 *
 * @param int $subscription_id Subscription post ID.
 * @return bool
 */
function wpep_current_user_owns_subscription( $subscription_id ) {
	$subscription_id = absint( $subscription_id );

	if ( ! $subscription_id || ! is_user_logged_in() ) {
		return false;
	}

	$subscription_email = get_post_meta( $subscription_id, 'wpep_logged_email', true );

	if ( empty( $subscription_email ) ) {
		return false;
	}

	$current_user = wp_get_current_user();

	return 0 === strcasecmp( $subscription_email, $current_user->user_email );
}

/**
 * Require admin or subscription-owner authorization for subscription AJAX.
 *
 * @param int $subscription_id Subscription post ID.
 * @return void
 */
function wpep_require_subscription_management_auth( $subscription_id ) {
	if ( wpep_current_user_can_manage() ) {
		$has_global_nonce = isset( $_POST['wp_global_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_global_nonce'] ) ), 'wp_global_nonce' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$has_subscription_nonce = isset( $_POST['wp_subscription_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_subscription_nonce'] ) ), 'wpep_subscription_action' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( ! $has_global_nonce && ! $has_subscription_nonce ) {
			wp_die(
				esc_html__( 'Security check failed.', 'wp_easy_pay' ),
				esc_html__( 'Forbidden', 'wp_easy_pay' ),
				array( 'response' => 403 )
			);
		}
		return;
	}

	if ( ! wpep_current_user_owns_subscription( $subscription_id ) ) {
		wp_die(
			esc_html__( 'You do not have permission to perform this action.', 'wp_easy_pay' ),
			esc_html__( 'Forbidden', 'wp_easy_pay' ),
			array( 'response' => 403 )
		);
	}

	if ( ! isset( $_POST['wp_subscription_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_subscription_nonce'] ) ), 'wpep_subscription_action' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		wp_die(
			esc_html__( 'Security check failed.', 'wp_easy_pay' ),
			esc_html__( 'Forbidden', 'wp_easy_pay' ),
			array( 'response' => 403 )
		);
	}
}
