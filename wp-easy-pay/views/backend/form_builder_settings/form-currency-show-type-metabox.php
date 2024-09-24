<?php
/**
 * Filename: form-currency-show-type-metabox.php
 * Description: form currency show backend.
 *
 * @package WP_Easy_Pay
 */

$currency_symbol_type = ! empty( get_post_meta( get_the_ID(), 'currencySymbolType', true ) ) ? get_post_meta( get_the_ID(), 'currencySymbolType', true ) : 'code';
?>

<div class="form-group">
	<label for="code">
		<input type="radio" class="currencySymbolType" name="currencySymbolType" id="code"
				value="code" 
				<?php
				if ( 'code' === $currency_symbol_type ) :
					echo 'checked';
endif;
				?>
				> Currency Code (e.x:
		USD)
	</label><br><br>

	<label for="symbol">
		<input type="radio" class="currencySymbolType" name="currencySymbolType" id="symbol"
				value="symbol" 
				<?php
				if ( 'symbol' === $currency_symbol_type ) :
					echo 'checked';
endif;
				?>
				> Currency Symbol
		(e.x: $)
	</label>
</div>
