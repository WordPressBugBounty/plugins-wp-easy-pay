<?php
/**
 * Filename: coupons.php
 * Description: coupons frontend.
 *
 * @package WP_Easy_Pay
 */

?>
// continue on Monday 8 feb 2021.

<div id="wpep-coupons-<?php echo esc_attr( $wpep_current_form_id ); ?>" class="wpep-coupons">
	<div class="s_ft noMulti">
		<h2>Discount</h2>
	</div>
	<h5 class="noSingle">Discount</h5>
	<div class="coupon-field form-group">
		<label class="wizard-form-text-label" data-label-show="yes"> Enter Coupon Code </label>
		<input type="text" class="form-control" name="wpep-coupon">
		<input type="button" class="cp-apply wpep-single-form-submit-btn wpep-single-form-submit-btn" name="wpep-cp-submit" value="Apply" />
	</div>
</div>
