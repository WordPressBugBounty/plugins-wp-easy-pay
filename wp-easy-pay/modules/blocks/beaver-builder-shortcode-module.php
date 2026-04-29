<?php
/**
 * Beaver Builder Module for WP Easy Pay
 * 
 * This module allows users to add WP Easy Pay forms in Beaver Builder pages.
 * Similar to Gutenberg block compatibility.
 *
 * @package wp_easy_pay
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Early exit if Beaver Builder is not installed or not active
if ( ! class_exists( 'FLBuilder' ) ) {
	return;
}

/**
 * Helper function to get WP Easy Pay forms list for dropdown
 *
 * @return array Array of form options for select field
 */
function wpep_get_beaver_forms_list() {
	// Use WP_Query for better reliability
	$query = new WP_Query(
		array(
			'post_type'      => 'wp_easy_pay',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	
	$options = array();
	
	// Always add the default option first
	$options[''] = __( 'Please select your form', 'wp_easy_pay' );
	
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$form_id    = get_the_ID();
			$form_title = get_the_title();
			
			if ( ! empty( $form_title ) ) {
				$options[ $form_id ] = $form_title;
			} else {
				$options[ $form_id ] = __( 'Untitled form', 'wp_easy_pay' ) . ' (ID: ' . $form_id . ')';
			}
		}
		wp_reset_postdata();
	}
	
	// Ensure we always return a valid array with at least the default option
	if ( count( $options ) === 1 ) {
		// Only default option, no forms found - update the message
		$options[''] = __( 'No forms found. Please create a form first.', 'wp_easy_pay' );
	}
	
	return $options;
}

/**
 * Register the module after Beaver Builder is loaded.
 */
function wpep_register_beaver_builder_module() {
	// Check if Beaver Builder is active.
	if ( ! class_exists( 'FLBuilder' ) || ! class_exists( 'FLBuilderModule' ) ) {
		return;
	}

	// Prevent multiple registrations
	static $registered = false;
	if ( $registered ) {
		return;
	}

	// Check if class already exists to avoid redeclaration.
	if ( ! class_exists( 'FLWPEasyPayModule' ) ) {
		/**
		 * @class FLWPEasyPayModule
		 * 
		 * Beaver Builder module class for WP Easy Pay forms
		 */
		class FLWPEasyPayModule extends FLBuilderModule {

			/**
			 * Constructor
			 */
			public function __construct() {
				// Get logo URL for icon
				$logo_url = WPEP_ROOT_URL . 'assets/backend/img/Logo.png';
				
				// Create inline SVG icon with logo image
				// Beaver Builder's get_icon() method will detect this as inline SVG
				$custom_icon = '<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
					<image x="0" y="0" width="20" height="20" xlink:href="' . esc_url( $logo_url ) . '" preserveAspectRatio="xMidYMid meet"/>
				</svg>';
				
				parent::__construct(
					array(
						'name'            => __( 'WP Easy Pay Form', 'wp_easy_pay' ),
						'description'     => __( 'Display WP Easy Pay payment form using shortcode', 'wp_easy_pay' ),
						'category'        => __( 'WP Easy Pay', 'wp_easy_pay' ),
						'icon'            => $custom_icon, // Custom SVG with logo image
						'partial_refresh' => true,
						'dir'             => WPEP_ROOT_PATH . 'modules/blocks/beaver-builder/',
						'url'             => WPEP_ROOT_URL . 'modules/blocks/beaver-builder/',
						'slug'            => 'wpeasy-pay-form',
					)
				);
			}

			/**
			 * Enqueue scripts for this module
			 */
			public function enqueue_scripts() {
				// Get form ID from settings
				if ( isset( $this->settings->form_id ) && ! empty( $this->settings->form_id ) ) {
					$form_id = absint( $this->settings->form_id );
					
					// Set transient for script detection
					set_transient( 'wpep_guten_id', $form_id, 60 );
					
					// Ensure scripts are enqueued
					if ( function_exists( 'wpep_form_backend_parent_scripts' ) ) {
						wpep_form_backend_parent_scripts();
					}
				}
			}
		}
	}

	// Register the module only if not already registered.
	if ( class_exists( 'FLWPEasyPayModule' ) ) {
		// Get forms list with error handling
		try {
			$forms_options = wpep_get_beaver_forms_list();
			
			// Ensure options is always a valid array
			if ( ! is_array( $forms_options ) ) {
				$forms_options = array( '' => __( 'Please select your form', 'wp_easy_pay' ) );
			}
			
			// Ensure we have at least one option
			if ( empty( $forms_options ) ) {
				$forms_options = array( '' => __( 'No forms available', 'wp_easy_pay' ) );
			}
		} catch ( Exception $e ) {
			// Fallback if function fails
			$forms_options = array( '' => __( 'Error loading forms', 'wp_easy_pay' ) );
		}
		
		// Register module with form settings
		$module_form = array(
			'general' => array(
				'title'    => __( 'General', 'wp_easy_pay' ),
				'sections' => array(
					'general' => array(
						'title'  => '',
						'fields' => array(
							'form_id' => array(
								'type'    => 'select',
								'label'   => __( 'Select Form', 'wp_easy_pay' ),
								'options' => $forms_options,
								'default' => '',
								'help'    => __( 'Select the payment form you want to display', 'wp_easy_pay' ),
								'preview' => array(
									'type' => 'none',
								),
							),
						),
					),
				),
			),
		);
		
		FLBuilder::register_module( 'FLWPEasyPayModule', $module_form );
		
		// Mark as registered
		$registered = true;
	}
}

// Register module after Beaver Builder loads its extensions (primary method).
add_action( 'fl_builder_register_extensions', 'wpep_register_beaver_builder_module', 10 );

// Fallback: Register on init with higher priority to ensure it happens after Beaver Builder init.
add_action( 'init', 'wpep_register_beaver_builder_module', 99 );
