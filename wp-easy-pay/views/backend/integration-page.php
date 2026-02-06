<?php
/**
 * WP EasyPay Integrations Dashboard Template.
 *
 * This file renders the integrations dashboard page for the WP EasyPay plugin.
 * It displays available integrations such as reCAPTCHA and Mailchimp and provides
 * an upgrade button for premium features.
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
				<h1>Integrations</h1>
			</div>
		</div>
		<div class="contentBody">
			<div class="integration_container_body">
				<div class="integration_img">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/integration.png' ); ?>" width="100%" class="wpep_logo">
				</div>
				<div class="integration_content">
					<h1 class="recaptcha_heading">reCAPTCHA</h1>
					<p class="recaptcha_text">Streamline user registrations, protect against spam, effortlessly grow your audience by seamlessly integrating by reCAPTCHA and Mailchip for automatic email marketing.</p>
					<div class="integration_button_div">
						<a href="https://wpeasypay.com/pricing/?utm_source=plugin&utm_medium=integration_page">
							<button class="integration_button">
								Upgrade to Premium
							</button>
						</a>
					</div>
				</div>
			</div>
			<div class="mailchimp_container_body">
				<div class="mailchimp_content">
					<h1 class="recaptcha_heading">Mailchimp</h1>
					<ul class="square-list">
						<li>Safeguard your site with integrated</li>
						<li>Streamline user registration and form submissions</li>
						<li>Expand your reach by effortlessly integrating</li>
						<li>Grow your subscriber base and enhance communicaiton</li>
					</ul>
				</div>
				<div class="mailchimp_img">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/mailchimp.png' ); ?>" width="100%" class="wpep_logo">
				</div>
			</div>
		</div>
		<div class="contentFooter">
			<div class="footer_container">
				<div class="footer_table_text">
					<p>Upgrade to <strong>WP EasyPay Premium</strong> for enhanced features and unparalleled convenience</p>
				</div>
				<div class="footer_table_button">
					<a href="https://wpeasypay.com/pricing/?utm_source=plugin&utm_medium=integration_page">
						<button class="footer_button">
							Upgrade to Premium
						</button>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>