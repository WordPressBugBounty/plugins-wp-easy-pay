<?php
/**
 * Filename: admin-email.php
 * Description: silence is golden.
 *
 * @package WP_Easy_Pay
 */

/**
 * Sends a payment summary email with transaction details for a specific form.
 *
 * @param int    $current_form_id The ID of the current form for which the summary is generated.
 * @param string $form_title      The title of the form.
 * @param string $start_date      The start date for the payment summary period.
 * @param string $end_date        The end date for the payment summary period.
 *
 * @return void
 */
function wpep_send_wpep_payment_summary_email( $current_form_id, $form_title, $start_date, $end_date ) {

	global $wpdb;
	$prepare     = 'prepare';
	$get_results = 'get_results';
	$query       = $wpdb->$prepare(
		"
		SELECT ID FROM `{$wpdb->prefix}posts`
		WHERE post_date BETWEEN %s AND %s
		AND post_type IN (%s, %s)
		ORDER BY post_date DESC
		",
		$start_date,
		$end_date,
		'wpep_reports',
		'wpep_subscriptions'
	);

	$sql_by_date = $wpdb->$get_results( $query );

	$total_transactions_count = count( $sql_by_date );
	$simple_payment_total     = 0;
	$donation_payment_total   = 0;
	$donation_recurring_total = 0;
	$subscription_total       = 0;
	$successful_transactions  = array();
	$total_customers          = array();
	foreach ( $sql_by_date as $res ) {

		$wpep_form_id = get_post_meta( $res->ID, 'wpep_form_id', true );

		if ( isset( $wpep_form_id ) && $current_form_id === $wpep_form_id ) {
			$wpep_transaction_type   = get_post_meta( $res->ID, 'wpep_transaction_type', true );
			$wpep_transaction_status = get_post_meta( $res->ID, 'wpep_transaction_status', true );
			$wpep_form_type          = get_post_meta( $res->ID, 'wpep_form_type', true );
			$sub_transactions        = wpep_get_subscription_transactions( $res->ID );
			$wpep_sub_transaction    = get_post_meta( $res->ID, 'wpep_subscription_transactions', true );
			$wpep_email              = get_post_meta( $res->ID, 'wpep_email', true );

			if ( isset( $sub_transactions ) || isset( $wpep_form_type ) ) {
				if ( 'donation_recurring' === $wpep_form_type || 'subscription' === $wpep_form_type ) {
					foreach ( $sub_transactions as $inv_id => $sub_trans ) {
						if ( isset( $sub_trans ) && 'PAID' === $sub_trans['status'] ) {
							$successful_transactions[ $res->ID ] = $sub_trans['status'];
						}
						if ( isset( $wpep_form_type ) && 'donation_recurring' === $wpep_form_type ) {
							$donation_recurring_amount = $sub_trans['amount'];
							$donation_recurring_total  = $donation_recurring_total + $donation_recurring_amount;
						}

						if ( isset( $wpep_form_type ) && 'subscription' === $wpep_form_type ) {
							$subscription_amount = $sub_trans['amount'];
							$subscription_total  = $subscription_total + $subscription_amount;
						}
					}
				}
			}
			if ( ! in_array( $wpep_email, $total_customers, true ) ) {
				$total_customers[] = $wpep_email;
			}
			if ( isset( $wpep_transaction_status ) && 'COMPLETED' === $wpep_transaction_status ) {
				$successful_transactions[ $res->ID ] = $wpep_transaction_status;
			}
			if ( isset( $wpep_transaction_type ) && 'simple' === $wpep_transaction_type ) {
				$payment_type  = 'Simple Payments';
				$simple_amount = get_post_meta( $res->ID, 'wpep_square_charge_amount', true );

				$simple_payment_total = $simple_payment_total + $simple_amount;
			}
			if ( isset( $wpep_transaction_type ) && 'donation' === $wpep_transaction_type ) {
				$payment_type    = 'Donation Payments';
				$donation_amount = get_post_meta( $res->ID, 'wpep_square_charge_amount', true );

				$donation_payment_total = $donation_payment_total + $donation_amount;
			}
		}
	}
	$total_customers               = count( $total_customers );
	$total_amount                  = $simple_payment_total + $donation_payment_total + $donation_recurring_total + $subscription_total;
	$total_successful_transactions = count( $successful_transactions );

	$to                              = get_post_meta( $current_form_id, 'wpep_square_summary_email_to_field', true );
	$from                            = get_post_meta( $current_form_id, 'wpep_square_summary_email_from_field', true );
	$subject                         = get_post_meta( $current_form_id, 'wpep_square_summary_email_subject_field', true );
	$current_user                    = wp_get_current_user();
	$new_start_date                  = new DateTime( $start_date );
	$new_start_date                  = $new_start_date->format( 'F j, Y' );
	$new_end_date                    = new DateTime( $end_date );
	$new_end_date                    = $new_end_date->format( 'F j, Y' );
	$wpep_square_payment_mode_global = get_option( 'wpep_square_payment_mode_global', true );
	if ( isset( $wpep_square_payment_mode_global ) && 'on' === $wpep_square_payment_mode_global ) {
		$currency = get_option( 'wpep_square_currency_new' );
	} else {
		$currency = get_option( 'wpep_square_currency_test' );
	}
	if ( isset( $currency ) ) {
		if ( 'USD' === $currency ) {
			$currency_symbol = '$';
		} elseif ( 'CAD' === $currency ) {
			$currency_symbol = 'C$';
		} elseif ( 'GBP' === $currency ) {
			$currency_symbol = '£';
		} elseif ( 'JPY' === $currency ) {
			$currency_symbol = '¥';
		} elseif ( 'AUD' === $currency ) {
			$currency_symbol = 'A$';
		}
	}
	ob_start();
	include WPEP_ROOT_PATH . 'modules/email_notifications/templates/payment-summary-email-template.php';
	$message = ob_get_clean();

	$headers  = 'From: ' . get_bloginfo( 'name' ) . ' <' . wp_strip_all_tags( $from ) . ">\r\n";
	$headers .= 'Reply-To: ' . wp_strip_all_tags( $from ) . "\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
	$message  = $message;
	wp_mail( $to, $subject, $message, $headers );
}
