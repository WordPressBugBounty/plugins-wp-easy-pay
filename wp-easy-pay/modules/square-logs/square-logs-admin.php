<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'logs';

echo '<div class="wrap">';
echo '<h1>' . esc_html__( 'Square Logs', 'wp_easy_pay' ) . '</h1>';

// Tabs nav
echo '<h2 class="nav-tab-wrapper">';
echo '<a href="?post_type=wp_easy_pay&page=square-logs&tab=logs" class="squareLogTabs nav-tab ' . ( 'logs' === $current_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Logs', 'wp_easy_pay' ) . '</a>';
echo '<a href="?post_type=wp_easy_pay&page=square-logs&tab=email" class="squareLogTabs nav-tab ' . ( 'email' === $current_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Alerts', 'wp_easy_pay' ) . '</a>';
echo '</h2>';

// Load tab content
if ( 'email' === $current_tab ) {
	include plugin_dir_path( __FILE__ ) . 'view/email-alerts.php';
} else {
	include plugin_dir_path( __FILE__ ) . 'view/logs-list.php';
}

echo '</div>';
