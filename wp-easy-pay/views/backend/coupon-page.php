<?php
/**
 * WP EasyPay Coupon Feature Template.
 *
 * This file renders the coupon feature page for the WP EasyPay plugin.
 * It displays information about creating custom coupon codes to offer discounts
 * to customers and encourages users to upgrade to the premium version for more features.
 *
 * @package WP EasyPay
 */

?>

<div class="wpeasyPay-dashboard">
	<div class="contentWrap wpeasyPay">
		<div class="contentHeader">
			<div class="wp_easypay_logo">
				<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Logo.png' ); ?>" class="wpep_logo">
			</div>
			<div class="wp_easypay_heading">
				<h1>Coupon</h1>
			</div>
		</div>
		<div class="contentBody">
			<div class="integration_container_body">
				<div class="integration_img">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/coupon.png' ); ?>" class="wpep_logo">
				</div>
				<div class="integration_content">
					<h1 class="recaptcha_heading">Create coupon codes for your customers</h1>
					<p class="recaptcha_text">With WP EasyPay, you can easily create custom coupon codes to offer discounts to your customers to boost sales and attract more audiences.</p>
					<div class="integration_button_div">
						<a href="https://wpeasypay.com/pricing/?utm_source=plugin&utm_medium=coupon_page">
							<button class="integration_button">
								Upgrade to Premium
							</button>
						</a>
					</div>
				</div>
			</div>
			<div class="mailchimp_container_body">
				<div class="mailchimp_content">
					<ul class="square-list">
						<li>Create unlimited personalized coupons</li>
						<li>Offer fixed or percentage discounts to captivate your audience</li>
						<li>Include or exclude specific payment forms</li>
						<li>Boost sales by enticing users with limited-time offers and targeted discounts.</li>
					</ul>
					<div class="footer_table_button">
						<a href="https://wpeasypay.com/pricing/?utm_source=plugin&utm_medium=coupon_page">
							<button class="footer_button">
								Upgrade to Premium
							</button>
						</a>
					</div>
				</div>
				<div class="mailchimp_img">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/coupon2.png' ); ?>" class="wpep_logo">
				</div>
			</div>
		</div>
		<div class="contentFooter">
			<div class="footer_container">
				<div class="footer_table_text">
					<p>Upgrade to <strong>WP EasyPay Premium</strong> for enhanced features and unparalleled convenience</p>
				</div>
				<div class="footer_table_button">
					<a href="https://wpeasypay.com/pricing/?utm_source=plugin&utm_medium=coupon_page">
						<button class="footer_button">
							Upgrade to Premium
						</button>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>