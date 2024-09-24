<?php
/**
 * Filename: user-email.php
 * Description: silence is golden.
 *
 * @package WP_Easy_Pay
 */

/**
 * Sends a user email.
 *
 * @param int   $current_form_id  The ID of the current form.
 * @param array $form_values      The values submitted in the form.
 * @param int   $transaction_id   The ID of the transaction.
 */
function wpep_send_user_email( $current_form_id, $form_values, $transaction_id ) {

	$from              = get_post_meta( $current_form_id, 'wpep_square_user_email_from_field', true );
	$subject           = get_post_meta( $current_form_id, 'wpep_square_user_email_subject_field', true );
	$message           = get_post_meta( $current_form_id, 'wpep_square_user_email_content_field', true );
	$html_content_type = get_post_meta( $current_form_id, 'wpep_square_user_email_content_type_html', true );
	$current_user      = wp_get_current_user();

	// $payment_received = file_get_contents( WPEP_ROOT_PATH . 'modules/email_notifications/templates/payment_sent.php' );

	$img_url = WPEP_ROOT_URL . 'assets/frontend/img/payment-done.png';

	$message         = str_replace( '[wpep_payment_received_img]', $img_url, $message );
	$message         = str_replace( '[wpep_email_body]', $message, $message );
	$transaction_tag = '[transaction_id]';

	$form_values = (object) $form_values;

	/* Parsing Tags */
	foreach ( $form_values as $form_value ) {

		if ( isset( $form_value['label'] ) && isset( $form_value['value'] ) ) {

			$label = $form_value['label'];
			$value = $form_value['value'];

			if ( null !== $label ) {

				if ( 'Email' === $label ) {
					$label = 'user_email';
					$to    = $value;
				}
				$tag     = '[' . str_replace( ' ', '_', strtolower( $label ) ) . ']';
				$message = str_replace( $tag, $value, $message );

				$subject = str_replace( $tag, $value, $subject );
			}
		}
	}

		$message = str_replace( $transaction_tag, $transaction_id, $message );
		$subject = str_replace( $transaction_tag, $transaction_id, $subject );

		$headers  = 'From: ' . get_bloginfo( 'name' ) . ' <' . wp_strip_all_tags( $from ) . ">\r\n";
		$headers .= 'Reply-To: ' . wp_strip_all_tags( $from ) . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		$message  = $message;
		wp_mail( $to, $subject, $message, $headers );
}
