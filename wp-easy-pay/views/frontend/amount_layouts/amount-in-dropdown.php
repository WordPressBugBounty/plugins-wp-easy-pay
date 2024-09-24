<?php
/**
 * Filename: amount-in-dropdown.php
 * Description: amount in dropdown frontend.
 *
 * @package WP_Easy_Pay
 */

?>
<?php
$wpep_dropdown_amounts = get_post_meta( $wpep_current_form_id, 'wpep_dropdown_amounts', true );

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

	if ( 'USD' === $square_currency ) {
		$square_currency = '$';
	}

	if ( 'CAD' === $square_currency ) {
		$square_currency = 'C$';
	}

	if ( 'AUD' === $square_currency ) {
		$square_currency = 'A$';
	}

	if ( 'JPY' === $square_currency ) {
		$square_currency = '¥';
	}

	if ( 'GBP' === $square_currency ) {
		$square_currency = '£';
	}

	if ( 'EUR' === $square_currency ) {
		$square_currency = '€';
	}
}

?>

<label class="selectAmount">*Select Amount</label>

<div class="form-group cusPaymentSec paydlayout">
	<?php if ( ! empty( $wpep_dropdown_amounts ) ) { ?>
		<select class="form-control custom-select paynowDrop" name="" id="">
			<option value="" selected="selected">Select...</option>

			<?php
			foreach ( $wpep_dropdown_amounts as $key => $amount ) {
				++$key;

				if ( $key === $price_selected ) {
					$checked = 'selected="selected"';
				} else {
					$checked = '';
				}

				if ( empty( $amount['label'] ) ) {
					$amount['label'] = $amount['amount'];
				}

				if ( 'symbol' === $currency_symbol_type ) {
					echo '<option value="' . esc_html( $square_currency . $amount['amount'] ) . '" ' . esc_attr( $checked ) . '>' . esc_html( $amount['label'] ) . '</option>';
				} else {
					echo '<option value="' . esc_html( $amount['amount'] . ' ' . $square_currency ) . '" ' . esc_attr( $checked ) . '>' . esc_html( $amount['label'] ) . '</option>';
				}
			}
			?>
		</select>
	<?php } else { ?>
		<div class="wpep-alert wpep-alert-danger wpep-alert-dismissable">Please set the amount from backend</div>
	<?php } ?>
</div>
