<?php
/**
 * Onboarding screen view for WP Easy Pay.
 *
 * @package wp_easy_pay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpep_render_onboarding_screen_content' ) ) {
	/**
	 * Render onboarding content.
	 *
	 * @return void
	 */
	function wpep_render_onboarding_screen_content() {
		$wpep_live_token_upgraded = get_option( 'wpep_live_token_upgraded', false );
		$wpep_square_test_token   = get_option( 'wpep_square_test_token_global', false );
		// Same rule as square-oauth-notice: connected if live OR sandbox global token exists.
		$wpep_square_connected    = ! ( empty( $wpep_live_token_upgraded ) && empty( $wpep_square_test_token ) );
		?>
		<div class="wpep-onboarding-container">

			<div class="wpep-onboarding-main">

				<!-- Card 1: Welcome -->
				<div class="wpep-card text-center welcome-card">
					<div class="welcome-logos d-flex justify-content-center align-items-center gap-4">
						<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/wpep-logo-pro.png' ); ?>" class="wpep-logo-onboarding" alt="WP EasyPay">
						<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/plus-icon-full-blue.png' ); ?>" class="plus-icon-onboarding" />
						<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/square-logo-black.png' ); ?>" class="square-logo" alt="Square">
					</div>

					<h1 class="welcome-title"><?php esc_html_e( 'Welcome to WP EasyPay', 'wp_easy_pay' ); ?></h1>
					<p class="welcome-desc">
						<?php esc_html_e( 'The simplest way to accept Square payments, donations, and subscriptions on WordPress — no code required.', 'wp_easy_pay' ); ?>
					</p>

					<div class="welcome-badges d-flex justify-content-center gap-3">
						<span class="badge-item"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11 5.5V4.5C11 2.84315 9.65685 1.5 8 1.5C6.34315 1.5 5 2.84315 5 4.5V5.5M4 5.5H12C12.5523 5.5 13 5.94772 13 6.5V13.5C13 14.0523 12.5523 14.5 12 14.5H4C3.44772 14.5 3 14.0523 3 13.5V6.5C3 5.94772 3.44772 5.5 4 5.5Z" stroke="#0046E8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg> <?php esc_html_e( 'SCA ready', 'wp_easy_pay' ); ?></span>
						<span class="badge-item"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 1.5L2 4V7.5C2 11.0899 4.60603 14.1832 8 15C11.394 14.1832 14 11.0899 14 7.5V4L8 1.5Z" stroke="#0046E8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.5 8.5L7 10L10.5 6.5" stroke="#0046E8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg> <?php esc_html_e( 'PCI compliant', 'wp_easy_pay' ); ?></span>
						<span class="badge-item"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 8C9.65685 8 11 6.65685 11 5C11 3.34315 9.65685 2 8 2C6.34315 2 5 3.34315 5 5C5 6.65685 6.34315 8 8 8Z" stroke="#0046E8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 14C4 11.7909 5.79086 10 8 10C10.2091 10 12 11.7909 12 14" stroke="#0046E8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg> <?php esc_html_e( 'OAuth secure', 'wp_easy_pay' ); ?></span>
					</div>

					<div class="welcome-footer">
						<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 12.8333L6.15417 12.0625C2.93333 9.14583 0.833336 7.23958 0.833336 4.95833C0.833336 3.09167 2.29167 1.63333 4.15834 1.63333C5.22667 1.63333 6.2475 2.135 7 2.9225C7.7525 2.135 8.77334 1.63333 9.84167 1.63333C11.7083 1.63333 13.1667 3.09167 13.1667 4.95833C13.1667 7.23958 11.0667 9.14583 7.84584 12.0683L7 12.8333Z" stroke="#0046E8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg> <?php esc_html_e( 'Trusted by thousands of WordPress sites', 'wp_easy_pay' ); ?>
					</div>
				</div>

				<!-- Card 2: Quick Walkthrough -->
				<div class="wpep-card walkthrough-card">
					<div class="card-header d-flex align-items-center gap-2">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM8 14.5V5.5L14 10L8 14.5Z" fill="#0046E8"/></svg>
						<h2 class="card-title m-0"><?php esc_html_e( 'Quick Walkthrough', 'wp_easy_pay' ); ?></h2>
					</div>
					<p class="card-desc"><?php esc_html_e( 'Watch a 2-minute walkthrough of building your first Square payment form.', 'wp_easy_pay' ); ?></p>
					
					<div class="video-thumbnail wpep-onboarding-walkthrough d-flex justify-content-center align-items-center">
						<div class="video-thumbnail-preview d-flex justify-content-center align-items-center w-100 h-100">
							<div class="d-flex justify-content-center align-items-center">
								<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/play-button-onboarding.png' ); ?>" class="play-btn-new" alt="<?php esc_attr_e( 'Play walkthrough video', 'wp_easy_pay' ); ?>">
							</div>
						</div>
						<div class="video-thumbnail-embed d-none" aria-hidden="true">
							<iframe
								class="wpep-walkthrough-iframe"
								src=""
								data-src="<?php echo esc_url( 'https://www.youtube.com/embed/EXXfCp4VNx8?autoplay=1&mute=1' ); ?>"
								title="<?php esc_attr_e( 'WP EasyPay | Payment Form Builder for WordPress', 'wp_easy_pay' ); ?>"
								allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
								referrerpolicy="strict-origin-when-cross-origin"
								allowfullscreen
							></iframe>
						</div>
					</div>

					<!-- <div class="text-center mt-4 connectSquare">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpep-settings' ) ); ?>" class="btn-primary wpep-btn">
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="16" height="16" rx="2" fill="white"/><rect x="4" y="4" width="8" height="8" rx="1" fill="#0046E8"/></svg>
							<?php esc_html_e( 'Connect Your Square Account', 'wp_easy_pay' ); ?>
						</a>
					</div> -->
					<div class="text-center mt-4 connectSquare">
						<?php if ( $wpep_square_connected ) : ?>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=wp_easy_pay' ) ); ?>" class="btn-primary wpep-btn">
							<?php esc_html_e( 'Start Creating Your Payment Form &rarr;', 'wp_easy_pay' ); ?>
						</a>
						<?php else : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpep-settings' ) ); ?>" class="btn-primary wpep-btn">
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="16" height="16" rx="2" fill="white"/><rect x="4" y="4" width="8" height="8" rx="1" fill="#0046E8"/></svg>
							<?php esc_html_e( 'Connect Your Square Account', 'wp_easy_pay' ); ?>
						</a>
						<?php endif; ?>
					</div>
				</div>

				<!-- Card 3: Get Started in 3 Steps -->
				<div class="wpep-card steps-card">
					<h2 class="card-title"><?php esc_html_e( 'Get Started in 3 Steps', 'wp_easy_pay' ); ?></h2>
					<p class="card-desc"><?php esc_html_e( 'From zero to your first live payment form in minutes.', 'wp_easy_pay' ); ?></p>

					<div class="steps-container d-flex justify-content-between align-items-start mt-4 position-relative">
						<div class="step-item text-center flex-1">
							<div class="d-flex justify-content-center align-items-center mx-auto">
								<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/start-step-icon-one.png' ); ?>" class="get-start-steps-icons" alt="WP EasyPay">
							</div>
							<h3 class="step-title"><?php esc_html_e( 'Connect Square Account', 'wp_easy_pay' ); ?></h3>
							<p class="step-desc"><?php esc_html_e( 'OAuth in one click, no API keys to copy.', 'wp_easy_pay' ); ?></p>
							<a href="https://wpeasypay.com/docs/getting-started/connect-square-account/" class="step-link"><?php esc_html_e( 'View guide', 'wp_easy_pay' ); ?></a>
						</div>
						<div class="step-arrow d-flex align-items-center">
							<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/get-steps-arrow.png' ); ?>" class="get-start-steps-arrow" alt="WP EasyPay">    
						</div>
						<div class="step-item text-center flex-1">
							<div class="d-flex justify-content-center align-items-center mx-auto">
								<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/start-step-icon-two.png' ); ?>" class="get-start-steps-icons" alt="WP EasyPay">
							</div>
							<h3 class="step-title"><?php esc_html_e( 'Create Your First Form', 'wp_easy_pay' ); ?></h3>
							<p class="step-desc"><?php esc_html_e( 'Choose Simple, Donation or Subscription.', 'wp_easy_pay' ); ?></p>
							<a href="https://wpeasypay.com/docs/getting-started/create-square-payment-form/" class="step-link"><?php esc_html_e( 'View guide', 'wp_easy_pay' ); ?></a>
						</div>
						<div class="step-arrow step-arrow-2 d-flex align-items-center">
							<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/get-steps-arrow-down.png' ); ?>" class="get-start-steps-down" alt="WP EasyPay">
						</div>
						<div class="step-item text-center flex-1">
							<div class="d-flex justify-content-center align-items-center mx-auto">
								<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/start-step-icon-three.png' ); ?>" class="get-start-steps-icons" alt="WP EasyPay">
							</div>
							<h3 class="step-title"><?php esc_html_e( 'Embed on Any Page', 'wp_easy_pay' ); ?></h3>
							<p class="step-desc"><?php esc_html_e( 'Use the Gutenberg block or shortcode.', 'wp_easy_pay' ); ?></p>
							<a href="https://wpeasypay.com/docs/getting-started/create-square-payment-form/#display-the-form-on-a-page:~:text=from%20the%20list-,Display%20the%20Form%20on%20a%20Page,-You%20can%20add" class="step-link"><?php esc_html_e( 'View guide', 'wp_easy_pay' ); ?></a>
						</div>
					</div>

					<div class="text-center mt-5 connectSquare">
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=wp_easy_pay' ) ); ?>" class="btn-primary wpep-btn">
							<?php esc_html_e( 'Start Creating Your Payment Form &rarr;', 'wp_easy_pay' ); ?>
						</a>
					</div>
				</div>

				<?php if ( function_exists( 'wepp_fs' ) && ! wepp_fs()->is__premium_only() ) : ?>
					<!-- Card 4: Pro Features -->
					<div class="wpep-card pro-card text-center text-white pro-features">
						<span class="pro-badge">
							<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/pro-tag-star.png' ); ?>" class="pro-tag-star" alt="WP EasyPay">
							<?php esc_html_e( 'UNLOCK PRO', 'wp_easy_pay' ); ?>
						</span>
						<h2 class="pro-title"><?php esc_html_e( 'Enhance your Square payment forms', 'wp_easy_pay' ); ?><br><?php esc_html_e( 'with', 'wp_easy_pay' ); ?> <span class="text-warning"><?php esc_html_e( 'Premium Features', 'wp_easy_pay' ); ?></span></h2>
						
						<div class="pro-features-grid mt-4 text-start">
							<div class="row features-list-ul">
								<div class="col-4">
									<ul class="pro-feature-list">
										<li><span class="check-circle"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/recurring-bill-icon.png' ); ?>" class="features-list-icon" alt="WP EasyPay"></span> 
											<?php esc_html_e( 'Recurring Billing', 'wp_easy_pay' ); ?>
										</li>
											<li><span class="check-circle"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/square-syncing-icon.png' ); ?>" class="features-list-icon" alt="WP EasyPay"></span> 
											<?php esc_html_e( 'Square Product Sync', 'wp_easy_pay' ); ?>
										</li>
											<li><span class="check-circle"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/customize-form-icon.png' ); ?>" class="features-list-icon" alt="WP EasyPay"></span> 
											<?php esc_html_e( 'Customizable Forms', 'wp_easy_pay' ); ?>
										</li>
									</ul>
								</div>
								<div class="col-4">
									<ul class="pro-feature-list">
										<li><span class="check-circle"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/multi-form-icon.png' ); ?>" class="features-list-icon" alt="WP EasyPay"></span> 
											<?php esc_html_e( 'Multi-Step Forms', 'wp_easy_pay' ); ?>
										</li>
											<li><span class="check-circle"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/giftcard-icon.png' ); ?>" class="features-list-icon" alt="WP EasyPay"></span> 
											<?php esc_html_e( 'Gift Card Support', 'wp_easy_pay' ); ?>
										</li>
											<li><span class="check-circle"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/transaction-show-icon.png' ); ?>" class="features-list-icon" alt="WP EasyPay"></span> 
											<?php esc_html_e( 'Transaction Details', 'wp_easy_pay' ); ?>
										</li>
									</ul>
								</div>
								<div class="col-4">
									<ul class="pro-feature-list">
										<li><span class="check-circle"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/digital-wallet-icon.png' ); ?>" class="features-list-icon" alt="WP EasyPay"></span> 
											<?php esc_html_e( '6+ Digital Wallet Support', 'wp_easy_pay' ); ?>
										</li>
											<li><span class="check-circle"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/captchas-icon.png' ); ?>" class="features-list-icon" alt="WP EasyPay"></span> 
											<?php esc_html_e( 'Flexible Captchas', 'wp_easy_pay' ); ?>
										</li>
											<li><span class="check-circle"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/multi-currency-icon.png' ); ?>" class="features-list-icon" alt="WP EasyPay"></span> 
											<?php esc_html_e( 'Multi-Currency Support', 'wp_easy_pay' ); ?>
										</li>
									</ul>
								</div>
							</div>
						</div>

						<div class="mt-4">
							<a href="https://wpeasypay.com/pricing/?utm_source=onboarding&utm_medium=banner" target="_blank" class="btn-warning wpep-btn"><?php esc_html_e( 'Get Pro Now', 'wp_easy_pay' ); ?><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/pro-btn-arrow.png' ); ?>" class="pro-btn-arrow" alt="WP EasyPay"></a>
						</div>
					</div>
				<?php endif; ?>

				<!-- Card 5: Explore Our Guides -->
				<div class="wpep-card guides-card">
					<div class="d-flex justify-content-between align-items-center mb-4">
						<h2 class="card-title m-0"><?php esc_html_e( 'Explore Our Guides', 'wp_easy_pay' ); ?></h2>
						<a href="https://wpeasypay.com/docs/" class="view-all-link"><?php esc_html_e( 'View all docs', 'wp_easy_pay' ); ?></a>
					</div>

					<div class="row">
						<div class="col-6">
							<div class="guide-col-boxes-left">
								<h4 class="guide-col-title text-primary text-uppercase"><?php esc_html_e( 'Getting Started', 'wp_easy_pay' ); ?></h4>
								<ul class="guide-list">
									<li><a href="https://wpeasypay.com/docs/getting-started/introduction/"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/doc-icon.png' ); ?>" class="doc-list-icon" alt="WP EasyPay"> <?php esc_html_e( 'Introduction to Wp Easy Pay', 'wp_easy_pay' ); ?></a></li>
									<li><a href="https://wpeasypay.com/docs/getting-started/connect-square-account/"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/doc-icon.png' ); ?>" class="doc-list-icon" alt="WP EasyPay"> <?php esc_html_e( 'Connect Your Square Account', 'wp_easy_pay' ); ?></a></li>
									<li><a href="https://wpeasypay.com/docs/getting-started/create-square-payment-form/"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/doc-icon.png' ); ?>" class="doc-list-icon" alt="WP EasyPay"> <?php esc_html_e( 'Create Your First Square Payment Form', 'wp_easy_pay' ); ?></a></li>
									<li><a href="https://wpeasypay.com/docs/getting-started/activate-wp-easypay-pro/"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/doc-icon.png' ); ?>" class="doc-list-icon" alt="WP EasyPay"> <?php esc_html_e( 'Activate WP Easy Pay Premium', 'wp_easy_pay' ); ?></a></li>
								</ul>
							</div>
						</div>
						<div class="col-6">
							<div class="guide-col-boxes-right">
								<h4 class="guide-col-title text-primary text-uppercase"><?php esc_html_e( 'Going Further', 'wp_easy_pay' ); ?></h4>
								<ul class="guide-list">
									<li><a href="https://wpeasypay.com/docs/donations/create-donation-form/"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/doc-icon.png' ); ?>" class="doc-list-icon" alt="WP EasyPay"> <?php esc_html_e( 'Create Your First Donation Form', 'wp_easy_pay' ); ?></a></li>
									<li><a href="https://wpeasypay.com/docs/donations/set-donation-goal/"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/doc-icon.png' ); ?>" class="doc-list-icon" alt="WP EasyPay"> <?php esc_html_e( 'Set Donation Goals & Track Progress', 'wp_easy_pay' ); ?></a></li>
									<li><a href="https://wpeasypay.com/docs/payment-form-settings/customize-form-appearance-fields/"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/doc-icon.png' ); ?>" class="doc-list-icon" alt="WP EasyPay"> <?php esc_html_e( 'Customize Form Appearance & Fields', 'wp_easy_pay' ); ?></a></li>
									<li><a href="https://wpeasypay.com/docs/payment-form-settings/notifications-transaction-notes/"><img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/doc-icon.png' ); ?>" class="doc-list-icon" alt="WP EasyPay"> <?php esc_html_e( 'Set Up Payment Notifications/Transaction Notes', 'wp_easy_pay' ); ?></a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<!-- Card 6: Testimonials -->
				<div class="wpep-card testimonials-card">
					<div class="d-flex justify-content-between align-items-center mb-4">
						<h2 class="card-title m-0"><?php esc_html_e( 'Testimonials', 'wp_easy_pay' ); ?></h2>
						<a href="https://wordpress.org/support/plugin/wp-easy-pay/reviews/" class="view-all-link" target="_blank"><?php esc_html_e( 'Read all reviews', 'wp_easy_pay' ); ?></a>
					</div>

					<div class="row">
						<div class="col-6 pr-3">
							<div class="testimonial-item">
								<div class="stars mb-2">
									<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/wp-review-stars.png' ); ?>" class="wp-reviews-stars" alt="WP EasyPay">
								</div>
								<p class="testimonial-text"><?php esc_html_e( '"Amazing plugin, easy to setup. Works well and simple for customers to pay with as it’s on the website and trusted without need for complicated shopping cart systems."', 'wp_easy_pay' ); ?></p>
								<div class="testimonial-author d-flex align-items-center justify-content-between mt-3">
									<div>
										<strong><?php esc_html_e( 'Darthvaderwp', 'wp_easy_pay' ); ?></strong><br>
										<span><?php esc_html_e( 'WordPress.org', 'wp_easy_pay' ); ?></span>
									</div>
									<div class="wp-icon">
										<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/wpep-wp-logo.png' ); ?>" class="wp-logo-review" alt="WP EasyPay">
									</div>
								</div>
							</div>
						</div>
						<div class="col-6 pl-3">
							<div class="testimonial-item">
								<div class="stars mb-2">
									<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/wp-review-stars.png' ); ?>" class="wp-reviews-stars" alt="WP EasyPay">
								</div>
								<p class="testimonial-text"><?php esc_html_e( '"Having been using starter version of app since May 2022. It’s very simple and what we’ve needed for the site: only receive payment via Square for a single product. Tech support has been good."', 'wp_easy_pay' ); ?></p>
								<div class="testimonial-author d-flex align-items-center justify-content-between mt-3">
									<div>
										<strong><?php esc_html_e( 'Threeels', 'wp_easy_pay' ); ?></strong><br>
										<span><?php esc_html_e( 'WordPress.org', 'wp_easy_pay' ); ?></span>
									</div>
									<div class="wp-icon">
										<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/wpep-wp-logo.png' ); ?>" class="wp-logo-review" alt="WP EasyPay">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
		<?php
	}
}
