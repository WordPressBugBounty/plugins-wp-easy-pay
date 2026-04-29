<?php
function wpep_form_shortcode_scripts() {
	wp_register_script( 'wpep_shortcode_block', WPEP_ROOT_URL . 'assets/backend/js/gutenberg_shortcode_block/build/index.js?rand=' . wp_rand(), array( 'wp-blocks' ), WPEP_VERSION, true );
	wp_enqueue_script( 'wpep_shortcode_block' );
	$my_data = array(
		'wpep_recaptcha_version' => get_option( 'wpep_recaptcha_version' ),
		'plugin_url'             => WPEP_ROOT_URL, // for assets in JS
	);

	// Localize the script to pass the data to JavaScript
	wp_localize_script( 'wpep_shortcode_block', 'wpep_data', $my_data );
}
add_action( 'admin_enqueue_scripts', 'wpep_form_shortcode_scripts' );
function wpep_register_gutenberg_blocks() {
	$args               = array(
		'numberposts' => 10,
		'post_type'   => 'wp_easy_pay',
	);
	$latest_books       = get_posts( $args );
	$wpep_payment_forms = array();
	$count              = 0;
	foreach ( $latest_books as $value ) {
		$form_title = trim( (string) $value->post_title );
		if ( '' === $form_title ) {
			$form_title = __( 'Untitled form', 'wp_easy_pay' ) . ' (ID: ' . $value->ID . ')';
		}
		$wpep_payment_forms[ $count ]['ID']    = $value->ID;
		$wpep_payment_forms[ $count ]['title'] = $form_title;
		++$count;
	}

	$wpep_forms = array(
		'forms' => $wpep_payment_forms,
	);

	wp_localize_script( 'wpep_shortcode_block', 'wpep_forms', $wpep_forms );
}

register_block_type(
	'wpep/shortcode',
	array(
		'editor_script'   => 'wpep_shortcode_block',
		'render_callback' => 'custom_gutenberg_render_html',
	)
);


function custom_gutenberg_render_html( $attributes, $content ) {
	$shortcode = $content;

	if ( isset( $attributes['type'] ) ) {
		$shortcode = '[wpep-form id="' . $attributes['type'] . '"]';
		set_transient( 'wpep_guten_id', $attributes['type'], 60 );
	}

	return $shortcode;
}
add_action( 'admin_enqueue_scripts', 'wpep_register_gutenberg_blocks' );
