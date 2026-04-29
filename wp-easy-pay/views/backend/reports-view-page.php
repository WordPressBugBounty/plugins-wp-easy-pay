<?php
/**
 * Filename: reports-view-page.php
 * Description: reports view page backend.
 *
 * @package WP_Easy_Pay
 */

	$current_post_id = get_the_ID();

	$firstname                 = get_post_meta( $current_post_id, 'wpep_first_name', true );
	$lastname                  = get_post_meta( $current_post_id, 'wpep_last_name', true );
	$email                     = get_post_meta( $current_post_id, 'wpep_email', true );
	$charge_amount             = get_post_meta( $current_post_id, 'wpep_square_charge_amount', true );
	$charge_signup_amount      = get_post_meta( $current_post_id, 'wpep_square_signup_amount', true );
	$charge_amount_no_currency = get_post_meta( $current_post_id, 'wpep_square_charge_amount', true );
	$charge_currency           = get_post_meta( $current_post_id, 'wpep_charge_currency', true );
	$discount_amount           = get_post_meta( $current_post_id, 'wpep_square_discount', true );
	$taxes                     = get_post_meta( $current_post_id, 'wpep_square_taxes', true );
	$transaction_status        = get_post_meta( $current_post_id, 'wpep_transaction_status', true );
	$transaction_source        = get_post_meta( $current_post_id, 'wpep_transaction_source', true );
	$transaction_id            = get_the_title( $current_post_id );
	$transaction_type          = get_post_meta( $current_post_id, 'wpep_transaction_type', true );
	$form_id                   = get_post_meta( $current_post_id, 'wpep_form_id', true );
	$form_values               = get_post_meta( $current_post_id, 'wpep_form_values', true );
	$wpep_transaction_error    = get_post_meta( $current_post_id, 'wpep_transaction_error', true );
	$wpep_refund_id            = get_post_meta( $current_post_id, 'wpep_square_refund_id', true );

	$wpep_refund_amount = get_post_meta( $current_post_id, 'wpep_refunded_amount', true );


	$currency_symbols = array( 'USD', 'CAD', 'GBP', 'AUD', 'JPY', 'EUR', '$', 'C$', 'A$', '¥', '£', '€' );

foreach ( $currency_symbols as $value ) {

	$charge_amount_no_currency = str_replace( $value, '', $charge_amount_no_currency );
}

	$charge_amount_no_currency = str_replace( ',', '', $charge_amount_no_currency );
	$charge_amount_no_currency = (float) $charge_amount_no_currency * 100;

	$full_refunded = false;
if ( '' !== $wpep_refund_amount && false !== $wpep_refund_amount ) {
	$refund_amount = $wpep_refund_amount;
} else {
	$refund_amount = 0;
}

if ( $charge_amount_no_currency === $wpep_refund_amount ) {
	$full_refunded = true;
}


	$charge_amount_no_currency = $charge_amount_no_currency / 100;
	$refund_amount             = (float) $refund_amount / 100;

?>

<script>

jQuery(document).ready(function() {

	jQuery('form input').keydown(function (e) {
	if (e.keyCode == 13) {
		e.preventDefault();
		return false;
	}
});

});

</script>
<div class="reportDetailsContainer">
	<div class="reportDetails">

		<h3 class="detailsTableTitle">Payment Details</h3>
		<table class="detailsTable">
			<tbody>
				<tr>
					<th>Payment type</th>
					<th>Payment method	</th>
					<th>Transaction id	</th>
					<th>Payments amount	</th>
					<th>Signup fees	</th>
					<th>WPEP form</th>
				</tr>
				<tr>
					<td><?php echo esc_html( $transaction_type ); ?></td>
					<td><?php echo esc_html( $transaction_source ); ?></td>
					<td><?php echo esc_html( get_the_title() ); ?></td>
					<td><?php echo esc_html( $charge_amount ); ?></td>
					<td>
						<?php 
						if ( isset( $charge_signup_amount ) && 0 !== $charge_signup_amount ) { 
							echo esc_html( $charge_signup_amount ) . ' ' . esc_html( $charge_currency );
						} else {
							echo '-';
						}
						?>
					</td>
					<td><a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( get_edit_post_link( $form_id ) ); ?>"> click here </a></td>
				</tr>

				<tr>
					<th>Discount</th>
					<th>Payments status</th>
					<th>User name</th>
					<th>User email</th>
					<?php if ( wepp_fs()->is__premium_only() ) { ?>
						<th>Refund id</th>
					<?php } ?>
				</tr>
				<tr>
					<td><?php echo esc_html( $discount_amount ); ?></td>
					<td><?php echo esc_html( $transaction_status ); ?></td>
					<td><?php echo esc_html( $firstname . ' ' . $lastname ); ?></td>
					<td><?php echo esc_html( $email ); ?></td>
					<?php if ( wepp_fs()->is__premium_only() ) { ?>
						<td class="refundIdWrap"><?php echo esc_html( $wpep_refund_id ); ?></td>
					<?php } ?>
				</tr>
			</tbody>
		</table>

		<?php 
		if ( ! empty( $taxes ) ) {
			foreach ( $taxes['name'] as $key => $fees ) {
				$fees_check  = isset( $taxes['check'][ $key ] ) ? $taxes['check'][ $key ] : 'no';
				$fees_name   = isset( $taxes['name'][ $key ] ) ? $taxes['name'][ $key ] : '';
				$fees_value  = isset( $taxes['value'][ $key ] ) ? $taxes['value'][ $key ] : '';
				$charge_type = isset( $taxes['type'][ $key ] ) ? $taxes['type'][ $key ] : '';

				if ( 'yes' === $fees_check ) {

					if ( 'percentage' === $charge_type ) {
						$charge_type = '%';
					} else {
						$charge_type = 'fixed';
					}

					?>
					<h3 class="detailsTableTitle">Extra fees</h3>
					<table class="detailsTable">
						<tbody>
							<tr>
								<th><?php echo esc_html( $fees_name ); ?></th>
							</tr>
							<tr>
								<td><?php echo esc_html( $fees_value ) . ' <small>(' . esc_html( $charge_type ) . ')</small>'; ?></td>
							</tr>
						</tbody>
					</table>
						<?php
				}
			}
		}
		?>

		<?php
		if ( wepp_fs()->is__premium_only() ) {
			if ( 'Failed' !== $transaction_status ) {
				?>
				<h3 class="detailsTableTitle">Refund Now</h3>
				<?php
				if ( false !== $wpep_refund_id && isset( $wpep_refund_id ) && ! empty( $wpep_refund_id ) && true === $full_refunded ) {

					echo '<button disabled> Refunded </button>';

				} else {
					$available_refund = $charge_amount_no_currency - $refund_amount;
					?>

					<table class="detailsTable">
						<tbody>
							<tr>
								<th><?php echo esc_html( $available_refund ); ?> <span><?php echo esc_html( $charge_currency ); ?></span>  available to refund	</th>
								<th><?php echo esc_html( $refund_amount ); ?><span> <?php echo esc_html( $charge_currency ); ?></span> has been refunded	</th>
								<th>
									<input type="text" id="wpep_refund_amount" placeholder="Refund Amount" pattern="[0-9]+" 
										<?php
										if ( 0 === $available_refund ) {
											echo 'disabled'; }
										?>
									/>
								</th>
								<th>
									<button id="give_refund_button" class="give_refund_button" data-postid="<?php echo esc_attr( $current_post_id ); ?>" data-amount="<?php echo esc_attr( $charge_amount ); ?>" data-transactionid="<?php echo esc_attr( $transaction_id ); ?>" 
											<?php
											if ( 0 === $available_refund ) {
												echo 'disabled'; }
											?>
										> 
										Refund 
										<span id="wpep_refund_number"> 0.00 </span> 
										<span> <?php echo esc_html( $charge_currency ); ?> </span>
									</button>
								</th>
							</tr>
						</tbody>
					</table>
					<?php
				}
			}
		}
		?>

<?php
if ( wepp_fs()->is__premium_only() ) {
	if ( isset( $form_values ) && ! empty( $form_values ) ) {

		echo '<h3 class="detailsTableTitle">Line Items</h3>';
		echo '<table class="detailsTable"><tbody>';

		foreach ( $form_values as $key => $value ) {
			
			if ( isset( $value['label'] ) && 'Line Items' === $value['label'] ) {

				$json = json_decode( $value['value'], true );

				echo '<tr>';
				echo '<th>Label</th>';
				echo '<th>Quantity</th>';
				echo '<th>Price</th>';
				echo '<th>Cost</th>';
				echo '</tr>';

				if ( ! empty( $json ) && is_array( $json ) ) {
					foreach ( $json as $item ) {
						echo '<tr>';
						echo '<td>' . esc_html( $item['label'] ?? '' ) . '</td>';
						echo '<td>' . esc_html( $item['quantity'] ?? '' ) . '</td>';
						echo '<td>' . esc_html( $item['price'] ?? '' ) . '</td>';
						echo '<td>' . esc_html( $item['cost'] ?? '' ) . '</td>';
						echo '</tr>';
					}
				} else {
					echo '<tr><td colspan="4">No Line Items Found</td></tr>';
				}
			}
		}

		echo '</tbody></table>';

		
	}
	
	echo '<h3 class="detailsTableTitle">Form Field</h3>';
		echo '<table class="detailsTable"><tbody>';

	foreach ( $form_values as $key => $value ) {
		// Skip if label missing or Line Items field
		if ( ! isset( $value['label'] ) || 'Line Items' === $value['label'] ) {
			continue;
		}

		echo '<tr>';

		// Label
		$label = ucfirst( str_replace( '_', ' ', $value['label'] ) );
		echo '<th>' . esc_html( $label ) . '</th>';

		// Value
		if ( in_array( $value['label'], array( 'Uploaded File URL', 'Uploaded URL' ), true ) && isset( $value['value'] ) ) {
			$raw_urls        = explode( ',', (string) $value['value'] );
			$uploaded_output = array();

			foreach ( $raw_urls as $raw_url ) {
				$file_url = trim( $raw_url );
				if ( '' === $file_url ) {
					continue;
				}

				$file_url_escaped = esc_url( $file_url );
				if ( '' === $file_url_escaped ) {
					continue;
				}

				$file_type = wp_check_filetype( $file_url_escaped );
				$is_image  = isset( $file_type['type'] ) && 0 === strpos( $file_type['type'], 'image/' );

				if ( $is_image ) {
					$uploaded_output[] = '<a target="_blank" rel="noopener noreferrer" href="' . $file_url_escaped . '" style="display:inline-block;margin-right:8px;margin-bottom:8px;"><img src="' . $file_url_escaped . '" alt="' . esc_attr__( 'Uploaded file preview', 'wp_easy_pay' ) . '" width="100" height="100" style="width:100px;height:100px;object-fit:cover;border:1px solid #ddd;border-radius:4px;" /></a>';
				} else {
					$uploaded_output[] = '<a target="_blank" rel="noopener noreferrer" href="' . $file_url_escaped . '">' . esc_html( $file_url ) . '</a>';
				}
			}

			if ( ! empty( $uploaded_output ) ) {
				echo '<td>' . wp_kses_post( implode( '', $uploaded_output ) ) . '</td>';
			} else {
				echo '<td>-</td>';
			}
		} elseif ( isset( $value['value'] ) ) {
			echo '<td>' . esc_html( $value['value'] ) . '</td>';
		} else {
			echo '<td>-</td>';
		}

		echo '</tr>';
	}

		echo '</tbody></table>';
}
?>

		

	</div>


	










	</div>
