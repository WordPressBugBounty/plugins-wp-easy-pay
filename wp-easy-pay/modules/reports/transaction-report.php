<?php
/**
 * Filename: transaction_report.php
 * Description: transaction report.
 *
 * @package WP_Easy_Pay
 */

/**
 * Generate a report for a single transaction.
 *
 * This function generates a report for a single transaction in the wpep system.
 * It may process the provided transaction data, create a report, or send notification emails.
 *
 * @param array $transaction_data An array containing the transaction data.
 * @param int   $post_id The ID of the transaction post.
 * @param array $personal_information An array containing personal information of the user.
 */
function wpep_single_transaction_report( $transaction_data, $post_id, $personal_information ) {

	$transaction_report = array(

		'post_title'  => $transaction_data['transaction_id'],
		'post_type'   => 'wpep_reports',
		'post_status' => 'publish',
	);

	$transaction_report_id = wp_insert_post( $transaction_report );
	$payment_type          = get_post_meta( $personal_information['current_form_id'], 'wpep_square_payment_type', true );

	update_post_meta( $transaction_report_id, 'wpep_first_name', $personal_information['first_name'] );
	update_post_meta( $transaction_report_id, 'wpep_last_name', $personal_information['last_name'] );
	update_post_meta( $transaction_report_id, 'wpep_email', $personal_information['email'] );
	update_post_meta( $transaction_report_id, 'wpep_square_charge_amount', $personal_information['amount'] );
	update_post_meta( $transaction_report_id, 'wpep_square_signup_amount', $personal_information['signup_amount'] );
	update_post_meta( $transaction_report_id, 'wpep_charge_currency', $personal_information['currency'] );
	update_post_meta( $transaction_report_id, 'wpep_square_discount', $personal_information['discount'] );
	if ( isset( $personal_information['taxes'] ) ) {
		update_post_meta( $transaction_report_id, 'wpep_square_taxes', $personal_information['taxes'] );
	}
	update_post_meta( $transaction_report_id, 'wpep_subscription_post_id', $post_id );
	update_post_meta( $transaction_report_id, 'wpep_transaction_status', $transaction_data['transaction_status'] );
	update_post_meta( $transaction_report_id, 'wpep_transaction_type', $payment_type );
	update_post_meta( $transaction_report_id, 'wpep_form_id', $personal_information['current_form_id'] );
	update_post_meta( $transaction_report_id, 'wpep_form_values', $personal_information['form_values'] );

	return $transaction_report_id;
}
/**
 * Generate a report for a failed single transaction.
 *
 * This function generates a report for a failed single transaction in the wpep system.
 * It may process the provided data, create a report, or send notification emails.
 *
 * @param int    $post_id The ID of the transaction post.
 * @param array  $personal_information An array containing personal information of the user.
 * @param string $error_message The error message related to the failed transaction.
 * @return void
 */
function wpep_failed_single_transaction_report( $post_id, $personal_information, $error_message ) {

	$transaction_report = array(
		'post_title'  => '---',
		'post_type'   => 'wpep_reports',
		'post_status' => 'publish',
	);

	$transaction_report_id = wp_insert_post( $transaction_report );
	$payment_type          = get_post_meta( $personal_information['current_form_id'], 'wpep_square_payment_type', true );

	update_post_meta( $transaction_report_id, 'wpep_first_name', $personal_information['first_name'] );
	update_post_meta( $transaction_report_id, 'wpep_last_name', $personal_information['last_name'] );
	update_post_meta( $transaction_report_id, 'wpep_email', $personal_information['email'] );
	update_post_meta( $transaction_report_id, 'wpep_square_charge_amount', $personal_information['amount'] );
	update_post_meta( $transaction_report_id, 'wpep_square_signup_amount', $personal_information['signup_amount'] );
	update_post_meta( $transaction_report_id, 'wpep_subscription_post_id', $post_id );
	update_post_meta( $transaction_report_id, 'wpep_transaction_status', 'Failed' );
	update_post_meta( $transaction_report_id, 'wpep_transaction_type', $payment_type );
	update_post_meta( $transaction_report_id, 'wpep_form_id', $personal_information['current_form_id'] );
	update_post_meta( $transaction_report_id, 'wpep_form_values', $personal_information['form_values'] );
	update_post_meta( $transaction_report_id, 'wpep_transaction_error', $error_message );
}
