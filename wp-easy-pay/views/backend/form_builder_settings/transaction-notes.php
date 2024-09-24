<?php
/**
 * Filename: transaction-notes.php
 * Description: transaction notes backend.
 *
 * @package WP_Easy_Pay
 */

$selected_transaction_notes = get_post_meta( get_the_ID(), 'wpep_transaction_notes_box', true );
$form_fields                = json_decode( get_post_meta( get_the_ID(), 'wpep_square_form_builder_fields', true ) );
$fees_data                  = get_post_meta( get_the_ID(), 'fees_data' );
?>

<main>
	<h3>Square Note Fields</h3>
	<div class="notificationsWrap clearfix">
	<div class="notificationTitle">
			<span class="titleTags">Default note tags</span>
			<p style="margin-top:0">
				<span>
					First Name:
					<small class="wpep_tags"> [first_name] </small>
				</span>

				<span>
					Last Name:
					<small class="wpep_tags"> [last_name] </small>
				</span>
				<span>
					Email:
					<small class="wpep_tags"> [user_email] </small>
				</span>
				<span>
					Amount:
					<small class="wpep_tags"> [total_amount] </small>
				</span>
			</p>
		</div>

		<div class="notificationTitle">
			<span class="titleTags">Extra fields mail tags</span>
			<div id="wpep_extra_fields_notes_tags">
			  
			</div>
		</div>

		<div class="notificationTitle">
			<span class="titleTags">Additional Charges tags</span>
			<p style="margin-top:0" id="additional_charges_tags">
				<?php
				if ( isset( $fees_data[0] ) && count( $fees_data[0] ) > 0 ) {
					foreach ( $fees_data[0]['name'] as $key => $fees ) {
						$fees_check = isset( $fees_data[0]['check'][ $key ] ) ? $fees_data[0]['check'][ $key ] : 'no';
						$fees_name  = isset( $fees_data[0]['name'][ $key ] ) ? $fees_data[0]['name'][ $key ] : '';
						if ( 'yes' === $fees_check ) {
							echo '<span>' . esc_html( $fees_name ) . ': ';
								echo '<small class="wpep_tags"> [' . esc_html( $fees_name ) . '] </small>';
							echo '</span>';
						}
					}
				}
				?>
			</p>
		</div>

		</div>

	  

	<div class="form-group">
		<label>Transaction Notes:</label>
		<textarea type="text" class="form-control form-control-textarea" placeholder="Please Enter popup description" name="wpep_transaction_notes_box" spellcheck="false"> <?php echo esc_html( $selected_transaction_notes ); ?> </textarea>
	</div>
	<div class="note">
		Note : There is a limit of 60 characters for transaction note in
		Square API, so if you exceed this limit it will automatically
		ignore.
	</div>
</main>
