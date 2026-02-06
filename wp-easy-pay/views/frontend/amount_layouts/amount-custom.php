<?php
/**
 * Filename: amount-custom.php
 * Description: custom amount frontend.
 *
 * @package WP_Easy_Pay
 */

$wpep_square_payment_box_1 = get_post_meta( $wpep_current_form_id, 'wpep_square_payment_box_1', true );
$wpep_square_payment_box_2 = get_post_meta( $wpep_current_form_id, 'wpep_square_payment_box_2', true );
$wpep_square_payment_box_3 = get_post_meta( $wpep_current_form_id, 'wpep_square_payment_box_3', true );
$wpep_square_payment_box_4 = get_post_meta( $wpep_current_form_id, 'wpep_square_payment_box_4', true );
$default_price_selected    = ! empty( get_post_meta( $wpep_current_form_id, 'defaultPriceSelected', true ) ) ? get_post_meta( $wpep_current_form_id, 'defaultPriceSelected', true ) : '';

$wpep_square_user_defined_amount = get_post_meta( $wpep_current_form_id, 'wpep_square_user_defined_amount', true );
$wpep_square_payment_min         = get_post_meta( $wpep_current_form_id, 'wpep_square_payment_min', true );
$wpep_square_payment_max         = get_post_meta( $wpep_current_form_id, 'wpep_square_payment_max', true );
$square_currency                 = wpep_get_form_currency( $wpep_current_form_id );
$currency_symbol_type            = ! empty( get_post_meta( $wpep_current_form_id, 'currencySymbolType', true ) ) ? get_post_meta( $wpep_current_form_id, 'currencySymbolType', true ) : 'code';

?>

<div class="form-group cusPaymentSec">

	<?php

	$show_other = false;
	if ( 'on' !== $wpep_square_user_defined_amount && '' === $wpep_square_payment_box_1 && '' === $wpep_square_payment_box_2 && '' === $wpep_square_payment_box_3 && '' === $wpep_square_payment_box_4 ) {
		$show_other = true;

		if ( '' === $wpep_square_payment_min && '' === $wpep_square_payment_min ) {

			$wpep_square_payment_min = 1;
			$wpep_square_payment_max = 50000;

		}
	} else {

		echo '<label class="selectAmount">*Select Amount</label>';
	}

	?>


	<div class="paymentSelect">
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
				<label for="doller1_<?php echo esc_attr( $wpep_current_form_id ); ?>" class="paynow payamount-<?php echo esc_attr( $wpep_current_form_id ); ?>">
					<?php
					if ( 'symbol' === $currency_symbol_type ) {
							echo esc_html( $square_currency . $wpep_square_payment_box_1 );
					} elseif ( 'code' === $currency_symbol_type ) {
						echo esc_html( $wpep_square_payment_box_1 . ' ' . $square_currency );
					} else {
						echo esc_html( $wpep_square_payment_box_1 );
					}
					?>
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
				<label for="doller2_<?php echo esc_attr( $wpep_current_form_id ); ?>" class="paynow payamount-<?php echo esc_attr( $wpep_current_form_id ); ?>">
					<?php
					if ( 'symbol' === $currency_symbol_type ) {
							echo esc_html( $square_currency . $wpep_square_payment_box_2 );
					} elseif ( 'code' === $currency_symbol_type ) {
						echo esc_html( $wpep_square_payment_box_2 . ' ' . $square_currency );
					} else {
						echo esc_html( $wpep_square_payment_box_2 );
					}
					?>
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
				<label for="doller5_<?php echo esc_attr( $wpep_current_form_id ); ?>" class="paynow payamount-<?php echo esc_attr( $wpep_current_form_id ); ?>">
					<?php
					if ( 'symbol' === $currency_symbol_type ) {
							echo esc_html( $square_currency . $wpep_square_payment_box_3 );
					} elseif ( 'code' === $currency_symbol_type ) {
						echo esc_html( $wpep_square_payment_box_3 . ' ' . $square_currency );
					} else {
						echo esc_html( $wpep_square_payment_box_3 );
					}
					?>
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
				<label for="doller10_<?php echo esc_attr( $wpep_current_form_id ); ?>" class="paynow payamount-<?php echo esc_attr( $wpep_current_form_id ); ?>">
					<?php
					if ( 'symbol' === $currency_symbol_type ) {
							echo esc_html( $square_currency . $wpep_square_payment_box_4 );
					} elseif ( 'code' === $currency_symbol_type ) {
						echo esc_html( $wpep_square_payment_box_4 . ' ' . $square_currency );
					} else {
						echo esc_html( $wpep_square_payment_box_4 );
					}
					?>
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

	<div class="selection showPayment" 
	<?php
	if ( ! $show_other ) {
		echo 'style="display: none;"';
	}
	?>
	>
		<div class="otherpInput">

			<input class="form-control text-center customPayment otherPayment other-<?php echo esc_attr( $wpep_current_form_id ); ?>"
					Placeholder="Enter your amount <?php echo esc_attr( $wpep_square_payment_min ); ?> - <?php echo esc_attr( $wpep_square_payment_max ); ?>"
					name="somename" min="<?php echo esc_attr( $wpep_square_payment_min ); ?>"
					max="<?php echo esc_attr( $wpep_square_payment_max ); ?>" type="number"/>
					
			<span class="valueCheckWpep"></span>

		</div>
	</div>
</div>
