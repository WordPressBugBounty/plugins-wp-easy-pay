<div class="form-group cusPaymentSec donationGoalWrapper">
	<div class="s_ft">
		<h2 class="">Donation Goal</h2>
	</div>

	<div class="donationGoalBar">
		<span class="donationGoalProgressBar">
			<?php
			/**
			 * Filename: donation-goals.php
			 * Description: donation goals frontend.
			 *
			 * @package WP_Easy_Pay
			 */

			?>
			<?php
			$wpep_donation_goal_amount   = floatval( $wpep_donation_goal_amount );
			$wpep_donation_goal_achieved = floatval( $wpep_donation_goal_achieved );
			$percentage                  = 0.00;

			$goal_is_achieve = false;
			if ( $wpep_donation_goal_achieved > 0 && $wpep_donation_goal_amount > 0 ) {
				if ( $wpep_donation_goal_achieved < $wpep_donation_goal_amount ) {
					$percentage = ( $wpep_donation_goal_achieved / $wpep_donation_goal_amount ) * 100;
				} else {
					$percentage = 100;
				}

				if ( $wpep_donation_goal_achieved === $wpep_donation_goal_amount ) {
					$goal_is_achieve = true;
				}
			}
			?>
			<span style="width: <?php echo esc_attr( round( $percentage, 2 ) ); ?>%"></span>
		</span>
		<div class="donationGoalDetails">
			<?php
			if ( 'symbol' === $currency_symbol_type ) {
				?>
				<?php echo esc_attr( $square_currency ) . esc_attr( number_format( $wpep_donation_goal_achieved, 2 ) ); ?> <small>of <?php echo esc_attr( $square_currency ) . esc_attr( number_format( $wpep_donation_goal_amount, 2 ) ); ?>  raised</small>
				<?php
			} else {
				?>
				<?php echo esc_attr( number_format( $wpep_donation_goal_achieved, 2 ) ) . ' ' . esc_attr( $square_currency ); ?> <small>of <?php echo esc_attr( number_format( $wpep_donation_goal_amount, 2 ) ) . ' ' . esc_attr( $square_currency ); ?> raised</small>
				<?php
			}
			?>
		</div>
	</div>
</div>
