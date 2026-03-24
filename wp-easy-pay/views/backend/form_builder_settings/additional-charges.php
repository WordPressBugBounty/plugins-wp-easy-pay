<?php
/**
 * Additional charges settings panel.
 *
 * @package WP_Easy_Pay
 */

$fees_data     = get_post_meta( get_the_ID(), 'fees_data', false );
$signup_check  = get_post_meta( get_the_ID(), 'wpep_enable_signup_fees', true );
$signup_amount = get_post_meta( get_the_ID(), 'wpep_signup_fees_amount', true );
$signup_label  = get_post_meta( get_the_ID(), 'wpep_signup_fees_label', true );

if ( wepp_fs()->is__premium_only() ) {
	?>
<main>
	<div id="formPage">
		<div class="testPayment">
			<h3 class=""> Additional Charges </h3>

			<div class="wpeasyPay__body">

			<div id="wpep_additional_charges" class="form-group">
			
				<?php


				if ( isset( $fees_data[0] ) && count( $fees_data[0] ) > 0 ) {

					foreach ( $fees_data[0]['name'] as $key => $fees ) {

						$fees_check  = isset( $fees_data[0]['check'][ $key ] ) ? $fees_data[0]['check'][ $key ] : 'no';
						$fees_name   = isset( $fees_data[0]['name'][ $key ] ) ? $fees_data[0]['name'][ $key ] : '';
						$charge_type = isset( $fees_data[0]['type'][ $key ] ) ? $fees_data[0]['type'][ $key ] : '';
						$fees_value  = isset( $fees_data[0]['value'][ $key ] ) ? $fees_data[0]['value'][ $key ] : '';

						?>
						<div class="multiInput">
						<div class="inputWrapperCus innerMultiInput">
						<div class="cusblock1">

						<?php

						if ( 'yes' === $fees_check ) {
							$checked = 'checked';
						} else {
							$checked = '';
						}

						?>
						<label class="fees_label">
							<input type="checkbox" class="wpep-fee-checker" value="yes" <?php echo esc_attr( $checked ); ?>  >
							<input type="hidden" class="hdnFeeChk"  name="wpep_service_fees_check[]" value="<?php echo esc_attr( $fees_check ); ?>"  >
						</label>
						
						<input type="text" name="wpep_service_fees_name[]" value="<?php echo esc_attr( $fees_name ); ?>" placeholder="Service Name" class="form-control tamountfield">

						<select name="wpep_service_charge_type[]">
							<option value="percentage" 
							<?php
							if ( 'percentage' === $charge_type ) {
								echo 'selected'; }
							?>
							> Percentage </option>
							<option value="static_price" 
							<?php
							if ( 'static_price' === $charge_type ) {
								echo 'selected'; }
							?>
							> Static price </option>
						</select>
					
						<input type="text" name="wpep_fees_value[]" value="<?php echo esc_attr( $fees_value ); ?>" placeholder="Value" class="form-control tqtufield">
						</div>
						<input type="button" class="btnplus add_new_additional_fees_field" value="">
							<?php if ( 0 !== $key ) { ?>
							<input type="button" class="btnminus remove_additional_fees_field" value="">
						<?php } ?>
		
						</div>
						</div>
							<?php
					}
				} else {
					?>

						<div class="multiInput">
						<div class="inputWrapperCus innerMultiInput">
						<div class="cusblock1">

						<label class="fees_label">
							<input type="checkbox" class="wpep-fee-checker" value="yes" >
							<input type="hidden" class="hdnFeeChk"  name="wpep_service_fees_check[]" value="no"  >
						</label>

						<input type="text" name="wpep_service_fees_name[]" value="" placeholder="Service Name" class="form-control tamountfield">

						<select name="wpep_service_charge_type[]">
							<option value="percentage" > Percentage </option>
							<option value="static_price"> Static price </option>
						</select>
					
						<input type="text" name="wpep_fees_value[]" value="" placeholder="Value" class="form-control tqtufield">
						</div>
						<input type="button" class="btnplus add_new_additional_fees_field" value="">
						<!-- <input type="button" class="btnminus remove_additional_fees_field" value=""> -->
		

						</div>
						</div>

					<?php
				}

				?>
			</div>

			<div class="wpeasyPay__body">

				<div id="wpep_signup_charges" style="display: none;" class="form-group">
					<h3 style="margin-bottom: 30px;"> Signup fees </h3>
					<div class="multiInput">
						<div class="inputWrapperCus innerMultiInput">
						<div class="cusblock1">
					<?php

					if ( 'yes' === $signup_check ) {
						$checked = 'checked';
					} else {
						$checked = '';
					}

					?>
						<label class="signup_fees_label">
							<input type="checkbox" value="yes" name="wpep_enable_signup_fees" <?php echo esc_attr( $checked ); ?>>
						</label>

						<input type="text" name="wpep_signup_fees_label" value="<?php echo esc_attr( $signup_label ); ?>" placeholder="Label" class="form-control tamountfield">
						<input type="text" name="wpep_signup_fees_amount" value="<?php echo esc_attr( $signup_amount ); ?>" placeholder="Amount" class="form-control tamountfield">
						</div>
						</div>
					</div>
				</div>
				</div>
			</div>
	</div>
</main>
	<?php
} else {
	?>
	<main>
		<div class="MainDiv">
			<div class="FieldProTag pro_tag">
				<h3 class="FieldHeading">Additional charges</h3>
				<span class="pro_tag" id="pro_tag">Pro</span>
			</div>
			<div class="FieldImg pro_tag">
				<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/additional-charges-tab.png' ); ?>">
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
				<h3 class="proPopHeading">Enhance your square payment forms with premium features.</h3>
				<div class="featuresListPopup">
					<div class="row">
						<div class="col-6">
							<ul>
								<li><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-tick.png' ); ?>" class="" /> 
									<p>5+ digital wallets</p>
								</li>
								<li><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-tick.png' ); ?>" class="" /> 
									<p>Square product sync</p>
								</li>
							</ul>
						</div>
						<div class="col-6">
							<ul>	
								<li><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-tick.png' ); ?>" class="" /> 
									<p>Square gift card</p>
								</li>
								<li><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/popup-tick.png' ); ?>" class="" /> 
									<p>Manage subscriptions</p>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="integration_button_div">
					<a href="https://wpeasypay.com/pricing?utm_source=plugin&utm_medium=additional_charges" target="_blank" rel="noopener noreferrer" class="wpep-no-save-popup">
						<button type="button" class="upgradeBtn">
							Upgrade now <img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/upgrade-btn-arrow.png' ); ?>" class="" /> 
						</button>
					</a>
				</div>
			</div>
		</div>
	</div>
	<?php
}
?>
