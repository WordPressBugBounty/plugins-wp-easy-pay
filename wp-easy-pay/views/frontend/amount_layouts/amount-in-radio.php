<?php
/**
 * Filename: amount-in-radio.php
 * Description: amount in radio frontend.
 *
 * @package WP_Easy_Pay
 */

$wpep_radio_amounts  = get_post_meta( $wpep_current_form_id, 'wpep_radio_amounts', true );
$form_payment_global = get_post_meta( $wpep_current_form_id, 'wpep_individual_form_global', true );
$price_selected      = ! empty( get_post_meta( $wpep_current_form_id, 'PriceSelected', true ) ) ? get_post_meta( $wpep_current_form_id, 'PriceSelected', true ) : '1';

?>

<div class="subscriptionPlan selectedPlan">
	<label class="cusLabel">*Select Amount</label>
	<?php

	if ( isset( $wpep_radio_amounts[0]['amount'] ) && ! empty( $wpep_radio_amounts[0]['amount'] ) ) {
		?>
		<?php
		foreach ( $wpep_radio_amounts as $key => $amount ) {

			$count = $key;
			++$count;

			if ( empty( $amount['label'] ) ) {
				$amount['label'] = $amount['amount'];
			}
			if ( intval( $price_selected ) === $count ) {
				$checked = 'checked';
			} else {
				$checked = '';
			}

			echo '<div class="wizard-form-radio">';
			echo '<input class="radio_amount" data-label="' . esc_html( $amount['label'] ) . '" name="radio-name" id="subsp-' . esc_attr( $wpep_current_form_id ) . '-' . esc_attr( $key ) . '" type="radio" value="' . esc_attr( $amount['amount'] ) . '" ' . esc_attr( $checked ) . '>';
			echo '<label for="subsp-' . esc_attr( $wpep_current_form_id ) . '-' . esc_attr( $key ) . '" class=""> ' . esc_html( $amount['label'] ) . '</label>';
			echo '</div>';
		}
		?>
	<?php } else { ?>
		<div class="wpep-alert wpep-alert-danger wpep-alert-dismissable">Please set the amount from backend</div>
	<?php } ?>

</div>
