<?php
/**
 * WP Easy Pay Frontend Payment Options
 *
 * This file is responsible for rendering the payment options for the WP Easy Pay plugin.
 * It dynamically includes payment methods based on the configurations set in the form
 * and global settings, such as Google Pay, Apple Pay, After Pay, Terminal Pay, and Cash App.
 * Additionally, it includes functionality for handling terms and conditions and reCAPTCHA verification.
 *
 * @package    WP_Easy_Pay
 * @version    1.0.0
 * @since      1.0.0
 * @license    GPL-2.0-or-later
 */

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

		$gpay            = get_option( 'wpep_square_google_pay', true );
		$applepay        = get_option( 'wpep_square_apple_pay', true );
		$afterpay        = get_option( 'wpep_square_after_pay', true );
		$cashapp         = get_option( 'wpep_square_cash_app', true );
		$giftcard        = get_option( 'wpep_square_giftcard', true );
		$ach_debit       = get_option( 'wpep_square_ach_debit', true );
		$terminal_pay    = get_option( 'wpep_square_terminal', true );
		$square_currency = get_option( 'wpep_square_currency_new' );

	} else {

		$gpay            = get_option( 'wpep_square_test_google_pay_global', true );
		$applepay        = get_option( 'wpep_square_test_apple_pay', true );
		$afterpay        = get_option( 'wpep_square_test_after_pay', true );
		$cashapp         = get_option( 'wpep_square_test_cash_app', true );
		$giftcard        = get_option( 'wpep_square_test_giftcard', true );
		$ach_debit       = get_option( 'wpep_square_test_ach_debit', true );
		$square_currency = get_option( 'wpep_square_currency_test' );


	}
} else {

	$live_mode = get_post_meta( $wpep_current_form_id, 'wpep_payment_mode', true );

	if ( 'on' === $live_mode ) {

		$gpay            = get_post_meta( $wpep_current_form_id, 'wpep_square_google_pay_live', true );
		$afterpay        = get_post_meta( $wpep_current_form_id, 'wpep_square_after_pay_live', true );
		$applepay        = get_post_meta( $wpep_current_form_id, 'wpep_square_apple_pay_live', true );
		$cashapp         = get_post_meta( $wpep_current_form_id, 'wpep_square_cash_app_live', true );
		$giftcard        = get_post_meta( $wpep_current_form_id, 'wpep_square_giftcard_live', true );
		$ach_debit       = get_post_meta( $wpep_current_form_id, 'wpep_square_ach_debit_live', true );
		$terminal_pay    = get_post_meta( $wpep_current_form_id, 'wpep_square_terminal', true );
		$square_currency = get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_new', true );
	} else {

		$gpay            = get_post_meta( $wpep_current_form_id, 'wpep_square_google_pay', true );
		$afterpay        = get_post_meta( $wpep_current_form_id, 'wpep_square_after_pay', true );
		$applepay        = get_post_meta( $wpep_current_form_id, 'wpep_square_apple_pay', true );
		$cashapp         = get_post_meta( $wpep_current_form_id, 'wpep_square_cash_app', true );
		$giftcard        = get_post_meta( $wpep_current_form_id, 'wpep_square_giftcard', true );
		$ach_debit       = get_post_meta( $wpep_current_form_id, 'wpep_square_ach_debit', true );
		$square_currency = get_post_meta( $wpep_current_form_id, 'wpep_post_square_currency_test', true );
	}
}
if ( 'on' === $cashapp ) {
	$cashapp_available = is_available( 'cashapp', $square_currency );
}
if ( 'on' === $giftcard ) {
	$giftcard_available = is_available( 'giftcard', $square_currency );
}
if ( 'on' === $gpay ) {
	$gpay_available = is_available( 'gpay', $square_currency );
}
if ( 'on' === $afterpay ) {
	$afterpay_available = is_available( 'afterpay', $square_currency );
}
if ( 'on' === $applepay ) {
	$applepay_available = is_available( 'applepay', $square_currency );
}
if ( 'on' === $ach_debit ) {
	$ach_debit_available = is_available( 'achDebit', $square_currency );
}

?>
	<div class="paymentsBlocks-<?php echo esc_attr( $wpep_current_form_id ); ?>">
		<ul class="wpep_tabs wpep_tabs-<?php echo esc_attr( $wpep_current_form_id ); ?>">
			<li class="tab-link current" data-tab="creditCard-<?php echo esc_attr( $wpep_current_form_id ); ?>">
				<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/creditcard.svg' ); ?>" alt="Avatar" width="45"
					class="doneorder" alt="Credit Card">
				<!-- <h4 class="">Credit Card</h4> -->
				<span>Payment Card</span>
			</li>
			<?php
			if ( 'on' === $gpay && true === $gpay_available && 'subscription' !== $payment_type && 'donation_recurring' !== $payment_type ) {
				?>
				<li class="tab-link" data-tab="googlePay-<?php echo esc_attr( $wpep_current_form_id ); ?>">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/googlepay.svg' ); ?>" alt="Avatar" width="45"
						class="doneorder" alt="Google Pay">
					<span>Google Pay</span>
				</li>
				<?php
			}


			if ( 'on' === $applepay && true === $applepay_available && 'subscription' !== $payment_type && 'donation_recurring' !== $payment_type ) {
				?>
				<li class="tab-link" data-tab="applePayfather-<?php echo esc_attr( $wpep_current_form_id ); ?>">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/apple_pay.svg' ); ?>" alt="Avatar" width="45"
						class="doneorder" alt="Google Pay">
					<span>Apple Pay</span>
				</li>
				<?php
			}

			if ( ! empty( $afterpay ) && 'on' === $afterpay && true === $afterpay_available && 'subscription' !== $payment_type && 'donation_recurring' !== $payment_type ) {
				?>
				<li class="tab-link" data-tab="afterpay-<?php echo esc_attr( $wpep_current_form_id ); ?>">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/after-pay.png' ); ?>" alt="Avatar" width="45"
						class="doneorder" alt="Google Pay">
					<span>After Pay</span>
				</li>
				<?php
			}


			if ( ! empty( $terminal_pay ) && 'on' === $terminal_pay && 'subscription' !== $payment_type && 'donation_recurring' !== $payment_type ) {
				?>
				<li class="tab-link" data-tab="terminialpay-<?php echo esc_attr( $wpep_current_form_id ); ?>">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/tpay.png' ); ?>" alt="Avatar" width="45"
						class="doneorder" alt="tpay">
					<span>Terminal Pay</span>
				</li>
				<?php
			}

			if ( 'on' === $cashapp && true === $cashapp_available && 'subscription' !== $payment_type && 'donation_recurring' !== $payment_type ) {
				?>
				<li class="tab-link" data-tab="cashapp-<?php echo esc_attr( $wpep_current_form_id ); ?>">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/cashapp.png' ); ?>" alt="Avatar" width="45"
						class="doneorder" alt="Cash App">
					<span>Cash App</span>
				</li>
				<?php
			}

			if ( 'on' === $giftcard && true === $giftcard_available && 'subscription' !== $payment_type && 'donation_recurring' !== $payment_type && wepp_fs()->is__premium_only() ) {
				?>
				<li class="tab-link" data-tab="giftcard-<?php echo esc_attr( $wpep_current_form_id ); ?>">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/square-logo.png' ); ?>" alt="Avatar" width="45"
						class="doneorder" alt="Square Giftcard">
					<span>Square Gift Card</span>
				</li>
				<?php
			}

			if ( 'on' === $ach_debit && true === $ach_debit_available && 'subscription' !== $payment_type && 'donation_recurring' !== $payment_type ) {
				?>
				<li class="tab-link" data-tab="achdebit-<?php echo esc_attr( $wpep_current_form_id ); ?>">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/plaid.png' ); ?>" alt="Avatar" width="45"
						class="doneorder" alt="Cash App">
					<span> ACH Debit </span>
				</li>
				<?php
			}
			?>
		</ul>

		<div id="creditCard-<?php echo esc_attr( $wpep_current_form_id ); ?>" class="tab-content tab-content-<?php echo esc_attr( $wpep_current_form_id ); ?> current">
			<div class="clearfix">
				<h3 style="display:none">Credit Card</h3>
				<div class="cardsBlock01">
					<div class="cardsBlock02">
						<div class="wizard-form-radio">
							<label for="newCard"><input type="radio" name="savecards" id="newCard" checked="checked"
														value="2"/>Add New Card</label>
						</div>

						<?php
						$wpep_square_customer_cof = get_user_meta( get_current_user_id(), 'wpep_square_customer_cof', true );
						if ( isset( $wpep_square_customer_cof ) && ! empty( $wpep_square_customer_cof ) ) {
							?>
							<div class="wizard-form-radio">
								<label for="existingCard"><input type="radio" name="savecards" id="existingCard"
																value="3"/>Use Existing Card</label>
							</div>
							<?php
						}
						?>
					</div>

					<div id="cardContan2" class="desc">
						<?php
						wpep_print_credit_card_fields( $wpep_current_form_id );
						if ( 'on' === $wpep_save_card ) {
							if ( is_user_logged_in() ) {
								?>
							<div class="wizard-form-checkbox saveCarLater">
								<input name="savecardforlater" id="saveCardLater" type="checkbox" required="true">
								<label for="saveCardLater">Save card for later use</label>
							</div>
								<?php
							}
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
		<?php if ( isset( $gpay_available ) && true === $gpay_available ) { ?>
		<div id="googlePay-<?php echo esc_attr( $wpep_current_form_id ); ?>" class="tab-content tab-content-<?php echo esc_attr( $wpep_current_form_id ); ?> ">
			<div id="google-pay-button-<?php echo esc_attr( $wpep_current_form_id ); ?>" class="wp-square-wallet" style="text-align: center;"></div>
			<div id="gpay-amount" style="display:none"><p style="display: flex;justify-content: center;">Please define in range Amount</p></div>
			<div class="loader"></div>
		</div>
			<?php
		}
		if ( isset( $applepay_available ) && true === $applepay_available ) {
			?>
		<div id="applePayfather-<?php echo esc_attr( $wpep_current_form_id ); ?>" class="tab-content tab-content-<?php echo esc_attr( $wpep_current_form_id ); ?>">
			<div id="applePay-<?php echo esc_attr( $wpep_current_form_id ); ?>" class="apple-pay-button"></div>
			<div id="apple-pay-button-<?php echo esc_attr( $wpep_current_form_id ); ?>" class="wallet-not-enabled wp-square-wallet"></div>
			<div id="applepay-amount" style="display:none"><p style="display: flex;justify-content: center;">Please define in range Amount</p></div>
		</div>
		
			<?php
		}
		if ( isset( $afterpay_available ) && true === $afterpay_available ) {
			?>
		
		<div id="afterpay-<?php echo esc_attr( $wpep_current_form_id ); ?>" class="tab-content tab-content-<?php echo esc_attr( $wpep_current_form_id ); ?>">
			<div id="afterpay-amount" style="display:none"><p style="display: flex;justify-content: center;">Please define in range Amount</p></div>
			<div id="afterpay-button-<?php echo esc_attr( $wpep_current_form_id ); ?>" class="wp-square-wallet" style="text-align: center;"></div>
			<div class="loader"></div>
		</div>
			<?php
		}
		if ( isset( $ach_debit_available ) && true === $ach_debit_available ) {
			?>
		<div id="achdebit-<?php echo esc_attr( $wpep_current_form_id ); ?>" class="tab-content tab-content-<?php echo esc_attr( $wpep_current_form_id ); ?>">
		<div class="wp-square-wallet-ach">
			<button id="ach-button-<?php echo esc_attr( $wpep_current_form_id ); ?>"> <img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/plaid.png' ); ?>" alt="Avatar" class="doneorder" alt="Plaid"> Pay With ACH</button>
		</div>	
		</div>	
		<?php } ?>
		<div id="terminialpay-<?php echo esc_attr( $wpep_current_form_id ); ?>" class="tab-content tab-content-<?php echo esc_attr( $wpep_current_form_id ); ?>">	
			<button id="terminialpay-button"> <img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/tpay.png' ); ?>" alt="Avatar" class="doneorder" alt="Plaid"> Pay with Terminal</button>
			<div class="loader"></div>
		</div>
		<?php if ( isset( $cashapp_available ) && true === $cashapp_available ) { ?>
		<div id="cashapp-<?php echo esc_attr( $wpep_current_form_id ); ?>" style="text-align: center;" class="tab-content tab-content-<?php echo esc_attr( $wpep_current_form_id ); ?>">
			<div id="cash-app-pay-<?php echo esc_attr( $wpep_current_form_id ); ?>" class="wp-square-wallet"></div>
			<div id="cashapp-amount" style="display:none"><p style="display: flex;justify-content: center;">Please define in range Amount</p></div>
			<div class="loader"></div>
		</div>
		<?php } ?>
		<?php if ( isset( $giftcard_available ) && true === $giftcard_available && wepp_fs()->is__premium_only() ) { ?>
		<div id="giftcard-<?php echo esc_attr( $wpep_current_form_id ); ?>" style="text-align: center;" class="tab-content tab-content-<?php echo esc_attr( $wpep_current_form_id ); ?>">
			
			<div id="wc_woosquare_gc_cart_redeem_form">
				<div class="woowoosquare_gift_card_coupen_code_notices"></div>
				<div id="sq-gift-card-coupen"></div>
				<div class="square-gift-card-note" style="font-size: small"><strong>Note: </strong>Gift Cards can only be redeemed for full payments, Partial payments are not supported.</div>
			</div>
			
			<input type="hidden" name="square_giftcard_nonce" value="<?php echo esc_attr( wp_create_nonce( 'square-giftcard-nonce' ) ); ?>" />
			
			<div id="giftcard-pay-<?php echo esc_attr( $wpep_current_form_id ); ?>" class="wp-square-wallet"></div>
			<div id="giftcard-amount" style="display:none"><p style="display: flex;justify-content: center;">Please define in range Amount</p></div>
			<div class="loader"></div>
		</div>
		<?php } ?>
		<div id = "testing_apple">
		</div>
		
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
if ( wepp_fs()->is__premium_only() ) {
	wpep_print_recaptcha_fields( $wpep_current_form_id );
}
?>
