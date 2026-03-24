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
		<div class="contentBody">
			<div class="integration_container_body">
				<div class="integration_img">
					<main>
						<div class="MainDiv">
							<div class="FieldProTag">
								<h3 class="pagesHeading">Subscriptions</h3>
								<div class="pro_tag_lock pro_tag" id="pro_tag">
									<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/lock.png' ); ?>" class="proLockImg">
									Pro
								</div>
							</div>
							<div class="subscriptionImgTwo pro_tag">
								<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/subscription-frame-two.png' ); ?>">
							</div>
							<div class="subscriptionImgOne pro_tag">
								<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/subscription-frame-one.png' ); ?>">
							</div>
						</div>	
					</main>
				</div>
			</div>
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
			<h3 class="proPopHeading">Enhance your square payment forms with premium features.</h3>
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
				<a href="https://wpeasypay.com/pricing?utm_source=plugin&utm_medium=subscription_payment" target="_blank" rel="noopener noreferrer" class="wpep-no-save-popup">
					<button type="button" class="upgradeBtn">
						Upgrade Now <img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/upgrade-btn-arrow.png' ); ?>" class="" /> 
					</button>
				</a>
			</div>
		</div>
	</div>
</div>
