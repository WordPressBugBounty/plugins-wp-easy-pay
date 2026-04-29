<?php
/**
 * Frontend render file for Beaver Builder WP Easy Pay Module
 * 
 * This file renders the WP Easy Pay form shortcode on the frontend
 *
 * @package wp_easy_pay
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Exit if Beaver Builder is not active
if ( ! class_exists( 'FLBuilderModel' ) ) {
	return;
}

// Check if form_id is set and not empty.
if ( isset( $settings->form_id ) && ! empty( $settings->form_id ) ) {
	$form_id = absint( $settings->form_id );
	
	// Set transient for script detection (similar to Gutenberg block)
	set_transient( 'wpep_guten_id', $form_id, 60 );
	
	// Manually enqueue scripts for Beaver Builder module
	// The wpep_form_backend_parent_scripts function checks post content,
	// but in Beaver Builder we need to ensure it detects our form
	if ( function_exists( 'wpep_form_backend_parent_scripts' ) ) {
		global $post;
		$original_content = '';
		
		// Temporarily modify post content to help script detection
		if ( $post && isset( $post->post_content ) ) {
			$original_content = $post->post_content;
			// Add shortcode to post content temporarily for detection
			if ( strpos( $post->post_content, '[wpep-form' ) === false ) {
				$post->post_content = '[wpep-form id="' . $form_id . '"]' . $post->post_content;
			}
		}
		
		// This will enqueue necessary CSS and JS files
		wpep_form_backend_parent_scripts();
		
		// Restore original content
		if ( $post && ! empty( $original_content ) ) {
			$post->post_content = $original_content;
		}
	}
	
	// Show informative message in builder mode
	if ( class_exists( 'FLBuilderModel' ) && FLBuilderModel::is_builder_active() ) {
		$form_title = get_the_title( $form_id );
		$form_title = ! empty( $form_title ) ? $form_title : __( 'Untitled Form', 'wp_easy_pay' );
		
		$logo_url = WPEP_ROOT_URL . 'assets/backend/img/Logo.png';
		
		echo '<div class="fl-builder-wpep-info-box" style="padding: 20px; margin-bottom: 20px; text-align: center; border: 2px solid #5D97FF; background: #f0f7ff; border-radius: 8px; box-shadow: 0 2px 8px rgba(93, 151, 255, 0.1);">';
		echo '<div style="margin-bottom: 10px;">';
		echo '<img src="' . esc_url( $logo_url ) . '" alt="WP Easy Pay Logo" style="max-width: 120px; height: auto; margin: 0 auto; display: block;">';
		echo '</div>';
		echo '<p style="margin: 8px 0; color: #333; font-size: 14px;"><strong>' . esc_html__( 'Form Name:', 'wp_easy_pay' ) . '</strong> ' . esc_html( $form_title ) . '</p>';
		echo '<p style="margin: 8px 0; color: #333; font-size: 14px;"><strong>' . esc_html__( 'Form ID:', 'wp_easy_pay' ) . '</strong> <code style="background: #fff; padding: 4px 8px; border-radius: 4px; color: #5D97FF; font-weight: 600;">' . esc_html( $form_id ) . '</code></p>';
		echo '</div>';
	}
	
	$shortcode = '[wpep-form id="' . $form_id . '"]';
	
	// Output the shortcode (will show on frontend, hidden in builder preview)
	if ( ! class_exists( 'FLBuilderModel' ) || ! FLBuilderModel::is_builder_active() ) {
		echo do_shortcode( $shortcode );
	} else {
		// In builder, show a note that form will appear on frontend
		echo '<div style="padding: 15px; background: #f0f7ff; border: 2px solid #5D97FF; border-radius: 4px; margin-top: 10px; text-align: center; color: #000000; font-size: 13px;">';
		echo '<strong>' . esc_html__( 'Note:', 'wp_easy_pay' ) . '</strong> ' . esc_html__( 'The payment form will be displayed on the front page after publish', 'wp_easy_pay' );
		echo '</div>';
	}
} elseif ( class_exists( 'FLBuilderModel' ) && FLBuilderModel::is_builder_active() ) {
	// Show message in builder if form is not selected.
	echo '<div class="fl-builder-wpep-placeholder" style="padding: 20px; text-align: center; border: 2px dashed #5D97FF; background: #f0f7ff; border-radius: 8px;">';
	echo '<p style="margin: 0; color: #000000;font-size: 14px;">' . esc_html__( 'Please select a form from the module settings.', 'wp_easy_pay' ) . '</p>';
	echo '</div>';
}
