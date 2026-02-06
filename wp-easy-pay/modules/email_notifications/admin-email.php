<?php
/**
 * Filename: admin-email.php
 * Description: silence is golden.
 *
 * @package WP_Easy_Pay
 */

/**
 * Sends an admin email.
 *
 * @param int    $current_form_id  The ID of the current form.
 * @param array  $form_values      The values submitted in the form.
 * @param string $transaction_id   The transaction ID associated with the form submission.
 * @param string $currency   The currency of the current form.
 */
function wpep_send_admin_email( $current_form_id, $form_values, $transaction_id, $currency ) {

	$to           = get_post_meta( $current_form_id, 'wpep_square_admin_email_to_field', true );
	$cc           = get_post_meta( $current_form_id, 'wpep_square_admin_email_cc_field', true );
	$bcc          = get_post_meta( $current_form_id, 'wpep_square_admin_email_bcc_field', true );
	$from         = get_post_meta( $current_form_id, 'wpep_square_admin_email_from_field', true );
	$subject      = get_post_meta( $current_form_id, 'wpep_square_admin_email_subject_field', true );
	$message      = get_post_meta( $current_form_id, 'wpep_square_admin_email_content_field', true );
	$current_user = wp_get_current_user();

	$img_url = WPEP_ROOT_URL . 'assets/frontend/img/payment.png';

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
				}
				$tag = '[' . str_replace( ' ', '_', strtolower( $label ) ) . ']';
				if ( '[line_items]' === $tag ) {

					$wpep_square_payment_type = get_post_meta( $current_form_id, 'wpep_square_payment_type', true );
					$value                    = str_replace( '\"', '"', $value );
					$value                    = json_decode( $value, true );
					if ( is_array( $value ) ) {
						if ( isset( $wpep_square_payment_type ) && 'simple' === $wpep_square_payment_type ) {
							$line_items = '<strong>Simple Payment</strong><br><br>';
						} elseif ( isset( $wpep_square_payment_type ) && 'donation' === $wpep_square_payment_type ) {
							$line_items = '<strong>Donation Payment</strong><br><br>';
						} elseif ( isset( $wpep_square_payment_type ) && 'donation_recurring' === $wpep_square_payment_type ) {
							$line_items = '<strong>Donation Recurring</strong><br><br>';
						} elseif ( isset( $wpep_square_payment_type ) && 'subscription' === $wpep_square_payment_type ) {
							$line_items = '<strong>Subscription Payment</strong><br><br>';
						}

						foreach ( $value as $val ) {

							if ( $val['quantity'] > 0 ) {
								$line_item_label = trim( $val['label'], "'" );

								// Replace remaining escaped characters with actual characters.
								$line_item_label = str_replace( '\\n', "\n", $line_item_label );
								$line_item_label = str_replace( '\\t', "\t", $line_item_label );
								$line_item_price = explode( ' ', $val['price'] );

								$line_items .= $line_item_price[0] * $val['quantity'] . ' ' . $currency . ' - ' . $line_item_label . '<br>';

							}
						}
						$value = (string) $line_items;
					}
				}
				$message = str_replace( $tag, $value, $message );

				$subject = str_replace( $tag, $value, $subject );

			}
		}
	}
		$message = str_replace( $transaction_tag, $transaction_id, $message );
		$subject = str_replace( $transaction_tag, $transaction_id, $subject );

		$headers  = 'From: ' . get_bloginfo( 'name' ) . ' <' . wp_strip_all_tags( $from ) . ">\r\n";
		$headers .= 'Reply-To: ' . wp_strip_all_tags( $from ) . "\r\n";
		$headers .= 'Cc: ' . $cc . "\r\n";
		$headers .= 'Bcc: ' . $bcc . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		$message  = $message;

		wp_mail( $to, $subject, $message, $headers );
}
