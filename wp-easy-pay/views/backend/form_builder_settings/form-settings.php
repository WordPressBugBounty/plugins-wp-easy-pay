<?php
/**
 * Filename: form-settings.php
 * Description: form Settings backend.
 *
 * @package WP_Easy_Pay
 */

$wpep_radio_amounts         = get_post_meta( get_the_ID(), 'wpep_radio_amounts', true );
$wpep_dropdown_amounts      = get_post_meta( get_the_ID(), 'wpep_dropdown_amounts', true );
$price_selected             = ! empty( get_post_meta( get_the_ID(), 'PriceSelected', true ) ) ? get_post_meta( get_the_ID(), 'PriceSelected', true ) : '1';
$save_card_future           = get_post_meta( get_the_ID(), 'wpep_save_card', true );
$wpep_prods_without_images  = get_post_meta( get_the_ID(), 'wpep_prods_without_images', true );
$wpep_mailchimp_integration = get_post_meta( get_the_ID(), 'wpep_mailchimp_integration', true );
$wpep_products_with_labels  = get_post_meta( get_the_ID(), 'wpep_products_with_labels', true );
$wpep_subscription_trial    = get_post_meta( get_the_ID(), 'wpep_subscription_trial', true );




wp_localize_script(
	'wpep_backend_script',
	'wpep_form_setting_amounts',
	array(
		'wpep_radio_amounts'        => $wpep_radio_amounts,
		'wpep_dropdown_amounts'     => $wpep_dropdown_amounts,
		'PriceSelected'             => $price_selected,
		'wpep_tabular_products'     => $wpep_products_with_labels,
		'wp_payment_donation_nonce' => wp_create_nonce( 'donation_nonce' ),
	)
);

global $post;

$wpep_title                 = $post->post_title;
$wpep_form_theme_color      = ! empty( get_post_meta( get_the_ID(), 'wpep_form_theme_color', true ) ) ? get_post_meta( get_the_ID(), 'wpep_form_theme_color', true ) : '#5d97ff';
$wpep_content               = $post->post_content;
$wpep_payment_success_url   = get_post_meta( get_the_ID(), 'wpep_square_payment_success_url', true );
$wpep_payment_success_label = get_post_meta( get_the_ID(), 'wpep_square_payment_success_label', true );
$wpep_payment_success_msg   = get_post_meta( get_the_ID(), 'wpep_payment_success_msg', true );

$wpep_square_payment_box_1 = get_post_meta( get_the_ID(), 'wpep_square_payment_box_1', true );
$wpep_square_payment_box_2 = get_post_meta( get_the_ID(), 'wpep_square_payment_box_2', true );
$wpep_square_payment_box_3 = get_post_meta( get_the_ID(), 'wpep_square_payment_box_3', true );
$wpep_square_payment_box_4 = get_post_meta( get_the_ID(), 'wpep_square_payment_box_4', true );
$default_price_selected    = ! empty( get_post_meta( get_the_ID(), 'defaultPriceSelected', true ) ) ? get_post_meta( get_the_ID(), 'defaultPriceSelected', true ) : '';

$wpep_square_payment_type = get_post_meta( get_the_ID(), 'wpep_square_payment_type', true );

$wpep_square_user_defined_amount = get_post_meta( get_the_ID(), 'wpep_square_user_defined_amount', true );
$wpep_square_amount_type         = ! empty( get_post_meta( get_the_ID(), 'wpep_square_amount_type', true ) ) ? get_post_meta( get_the_ID(), 'wpep_square_amount_type', true ) : 'payment_custom';
$wpep_button_title               = get_post_meta( get_the_ID(), 'wpep_button_title', true );
$wpep_open_in_popup              = get_post_meta( get_the_ID(), 'wpep_open_in_popup', true );

$wpep_subscription_cycle_interval = get_post_meta( get_the_ID(), 'wpep_subscription_cycle_interval', true );
$wpep_subscription_cycle          = get_post_meta( get_the_ID(), 'wpep_subscription_cycle', true );
$wpep_subscription_length         = get_post_meta( get_the_ID(), 'wpep_subscription_length', true );
$wpep_organization_name           = get_post_meta( get_the_ID(), 'wpep_organization_name', true );

$wpep_square_payment_min = get_post_meta( get_the_ID(), 'wpep_square_payment_min', true );
$wpep_square_payment_max = get_post_meta( get_the_ID(), 'wpep_square_payment_max', true );

$wpep_show_wizard = get_post_meta( get_the_ID(), 'wpep_show_wizard', true );
$wpep_show_shadow = get_post_meta( get_the_ID(), 'wpep_show_shadow', true );
$wpep_btn_theme   = get_post_meta( get_the_ID(), 'wpep_btn_theme', true );

$currency_symbol_type = ! empty( get_post_meta( get_the_ID(), 'currencySymbolType', true ) ) ? get_post_meta( get_the_ID(), 'currencySymbolType', true ) : 'code';
$want_redirection     = ! empty( get_post_meta( get_the_ID(), 'wantRedirection', true ) ) ? get_post_meta( get_the_ID(), 'wantRedirection', true ) : 'No';
$redirection_delay    = ! empty( get_post_meta( get_the_ID(), 'redirectionDelay', true ) ) ? get_post_meta( get_the_ID(), 'redirectionDelay', true ) : '';

$enable_terms_condition = get_post_meta( get_the_ID(), 'enableTermsCondition', true );
$enable_quantity        = get_post_meta( get_the_ID(), 'enableQuantity', true );
$enable_coupon          = get_post_meta( get_the_ID(), 'enableCoupon', true );

$terms_label = ! empty( get_post_meta( get_the_ID(), 'termsLabel', true ) ) ? get_post_meta( get_the_ID(), 'termsLabel', true ) : '';
$terms_link  = ! empty( get_post_meta( get_the_ID(), 'termsLink', true ) ) ? get_post_meta( get_the_ID(), 'termsLink', true ) : '';

$wpep_subscription_trial_days = get_post_meta( get_the_ID(), 'wpep_subscription_trial_days', true );

$wpep_donation_goal_switch         = get_post_meta( get_the_ID(), 'wpep_donation_goal_switch', true );
$wpep_donation_goal_amount         = get_post_meta( get_the_ID(), 'wpep_donation_goal_amount', true );
$wpep_donation_goal_message_switch = get_post_meta( get_the_ID(), 'wpep_donation_goal_message_switch', true );
$wpep_donation_goal_message        = get_post_meta( get_the_ID(), 'wpep_donation_goal_message', true );
$wpep_donation_goal_form_close     = get_post_meta( get_the_ID(), 'wpep_donation_goal_form_close', true );
$wpep_enable_mailchimp             = get_option( 'wpep_enable_mailchimp', true );
$api_key                           = get_option( 'wpep_mailchimp_api_key', false );
$server                            = get_option( 'wpep_mailchimp_server', false );
$mailchimp_audience                = get_post_meta( get_the_ID(), 'wpep_mailchimp_audience', true );

if ( 'on' === $wpep_enable_mailchimp && $api_key && $server ) {

	$api_key = get_option( 'wpep_mailchimp_api_key', false );
	$server  = get_option( 'wpep_mailchimp_server', false );

	try {
		$client = new MailchimpMarketing\ApiClient();
		$client->setConfig(
			array(
				'apiKey' => $api_key,
				'server' => $server,
			)
		);

		$audience = $client->lists->getAllLists();
	} catch ( GuzzleHttp\Exception\ConnectException $e ) {

		echo esc_html( $e );
	}
}
?>

<main>
	<div class="formTypeWrapContainer">
		<label for="formType1">
			<input type="checkbox" name="wpep_open_in_popup" id="formType1" 
			<?php
			if ( 'on' === $wpep_open_in_popup ) {

				echo 'checked';
			}
			?>
			/>
			Open form in popup
		</label>

	</div>

	<div id="formPopup" style="display: none">
		<div class="globalSettingsa">
			<div class="globalSettingswrap">
				<h2>Global settings is active</h2>

				<?php $global_setting_url = admin_url( 'edit.php?post_type=wp_easy_pay&page=wpep-settings', 'https' ); ?>
				<a href="<?php echo esc_url( $global_setting_url ); ?>" class="btn btn-primary btnglobal">Go to Square Connect
					Settings</a>
			</div>
		</div>
	</div>
	<div id="formPage">
		<div class="testPayment">
			<h3 class="">Payment Form Details</h3>

			<div class="wpeasyPay__body">

				<div class="form-group">
					<label>Form Title:</label>
					<input type="text" class="form-control" placeholder="please enter title" name="post_title"
							value="<?php echo esc_attr( $wpep_title ); ?>"/>
				</div>

				<div class="form-group">
					<label>Form Description:</label>
					<textarea type="text" class="form-control form-control-textarea"
								placeholder="Please Enter description"
								name="post_content"> <?php echo esc_html( $wpep_content ); ?> </textarea>
				</div>

				<div class="form-group" id="popupWrapper">
					<label>Popup Button Title:</label>
					<input type="text" class="form-control" name="wpep_button_title"
							placeholder="please enter button title" name="button_title"
							value="<?php echo esc_attr( $wpep_button_title ); ?>"/>
				</div>


				<div class="form-group">
					<label>Select Payment Type:</label>
					<select class="form-control" name="wpep_square_payment_type" id="paymentTypeSel">
						<option value="simple" 
						<?php
						if ( 'simple' === $wpep_square_payment_type ) {
							echo 'selected';
						}
						?>
						> Simple Payment
						</option>

						<option value="donation" 
						<?php
						if ( 'donation' === $wpep_square_payment_type ) {
							echo 'selected';
						}
						?>
						> Donation Payment
						</option>
					</select>
				</div>

				<div id="donation" class="drop-payment-select-show-hide">
					<div class="form-group">
						<label>Organization name:</label>
						<input type="text" class="form-control" placeholder="please enter organization name"
								name="wpep_organization_name" value="<?php echo esc_attr( $wpep_organization_name ); ?>"/>
					</div>
				</div>
				<div class="form-group">
					<label>Amount Type:</label>
					<select class="form-control" name="wpep_square_amount_type" id="paymentDrop">
					
						<option value="payment_custom" 
						<?php
						if ( 'payment_custom' === $wpep_square_amount_type ) {
							echo 'selected';
						}
						?>
						> Payment custom layout
						</option>
					</select>

				</div>

				<?php

				$wpep_payment_mode = get_option( 'wpep_square_payment_mode_global' );

				if ( 'on' === $wpep_payment_mode ) {
					/* if live is on */
					$square_currency = get_option( 'wpep_square_currency_new', true );
				}

				if ( 'on' !== $wpep_payment_mode ) {
					/* if test is on */
					$square_currency = get_option( 'wpep_square_currency_test', true );
				}
				?>
				<div id="payment_radio"
					class="form-group drop-down-show-hide wpep_currency_<?php echo esc_attr( $square_currency . ' ' . $currency_symbol_type ); ?>">
					<textarea class="form-control" id="amountInList" name="amountInList"></textarea>
				</div>

				<div id="payment_drop"
					class="form-group drop-down-show-hide wpep_currency_<?php echo esc_attr( $square_currency . ' ' . $currency_symbol_type ); ?>">
					<textarea class="form-control" id="amountInDrop" name="amountInDrop"></textarea>
				</div>
				<div id="payment_custom"
					class="form-group drop-down-show-hide wpep_currency_<?php echo esc_attr( $square_currency . ' ' . $currency_symbol_type ); ?>">
					<div class="paymentSelect paymentSelectB">
						<input type="radio" class="defaultPriceSelected" name="defaultPriceSelected" value="dollar1"
							<?php
							if ( 'dollar1' === $default_price_selected || '' === $default_price_selected ) :
								echo 'checked';
endif;
							?>
							>
						<div class="selection not-empty">
							<input class="form-group" id="doller1" type="text" placeholder="Enter amount"
									name="wpep_square_payment_box_1" value="<?php echo esc_attr( $wpep_square_payment_box_1 ); ?>"/>
						</div>

						<input type="radio" class="defaultPriceSelected" name="defaultPriceSelected" value="dollar2"
							<?php
							if ( 'dollar2' === $default_price_selected ) :
								echo 'checked';
endif;
							?>
							>
						<div class="selection not-empty">
							<input class="form-group" id="doller2" type="text" placeholder="Enter amount"
									value="<?php echo esc_attr( $wpep_square_payment_box_2 ); ?>" name="wpep_square_payment_box_2"/>
						</div>

						<input type="radio" class="defaultPriceSelected" name="defaultPriceSelected" value="dollar3"
							<?php
							if ( 'dollar3' === $default_price_selected ) :
								echo 'checked';
endif;
							?>
							>
						<div class="selection empty">
							<input class="form-group" id="doller3" type="text" placeholder="Enter amount"
									value="<?php echo esc_attr( $wpep_square_payment_box_3 ); ?>" name="wpep_square_payment_box_3"/>
						</div>

						<input type="radio" class="defaultPriceSelected" name="defaultPriceSelected" value="dollar4"
							<?php
							if ( 'dollar4' === $default_price_selected ) :
								echo 'checked';
endif;
							?>
							>
						<div class="selection secLast empty">
							<input class="form-group" id="doller4" type="text" placeholder="Enter amount"
									value="<?php echo esc_attr( $wpep_square_payment_box_4 ); ?>" name="wpep_square_payment_box_4"/>
						</div>

						<div class="selectionBlock saveCardFeature">
							<label for="checkbox1"><input type="checkbox" id="checkbox1"
															name="wpep_square_user_defined_amount" 
															<?php
															if ( 'on' === $wpep_square_user_defined_amount ) {
																echo 'checked';
															}
															?>
								> Enable other
								amount field on payment form</label>
							
						</div>

						<div id="paymentLimit" style="display: none;">
							<div class="selection empty">
								<input class="form-group" id="paymin" type="text" placeholder="Min amount"
										name="wpep_square_payment_min" value="<?php echo esc_attr( $wpep_square_payment_min ); ?>"/>

							</div>
							<div class="selection empty">
								<input class="form-group" id="paymax" type="text" placeholder="Max amount"
										name="wpep_square_payment_max" value="<?php echo esc_attr( $wpep_square_payment_max ); ?>"/>
							</div>
						</div>
					</div>

				</div>
			


				<?php

				if ( isset( $wpep_products_with_labels ) && ! empty( $wpep_products_with_labels ) ) {

					echo '<div id="payment_tabular" class="form-group drop-down-show-hide">';
					$count = 0;
					foreach ( $wpep_products_with_labels as $key => $product ) {

						echo '<div class="multiInput">';
						echo '<div class="inputWrapperCus">';
						echo '<div class="cusblock1">';

						if ( isset( $product['products_url'] ) && ! empty( $product['products_url'] ) ) {
							$product_url = $product['products_url'];
						} else {
							$product_url = WPEP_ROOT_URL . 'assets/backend/img/placeholder-image.png';
						}
						echo '<div class="timgfield"><input type="file" name="wpep_tabular_products_image[]" data-proid="' . esc_attr( $count ) . '" onchange="readURL(this);"><img src="' . esc_url( $product_url ) . '" id="image_div_' . esc_html( $count ) . '" width="66px"></div>';
						echo '<input type="text" name="wpep_tabular_products_price[]" value="' . esc_attr( $product['amount'] ) . '"  placeholder="Product Price" class="form-control tamountfield">';
						echo '<input type="text" name="wpep_tabular_products_label[]" value="' . esc_attr( $product['label'] ) . '" placeholder="Label" class="form-control tlabbelfield">';
						echo '<input type="text" name="wpep_tabular_products_qty[]" value="' . esc_attr( $product['quantity'] ) . '" placeholder="Quantity" class="form-control tqtufield">';
						echo '<input type="hidden" name="wpep_tabular_product_hidden_image_nonce" value="' . esc_attr( $nonce ) . '" />';
						echo '<input type="hidden" name="wpep_tabular_product_hidden_image[]" value="' . esc_url( $product['products_url'] ) . '">';
						echo '</div>';
						echo '<input type="button" class="btnplus" onclick="wpep_add_repeator_field_product(' . esc_html( $count ) . ');" value="">';

						if ( 0 === $count ) {
							echo '<input type="button" class="btnminus" value="">';
						} else {
							echo '<input type="button" class="btnminus" onclick="wpep_delete_repeator_field_product(this);" value="">';
						}

						echo '</div>';
						echo '</div>';
						++$count;
					}


					echo '</div>';
				}
				?>
				<div class="formwrapper">
					<div class="formFlex formFlexAllow form-group">
						<label>Redirection on success:</label>
						<select name="wantRedirection" id="allowRedirection" class="form-control">
							<option>Please Select</option>
							<option value="Yes"
								<?php
								if ( 'Yes' === $want_redirection ) :
									echo 'selected="selected"';
endif;
								?>
								>Yes
							</option>
							<option
								value="No" 
								<?php
								if ( 'No' === $want_redirection ) :
									echo 'selected="selected"';
endif;
								?>
								>
								No
							</option>
						</select>

					</div>
					<div class="formFlex formFlexTime form-group">
						<label>Redirection in seconds:</label>
						<input id="redirectionCheck" class="redirectionCheckInput form-control" type="number"
								name="redirectionDelay" placeholder="Example: 5" value="<?php echo esc_attr( $redirection_delay ); ?>">
					</div>


					<div class="formFlex form-group">
						<label>Payment Success Button Label:</label>
						<input type="text" class="form-control" placeholder="Enter Label"
								name="wpep_square_payment_success_label"
								value="<?php echo esc_attr( $wpep_payment_success_label ); ?>"/>
					</div>
					<div class="formFlex form-group">
						<label>Payment Success Button URL:</label>
						<input type="text" class="form-control" placeholder="Redirect url"
								name="wpep_square_payment_success_url" value="<?php echo esc_url( $wpep_payment_success_url ); ?>"/>
					</div>
				</div>

				<div class="form-group">
					<label>Payment Success Message:</label>
					<textarea type="text" class="form-control form-control-textarea"
								placeholder="Please Enter success message"
								name="wpep_payment_success_msg"> <?php echo esc_html( $wpep_payment_success_msg ); ?> </textarea>
				</div>

				<div class="clearfix" id="enableTCWrap" style="display: none;">
					<div class="form-group wpep-form-group-half-left">
						<label>Link Label:</label>
						<input type="text" class="form-control" placeholder="Please Enter Label" name="termsLabel"
								value="<?php echo esc_attr( $terms_label ); ?>">
					</div>
					<div class="form-group wpep-form-group-half-right">
						<label>Link to page:</label>
						<input type="text" class="form-control" placeholder="Please Enter url for user redirect"
								name="termsLink" value="<?php echo esc_url( $terms_link ); ?>">
					</div>
				</div>
			</div>
		</div>

	</div>

</main>
