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
		<div class="contentBody">
			<div class="integration_container_body">
				<div class="integration_img">
					<main>
						<div class="MainDiv">
							<div class="FieldProTag">
								<h3 class="pagesHeading">Coupons</h3>
								<div class="pro_tag_lock pro_tag" id="pro_tag">
									<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/lock.png' ); ?>" class="proLockImg">
									Pro
								</div>
							</div>
							<div class="couponImg pro_tag">
								<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/coupon-screen.png' ); ?>">
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
				<a href="https://wpeasypay.com/pricing?utm_source=plugin&utm_medium=coupons" target="_blank" rel="noopener noreferrer" class="wpep-no-save-popup">
					<button type="button" class="upgradeBtn">
						Upgrade Now <img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/upgrade-btn-arrow.png' ); ?>" class="" /> 
					</button>
				</a>
			</div>
		</div>
	</div>
</div>