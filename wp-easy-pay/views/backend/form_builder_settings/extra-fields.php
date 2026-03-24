<?php
/**
 * Filename: extra-fields.php
 * Description: form extra fields backend.
 *
 * @package WP_Easy_Pay
 */

$form_fields = get_post_meta( get_the_ID(), 'wpep_square_form_builder_fields', true );
?>

<main>
	<div class="MainDiv">
		<div class="FieldProTag pro_tag">
			<h3 class="FieldHeading">Extra Fields</h3>
			<span class="pro_tag" id="pro_tag">Pro</span>
		</div>
		<div class="FieldImg pro_tag">
			<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/extra-fields.png' ); ?>">
		</div>
	</div>	
</main>
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
				<a href="https://wpeasypay.com/pricing?utm_source=plugin&utm_medium=extra_fields" target="_blank" rel="noopener noreferrer" class="wpep-no-save-popup">
					<button type="button" class="upgradeBtn">
						Upgrade Now <img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/upgrade-btn-arrow.png' ); ?>" class="" /> 
					</button>
				</a>
			</div>
		</div>
	</div>
</div>
<input type="hidden" id="wpep_form_builder_json" name="wpep_square_form_builder_fields"
			value='<?php echo esc_attr( $form_fields ); ?>'>
<style>
	.form-wrap.form-builder .frmb-control li {
		margin: 0px 0px -2px 0;
		padding: 20px;
		/* border-radius: 0px !important; */
		transition: all 0.3s ease;
		box-shadow: inset 0 0 0 1px #ebebeb;
	}
</style>
