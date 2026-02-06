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
		<h3>Payment Details</h3>
		<table>
		<tbody>

		<?php
		if ( wepp_fs()->is__premium_only() ) {
			if ( 'Failed' !== $transaction_status ) {
				?>
		<tr>
			<th>Refund Now</th>
			<td>
				<?php
				if ( false !== $wpep_refund_id && isset( $wpep_refund_id ) && ! empty( $wpep_refund_id ) && true === $full_refunded ) {

					echo '<button disabled> Refunded </button>';

				} else {

					$available_refund = $charge_amount_no_currency - $refund_amount;
					?>
				<p> <strong><?php echo esc_html( $available_refund ); ?> <span><?php echo esc_html( $charge_currency ); ?></span> </strong> available to refund </p>
				<p> <strong><?php echo esc_html( $refund_amount ); ?><span> <?php echo esc_html( $charge_currency ); ?></span></strong> has been refunded </p>
				<input type="text" id="wpep_refund_amount" placeholder="Refund Amount" pattern="[0-9]+" 
					<?php
					if ( 0 === $available_refund ) {
						echo 'disabled';}
					?>
				/>
				<p> <button id="give_refund_button" class="give_refund_button" data-postid="<?php echo esc_attr( $current_post_id ); ?>" data-amount="<?php echo esc_attr( $charge_amount ); ?>" data-transactionid="<?php echo esc_attr( $transaction_id ); ?>" 
					<?php
					if ( 0 === $available_refund ) {
						echo 'disabled';}
					?>
				> Refund <span id="wpep_refund_number"> 0.00 </span> <span> <?php echo esc_html( $charge_currency ); ?> </span></button>		</p>
					<?php
				}
				?>
			</td>
		</tr>
				<?php
			}
		}
		?>
			<tr>
			<th>Payment type</th>
			<td><?php echo esc_html( $transaction_type ); ?></td>
			</tr>
			<tr>
			<th>Payment Method</th>
			<td><?php echo esc_html( $transaction_source ); ?></td>
			</tr>
			<tr>
			<th>Transaction ID</th>
			<td><?php echo esc_html( get_the_title() ); ?></td>
			</tr>
	  
			<tr>
			<th>Payments Amount</th>
			<td><?php echo esc_html( $charge_amount ); ?></td>
			</tr>
			<?php if ( isset( $charge_signup_amount ) && 0 !== $charge_signup_amount ) { ?>
			<tr>
			<th>Signup Fees</th>
			<td><?php echo esc_html( $charge_signup_amount ); ?> <span><?php echo esc_html( $charge_currency ); ?></span></td>
			</tr>
				<?php
			}
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
					<tr>
						<th><?php echo esc_html( $fees_name ); ?></th>
						<td><?php echo esc_html( $fees_value ) . ' <small>(' . esc_html( $charge_type ) . ')</small>'; ?></td>
					</tr>
						<?php
					}
				}
			}
			if ( wepp_fs()->is__premium_only() ) {
				?>
			<tr>
			<th>Discount</th>
			<td><?php echo esc_html( $discount_amount ); ?></td>
			</tr>
		<?php } ?>
			<tr>
			<th>Payments Status</th>
			<td><?php echo esc_html( $transaction_status ); ?></td>
			</tr>
	  

			<?php
			if ( isset( $wpep_transaction_error ) && ! empty( $wpep_transaction_error ) ) {
				?>
			<tr>
			<th>Payment Error</th>
			<td><?php echo esc_html( $wpep_transaction_error ); ?></td>
			</tr>
				<?php
			}
			?>

			<tr>
			<th>WPEP Form</th>
			<td><a  target="_blank" href="<?php echo esc_url( get_edit_post_link( $form_id ) ); ?>"> click here </a></td>
			</tr>

			<tr>
			<th>User Name</th>
			<td><?php echo esc_html( $firstname . ' ' . $lastname ); ?></td>
			</tr>
		  
			<tr>
			<th>User Email</th>
			<td><?php echo esc_html( $email ); ?></td>
			</tr>
		<?php if ( wepp_fs()->is__premium_only() ) { ?>
			<tr>
				<th>Refund ID</th>
				<td><?php echo esc_html( $wpep_refund_id ); ?></td>
			</tr>
		<?php } ?>
		</tbody>
		</table>
	</div>


	<?php
	if ( wepp_fs()->is__premium_only() ) {
		if ( isset( $form_values ) && ! empty( $form_values ) ) {

			echo '<div class="reportDetails">
		<h3>Form Field</h3>
		<table>
		  <tbody>';

			foreach ( $form_values as $key => $value ) {

				echo '<tr>';
				if ( isset( $value['label'] ) ) {

					$label = esc_html( ucfirst( str_replace( '_', ' ', $value['label'] ) ) );

					echo '<th scope="col">' . esc_html( $label ) . '</th>';

				}

				if ( isset( $value['label'] ) ) {

					if ( 'Uploaded File URL' === $value['label'] ) {
						$uploaded_file_link = "<a target='_blank' href='" . $value['value'] . "'> Click to see uploaded file </a>";
						echo '<td scope="col">' . esc_html( $uploaded_file_link ) . '</td>';
					} elseif ( 'Line Items' === $value['label'] ) {

						$json = json_decode( $value['value'], true );
						echo '<td scope="col">';
						echo '<ol>';
						foreach ( $json as $key => $value ) {
							echo '<li class="prodData"> Label: ' . esc_html( $value['label'] ) . '<br> Quantity: ' . esc_html( $value['quantity'] ) . '<br> Price: ' . esc_html( $value['price'] ) . '<br> Cost: ' . esc_html( $value['cost'] ) . '</div>';
						}
						echo '</ol>';
						echo '</td>';
					} elseif ( isset( $value['value'] ) ) {
							echo '<td scope="col">' . esc_html( $value['value'] ) . '</td>';
					}
				}


				echo '</tr>';


			}

			echo '</tbody>
		</table>
	  </div>';
		}
	}
	?>
	</div>
