<?php
/**
 * Filename: form_helper_functions.php
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
	echo "<label data-label-show='" . esc_attr( $checkbox_group->hide_label ) . "'>";
	echo esc_html( $checkbox_group->label );
	echo '' . ( ( isset( $checkbox_group->required ) ) ? esc_html( $if_required ) : '' ) . '</label>';
	echo "<div class='wpep-checkboxWrapper'>";
	foreach ( $checkbox_group->values as $value ) {
		echo "<div class='wizard-form-checkbox " . ( ( isset( $checkbox_group->required ) ) ? 'wpep-required' : '' ) . "'><div class='form-group wpep-m-0'><input type='checkbox' name='" . esc_attr( $checkbox_group->name ) . "' data-label='" . esc_attr( $value->label ) . "' data-main-label='" . esc_attr( $checkbox_group->label ) . "'  id='radio_id_" . esc_attr( $value->value ) . "' value='" . esc_attr( $value->value ) . "' required='" . ( ( isset( $checkbox_group->required ) ) ? 'true' : 'false' ) . "'><label for='radio_id_" . esc_attr( $value->value ) . "'>" . esc_html( $value->label ) . '</label></div></div>';
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
	echo "<label data-label-show='" . esc_attr( $radio_group->hide_label ) . "'>";
	echo esc_html( $radio_group->label );
	echo '' . ( ( isset( $radio_group->required ) ) ? esc_html( $if_required ) : '' ) . '</label>';
	echo "<div class='wpep-radioWrapper'>";
	foreach ( $radio_group->values as $value ) {
		echo "<div class='wizard-form-radio " . ( ( isset( $radio_group->required ) ) ? 'wpep-required' : '' ) . "'><div class='form-group wpep-m-0'><input type='radio' name='" . esc_attr( $radio_group->name ) . "' id='radio_id_" . esc_attr( $value->value ) . "' data-label='" . esc_attr( $value->label ) . "' data-main-label='" . esc_attr( $radio_group->label ) . "' value='" . esc_attr( $value->value ) . "' required='" . ( ( isset( $radio_group->required ) ) ? 'true' : 'false' ) . "'><label for='radio_id_" . esc_attr( $value->value ) . "'>" . esc_html( $value->label ) . '</label></div></div>';
	}
	if ( isset( $radio_group->description ) && '' !== $radio_group->description ) {
		echo "<span class='wpep-help-text'>" . esc_html( $radio_group->description ) . '</span>';
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
	echo "<label data-label-show='" . esc_html( $select_dropdown->hide_label ) . "'>";
	echo esc_html( $select_dropdown->label );
	echo '' . ( ( isset( $select_dropdown->required ) ) ? esc_html( $if_required ) : '' ) . '</label>';

	echo "<div class='form-group " . ( ( isset( $select_dropdown->required ) ) ? 'wpep-required' : '' ) . "'><select data-label='" . esc_attr( $select_dropdown->label ) . "' class='" . esc_attr( $select_dropdown->class_name ) . "' name='" . esc_attr( $select_dropdown->name ) . "' " . ( isset( $select_dropdown->multiple ) ? 'multiple style="height:auto;"' : '' ) . "  required='" . ( ( isset( $select_dropdown->required ) ) ? 'true' : 'false' ) . "'>";

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

	$label       = isset( $textarea->label ) ? $textarea->label : '';
	$placeholder = isset( $textarea->placeholder ) ? $textarea->placeholder : 'Text Area';
	$class_name  = isset( $textarea->classname ) ? $textarea->class_name : '';
	$value       = isset( $textarea->value ) ? $textarea->value : '';
	$name        = isset( $textarea->name ) ? $textarea->name : '';
	$required    = isset( $textarea->required ) ? 'true' : 'false';
	$if_required = " <span class='fieldReq'>*</span>";
	if ( 'true' === $required ) {
		echo '<div class="form-group text-field wpep-required">
		<label class="wizard-form-text-label"> ' . ( ( isset( $label ) ) ? esc_html( $label ) : '' ) . esc_html( $if_required ) . '</label><textarea rows="6" data-label="' . esc_attr( $label ) . '" name="' . esc_attr( $name ) . '" placeholder="' . esc_attr( $placeholder ) . '" class="' . esc_attr( $class_name ) . ' form-control" rows="4" cols="100" required="' . esc_attr( $required ) . '">' . esc_textarea( $value ) . '</textarea></div>';
	} else {
		echo '<div class="form-group text-field"><label class="wizard-form-text-label"> ' . ( ( isset( $label ) ) ? esc_html( $label ) : '' ) . ' </label><textarea rows="6" data-label="' . esc_attr( $label ) . '" name="' . esc_attr( $name ) . '" placeholder="' . esc_attr( $placeholder ) . '" class="' . esc_attr( $class_name ) . ' form-control" rows="4" cols="100" required="' . esc_attr( $required ) . '">' . esc_textarea( $value ) . '</textarea></div>';
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
	$wpep_square_cashapp = get_option( 'wpep_square_cashapp', false );
	if ( 'on' === $wpep_square_cashapp ) {
		?>
		<div id="cash-app-pay"></div>
		<div id="payment-status-container"></div>
		<?php
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
	$if_required = " <span class='fieldReq'>*</span>";
	echo '<label class="labelupload">' . esc_html( $file_upload->label ) . ( ( isset( $file_upload->required ) ) ? esc_html( $if_required ) : '' ) . '</label>';
	echo esc_html( "<div class='form-group file-upload-wrapper' data-text='Select your file!'><input accept='.gif, .jpg, .png, .doc, .pdf' type='$file_upload->type' name='$file_upload->name' id='wpep_file_upload_field' class='file-upload-field $file_upload->className'></div>" );
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
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/card-front.jpg' ); ?>" alt="Avatar" width="20">
				</div>
				<div class="CardIcon-back">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/frontend/img/card-back.jpg' ); ?>" alt="Avatar" width="20">
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
		<button id="sq-creditcard" class="wpep-free-form-submit-btn float-right wpep-disabled zeeeeee"><?php echo esc_html( get_option( 'wpep_free_btn_text' ) ); ?>
			<span>
				<b id="dosign" style="display: none;">$</b><small id="amount_display_<?php echo esc_attr( $wpep_current_form_id ); ?>" class="display"></small>
				<input type="hidden" name="wpep-selected-amount" value="">
			</span>
		</button>    
		</div>
	</div>

	<?php
	ob_end_flush();
}
