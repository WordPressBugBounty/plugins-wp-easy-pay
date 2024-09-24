<div class="easypayblock">

	<?php
	/**
	 * Filename: add-payment-form-custom-fields.php
	 * Description: add payment form custom fields backend.
	 *
	 * @package WP_Easy_Pay
	 */

	?>

	<?php require_once 'tabs-list-and-switches.php'; ?>
	<!-- THE PANELS -->
	<article id="panels" class="wpeasyPay">
		<div class="container">

		<?php
		if ( wepp_fs()->is__premium_only() ) {
			?>
			<section id="panel-1">
			<?php require_once 'square_account_settings.php'; ?>
			</section>
			<?php
		}
		?>
			<section id="panel-2">
				<?php require_once 'form-settings.php'; ?>
			</section>
			<section id="panel-3">
				<?php require_once 'extra-fields.php'; ?>
			</section>
			<section id="panel-4">
				<?php require_once 'email-notifications.php'; ?>
			</section>
			<section id="panel-5">
				<?php require_once 'transaction-notes.php'; ?>
			</section>

		<?php
		if ( wepp_fs()->is__premium_only() ) {
			?>
			<section id="panel-6">
			<?php require_once 'additional_charges.php'; ?>
			</section>
			<?php
		}
		?>
		</div>
	</article>

</div>
