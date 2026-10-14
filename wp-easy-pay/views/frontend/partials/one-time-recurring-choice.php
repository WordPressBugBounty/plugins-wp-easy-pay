<?php
/**
 * One-time + recurring customer choice UI.
 *
 * @package WP_Easy_Pay
 */

$wpep_otr_section = isset( $wpep_otr_section ) ? $wpep_otr_section : 'full';

$wpep_otr_display_mode     = get_post_meta( $wpep_current_form_id, 'wpep_otr_display_mode', true );
$wpep_otr_default_choice   = get_post_meta( $wpep_current_form_id, 'wpep_otr_default_choice', true );
$wpep_otr_one_time_label   = get_post_meta( $wpep_current_form_id, 'wpep_otr_one_time_label', true );
$wpep_otr_recurring_label  = get_post_meta( $wpep_current_form_id, 'wpep_otr_recurring_label', true );
$wpep_otr_checkbox_label   = get_post_meta( $wpep_current_form_id, 'wpep_otr_checkbox_label', true );
$wpep_otr_checkbox_default = get_post_meta( $wpep_current_form_id, 'wpep_otr_checkbox_default', true );
$wpep_otr_notice_text      = get_post_meta( $wpep_current_form_id, 'wpep_otr_notice_text', true );
$wpep_otr_hide_notice      = get_post_meta( $wpep_current_form_id, 'wpep_otr_hide_notice', true );

if ( empty( $wpep_otr_display_mode ) ) {
	$wpep_otr_display_mode = 'radio';
}
if ( empty( $wpep_otr_default_choice ) ) {
	$wpep_otr_default_choice = 'one_time';
}
if ( empty( $wpep_otr_one_time_label ) ) {
	$wpep_otr_one_time_label = __( 'One-time payment', 'wp_easy_pay' );
}
if ( empty( $wpep_otr_recurring_label ) ) {
	$wpep_otr_recurring_label = __( 'Recurring payment', 'wp_easy_pay' );
}
if ( empty( $wpep_otr_checkbox_label ) ) {
	$wpep_otr_checkbox_label = __( 'Make this a recurring payment', 'wp_easy_pay' );
}
if ( empty( $wpep_otr_notice_text ) ) {
	$wpep_otr_notice_text = __( 'You will be charged {Amount} {Subscription Cycle}', 'wp_easy_pay' );
}

$wpep_otr_cycle_label = function_exists( 'wpep_get_subscription_cycle_label' )
	? wpep_get_subscription_cycle_label( $wpep_current_form_id )
	: '';

$wpep_otr_is_recurring_default = ( 'checkbox' === $wpep_otr_display_mode && 'on' === $wpep_otr_checkbox_default )
	|| ( 'checkbox' !== $wpep_otr_display_mode && 'recurring' === $wpep_otr_default_choice );

if ( 'choice' === $wpep_otr_section || 'full' === $wpep_otr_section ) :
	?>
	<div class="wpep-otr-choice-wrapper" data-form-id="<?php echo esc_attr( $wpep_current_form_id ); ?>" data-display-mode="<?php echo esc_attr( $wpep_otr_display_mode ); ?>">
		<?php if ( 'toggle' === $wpep_otr_display_mode ) : ?>
			<div class="wpep-otr-tab-group" role="tablist" aria-label="<?php esc_attr_e( 'Payment frequency', 'wp_easy_pay' ); ?>">
				<label class="wpep-otr-tab">
					<input type="radio" name="wpep_otr_choice_<?php echo esc_attr( $wpep_current_form_id ); ?>" value="one_time" class="wpep-otr-choice-input" <?php checked( ! $wpep_otr_is_recurring_default ); ?>>
					<span class="wpep-otr-tab-label"><?php echo esc_html( $wpep_otr_one_time_label ); ?></span>
				</label>
				<label class="wpep-otr-tab">
					<input type="radio" name="wpep_otr_choice_<?php echo esc_attr( $wpep_current_form_id ); ?>" value="recurring" class="wpep-otr-choice-input" <?php checked( $wpep_otr_is_recurring_default ); ?>>
					<span class="wpep-otr-tab-label"><?php echo esc_html( $wpep_otr_recurring_label ); ?></span>
				</label>
			</div>
		<?php elseif ( 'checkbox' === $wpep_otr_display_mode ) : ?>
			<label class="wpep-otr-checkbox-option">
				<input type="checkbox" class="wpep-otr-checkbox-input" <?php checked( $wpep_otr_is_recurring_default ); ?>>
				<span><?php echo esc_html( $wpep_otr_checkbox_label ); ?></span>
			</label>
		<?php else : ?>
			<div class="wpep-otr-radio-group">
				<label class="wpep-otr-radio-option">
					<input type="radio" name="wpep_otr_choice_<?php echo esc_attr( $wpep_current_form_id ); ?>" value="one_time" class="wpep-otr-choice-input" <?php checked( ! $wpep_otr_is_recurring_default ); ?>>
					<span><?php echo esc_html( $wpep_otr_one_time_label ); ?></span>
				</label>
				<label class="wpep-otr-radio-option">
					<input type="radio" name="wpep_otr_choice_<?php echo esc_attr( $wpep_current_form_id ); ?>" value="recurring" class="wpep-otr-choice-input" <?php checked( $wpep_otr_is_recurring_default ); ?>>
					<span><?php echo esc_html( $wpep_otr_recurring_label ); ?></span>
				</label>
			</div>
		<?php endif; ?>

		<?php if ( 'full' === $wpep_otr_section && 'on' !== $wpep_otr_hide_notice ) : ?>
			<p class="wpep-otr-notice" data-notice-template="<?php echo esc_attr( $wpep_otr_notice_text ); ?>" data-cycle-label="<?php echo esc_attr( $wpep_otr_cycle_label ); ?>" <?php echo $wpep_otr_is_recurring_default ? '' : 'style="display:none;"'; ?>></p>
		<?php endif; ?>

		<input type="hidden" id="customer_recurring_enabled_<?php echo esc_attr( $wpep_current_form_id ); ?>" name="customer_recurring_enabled" value="<?php echo $wpep_otr_is_recurring_default ? 'true' : 'false'; ?>">
	</div>
	<?php
endif;

if ( 'notice' === $wpep_otr_section ) :
	if ( 'on' !== $wpep_otr_hide_notice ) :
		?>
		<div class="wpep-otr-notice-wrapper" data-form-id="<?php echo esc_attr( $wpep_current_form_id ); ?>">
			<p class="wpep-otr-notice" data-notice-template="<?php echo esc_attr( $wpep_otr_notice_text ); ?>" data-cycle-label="<?php echo esc_attr( $wpep_otr_cycle_label ); ?>" <?php echo $wpep_otr_is_recurring_default ? '' : 'style="display:none;"'; ?>></p>
		</div>
		<?php
	endif;
endif;
