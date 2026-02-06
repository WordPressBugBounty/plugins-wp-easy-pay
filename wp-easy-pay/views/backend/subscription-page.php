<?php
/**
 * WP EasyPay Subscription Dashboard Template.
 *
 * This file renders the subscription dashboard page for the WP EasyPay plugin.
 * It provides an overview of subscription features and offers an upgrade option for the premium version.
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
				<h1>Subscription</h1>
			</div>
		</div>
		<div class="contentBody">
			<div class="integration_container_body">
				<div class="integration_img">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/subscription1.png' ); ?>" width="100%" class="wpep_logo">
				</div>
			</div>
			<div class="subscription_container_body">
				<div class="subscription_content">
					<h1 class="recaptcha_heading">Offer flexible subscription</h1>
					<p class="recaptcha_text">Offer flexible plans, automate billing for hassle-free transactions, and foster long-term customer loyalty through convenient subscription options.</p>
				</div>
				<div class="subscription_content1">
					<ul class="square-list">
						<li>Flexible subscription plans, adapting to their unique needs</li>
						<li>Simplify billing processes with automated subscription renewals</li>
						<li>Seamless subscription integration for recurring revenue streams</li>
						<li>Offer convenient and customizable subscription options</li>
					</ul>
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