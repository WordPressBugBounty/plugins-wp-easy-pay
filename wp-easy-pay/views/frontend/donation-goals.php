<?php
/**
 * Donation goals frontend progress bar and messaging.
 *
 * @package WP_Easy_Pay
 */

?>
<div class="form-group cusPaymentSec donationGoalWrapper">
	<div class="s_ft">
		<?php
		$form_id             = ! empty( $wpep_current_form_id ) ? absint( $wpep_current_form_id ) : get_the_ID();
		$organization_name   = get_post_meta( $form_id, 'wpep_organization_name', true );
		$goal_message        = get_post_meta( $form_id, 'wpep_donation_goal_message', true );
		$goal_message_switch = get_post_meta( $form_id, 'wpep_donation_goal_message_switch', true );

		if ( ! empty( $organization_name ) ) {
			echo '<h2 class="">' . esc_html( $organization_name ) . '</h2>';
		}

		if ( 'checked' === $goal_message_switch && ! empty( $goal_message ) ) {
			echo '<p class="goal-message-front-view">' . esc_html( $goal_message ) . '</p>';
		}
		?>
	</div>

	<div class="donationGoalBar">
		<span class="donationGoalProgressBar">
			<?php
			$wpep_donation_goal_amount   = floatval( $wpep_donation_goal_amount );
			$wpep_donation_goal_achieved = floatval( $wpep_donation_goal_achieved );
			$percentage                  = 0.00;

			if ( $wpep_donation_goal_achieved > 0 && $wpep_donation_goal_amount > 0 ) {
				if ( $wpep_donation_goal_achieved < $wpep_donation_goal_amount ) {
					$percentage = ( $wpep_donation_goal_achieved / $wpep_donation_goal_amount ) * 100;
				} else {
					$percentage = 100;
				}
			}
			?>
			<span style="width: <?php echo esc_attr( (string) round( $percentage, 2 ) ); ?>%"></span>
		</span>
		<div class="donationGoalDetails">
			<?php
			$achieved_formatted = number_format( $wpep_donation_goal_achieved, 2 );
			$goal_formatted     = number_format( $wpep_donation_goal_amount, 2 );

			if ( 'symbol' === $currency_symbol_type ) {
				printf(
					'%1$s%2$s <small>of %1$s%3$s raised</small>',
					esc_html( (string) $square_currency ),
					esc_html( $achieved_formatted ),
					esc_html( $goal_formatted )
				);
			} elseif ( 'code' === $currency_symbol_type ) {
				printf(
					'%1$s %2$s <small>of %3$s %2$s raised</small>',
					esc_html( $achieved_formatted ),
					esc_html( (string) $square_currency ),
					esc_html( $goal_formatted )
				);
			} else {
				printf(
					'%1$s <small>of %2$s raised</small>',
					esc_html( $achieved_formatted ),
					esc_html( $goal_formatted )
				);
			}
			?>
		</div>
	</div>
</div>
