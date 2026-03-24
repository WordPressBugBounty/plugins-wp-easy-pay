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


<div class="integrations">

	<div class="integrationPageHeading">
		<div class="FieldProTag">
			<h3 class="pagesHeading">Integrations</h3>
			<div class="pro_tag_lock pro_tag" id="pro_tag">
				<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/lock.png' ); ?>" class="proLockImg">
				Pro
			</div>
		</div>
	</div>
	<div class="integrations_tab">
		<button class="tablinks active" onclick="open_integration_form(event, 'ReCaptcha')">ReCaptcha</button>
		<button class="tablinks" onclick="open_integration_form(event, 'MailChimp')">MailChimp</button>
	</div>

	<div id="ReCaptcha" class="integration_tab_content  pro_tag">
		<div class="integrationImg">
			<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/captcha-screen.png' ); ?>">
		</div>
	</div>

	<div id="MailChimp" class="integration_tab_content  pro_tag" style="display: none;">
		<div class="integrationImg">
			<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/mailchimp-screen.png' ); ?>">
		</div>
	</div>

</div>
<div id="pre-popupModal" class="pre-modal">
	<div class="pre-modal-content">
		<span class="pre-close">&times;</span>
		<div class="premium_popup_content">
			<div class="wp_easypay_logo">
				<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Logo_white.png' ); ?>" class="wpep_logo">
			</div>
			<h3 class="proPopHeading">Enhance Your Square Payment Forms With Premium Features.</h3>
			<div class="featuresListPopup">
				<div class="row">
					<div class="col-6">
						<ul>
							<li><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-tick.png' ); ?>" class="" /> 
								<p>5+ Digital Wallets</p>
							</li>
							<li><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-tick.png' ); ?>" class="" /> 
								<p>Square Product Sync</p>
							</li>
						</ul>
					</div>
					<div class="col-6">
						<ul>	
							<li><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-tick.png' ); ?>" class="" /> 
								<p>Square Gift Card</p>
							</li>
							<li><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-tick.png' ); ?>" class="" /> 
								<p>Manage Subscriptions</p>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="integration_button_div">
				<a href="https://wpeasypay.com/pricing?utm_source=plugin&utm_medium=integration_page" target="_blank" rel="noopener noreferrer" class="wpep-no-save-popup">
					<button type="button" class="upgradeBtn">
						Upgrade Now <img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/upgrade-btn-arrow.png' ); ?>" class="" /> 
					</button>
				</a>
			</div>
		</div>
	</div>
</div>