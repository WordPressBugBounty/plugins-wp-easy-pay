<?php
/**
 * Filename: amount-custom.php
 * Description: custom amount frontend.
 *
 * @package WP_Easy_Pay
 */

?>
<?php

$wpep_square_payment_box_1 = get_post_meta( $wpep_current_form_id, 'wpep_square_payment_box_1', true );
$wpep_square_payment_box_2 = get_post_meta( $wpep_current_form_id, 'wpep_square_payment_box_2', true );
$wpep_square_payment_box_3 = get_post_meta( $wpep_current_form_id, 'wpep_square_payment_box_3', true );
$wpep_square_payment_box_4 = get_post_meta( $wpep_current_form_id, 'wpep_square_payment_box_4', true );
$default_price_selected    = ! empty( get_post_meta( $wpep_current_form_id, 'defaultPriceSelected', true ) ) ? get_post_meta( $wpep_current_form_id, 'defaultPriceSelected', true ) : '';

$wpep_square_user_defined_amount = get_post_meta( $wpep_current_form_id, 'wpep_square_user_defined_amount', true );
$wpep_square_payment_min         = get_post_meta( $wpep_current_form_id, 'wpep_square_payment_min', true );
$wpep_square_payment_max         = get_post_meta( $wpep_current_form_id, 'wpep_square_payment_max', true );


$form_payment_global = get_post_meta( $wpep_current_form_id, 'wpep_individual_form_global', true );

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

<div class="form-group cusPaymentSec">

	<?php

	if ( 'on' !== $wpep_square_user_defined_amount && '' === $wpep_square_payment_box_1 && '' === $wpep_square_payment_box_2 && '' === $wpep_square_payment_box_3 && '' === $wpep_square_payment_box_4 ) {

		if ( '' === $wpep_square_payment_min && '' === $wpep_square_payment_min ) {

			$wpep_square_payment_min = 1;
			$wpep_square_payment_max = 50000;

		}
	} elseif ( '' !== $wpep_square_payment_box_1 || '' !== $wpep_square_payment_box_2 || '' !== $wpep_square_payment_box_3 || '' !== $wpep_square_payment_box_4 ) {
		echo '<label class="selectAmount">*Select Amount</label>';
	}

	?>


	<div class="paymentSelect" style="<?php echo ( '' === $wpep_square_payment_box_1 && '' === $wpep_square_payment_box_2 && '' === $wpep_square_payment_box_3 && '' === $wpep_square_payment_box_4 ) ? 'display: none;' : ''; ?>">
		<?php if ( '' !== $wpep_square_payment_box_1 ) { ?>
			<div class="selection">
				<input id="doller1_<?php echo esc_attr( $wpep_current_form_id ); ?>" name="doller"
						type="radio" 
						<?php
						if ( 'dollar1' === $default_price_selected || '' === $default_price_selected ) :
							echo esc_html( 'data-default="true" checked' );
endif;
						?>
						/>
				<label for="doller1_<?php echo esc_attr( $wpep_current_form_id ); ?>" class="paynow">
					<?php if ( 'symbol' === $currency_symbol_type ) { ?>
						<?php echo esc_html( $square_currency . $wpep_square_payment_box_1 ); ?>
					<?php } else { ?>
						<?php echo esc_html( $wpep_square_payment_box_1 . ' ' . $square_currency ); ?>
					<?php } ?>
				</label>
			</div>
		<?php } ?>
		<?php if ( '' !== $wpep_square_payment_box_2 ) { ?>
			<div class="selection">
				<input id="doller2_<?php echo esc_attr( $wpep_current_form_id ); ?>" name="doller"
						type="radio" 
						<?php
						if ( 'dollar2' === $default_price_selected ) :
							echo esc_html( 'data-default="true" checked' );
endif;
						?>
						/>
				<label for="doller2_<?php echo esc_attr( $wpep_current_form_id ); ?>" class="paynow">
					<?php if ( 'symbol' === $currency_symbol_type ) { ?>
						<?php echo esc_html( $square_currency . $wpep_square_payment_box_2 ); ?>
					<?php } else { ?>
						<?php echo esc_html( $wpep_square_payment_box_2 . ' ' . $square_currency ); ?>
					<?php } ?>
				</label>
			</div>
		<?php } ?>
		<?php if ( '' !== $wpep_square_payment_box_3 ) { ?>
			<div class="selection">
				<input id="doller5_<?php echo esc_attr( $wpep_current_form_id ); ?>" name="doller"
						type="radio" 
						<?php
						if ( 'dollar3' === $default_price_selected ) :
							echo esc_html( 'data-default="true" checked' );
endif;
						?>
						/>
				<label for="doller5_<?php echo esc_attr( $wpep_current_form_id ); ?>" class="paynow">
					<?php if ( 'symbol' === $currency_symbol_type ) { ?>
						<?php echo esc_html( $square_currency . $wpep_square_payment_box_3 ); ?>
					<?php } else { ?>
						<?php echo esc_html( $wpep_square_payment_box_3 . ' ' . $square_currency ); ?>
					<?php } ?>
				</label>
			</div>
		<?php } ?>
		<?php if ( '' !== $wpep_square_payment_box_4 ) { ?>
			<div class="selection">
				<input id="doller10_<?php echo esc_attr( $wpep_current_form_id ); ?>" name="doller"
						type="radio" 
						<?php
						if ( 'dollar4' === $default_price_selected ) :
							echo esc_attr( 'data-default="true" checked' );
endif;
						?>
						/>
				<label for="doller10_<?php echo esc_attr( $wpep_current_form_id ); ?>" class="paynow">
					<?php if ( 'symbol' === $currency_symbol_type ) { ?>
						<?php echo esc_html( $square_currency . $wpep_square_payment_box_4 ); ?>
					<?php } else { ?>
						<?php echo esc_html( $wpep_square_payment_box_4 . ' ' . $square_currency ); ?>
					<?php } ?>
				</label>
			</div>
		<?php } ?>

		<?php
		if ( 'on' === $wpep_square_user_defined_amount ) {


			?>

			<div class="selection">
				<input id="doller3_<?php echo esc_attr( $wpep_current_form_id ); ?>" name="doller"
						min="<?php echo esc_attr( $wpep_square_payment_min ); ?>" max="<?php echo esc_attr( $wpep_square_payment_max ); ?>"
						class="otherpayment" type="radio"/>
				<label for="doller3_<?php echo esc_attr( $wpep_current_form_id ); ?>">Other</label>
			</div>

		<?php } ?>


	</div>
<?php
if ( 'on' === $wpep_square_user_defined_amount ) {

	?>
	<div class="selection showPayment" 
	<?php echo ( '' === $wpep_square_payment_box_1 && '' === $wpep_square_payment_box_2 && '' === $wpep_square_payment_box_3 && '' === $wpep_square_payment_box_4 ) ? 'style="display: block;"' : 'style="display: none;"'; ?>
	>
		<div class="otherpInput">

			<input class="form-control text-center customPayment otherPayment"
					Placeholder="Enter your amount <?php echo esc_attr( $wpep_square_payment_min ); ?> - <?php echo esc_attr( $wpep_square_payment_max ); ?>"
					name="somename" min="<?php echo esc_attr( $wpep_square_payment_min ); ?>"
					max="<?php echo esc_attr( $wpep_square_payment_max ); ?>" type="number"/>


		</div>
	</div>
	<?php
}
?>
</div>
