<?php
/**
 * Filename: amount-in-radio.php
 * Description: amount in radio frontend.
 *
 * @package WP_Easy_Pay
 */

?>
<?php
$wpep_radio_amounts = get_post_meta( $wpep_current_form_id, 'wpep_radio_amounts', true );

$form_payment_global = get_post_meta( $wpep_current_form_id, 'wpep_individual_form_global', true );

$price_selected = ! empty( get_post_meta( $wpep_current_form_id, 'PriceSelected', true ) ) ? get_post_meta( $wpep_current_form_id, 'PriceSelected', true ) : '1';

if ( 'on' === $form_payment_global ) {

	$global_payment_mode = get_option( 'wpep_square_payment_mode_global', true );

	if ( 'on' === $global_payment_mode ) {
		/* If Global Form Live Mode */
		$wpep_square_currency = get_option( 'wpep_square_currency_new' );
	}

	if ( 'on' !== $global_payment_mode ) {
		/* If Global Form Test Mode */
		$wpep_square_currency = get_option( 'wpep_square_currency_test' );
	}
}

if ( 'on' !== $form_payment_global ) {

	$individual_payment_mode = get_post_meta( $wpep_current_form_id, 'wpep_payment_mode', true );

	if ( 'on' === $individual_payment_mode ) {

		/* If Individual Form Live Mode */
		$square_currency = get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_new', true );
	}

	if ( 'on' !== $individual_payment_mode ) {

		/* If Individual Form Test Mode */
		$square_currency = get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_test', true );
	}
}

$currency_symbol_type = ! empty( get_post_meta( $wpep_current_form_id, 'currencySymbolType', true ) ) ? get_post_meta( $wpep_current_form_id, 'currencySymbolType', true ) : 'code';

if ( 'symbol' === $currency_symbol_type ) {

	if ( 'USD' === $square_currency ) :
		$square_currency = '$';
	endif;

	if ( 'CAD' === $square_currency ) :
		$square_currency = 'C$';
	endif;

	if ( 'AUD' === $square_currency ) :
		$square_currency = 'A$';
	endif;

	if ( 'JPY' === $square_currency ) :
		$square_currency = '¥';
	endif;

	if ( 'GBP' === $square_currency ) :
		$square_currency = '£';
	endif;

	if ( 'EUR' === $square_currency ) :
		$square_currency = '€';
	endif;

}

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

			if ( $count === $price_selected ) {
				$checked = 'checked';
			} else {
				$checked = '';
			}

			if ( 'symbol' === $currency_symbol_type ) {
				echo '<div class="wizard-form-radio">';
				echo '<input class="radio_amount" data-label="' . esc_html( $amount['label'] ) . '" name="radio-name" id="subsp-' . esc_attr( $wpep_current_form_id ) . '-' . esc_attr( $key ) . '" type="radio" value="' . esc_html( $square_currency . $amount['amount'] ) . '" ' . esc_attr( $checked ) . '">';
				echo '<label for="subsp-' . esc_attr( $wpep_current_form_id ) . '-' . esc_attr( $key ) . '" class=""> ' . esc_html( $amount['label'] ) . '</label>';
				echo '</div>';
			} else {
				echo '<div class="wizard-form-radio">';
				echo '<input class="radio_amount" data-label="' . esc_html( $amount['label'] ) . '" name="radio-name" id="subsp-' . esc_attr( $wpep_current_form_id ) . '-' . esc_attr( $key ) . '" type="radio" value="' . esc_attr( $amount['amount'] ) . ' ' . esc_attr( $square_currency ) . '" ' . esc_attr( $checked ) . '>';
				echo '<label for="subsp-' . esc_attr( $wpep_current_form_id ) . '-' . esc_attr( $key ) . '" class=""> ' . esc_html( $amount['label'] ) . '</label>';
				echo '</div>';
			}
		}
		?>
	<?php } else { ?>
		<div class="wpep-alert wpep-alert-danger wpep-alert-dismissable">Please set the amount from backend</div>
	<?php } ?>

</div>
