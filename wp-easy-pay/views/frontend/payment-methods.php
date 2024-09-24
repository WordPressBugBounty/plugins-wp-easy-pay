<?php
/**
 * Filename: payment-methods.php
 * Description: payment method in frontend.
 *
 * @package WP_Easy_Pay
 */

?>
<?php

		$enable_coupon = get_post_meta( $wpep_current_form_id, 'enableCoupon', true );
		$payment_type  = get_post_meta( $wpep_current_form_id, 'wpep_square_payment_type', true );

if ( 'on' === $enable_coupon ) {
	// continue on Monday 8 feb 2021.
	require WPEP_ROOT_PATH . 'views/frontend/coupons.php';
}

		$wpep_individual_form_global = get_post_meta( $wpep_current_form_id, 'wpep_individual_form_global', true );
		$wpep_save_card              = get_post_meta( $wpep_current_form_id, 'wpep_save_card', true );

if ( 'on' === $wpep_individual_form_global ) {

	$live_mode = get_option( 'wpep_square_payment_mode_global', true );

	if ( 'on' === $live_mode ) {

		$cashapp   = get_option( 'wpep_square_cash_app', true );
		$ach_debit = get_option( 'wpep_square_ach_debit', true );
		$square_currency              = get_option( 'wpep_square_currency_new' );

	} else {

		$afterpay  = get_option( 'wpep_square_test_after_pay', true );
		$cashapp   = get_option( 'wpep_square_test_cash_app', true );
		$ach_debit = get_option( 'wpep_square_test_ach_debit', true );
		$square_currency              = get_option( 'wpep_square_currency_test' );

	}
} else {

	$live_mode = get_post_meta( $wpep_current_form_id, 'wpep_payment_mode', true );

	if ( 'on' === $live_mode ) {

		$afterpay  = get_post_meta( $wpep_current_form_id, 'wpep_square_after_pay_live', true );
		$cashapp   = get_post_meta( $wpep_current_form_id, 'wpep_square_cash_app_live', true );
		$ach_debit = get_post_meta( $wpep_current_form_id, 'wpep_square_ach_debit_live', true );
		$square_currency              = get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_new', true );

	} else {

		$afterpay  = get_post_meta( $wpep_current_form_id, 'wpep_square_after_pay', true );
		$cashapp   = get_post_meta( $wpep_current_form_id, 'wpep_square_cash_app', true );
		$ach_debit = get_post_meta( $wpep_current_form_id, 'wpep_square_ach_debit', true );
		$square_currency              = get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_test', true);
	}
}
if ( $cashapp == 'on' ) {
	$cashapp_available = is_available( 'cashapp', $square_currency );
}
if ( $afterpay == 'on' ) {
	$afterpay_available = is_available( 'afterpay', $square_currency );
}
if ( $ach_debit == 'on' ) {
	$ach_debit_available = is_available( 'ach_debit', $square_currency );
}
	
?>
	<div class="paymentsBlocks">
		<ul class="wpep_tabs">
			<li class="tab-link current" data-tab="creditCard">
				<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/creditcard.svg' ); ?>" alt="Avatar" width="45"
					class="doneorder" alt="Credit Card">
				<!-- <h4 class="">Credit Card</h4> -->
				<span>Payment Card</span>
			</li>
			<?php
			if ( 'on' === $afterpay && $afterpay_available == true && 'subscription' !== $payment_type && 'donation_recurring' !== $payment_type ) {
				?>
				<li class="tab-link" data-tab="afterpay">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/after-pay.png' ); ?>" alt="Avatar" width="45"
						class="doneorder" alt="Google Pay">
					<span>After Pay</span>
				</li>
				<?php
			}
			?>


			<?php
			if ( 'on' === $cashapp && $cashapp_available == true && 'subscription' !== $payment_type && 'donation_recurring' !== $payment_type ) {
				?>
				<li class="tab-link" data-tab="cashapp">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/cashapp.png' ); ?>" alt="Avatar" width="45"
						class="doneorder" alt="Cash App">
					<span>Cash App</span>
				</li>
				<?php
			}
			?>


			<?php
			if ( 'on' === $ach_debit && $ach_debit_available == true && 'subscription' !== $payment_type && 'donation_recurring' !== $payment_type ) {
				?>
				<li class="tab-link" data-tab="achdebit">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/plaid.png' ); ?>" alt="Avatar" width="45"
						class="doneorder" alt="Cash App">
					<span> ACH Debit </span>
				</li>
				<?php
			}
			?>
		</ul>

		<div id="creditCard" class="tab-content current">
			<div class="clearfix">
				<h3 style="display:none">Credit Card</h3>
				<div class="cardsBlock01">
					<div class="cardsBlock02">
						<div class="wizard-form-radio">
							<label for="newCard"><input type="radio" name="savecards" id="newCard" checked="checked"
														value="2"/>Add New Card</label>
						</div>

						<?php
						if ( isset( $wpep_square_customer_cof ) && ! empty( $wpep_square_customer_cof ) ) {
							?>
							<div class="wizard-form-radio">
								<label for="existingCard"><input type="radio" name="savecards" id="existingCard"
																value="3"/>Use
									Existing Card</label>

							</div>
							<?php
						}
						?>
					</div>

					<div id="cardContan2" class="desc">
						<?php
						wpep_print_credit_card_fields( $wpep_current_form_id );
						if ( 'on' === $wpep_save_card ) {
							?>
							<div class="wizard-form-checkbox saveCarLater">
								<input name="savecardforlater" id="saveCardLater" type="checkbox" required="true">
								<label for="saveCardLater">Save card for later use</label>
							</div>
							<?php
						}
						?>
					</div>

					<div id="cardContan3" class="desc" style="display: none;">
						<div class="wpep_saved_cards">
							<?php require WPEP_ROOT_PATH . 'views/frontend/saved-cards.php'; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php if ( $afterpay_available ) { ?>
		<div id="afterpay" class="tab-content">	
			<div id="afterpay-amount" style="display:none"><p style="    display: flex;justify-content: center;">Please define in range Amount</p></div>
			<div id="afterpay-button" style="text-align: center;"></div>
			<div class="loader"></div>
		</div>
		<?php } 
		if ( $ach_debit_available ) {
		?>
		<div id="achdebit" class="tab-content">	
			<button id="ach-button"> <img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/plaid.png' ); ?>" alt="Avatar" class="doneorder" alt="Plaid"> Pay with Bank Account</button>
		</div>	
		<?php } 
		if ( $cashapp_available ) {
		?>
		<div id="cashapp" style="text-align: center;" class="tab-content ">
			<div id="cash-app-pay"></div>
			<div id="cashapp-amount" style="display:none"><p style="display: flex;justify-content: center;">Please define in range Amount</p></div>
			<div class="loader"></div>
		</div>
		<?php } ?>
	</div>

<?php if ( 'on' === $enable_terms_condition && '' !== $terms_label && 'no' !== $terms_label && '' !== $terms_link && 'no' !== $terms_link ) { ?>
	<div class="termsCondition wpep-required form-group">
		<div class="wizard-form-checkbox">
			<input name="terms-condition-checkbox" id="termsCondition-<?php echo esc_attr( $wpep_current_form_id ); ?>" type="checkbox"
					required="true">
			<label for="termsCondition-<?php echo esc_attr( $wpep_current_form_id ); ?>">I accept the</label> <a
				href="<?php echo esc_url( $terms_link ); ?>"><?php echo esc_html( $terms_label ); ?></a>
		</div>
	</div>
<?php } else { ?>
	<div class="termsCondition wpep-required form-group" style="display:none">
		<div class="wizard-form-checkbox">
			<input name="terms-condition-checkbox" id="termsCondition-<?php echo esc_attr( $wpep_current_form_id ); ?>" type="checkbox"
					required="true" checked>
			<label for="termsCondition-<?php echo esc_attr( $wpep_current_form_id ); ?>">I accept the</label> <a
				href="<?php echo esc_url( $terms_link ); ?>"><?php echo esc_html( $terms_label ); ?></a>
		</div>
	</div>
<?php }
?>
