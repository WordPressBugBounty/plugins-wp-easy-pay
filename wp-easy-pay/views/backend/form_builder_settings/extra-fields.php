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
	<div class="contentBody">
		<div class="custom_container_body">
			<div class="custom_img">
				<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/cutom_fields.png' ); ?>" class="wpep_logo">
			</div>
			<div class="custom_content">
				<h2 class="custom_heading">Custom Fields</h2>
				<p class="custom_text">Tailor your checkout process with WP EasyPay’s Extra Field/Additional charges feature. Collect specific customer information through customizable fields and enhance your revenue system.</p>
				<div class="custom_button_div">
					<a href="https://wpeasypay.com/pricing/?utm_source=plugin&utm_medium=customization_charges" class="custom_button">
						Upgrade to Premium
					</a>
				</div>
			</div>
		</div>
		<div class="custom_container_body">
			<div class="custom_content">
				<h2 class="custom_heading">Additional Charges</h2>
				<ul class="custom_square-list">
					<li>Tailor the checkout process to your specific needs by adding extra fields</li>
					<li>Multiple fields are available to create forms that fit all industries</li>
					<li>Boost revenue with ease by implementing additional charges for premium services</li>
					<li>Provide a personalized and efficient purchasing experience.</li>
				</ul>
			</div>
			<div class="custom_img">
				<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/additional_charges.png' ); ?>" class="wpep_logo">
			</div>
		</div>
	</div>
	<div class="contentFooter">
		<div class="custom_footer_container">
			<div class="custom_footer_table_text">
				<p>Upgrade to <strong>WP EasyPay Premium</strong> for enhanced features and unparalleled convenience</p>
			</div>
			<div class="custom_footer_table_button">
				<a href="https://wpeasypay.com/pricing/?utm_source=plugin&utm_medium=customization_charges" class="custom_footer_button">
						Upgrade to Premium
				</a>
			</div>
		</div>
	</div>
<input type="hidden" id="wpep_form_builder_json" name="wpep_square_form_builder_fields"
			value='<?php echo esc_attr( $form_fields ); ?>'>
</main>

<style>
	.form-wrap.form-builder .frmb-control li {
		margin: 0px 0px -2px 0;
		padding: 20px;
		/* border-radius: 0px !important; */
		transition: all 0.3s ease;
		box-shadow: inset 0 0 0 1px #ebebeb;
	}
</style>
