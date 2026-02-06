<?php
/**
 * WP EasyPay Dashboard Template.
 *
 * This file renders the dashboard for the WP EasyPay plugin. It displays an overview of different
 * types of payments, including simple payments, donations, recurring payments, and subscription payments.
 * It also shows key metrics like total transactions, customers, and successful transactions.
 * Users can upgrade to the PRO version to unlock premium features.
 *
 * @package WP EasyPay
 */

$transient_data = get_transient( 'dashboard_transient_data' );

if ( false === $transient_data || empty( $transient_data ) ) {
	// If transient is not set, fetch the data immediately (fallback).
	wpep_fetch_dashboard_data_in_transient();
	$transient_data = get_transient( 'dashboard_transient_data' );
}
$wpep_square_payment_mode_global = get_option( 'wpep_square_payment_mode_global', true );
if ( isset( $wpep_square_payment_mode_global ) && 'on' === $wpep_square_payment_mode_global ) {
	$currency = get_option( 'wpep_square_currency_new' );
} else {
	$currency = get_option( 'wpep_square_currency_test' );
}
if ( isset( $currency ) && ! empty( $currency ) ) {
	if ( 'USD' === $currency ) {
		$currency_symbol = '$';
	} elseif ( 'CAD' === $currency ) {
		$currency_symbol = 'C$';
	} elseif ( 'GBP' === $currency ) {
		$currency_symbol = '£';
	} elseif ( 'JPY' === $currency ) {
		$currency_symbol = '¥';
	} elseif ( 'AUD' === $currency ) {
		$currency_symbol = 'A$';
	} elseif ( 'EUR' === $currency ) {
		$currency_symbol = '€';
	}
} else {
	$currency_symbol = '';
}
?>

<div class="wpeasyPay-dashboard">
	<div class="contentWrap wpeasyPay">
		<div class="contentHeader">
			<div class="wp_easypay_logo">
				<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Logo.png' ); ?>" class="wpep_logo">
			</div>
			<div class="wp_easypay_heading">
				<h1>Dashboard</h1>
			</div>
			<div class="wp_easypay_version">
				<h1>Ver <?php echo esc_html( WPEP_VERSION ); ?></h1>
			</div>
		</div>
		<div class="contentBody">
			<div class="payment_container_body">
				<div class="payment_container">
					<div class="payment_box">
						<div class="payment_box_icon">
							<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Star.png' ); ?>" class="wpep_logo">
						</div>
						<div class="payment_box_heading">
							<p>Simple Payments</p>
						</div>
						<div class="payment_box_total">
							<span><?php echo esc_html( $currency_symbol . ' ' . $transient_data['simple_payment_total'] ); ?></span>
						</div>
					</div>
					<div class="payment_box">
						<div class="payment_box_icon">
							<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Heart.png' ); ?>" class="wpep_logo">
						</div>
						<div class="payment_box_heading">
							<p>Donation Payments</p>
						</div>
						<div class="payment_box_total">
							<span><?php echo esc_html( $currency_symbol . ' ' . $transient_data['donation_payment_total'] ); ?></span>
						</div>
					</div>
					<div class="payment_box">
						<div class="payment_box_icon">
							<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Time Circle.png' ); ?>" class="wpep_logo">
						</div>
						<div class="payment_box_heading">
							<p>Donation Recurring</p>
						</div>
						<?php
						if ( ! wepp_fs()->is__premium_only() ) {
							?>
							<div class="payment_box_total_pro">
								<a href="https://wpeasypay.com/pricing/?utm_source=plugin&utm_medium=dashboard">
									<button class="unlock-button">
										<span class="icon-lock">
											<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16"><path fill="currentColor" d="M4 4a4 4 0 0 1 8 0v2h.25c.966 0 1.75.784 1.75 1.75v5.5A1.75 1.75 0 0 1 12.25 15h-8.5A1.75 1.75 0 0 1 2 13.25v-5.5C2 6.784 2.784 6 3.75 6H4Zm8.25 3.5h-8.5a.25.25 0 0 0-.25.25v5.5c0 .138.112.25.25.25h8.5a.25.25 0 0 0 .25-.25v-5.5a.25.25 0 0 0-.25-.25M10.5 6V4a2.5 2.5 0 1 0-5 0v2Z"/></svg>
										</span>
										Unlock PRO
									</button>
								</a>
							</div>
							<?php
						} else {
							?>
						<div class="payment_box_total">
							<span><?php echo esc_html( $currency_symbol . ' ' . $transient_data['donation_recurring_payment_total'] ); ?></span>
						</div>
							<?php
						}
						?>
					</div>
					<div class="payment_box">
						<div class="payment_box_icon">
							<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Message.png' ); ?>" class="wpep_logo">
						</div>
						<div class="payment_box_heading">
							<p>Subscription Payments</p>
						</div>
						<?php
						if ( ! wepp_fs()->is__premium_only() ) {
							?>
							<div class="payment_box_total_pro">
								<a href="https://wpeasypay.com/pricing/?utm_source=plugin&utm_medium=dashboard">
									<button class="unlock-button">
										<span class="icon-lock">
											<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16"><path fill="currentColor" d="M4 4a4 4 0 0 1 8 0v2h.25c.966 0 1.75.784 1.75 1.75v5.5A1.75 1.75 0 0 1 12.25 15h-8.5A1.75 1.75 0 0 1 2 13.25v-5.5C2 6.784 2.784 6 3.75 6H4Zm8.25 3.5h-8.5a.25.25 0 0 0-.25.25v5.5c0 .138.112.25.25.25h8.5a.25.25 0 0 0 .25-.25v-5.5a.25.25 0 0 0-.25-.25M10.5 6V4a2.5 2.5 0 1 0-5 0v2Z"/></svg>
										</span>
										Unlock PRO
									</button>
								</a>
							</div>
							<?php
						} else {
							?>
						<div class="payment_box_total">
							<span><?php echo esc_html( $currency_symbol . ' ' . $transient_data['subscription_payment_total'] ); ?></span>
						</div>
							<?php
						}
						?>
					</div>
					<div class="payment_box total_pay_box">
						<div class="payment_box_icon">
							<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Wallet.png' ); ?>" class="wpep_logo">
						</div>
						<div class="payment_box_heading">
							<p>Total Amount</p>
						</div>
						<div class="payment_box_total">
							<span><?php echo esc_html( $currency_symbol . ' ' . $transient_data['total_amount'] ); ?></span>
						</div>
					</div>
				</div>
			</div>
			<div class="payment_container_body_1">
				<div class="payment_container_1">
					<div class="payment_box after_div1">
						<div class="payment_box_icon">
							<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Swap.png' ); ?>" class="wpep_logo">
						</div>
						<div class="payment_box_heading">
							<p>Total Transactions</p>
						</div>
						<div class="payment_box_total">
							<span><?php echo esc_html( $transient_data['total_transactions_count'] ); ?></span>
						</div>
					</div>
					<div class="payment_box after_div2">
						<div class="payment_box_icon">
							<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/2 User.png' ); ?>" class="wpep_logo">
						</div>
						<div class="payment_box_heading">
							<p>Customers</p>
						</div>
						<div class="payment_box_total">
							<span><?php echo esc_html( $transient_data['total_customers_count'] ); ?></span>
						</div>
					</div>
					<div class="payment_box after_div3">
						<div class="payment_box_icon">
							<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Tick Square.png' ); ?>" class="wpep_logo">
						</div>
						<div class="payment_box_heading">
							<p>Successful Transactions</p>
						</div>
						<div class="payment_box_total">
							<span><?php echo esc_html( $transient_data['total_successful_transactions'] ); ?></span>
						</div>
					</div>
				</div>
			</div>
			<div class="payment_container_body_2">
				<div class="filter-container">
					<div class="datepicker-wrapper">
					<?php
					if ( ! wepp_fs()->is__premium_only() ) {
						?>
						<div class="payment_chart_overlay"></div>
						<?php
					}
					?>
						<i class="calendar-icon fa fa-calendar"></i>
						<input type="text" id="datepicker" name="daterange" />
						<input type="hidden" id="datepicker-start" name="start_date">
						<input type="hidden" id="datepicker-end" name="end_date">
					</div>
					<div class="filter-wrapper">
					<?php
					if ( ! wepp_fs()->is__premium_only() ) {
						?>
						<div class="payment_chart_overlay"></div>
						<?php
					}
					?>
						<label for="filter-options">Show:</label>
						<select id="filter-options">
							<option value="all">All</option>
							<option value="simple_payment">Simple Payment</option>
							<option value="donation_payment">Donation Payment</option>
							<option value="donation_recurring">Donation Recurring</option>
							<option value="subscription_payment">Subscription Payment</option>
						</select>
					</div>
				</div>
				<div class="chart_div">
					<canvas id="transactionChart" width="100%" height="400"></canvas>
					<?php
					if ( ! wepp_fs()->is__premium_only() ) {
						?>
					<div class="payment_chart_overlay">
					<div class="unclock_pro_div">
						<div class="center-button-icon">
							<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/pngwing 1.png' ); ?>" class="wpep_logo">
						</div>
						<div class="center-button-div">
							<a href="https://wpeasypay.com/pricing/?utm_source=plugin&utm_medium=dashboard">
								<button class="center-button">
									<span class="icon-lock">
										<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16"><path fill="currentColor" d="M4 4a4 4 0 0 1 8 0v2h.25c.966 0 1.75.784 1.75 1.75v5.5A1.75 1.75 0 0 1 12.25 15h-8.5A1.75 1.75 0 0 1 2 13.25v-5.5C2 6.784 2.784 6 3.75 6H4Zm8.25 3.5h-8.5a.25.25 0 0 0-.25.25v5.5c0 .138.112.25.25.25h8.5a.25.25 0 0 0 .25-.25v-5.5a.25.25 0 0 0-.25-.25M10.5 6V4a2.5 2.5 0 1 0-5 0v2Z"/></svg>
									</span>
									Unlock PRO
								</button>
							</a>
						</div> 
					</div>
				</div>
						<?php
					}
					?>
				</div>
				
				<input type="hidden" class="wpep_dashboard_nonce" name="wpep_dashboard_nonce" id="wpep_dashboard_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpep-dashboard-nonce-checker' ) ); ?>" />
			</div>
		</div>
	</div>
</div>