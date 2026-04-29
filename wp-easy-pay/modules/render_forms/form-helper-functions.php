<?php
/**
 * Filename: form-helper-functions.php
 * Description: form helper functions.
 *
 * @package WP_Easy_Pay
 */

/**
 * Print the checkbox group field.
 *
 * This function prints the checkbox group field on the frontend form.
 * It displays the label, options, and optional required indicator based on the provided data.
 *
 * @param object $checkbox_group An object containing checkbox group field data.
 */
function wpep_print_checkbox_group( $checkbox_group ) {

	$if_required = " <span class='fieldReq'>*</span>";
	$hide_label  = 'hideLabel';
	echo "<label data-label-show='" . esc_attr( $checkbox_group->$hide_label ) . "'>";
	echo esc_html( $checkbox_group->label );
	echo '' . ( ( isset( $checkbox_group->required ) ) ? wp_kses_post( $if_required ) : '' ) . '</label>';
	echo "<div class='wpep-checkboxWrapper'>";
	foreach ( $checkbox_group->values as $value ) {
		echo "<div class='wizard-form-checkbox " . esc_html( ( isset( $checkbox_group->required ) ) ? 'wpep-required' : '' ) . "'><div class='form-group wpep-m-0'><input type='checkbox' name='" . esc_attr( $checkbox_group->name ) . "' data-label='" . esc_attr( $value->label ) . "' data-main-label='" . esc_attr( $checkbox_group->label ) . "'  id='radio_id_" . esc_attr( $value->value ) . "' value='" . esc_attr( $value->value ) . "' required='" . esc_attr( ( isset( $checkbox_group->required ) ) ? 'true' : 'false' ) . "'><label for='radio_id_" . esc_attr( $value->value ) . "'>" . esc_html( $value->label ) . '</label></div></div>';
	}
	if ( isset( $checkbox_group->description ) && '' !== $checkbox_group->description ) {
		echo "<span class='wpep-help-text'>" . esc_html( $checkbox_group->description ) . '</span>';
	}
	echo '</div>';
}

/**
 * Print the radio group field.
 *
 * This function prints the radio group field on the frontend form.
 * It displays the label, options, and optional required indicator based on the provided data.
 *
 * @param object $radio_group An object containing radio group field data.
 */
function wpep_print_radio_group( $radio_group ) {

	$if_required = " <span class='fieldReq'>*</span>";
	$hide_label  = 'hideLabel';
	echo "<label data-label-show='" . esc_attr( $radio_group->$hide_label ) . "'>";
	echo wp_kses_post( $radio_group->label );
	echo wp_kses_post( ( isset( $radio_group->required ) ) ? $if_required : '' ) . '</label>';
	echo "<div class='wpep-radioWrapper'>";
	foreach ( $radio_group->values as $value ) {
		echo "<div class='wizard-form-radio " . esc_html( ( isset( $radio_group->required ) ) ? 'wpep-required' : '' ) . "'><div class='form-group wpep-m-0'><input type='radio' class='wpep-form-radio-btn' name='" . esc_attr( $radio_group->name ) . "' id='radio_id_" . esc_html( $value->value ) . "' data-label='" . esc_attr( $value->label ) . "' data-main-label='" . esc_attr( $radio_group->label ) . "' value='" . esc_attr( $value->value ) . "' required='" . ( ( isset( $radio_group->required ) ) ? 'true' : 'false' ) . "'><label for='radio_id_" . esc_html( $value->value ) . "'>" . esc_html( $value->label ) . '</label></div></div>';
	}
	if ( isset( $radio_group->description ) && '' !== $radio_group->description ) {
		echo "<span class='wpep-help-text'>" . wp_kses_post( $radio_group->description ) . '</span>';
	}
	echo '</div>';
}

/**
 * Print the select dropdown field.
 *
 * This function prints the select dropdown field on the frontend form.
 * It displays the label, options, and optional required indicator based on the provided data.
 *
 * @param object $select_dropdown An object containing select dropdown field data.
 */
function wpep_print_select_dropdown( $select_dropdown ) {
	$if_required = " <span class='fieldReq'>*</span>";
	$class_name  = 'className';
	$hide_label  = 'hideLabel';
	echo "<label data-label-show='" . esc_attr( $select_dropdown->$hide_label ) . "'>";
	echo esc_html( $select_dropdown->label );
	echo '' . wp_kses_post( ( isset( $select_dropdown->required ) ) ? $if_required : '' ) . '</label>';

	echo "<div class='form-group " . esc_html( ( isset( $select_dropdown->required ) ) ? 'wpep-required' : '' ) . "'><select data-label='" . esc_attr( $select_dropdown->label ) . "' class='" . esc_attr( $select_dropdown->$class_name ) . "' name='" . esc_attr( $select_dropdown->name ) . "' " . ( isset( $select_dropdown->multiple ) ? 'multiple style="height:auto;"' : '' ) . "  required='" . ( ( isset( $select_dropdown->required ) ) ? 'true' : 'false' ) . "'>";

	foreach ( $select_dropdown->values as $value ) {
		echo "<option value='" . esc_attr( $value->value ) . "'>" . esc_html( $value->label ) . '</option>';
	}

	echo '</select>';
	if ( isset( $select_dropdown->description ) && '' !== $select_dropdown->description ) {
		echo "<span class='wpep-help-text'>" . esc_html( $select_dropdown->description ) . '</span>';
	}
	echo '</div>';
}

/**
 * Print the textarea field.
 *
 * This function prints the textarea field on the frontend form.
 * It displays the label, input field, and optional required indicator based on the provided data.
 *
 * @param object $textarea An object containing textarea field data.
 */
function wpep_print_textarea( $textarea ) {
	$class_name  = 'className';
	$label       = isset( $textarea->label ) ? $textarea->label : '';
	$placeholder = isset( $textarea->placeholder ) ? $textarea->placeholder : 'Text Area';
	$classname   = isset( $textarea->$class_name ) ? $textarea->$class_name : '';
	$value       = isset( $textarea->value ) ? $textarea->value : '';
	$name        = isset( $textarea->name ) ? $textarea->name : '';
	$required    = isset( $textarea->required ) ? 'true' : 'false';
	$if_required = " <span class='fieldReq'>*</span>";
	if ( true === $required ) {
		echo '<div class="form-group text-field wpep-required">
		<label class="wizard-form-text-label"> ' . esc_html( ( isset( $label ) ) ? $label : '' ) . wp_kses_post( $if_required ) . '</label><textarea rows="6" maxlength="' . esc_attr($textarea->maxlength) . '" data-label="' . esc_attr( $label ) . '" name="' . esc_attr( $name ) . '" class="' . esc_attr( $classname ) . ' form-control" rows="4" cols="100" required="' . esc_attr( $required ) . '">' . esc_html( $value ) . '</textarea>';
		if ( isset( $textarea->description ) && '' !== $textarea->description ) {
			echo "<span class='wpep-help-text'>" . esc_html( $textarea->description ) . '</span>';
		}
		echo '</div>';
	} else {
		echo '<div class="form-group text-field"><label class="wizard-form-text-label"> ' . esc_html( ( isset( $label ) ) ? $label : '' ) . ' </label><textarea rows="6" maxlength="' . esc_attr($textarea->maxlength) . '"  data-label="' . esc_attr( $label ) . '" name="' . esc_attr( $name ) . '" class="' . esc_attr( $classname ) . ' form-control" rows="4" cols="100" required="' . wp_kses_post( $required ) . '">' . esc_html( $value ) . '</textarea>';
		if ( isset( $textarea->description ) && '' !== $textarea->description ) {
			echo "<span class='wpep-help-text'>" . esc_html( $textarea->description ) . '</span>';
		}
		echo '</div>';
	}
}

/**
 * Print the credit card fields for a payment form.
 *
 * This function prints the credit card fields for a specific payment form on the frontend.
 * It may display various credit card-related input fields required for the payment process.
 *
 * @param int $current_form_id The ID of the payment form for which to print the credit card fields.
 */
function wpep_print_credit_card_fields( $current_form_id ) {

	ob_start();
	?>

	<div id="form-container">
			<div id="card-container-<?php echo esc_attr( $current_form_id ); ?>"></div>
			<div id="payment-status-container"></div>
	</div>

	<?php
	ob_end_flush();
}

/**
 * Prints the captcha field based on selected provider (reCAPTCHA, hCaptcha, Turnstile, or None).
 *
 * This function checks the selected captcha provider and generates the appropriate HTML
 * for rendering the captcha widget on the form.
 *
 * - If reCAPTCHA is selected, it outputs reCAPTCHA widget (v2 visible, v3 hidden).
 * - If hCaptcha is selected, it outputs hCaptcha widget.
 * - If Turnstile is selected, it outputs Cloudflare Turnstile widget.
 * - If None is selected, no captcha is displayed.
 *
 * @param int $current_form_id The ID of the current form.
 *
 * @return void
 */
function wpep_print_recaptcha_fields( $current_form_id ) { // phpcs:ignore
	$captcha_provider = get_option( 'wpep_captcha_provider', 'none' );
	
	// If none is selected, don't show any captcha
	if ( 'none' === $captcha_provider || empty( $captcha_provider ) ) {
		return;
	}
	
	ob_start();
	
	// reCAPTCHA (V2 or V3)
	if ( 'recaptcha' === $captcha_provider ) {
		$recaptcha_version = get_option( 'wpep_recaptcha_version', 'v2' );
		
		if ( 'v2' === $recaptcha_version ) {
			$recaptcha_site_key_v2 = get_option( 'wpep_recaptcha_site_key_v2' );
			if ( ! empty( $recaptcha_site_key_v2 ) ) {
				?>
				<div class="wpep-captcha-wrapper">
					<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $recaptcha_site_key_v2 ); ?>"></div>
				</div>
				<?php
			}
		} elseif ( 'v3' === $recaptcha_version ) {
			$recaptcha_site_key_v3 = get_option( 'wpep_recaptcha_site_key_v3' );
			if ( ! empty( $recaptcha_site_key_v3 ) ) {
				?>
				<div class="wpep-captcha-wrapper">
					<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
					<input type="hidden" name="action" value="validate_captcha">
				</div>
				<?php
			}
		}
	}
	
	// hCaptcha
	if ( 'hcaptcha' === $captcha_provider ) {
		$hcaptcha_site_key = get_option( 'wpep_hcaptcha_site_key' );
		if ( ! empty( $hcaptcha_site_key ) ) {
			?>
			<div class="wpep-captcha-wrapper">
				<div class="h-captcha" data-sitekey="<?php echo esc_attr( $hcaptcha_site_key ); ?>"></div>
			</div>
			<?php
		}
	}
	
	// Cloudflare Turnstile
	if ( 'turnstile' === $captcha_provider ) {
		$turnstile_site_key = get_option( 'wpep_turnstile_site_key' );
		if ( ! empty( $turnstile_site_key ) ) {
			?>
			<div class="wpep-captcha-wrapper">
				<div class="cf-turnstile" data-sitekey="<?php echo esc_attr( $turnstile_site_key ); ?>"></div>
			</div>
			<?php
		}
	}
	
	ob_end_flush();
}

/**
 * Print the file upload field.
 *
 * This function prints the file upload field on the frontend form.
 * It displays the label, input field, and optional required indicator based on the provided data.
 *
 * @param object $file_upload An object containing file upload field data.
 */
function wpep_print_file_upload( $file_upload ) {
	$if_required        = '';
	$required           = false;
	$is_multiple        = isset( $file_upload->multiple );
	$input_name         = $file_upload->name . ( $is_multiple ? '[]' : '' );
	$multiple_attribute = $is_multiple ? ' multiple' : '';
	if ( isset( $file_upload->required ) ) {
		$if_required = " <span class='fieldReq'>*</span>";
		$required    = true;
	}
	$class_name         = 'className';
	if ( true === $required ) {
		echo "<label class='labelupload'>" . esc_html( $file_upload->label ) . wp_kses_post( $if_required ) . '</label>';
		echo "<div class='form-group file-upload-wrapper wpep-required' data-text='Select your file!'>";
		echo "<input accept='.gif, .jpg, .png, .doc, .pdf' type='" . esc_attr( $file_upload->type ) . "' name='" . esc_attr( $input_name ) . "' id='wpep_file_upload_field' class='file-upload-field " . esc_html( $file_upload->$class_name ) . "'" . esc_attr( $multiple_attribute ) . " required='required'>";
	} else {
		echo "<label class='labelupload'>" . esc_html( $file_upload->label ) . wp_kses_post( $if_required ) . '</label>';
		echo "<div class='form-group file-upload-wrapper' data-text='Select your file!'>";
		echo "<input accept='.gif, .jpg, .png, .doc, .pdf' type='" . esc_attr( $file_upload->type ) . "' name='" . esc_attr( $input_name ) . "' id='wpep_file_upload_field' class='file-upload-field " . esc_html( $file_upload->$class_name ) . "'" . esc_attr( $multiple_attribute ) . '>';

	}

	echo '</div>';
}

/**
 * Print the credit card fields for free payment form.
 *
 * This function prints the credit card fields for the free payment form on the frontend.
 * It may display various credit card-related input fields required for the payment process.
 */
function wpep_print_credit_card_fields_free() {
	ob_start();

	if ( ! isset( $wpep_current_form_id ) ) {
		$wpep_current_form_id = 1; // free form.
	}
	?>

	<div id="form-container">

		<div class="form-group form-control-wrap cred-card-wrap">
			<div class="CardIcon">
				<div class="CardIcon-inner">
					<div class="CardIcon-front">
						<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/card-front.jpg' ); ?>" alt="Avatar"
							width="20">
					</div>
					<div class="CardIcon-back">
						<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/card-back.jpg' ); ?>" alt="Avatar"
							width="20">
					</div>
				</div>
			</div>

			<div class="form-control-1 input-card" id="sq-card-number"></div>

			<div class="cred">
				<div class="form-control-1 input-date" id="sq-expiration-date"></div>
				<div class="form-control-1 input-ccv abc" id="sq-cvv"></div>
			</div>

		</div>
		<div class="form-group form-control-wrap pcode">
			<div class="form-control-1 input-postal" id="sq-postal-code"></div>
		</div>


		<div class="selection" id="showPayment">
			<div class="otherpInput">

				<input class="form-control text-center customPayment" id="wpep_user_defined_amount" name="wpep_user_defined_amount" value="" type="number" step="1" min="1" max="999" />


			</div>
		</div>


		<div class="btnGroup ifSingle">
			<button id="sq-creditcard"  class="wpep-free-form-submit-btn float-right wpep-disabled zeeeeee"><?php echo esc_html( get_option( 'wpep_free_btn_text' ) ); ?>
				<span>
					<b id="dosign" style="display: none;">$</b><small id="amount_display_<?php echo esc_attr( $wpep_current_form_id ); ?>" class="display"></small>
					<input type="hidden" id="wpep-selected-amount-<?php echo esc_attr( $wpep_current_form_id ); ?>" name="wpep-selected-amount" value="">
				</span>
			</button>            
		</div>
	</div>

	<?php
	ob_end_flush();
}
