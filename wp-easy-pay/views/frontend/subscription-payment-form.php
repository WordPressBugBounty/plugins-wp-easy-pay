<?php
/**
 * Filename: subscription-payment-form.php
 * Description: subscription payment form into frontend.
 *
 * @package WP_Easy_Pay
 */

?>
<?php
require_once WPEP_ROOT_PATH . 'modules/payments/square-authorization.php';
require_once WPEP_ROOT_PATH . 'modules/payments/square-payments.php';

?>
<input type="hidden" class="g-recaptcha-response" name="g-recaptcha-response"/>
<fieldset class="wizard-fieldset show">
	<div class="s_ft noMulti">
		<h2>Basic Info</h2>
	</div>

	<h5 class="noSingle">Personal Information</h5>
	<div id="wpep_personal_information" class="fieldMainWrapper <?= basename(__FILE__, '.p') ?>">

		<?php
		foreach ( $open_form_json as $value ) {

			if ( 'checkbox-group' === $value->type ) {

				wpep_print_checkbox_group( $value );

			} elseif ( 'radio-group' === $value->type ) {

				wpep_print_radio_group( $value );

			} elseif ( 'select' === $value->type ) {

				wpep_print_select_dropdown( $value );

			} elseif ( 'textarea' === $value->type ) {

				wpep_print_textarea( $value );

			} elseif ( 'file' === $value->type ) {

				wpep_print_file_upload( $value );

			} else {

				$modified_type = $value->type;
				$if_required   = " <span class='fieldReq'>*</span>";

				if ( isset( $value->subtype ) ) {
					$modified_type = $value->subtype;
				}
				echo "<div class='" . esc_attr( $modified_type ) . '-field form-group ' . ( ( isset( $value->required ) ) ? 'wpep-required' : '' ) . "'>";
				echo "<label class='wizard-form-text-label' data-label-show='" . esc_attr( $value->hide_label ) . "'> " . ( ( isset( $value->label ) ) ? esc_html( $value->label ) : '' ) . ( ( isset( $value->required ) ) ? esc_html( $if_required ) : '' ) . ' </label>';
				echo "<input type='" . esc_attr( $modified_type ) . "' maxlength='" . esc_attr( isset( $value->maxlength ) ? $value->maxlength : '' ) . "' min='" . esc_attr( isset( $value->min ) ? $value->min : '' ) . "' max='" . esc_attr( isset( $value->max ) ? $value->max : '' ) . "' step='" . esc_attr( isset( $value->step ) ? $value->step : '' ) . "' class='" . esc_attr( ( isset( $value->class_name ) ) ? $value->class_name : '' ) . "' data-label='" . esc_attr( ( isset( $value->label ) ) ? $value->label : '' ) . "' name='" . esc_attr( $value->name ) . "' required='" . esc_attr( ( isset( $value->required ) ) ? 'true' : 'false' ) . "' />";
				if ( isset( $value->description ) && '' !== $value->description ) {
					echo "<span class='wpep-help-text'>" . esc_html( $value->description ) . '</span>';
				}
				echo '</div>';

			}
		}
		?>
		<input type="hidden" id="wpep_payment_form_type_<?php echo esc_attr( $wpep_current_form_id ); ?>" value="subscription"/>

		<?php

		if ( isset( $enable_quantity ) ) {
			if ( 'on' === $enable_quantity ) {
				?>
				<div class="value-button" id="decrease"
					onclick="wpep_decreaseValue(<?php echo esc_attr( $wpep_current_form_id ); ?>)" value="Decrease Value">-
				</div>
				<input type="number" class="form-control" id="wpep_quantity_<?php echo esc_attr( $wpep_current_form_id ); ?>"
						name="wpep_quantity" value="0"/>
				<div class="value-button" id="increase"
					onclick="wpep_increaseValue(<?php echo esc_attr( $wpep_current_form_id ); ?>)" value="Increase Value">+
				</div>

				<?php
			}
		}
		?>

	</div>


	<div class="btnGroup btnGroupFirst noSingle">
		<!-- <a href="javascript:;" class="form-wizard-previous-btn float-left">Previous</a> -->
		<a href="javascript:;" class="form-wizard-next-btn float-right">Next</a>
	</div>
</fieldset>


<fieldset class="wizard-fieldset">
	<div class="s_ft noMulti">
	<?php
	if ( 'payment_tabular' !== $wpep_amount_layout_type ) {

		echo '<h2>Payment</h2>';

	}
	?>
	</div>

	<h5 class="noSingle">Payment Information</h5>
	<?php

	if ( 'payment_drop' === $wpep_amount_layout_type ) {
		require WPEP_ROOT_PATH . 'views/frontend/amount_layouts/amount-in-dropdown.php';

		if ( 'symbol' === $currency_symbol_type ) {
			$show_default_amount = $square_currency . $wpep_dropdown_amounts[ $price_selected - 1 ]['amount'];
		} else {
			$show_default_amount = $wpep_dropdown_amounts[ $price_selected - 1 ]['amount'] . ' ' . $square_currency;
		}
	}

	if ( 'payment_radio' === $wpep_amount_layout_type ) {
		require WPEP_ROOT_PATH . 'views/frontend/amount_layouts/amount-in-radio.php';

		if ( 'symbol' === $currency_symbol_type ) {
			$show_default_amount = $square_currency . $wpep_radio_amounts[ $price_selected - 1 ]['amount'];
		} else {
			$show_default_amount = $wpep_radio_amounts[ $price_selected - 1 ]['amount'] . ' ' . $square_currency;
		}
	}

	if ( 'payment_custom' === $wpep_amount_layout_type ) {

		require WPEP_ROOT_PATH . 'views/frontend/amount_layouts/amount-custom.php';

		if ( 'dollar1' === $default_price_selected || '' === $default_price_selected ) {
			if ( 'symbol' === $currency_symbol_type ) {
				$show_default_amount = $square_currency . $wpep_square_payment_box_1;
			} else {
				$show_default_amount = $wpep_square_payment_box_1 . ' ' . $square_currency;
			}
		}

		if ( 'dollar2' === $default_price_selected ) {
			if ( 'symbol' === $currency_symbol_type ) {
				$show_default_amount = $square_currency . $wpep_square_payment_box_2;
			} else {
				$show_default_amount = $wpep_square_payment_box_2 . ' ' . $square_currency;
			}
		}

		if ( 'dollar3' === $default_price_selected ) {
			if ( 'symbol' === $currency_symbol_type ) {
				$show_default_amount = $square_currency . $wpep_square_payment_box_3;
			} else {
				$show_default_amount = $wpep_square_payment_box_3 . ' ' . $square_currency;
			}
		}

		if ( 'dollar4' === $default_price_selected ) {
			if ( 'symbol' === $currency_symbol_type ) {
				$show_default_amount = $square_currency . $wpep_square_payment_box_4;
			} else {
				$show_default_amount = $wpep_square_payment_box_4 . ' ' . $square_currency;
			}
		}
	}

	if ( 'payment_tabular' === $wpep_amount_layout_type ) {
		require WPEP_ROOT_PATH . 'views/frontend/amount_layouts/amount-in-tabular.php';
	}


	require WPEP_ROOT_PATH . 'views/frontend/payment-methods.php';

	$wpep_btn_label = get_post_meta( $wpep_current_form_id, 'wpep_payment_btn_label', true );
	if ( isset( $wpep_btn_label ) && ! empty( $wpep_btn_label ) ) {
		$pay_button_label = $wpep_btn_label;
	} else {

		$pay_button_label = 'Pay';
	}
	?>

	<div class="btnGroup ifSingle">
		<a href="javascript:;" class="form-wizard-previous-btn float-left noSingle">Previous</a>

		<div style="display:flex">
			<?php
			$sub_total_amount        = isset( $show_default_amount ) ? floatval( str_replace( $square_currency, '', $show_default_amount ) ) : 0.00;
			$total_amount            = $sub_total_amount;
			$currency                = isset( $square_currency ) ? $square_currency : '$';
			$fees_data               = get_post_meta( $wpep_current_form_id, 'fees_data' );
			$wpep_enable_signup_fees = get_post_meta( $wpep_current_form_id, 'wpep_enable_signup_fees', true );

			if ( ( ! empty( $fees_data[0]['check'] ) && in_array( 'yes', $fees_data[0]['check'], true ) ) || 'yes' === $wpep_enable_signup_fees ) {
				?>
				<div class="wpep-payment-details-wrapper">
					<a href="#" class="wpep-open-details" data-id="<?php echo esc_attr( $wpep_current_form_id ); ?>"><?php echo esc_html__( 'Payment details', 'wp_easy_pay' ); ?></a>
					<div class="wpep-payment-details" id="wpep-payment-details-<?php echo esc_attr( $wpep_current_form_id ); ?>">
					<ul>
						<li class="wpep-fee-subtotal">
							<span class="fee_name"><?php echo esc_html__( 'Subtotal', 'wp_easy_pay' ); ?></span>
							<span class="fee_value"><?php echo esc_attr( number_format( $sub_total_amount, 2 ) ) . ' ' . esc_attr( $currency ); ?></span>
						</li>
						<?php
						if ( 'yes' === $wpep_enable_signup_fees && ( 'subscription' === $payment_type || 'donation_recurring' === $payment_type ) ) :
							$wpep_signup_fees_value = get_post_meta( $wpep_current_form_id, 'wpep_signup_fees_amount', true );
							?>
						<li class="wpep-fee-onetime">
							<span class="fee_name"><?php echo esc_html__( 'Signup Fee', 'wp_easy_pay' ); ?></span>
							<span class="fee_value"><?php echo esc_attr( number_format( $wpep_signup_fees_value, 2 ) ) . ' ' . esc_attr( $currency ); ?></span>
							<input type='hidden' value='<?php echo esc_attr( number_format( $wpep_signup_fees_value, 2 ) ); ?>' name='wpep-signup-amount'> 
						</li>
							<?php
							$total_amount = $total_amount + $wpep_signup_fees_value;
						endif;
						if ( isset( $fees_data[0]['check'] ) ) :
							foreach ( $fees_data[0]['check'] as $key => $fees ) :
								if ( 'yes' === $fees ) :

									if ( 'percentage' === $fees_data[0]['type'][ $key ] ) {
										$fees_tax = $sub_total_amount * ( $fees_data[0]['value'][ $key ] / 100 );
									} else {
										$fees_tax = $fees_data[0]['value'][ $key ];
									}

									$total_amount = $total_amount + $fees_tax;
									?>
									<li>
										<span class="fee_name"><?php echo esc_html( $fees_data[0]['name'][ $key ] ); ?></span>
										<span class="fee_value"><?php echo esc_attr( number_format( $fees_tax, 2 ) ) . ' ' . esc_attr( $currency ); ?></span>
									</li>
									<?php
								endif;
							endforeach;
						endif;
						?>
						<li class="wpep-fee-total">
							<span class="fee_name"><?php echo esc_html__( 'Total', 'wp_easy_pay' ); ?></span>
							<span class="fee_value"><?php echo esc_attr( number_format( $total_amount, 2 ) ) . ' ' . esc_attr( $currency ); ?></span>
						</li>
					</ul>
					</div>
				</div>
				<?php
			}
			?>
			<button class="
			<?php
			if ( 'on' === $wpep_show_wizard ) :
				echo 'wpep-wizard-form-submit-btn';
			else :
				echo 'wpep-single-form-submit-btn';
			endif;
			?>
			<?php
			if ( ! isset( $show_default_amount ) ) :
				echo 'wpep-disabled';
				endif;
			?>
				float-right"><?php echo esc_html( $pay_button_label ); ?>
				<span>
					<b id="dosign" style="display: none;">$</b><small id="amount_display_<?php echo esc_attr( $wpep_current_form_id ); ?>"
																	class="display">
																	<?php
																	if ( ! empty( $fees_data[0]['check'] ) && in_array( 'yes', $fees_data[0]['check'], true ) && isset( $total_amount ) ) :
																		echo esc_html( number_format( $total_amount, 2 ) . ' ' . $square_currency );
																		elseif ( 'yes' === $wpep_enable_signup_fees ) :
																			echo esc_html( number_format( $total_amount, 2 ) . ' ' . $square_currency );
																		elseif ( isset( $show_default_amount ) ) :
																			echo esc_html( $show_default_amount );
																		endif;
																		?>
																		</small>
					<input type="hidden" name="wpep-selected-amount"
						value="
						<?php
						if ( ! empty( $fees_data[0]['check'] ) && in_array( 'yes', $fees_data[0]['check'], true ) && isset( $total_amount ) ) :
								echo esc_html( number_format( $total_amount, 2 ) );
							elseif ( 'yes' === $wpep_enable_signup_fees ) :
								echo esc_html( number_format( $total_amount, 2 ) );
							elseif ( isset( $show_default_amount ) ) :
								echo esc_html( $show_default_amount );
							endif;
							?>
							">
					<input type="hidden" name="one_unit_cost" id="one_unit_cost"
						value="
						<?php
						if ( isset( $show_default_amount ) ) :
							echo esc_html( $show_default_amount );
							endif;
						?>
							"/>
				</span>
			</button>
			<?php
			if ( ! empty( $fees_data[0]['check'] ) && in_array( 'yes', $fees_data[0]['check'], true ) && isset( $total_amount ) ) :
				$gross_total = number_format( $total_amount, 2 );
			elseif ( 'yes' === $wpep_enable_signup_fees ) :
				$gross_total = number_format( $total_amount, 2 );
			elseif ( isset( $show_default_amount ) ) :
				$gross_total = $show_default_amount;
			endif;
			?>
			<input type="hidden" name="gross_total" value="<?php echo esc_html( $gross_total ); ?>">
		</div>
	</div>


</fieldset>
<input type="hidden" id="wpep_form_currency" name="wpep_currency" value="<?php echo esc_attr( $square_currency ); ?>"/>
<fieldset class="wizard-fieldset orderCompleted blockIfSingle">
	<div class="confIfSingleTop">
		<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/order-done.svg' ); ?>" alt="Avatar" width="70" class="doneorder">
		<h2>Payment Successful</h2>
	</div>

	<?php if ( '' !== $wpep_payment_success_msg ) { ?>
		<p><?php echo esc_html( $wpep_payment_success_msg ); ?></p>
	<?php } else { ?>
		<p>Thank you for your purchase.</p>
	<?php } ?>

	<?php if ( '' !== $wpep_payment_success_url && '' !== $wpep_payment_success_label ) { ?>
		<a href="<?php echo esc_url( $wpep_payment_success_url ); ?>"
			class="form-wizard-submit float-right"><?php echo esc_html( $wpep_payment_success_label ); ?></a><br><br>
	<?php } ?>

	<small class="counterText">Page will be redirected in <span id="counter-<?php echo esc_attr( $wpep_current_form_id ); ?>">5</span>
		seconds.
	</small>
</fieldset>
