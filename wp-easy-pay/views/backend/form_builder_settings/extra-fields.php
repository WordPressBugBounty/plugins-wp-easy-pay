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
	<div class="sectionFold">
		<div id="build-wrap">
			<?php
			if ( ! wepp_fs()->is__premium_only() ) {
				?>
			<div class="extra-field-overlays">
				<a href="https://wpeasypay.com/pricing/?utm_source=plugin&utm_medium=create_form_page">
					<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/premium-img.png' ); ?>" />
					<p>Upgrade to premium plugin</p>
				</a>
			</div>
				<?php
			}
			?>
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
