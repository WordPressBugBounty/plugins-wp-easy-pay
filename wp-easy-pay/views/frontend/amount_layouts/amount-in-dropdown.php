<?php
/**
 * Filename: amount-in-dropdown.php
 * Description: amount in dropdown frontend.
 *
 * @package WP_Easy_Pay
 */

$wpep_dropdown_amounts = get_post_meta( $wpep_current_form_id, 'wpep_dropdown_amounts', true );
$form_payment_global   = get_post_meta( $wpep_current_form_id, 'wpep_individual_form_global', true );
$price_selected        = ! empty( get_post_meta( $wpep_current_form_id, 'PriceSelected', true ) ) ? get_post_meta( $wpep_current_form_id, 'PriceSelected', true ) : '1';

?>

<label class="selectAmount">*Select Amount</label>

<div class="form-group cusPaymentSec paydlayout">
	<?php if ( ! empty( $wpep_dropdown_amounts ) ) { ?>
		<select class="form-control custom-select paynowDrop" name="" id="">
			<option value="" selected="selected">Select...</option>

			<?php
			foreach ( $wpep_dropdown_amounts as $key => $amount ) {
				++$key;
				if ( intval( $price_selected ) === $key ) {
					$checked = 'selected="selected"';
				} else {
					$checked = '';
				}

				if ( empty( $amount['label'] ) ) {
					$amount['label'] = $amount['amount'];
				}

				echo '<option value="' . esc_html( $amount['amount'] ) . '" ' . esc_attr( $checked ) . '>' . esc_html( $amount['label'] ) . '</option>';
			}
			?>
		</select>
	<?php } else { ?>
		<div class="wpep-alert wpep-alert-danger wpep-alert-dismissable">Please set the amount from backend</div>
	<?php } ?>
</div>
