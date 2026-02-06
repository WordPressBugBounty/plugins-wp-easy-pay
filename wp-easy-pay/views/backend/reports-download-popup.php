<?php
/**
 * WP EasyPay Reports Popup Template.
 *
 * This file renders the reports popup for exporting transaction details in the WP EasyPay plugin.
 * The popup allows users to select specific fields and download a report of transaction details.
 *
 * @package WP EasyPay
 */

?>
<div id="wpep-reports-popup-container" style="display:none;">
	<div class="wpep-reports-popup-overlay">&nbsp;</div>
	<div id="wpep-reports-popup">
		<div id="wpep-reports-content">
			<form action="" method="POST" id="wpep-form-selector">
				<div class="wpep-reports-header">
					<h3><?php esc_html_e( 'Download Transaction Details', 'wp_easy_pay' ); ?></h3>
					<a href="#" class="wpep-reports-close">x</a>
				</div>
				<div class="wpep-reports-body">
					<!-- Form Dropdownn -->
					<div class="wpep-form-select">
						<label for="wpep-form-selection"><?php esc_html_e( 'Select Form', 'wp_easy_pay' ); ?></label>
						<select id="wpep-form-selection">
							<option value=""><?php esc_html_e( 'Select a form', 'wp_easy_pay' ); ?></option>
							<?php
							$args  = array(
								'numberposts' => -1,
								'post_type'   => 'wp_easy_pay',
							);
							$forms = get_posts( $args );

							if ( ! empty( $forms ) ) {
								foreach ( $forms as $form ) {
									$payment_type = get_post_meta( $form->ID, 'wpep_square_payment_type', true );

									if ( in_array( $payment_type, array( 'simple', 'donation' ), true ) ) {
										$form_title = ! empty( trim( $form->post_title ) ) ? $form->post_title : 'Payment Form - ' . $form->ID;
										?>
										<option value="<?php echo esc_attr( $form->ID ); ?>">
											<?php echo esc_html( $form_title ); ?>
										</option>
										<?php
									}
								}
							}
							?>
						</select>

					</div>

					<!-- Select Fields Section -->
					<div class="wpep-check-all-fields" style="display:none;">
						<h4 class="wpep-select-fields">Select Form Fields</h4>
						<div class="wpep-check-all">
							<input type="checkbox" id="wpep-check-all">
							<label for="wpep-check-all"><?php echo esc_html__( 'Check All', 'wp_easy_pay' ); ?></label>
						</div>
					</div>

					<!-- Fields List with Scroll -->
					<div id="wpep-form-fields" class="wpep-form-fields-container" style="display:none;">
						<ul id="wpep-dynamic-fields" class="wpep-reports-list"></ul>
					</div>

				</div>
				<div class="wpep-reports-footer" style="display:none;">
					<button id="wpep-download-now" class="wpep-button wpep-button-primary" disabled><?php esc_html_e( 'Export CSV', 'wp_easy_pay' ); ?></button>
				</div>
			</form>
		</div>
	</div>
	
	<input type="hidden" id="wpep-download-report-nonce" name="wpep-download-report-nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpep-download-report-nonce' ) ); ?>" />
</div>
