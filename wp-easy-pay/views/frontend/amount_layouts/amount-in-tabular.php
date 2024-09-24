<?php
/**
 * Filename: amount-in-tabular.php
 * Description: amount in tabular frontend.
 *
 * @package WP_Easy_Pay
 */

?>
<?php
$wpep_products             = get_post_meta( $wpep_current_form_id, 'wpep_products_with_labels' );
$wpep_prods_without_images = get_post_meta( $wpep_current_form_id, 'wpep_prods_without_images', true );

?>
<div class="shopping-cart">
	<div class="listingHeadWrap">
		<div class="listingHead">
		<?php
		if ( 'on' !== $wpep_prods_without_images ) {
			?>
			<div class="headCol pImage">Image</div>
			<?php
		} else {
			?>

				<!-- <div class="headCol pImage raza">&nbsp;</div> -->
			<?php
		}
		?>
			<div class="headCol pDetails">Product Details</div>
			<div class="headCol pPrice 
			<?php
			if ( 'on' !== $wpep_prods_without_images ) {
				echo 'mleft30'; }
			?>
			">
				Price
			</div>
			<div class="headCol pQty">
				Qty
			</div>
			<div class="headCol pCost">Cost</div>
		</div>
	</div>
	<div class="basket-tbl">
		<?php

		if ( isset( $wpep_products[0] ) && ! empty( $wpep_products[0] ) ) {
			foreach ( $wpep_products[0] as $key => $product ) {
				?>
				<div class="wpItem">

				<?php
				if ( 'on' !== $wpep_prods_without_images ) {
					?>
					<div class="headCol pImage">
					<?php

					if ( empty( $product['products_url'] ) ) {
						echo '';
					} else {
						echo '<img src="' . esc_url( $product['products_url'] ) . '" alt="Avatar" width="120" alt="Placeholder">';
					}

					?>
					</div>
					<?php
				} else {

					?>
							<!-- <div class="headCol pImage">Test</div> -->
					<?php
				}
				?>


					<div class="headCol pDetails" <?php echo ( 'on' === $wpep_prods_without_images ) ? 'style="margin-left:20px!important"' : ''; ?>>
				<span class="product_label">
					<?php

					if ( empty( $product['label'] ) ) {
						echo esc_html( 'Product Label' );
					} else {
						echo esc_html( $product['label'] );
					}
					?>
				</span>
					</div>
					<div class="headCol pPrice">
						<input type="text" name="price" id="price" class="price form-control-em" value="
						<?php
						if ( isset( $product['amount'] ) && ! empty( $product['amount'] ) ) {
							echo esc_html( $product['amount'] );
						} else {
							echo 0; }
						?>
						" disabled>
					</div>
					<div class="headCol qty pQty">
						<input type="text" name="qty" id="qty" class="qty form-control-em" value="
						<?php
						if ( isset( $product['quantity'] ) && ! empty( $product['quantity'] ) ) {
							echo esc_html( $product['quantity'] );
						} else {
							echo 0; }
						?>
						">
					</div>
					<div class="headCol pCost">
						<input type="text" name="cost" id="cost" class="cost form-control-em" value="0" disabled>
					</div>
					<input type="hidden" id="wpep_amount_layout" value="tabular_layout"/>
					<div class="headCol pReset">
						<i class="fa fa-times <?php echo esc_html( 'wpep_delete_tabular_product' ); ?>" aria-hidden="true"></i>
					</div>
				</div>
				<?php
			}
		} else {

			echo esc_html( 'No Products Added' );
		}
		?>
	</div>
</div>
