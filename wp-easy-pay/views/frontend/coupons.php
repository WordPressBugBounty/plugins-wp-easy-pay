<?php
/**
 * Filename: coupons.php
 * Description: coupons frontend.
 *
 * @package WP_Easy_Pay
 */

?>

<div id="wpep-coupons-<?php echo esc_attr( $wpep_current_form_id ); ?>" class="wpep-coupons">
	<div class="s_ft noMulti">
		<h2>Discount</h2>
	</div>
	<h5 class="noSingle">Discount</h5>
	<div class="coupon-field form-group">
		<label class="wizard-form-text-label" data-label-show="yes"> Enter Coupon Code </label>
		<input type="text" class="form-control" name="wpep-coupon">
		<input type="button" class="cp-apply wpep-single-form-submit-btn wpep-single-form-submit-btn  wpep-single-form-"<?php echo esc_attr( $wpep_current_form_id ); ?> name="wpep-cp-submit" value="Apply" />
	</div>
	<div id="wpep_coupon_applied_<?php echo esc_attr( $wpep_current_form_id ); ?>" class="wpep_coupon_applied" style="display:none">
		<span class="wpep_coupon_applied_text"><i class="fa fa-tag"></i> Coupon applied</span>
		<button class="wpep_coupon_remove_btn">Remove</button>
	</div>
</div>
