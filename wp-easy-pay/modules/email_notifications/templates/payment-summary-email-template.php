<?php
/**
 * Payment Summary Email Template for WP EasyPay
 *
 * Generates a summary email for transactions within a specified date range, including payment totals,
 * successful transactions, and customer data. Includes links for upgrading to premium features and unsubscribing.
 *
 * @package WP_EasyPay
 * @subpackage Email_Notifications
 * @since 1.0.0
 */

?>

<div class="email_template_wrap" style="background: white;">
	<div class="email_template_header" style="display: block; text-align: center; padding: 20px;">
		<div class="header_logo">
			<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Logo.png' ); ?>" class="wpep_logo">
		</div>
	</div>
	<div class="email_template_header_2">
		<hr style="width:80%;text-align:center;">
		<div class="email_template_heading" style="text-align:center; margin-top:30px">
			<h2 style="color:#4B7BEC; font-family: 'circular'">Transactions</h2>
		</div>
		<div class="email_template_heading" style="text-align:center; margin-top:25px">
			<span style="font-size:50px; font-family: 'circular'">Summary Report</span>
		</div>
		<div class="email_template_img" style="text-align: center; margin-top: 25px;">
			<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/hero_image.png' ); ?>" class="wpep_logo">
		</div>
	</div>
	<div class="email_template_header_3">
		<div class="email_template_heading" style="text-align:center; margin-top:30px">
			<h1 style="font-family: 'circular'">Hello!</h1>
		</div>
		<div class="email_template_heading" style="text-align:center; margin-top:25px">
			<p style="font-family: 'circular'">Here’s a summary of the payment form: <?php echo esc_html( $form_title ); ?> activity on <a href="https://wpeasypay.com">wpeasypay.com</a> for the period of <?php echo esc_html( $new_start_date ); ?> to <?php echo esc_html( $new_end_date ); ?></p>
		</div>
	</div>
	<div class="payment_container_body" style="background: #F2F7FF; padding:5px 50px 30px 50px">
		<div class="payment_container_body_heading" style="text-align:center">
			<p style="font-size:30px; color: #4B7BEC; font-family: 'circular'">Your Transactions</p>
		</div>
		<div class="payment_container">
			<table role="presentation" style="text-align:center;width:100%;border-collapse:collapse;border:0;border-spacing:0;">
				<tr>
					<td style="width:260px;padding:20px;vertical-align:top;background:white">
						<div class="payment_box">
							<div class="payment_box_icon">
								<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Star.png' ); ?>" class="wpep_logo">
							</div>
							<div class="payment_box_heading">
								<p style="font-size:20px;font-family: 'circular'">Simple</p>
							</div>
							<div class="payment_box_total" style="margin-top:-20px;">
								<span style="font-size:20px;font-family: 'circular';font-weight: bolder;"><?php echo esc_html( $currency_symbol . ' ' . $simple_payment_total ); ?></span>
							</div>
						</div>
					</td>
					<td style="width:20px;padding:20px;font-size:0;line-height:0;">&nbsp;</td>
					<td style="width:260px;padding:20px;vertical-align:top;background:white">
						<div class="payment_box">
							<div class="payment_box_icon">
								<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Heart.png' ); ?>" class="wpep_logo">
							</div>
							<div class="payment_box_heading">
								<p style="font-size:20px;font-family: 'circular'">Donation</p>
							</div>
							<div class="payment_box_total" style="margin-top:-20px;">
								<span style="font-size:20px;font-family: 'circular';font-weight: bolder;"><?php echo esc_html( $currency_symbol . ' ' . $donation_payment_total ); ?></span>
							</div>
						</div>
					</td>
				</tr>
			</table>
			<?php if ( wepp_fs()->is__premium_only() ) { ?>
				<table role="presentation" style="text-align:center;width:100%;border-collapse:collapse;border:0;border-spacing:0;margin-top: 50px;">
					<tr>
						<td style="width:260px;padding:20px;vertical-align:top;background:white">
							<div class="payment_box">
								<div class="payment_box_icon">
									<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Time Circle.png' ); ?>" class="wpep_logo">
								</div>
								<div class="payment_box_heading">
									<p style="font-size:20px;font-family: 'circular'">Donation Recurring</p>
								</div>
								<div class="payment_box_total" style="margin-top:-20px;">
									<span style="font-size:20px;font-family: 'circular';font-weight: bolder;"><?php echo esc_html( $currency_symbol . ' ' . $donation_recurring_total ); ?></span>
								</div>
							</div>
						</td>
						<td style="width:20px;padding:20px;font-size:0;line-height:0;">&nbsp;</td>
						<td style="width:260px;padding:20px;vertical-align:top;background:white">
							<div class="payment_box">
								<div class="payment_box_icon">
									<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Message.png' ); ?>" class="wpep_logo">
								</div>
								<div class="payment_box_heading">
									<p style="font-size:20px;font-family: 'circular'">Subscription</p>
								</div>
								<div class="payment_box_total" style="margin-top:-20px;">
									<span style="font-size:20px;font-family: 'circular';font-weight: bolder;"><?php echo esc_html( $currency_symbol . ' ' . $subscription_total ); ?></span>
								</div>
							</div>
						</td>
					</tr>
				</table>
			<?php } ?>
			<table role="presentation" style="text-align:center;width:100%;border-collapse:collapse;border:0;border-spacing:0;margin-top: 50px;">
				<tr>
					<td style="width:100%;padding:20px;vertical-align:top;background:white">
						<div class="payment_box">
							<div class="payment_box_icon">
								<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/icon@img-56.png' ); ?>" class="wpep_logo">
							</div>
							<div class="payment_box_heading">
								<p style="font-size:20px;font-family: 'circular'">Total Amount</p>
							</div>
							<div class="payment_box_total" style="margin-top:-20px;">
								<span style="font-size:20px;font-family: 'circular';font-weight: bolder;"><?php echo esc_html( $currency_symbol . ' ' . $total_amount ); ?></span>
							</div>
						</div>
					</td>
				</tr>
			</table>
			<table role="presentation" style="text-align:center;width:100%;border-collapse:collapse;border:0;border-spacing:0;margin-top: 50px;">
				<tr>
					<td style="width:260px;padding:20px;vertical-align:top;background:white">
						<div class="payment_box">
							<div class="payment_box_icon">
								<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/grommet-icons_transaction.png' ); ?>" class="wpep_logo">
							</div>
							<div class="payment_box_heading">
								<p style="font-size:20px;font-family: 'circular'">Total Transactions</p>
							</div>
							<div class="payment_box_total" style="margin-top:-20px;">
								<span style="font-size:20px;font-family: 'circular';font-weight: bolder;"><?php echo esc_html( $total_transactions_count ); ?></span>
							</div>
						</div>
					</td>
					<td style="width:20px;padding:20px;font-size:0;line-height:0;">&nbsp;</td>
					<td style="width:260px;padding:20px;vertical-align:top;background:white">
						<div class="payment_box">
							<div class="payment_box_icon">
								<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/fluent-mdl2_group.png' ); ?>" class="wpep_logo">
							</div>
							<div class="payment_box_heading">
								<p style="font-size:20px;font-family: 'circular'">Customers</p>
							</div>
							<div class="payment_box_total" style="margin-top:-20px;">
								<span style="font-size:20px;font-family: 'circular';font-weight: bolder;"><?php echo esc_html( $total_customers ); ?></span>
							</div>
						</div>
					</td>
					<td style="width:20px;padding:20px;font-size:0;line-height:0;">&nbsp;</td>
					<td style="width:260px;padding:20px;vertical-align:top;background:white">
						<div class="payment_box">
							<div class="payment_box_icon">
								<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/mdi_success.png' ); ?>" class="wpep_logo">
							</div>
							<div class="payment_box_heading">
								<p style="font-size:20px;font-family: 'circular'">Successful Transactions</p>
							</div>
							<div class="payment_box_total" style="margin-top:-20px;">
								<span style="font-size:20px;font-family: 'circular';font-weight: bolder;"><?php echo esc_html( $total_successful_transactions ); ?></span>
							</div>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<div class="upgrade_premium">
		<div class="upgrade_premium_container">
			<div class="upgrade_premium_heading" style="margin-top:20px">
				<p style="font-size:25px; color: #4B7BEC; font-family: 'circular';margin-bottom: 0;">To upgrade or learn more about premium features</p>
			</div>
			<div class="upgrade_premium_list">
				<div class="upgrade_premium_list_content">
					<p style="margin:0; font-family: 'circular'"><span class="dashicons dashicons-yes-alt" style="color:green; margin-right:5px;font-size: 18px;"></span>Subscription processing & renewable payment schedule.</p>
					<p style="margin:0; font-family: 'circular'"><span class="dashicons dashicons-yes-alt" style="color:green; margin-right:5px;font-size: 18px;"></span>Additional descriptive data field.</p>
					<p style="margin:0; font-family: 'circular'"><span class="dashicons dashicons-yes-alt" style="color:green; margin-right:5px;font-size: 18px;"></span>Automate and facilitate your donor by recurring donation.</p>
					<p style="margin:0; font-family: 'circular'"><span class="dashicons dashicons-yes-alt" style="color:green; margin-right:5px;font-size: 18px;"></span>Donation target progress tracker.</p>
					<p style="margin:0; font-family: 'circular'"><span class="dashicons dashicons-yes-alt" style="color:green; margin-right:5px;font-size: 18px;"></span>Apple and google pay integration.</p>
				</div>
				<div class="upgrade_premium_button" style="text-align:center;margin-top: 20px;">
					<a href="https://wpeasypay.com/pricing/?utm_source=plugin&amp;utm_medium=payment_summary_email">
						<button class="upgrade_button" style="padding:8px 10px; font-family: 'circular'; border: 1px solid #FF8F00;background: #FFA41C;border-radius: 5px;"><a href="https://wpeasypay.com/pricing/?utm_source=report&utm_medium=transaction_summary&utm_campaign=plugin"><span class="fa-solid--crown">
							<svg xmlns="http://www.w3.org/2000/svg" width="1.25em" height="1em" viewBox="0 0 640 512"><path fill="#ffe747" d="M528 448H112c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h416c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16m64-320c-26.5 0-48 21.5-48 48c0 7.1 1.6 13.7 4.4 19.8L476 239.2c-15.4 9.2-35.3 4-44.2-11.6L350.3 85C361 76.2 368 63 368 48c0-26.5-21.5-48-48-48s-48 21.5-48 48c0 15 7 28.2 17.7 37l-81.5 142.6c-8.9 15.6-28.9 20.8-44.2 11.6l-72.3-43.4c2.7-6 4.4-12.7 4.4-19.8c0-26.5-21.5-48-48-48S0 149.5 0 176s21.5 48 48 48c2.6 0 5.2-.4 7.7-.8L128 416h384l72.3-192.8c2.5.4 5.1.8 7.7.8c26.5 0 48-21.5 48-48s-21.5-48-48-48"/></svg>
						</span> Get Pro Now</button>
					</a>
				</div>
			</div>
		</div>
	</div>
	<div class="email_summary_footer" style="background-color: #041129; margin-top: 30px;padding: 40px 180px;">
		<div class="copyright_text" style="text-align: -webkit-center;margin-top: 20px;">
			<table>
				<tr>
					<td class="footer" style="text-align: center; color:#7A88A1; font-size: 12px; font-family: 'circular'">
						<a style="color:#7A88A1" href="https://wpeasypay.com/faq/">FAQs</a>
					</td>
					<td style=" padding: 10px;"></td>
					<td class="footer" style="text-align: center; color:#7A88A1; font-size: 12px; font-family: 'circular'">
						<a style="color:#7A88A1" href="https://wpeasypay.com/refund-policy/">Refund Policy</a>
					</td>
					<td style=" padding: 10px;"></td>
					<td class="footer" style="text-align: center; color:#7A88A1; font-size: 12px; font-family: 'circular'">
						<a style="color:#7A88A1" href="<?php echo esc_url( admin_url() . 'post.php?post=' . $current_form_id . '&action=edit' ); ?>">Unsubscribe</a>
					</td>
				</tr>
			</table>
		</div>
		<div class="copyright_text" style="text-align: -webkit-center;margin-top: 20px;">
			<table>
				<tr>
					<td class="footer" style="text-align: center; color:#7A88A1; font-size: 12px; font-family: 'circular'">
						Copyright © 2023 WP EasyPay. All Rights Reserved.
						Square integrated payment solution for WordPress
					</td>
				</tr>
			</table>
		</div>
		<div class="wp_logo" style="text-align: -webkit-center;margin-top: 20px;">
			<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/wpeasy_white_icon 3.png' ); ?>" class="wpep_logo">
		</div>
	</div>
</div>