<?php
/**
 * Filename: wpep-render-form-style-meta-html.php,
 * Description: silence is golden.
 *
 * @package WP_Easy_Pay
 */

?>
<?php
$wpep_form_theme_color = ! empty( get_post_meta( get_the_ID(), 'wpep_form_theme_color', true ) ) ? get_post_meta( get_the_ID(), 'wpep_form_theme_color', true ) : '#5d97ff';
$wpep_show_shadow      = get_post_meta( get_the_ID(), 'wpep_show_shadow', true );
$wpep_btn_theme        = get_post_meta( get_the_ID(), 'wpep_btn_theme', true );
$wpep_btn_label        = get_post_meta( get_the_ID(), 'wpep_payment_btn_label', true );
?>

<style>
	.colorWra {
	}

	.colorWra .form-group {
		border-bottom: 1px solid #eaeaea;
		padding-bottom: 15px;
		margin-bottom: 15px;
	}

	.colorWra .form-group:last-child {
		border-bottom: none;
		padding: 0px;
	}

	.colorWra .form-control {
		display: block;
		width: 100%;
		height: 50px;
		padding: 0px 15px;
		font-size: 14px;
		font-weight: 500;
		line-height: 50px;
		color: #495057;
		background-color: #fff;
		background-clip: padding-box;
		border: 1px solid #e2e5ec;
		border-radius: 4px;
		-webkit-transition: all 0.3s ease;
		transition: all 0.3s ease;
		border-radius: 0px;
	}

	.colorWra label.lbltitle {
		display: block;
		margin-bottom: 10px;
		font-weight: 600;
	}
</style>

<div class="colorWra">
	
	<div class="form-group">
		<label class="lbltitle">Form theme color:</label>

		<div class="formsetting">
			<div class="formsblock1">
				<input class="form-control jscolor" value="<?php echo esc_attr( $wpep_form_theme_color ); ?>"
						name="wpep_form_theme_color"/>
			</div>
		</div>
	</div>


	<div class="form-group">

		<label class="lbltitle">Activate Shadow</label>

		<label><input type="checkbox" name="wpep_show_shadow" id="formType2" 
		<?php
		if ( 'on' === $wpep_show_shadow ) {

			echo 'checked';
		}
		?>
			/> Show form shadow</label>
	</div>


	<div class="form-group">

		<label class="lbltitle">Button Style</label>
		<label><input type="checkbox" name="wpep_btn_theme" id="formType2" 
		<?php
		if ( 'on' === $wpep_btn_theme ) {

			echo 'checked';
		}
		?>
			/> Use theme default popup button style</label>
	</div>
	<div class="form-group">

		<label class="lbltitle">Pay Button Label</label>
		<label><input type="text" name="wpep_payment_btn_label" placeholder="Pay Now" value="
		<?php
		if ( isset( $wpep_btn_label ) && ! empty( $wpep_btn_label ) ) {
			echo esc_html( trim( $wpep_btn_label ) );
		}
		?>
		" id="formType2"/></label>
	</div>
</div>
