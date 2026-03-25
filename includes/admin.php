<?php
/**
 * Admin UI functions for WP AI Client Demo.
 *
 * @package wp-ai-client-demo
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the WP AI SDK Demo Tools submenu page.
 *
 * @return void
 */
function wp_ai_client_demo_register_tools_submenu() {
	add_submenu_page(
		'tools.php',
		'WP AI SDK Demo',
		'WP AI SDK Demo',
		'manage_options',
		'wp-ai-client-demo-tools',
		'wp_ai_client_demo_tools_page_callback'
	);
}

/**
 * Render the WP AI SDK Demo Tools page.
 *
 * @return void
 */
function wp_ai_client_demo_tools_page_callback() {
	printf(
		'<div class="wrap" id="wp-ai-client-demo-app">%s</div>',
		esc_html__( 'Loading…', 'wp-ai-client-demo' )
	);
}

/**
 * Enqueue Editor assets.
 */
function wp_ai_client_demo_admin_enqueue_scripts() {
	$screen = get_current_screen();

	if ( 'tools_page_wp-ai-client-demo-tools' !== $screen->id ) {
		return;
	}

    wp_enqueue_script( 'wp-ai-client' );

    // Should be removed once 7.0 is released.
    wp_enqueue_script_module( '@wordpress/core-abilities' );
    wp_enqueue_script_module( '@wordpress/abilities' );

    $asset_file = include plugin_dir_path( __DIR__ ) . 'build/index.asset.php';

    wp_enqueue_script_module(
        'wp-ai-client-demo-script',
        plugins_url( 'build/index.js', __DIR__ ),
        array( '@wordpress/abilities' ),
        $asset_file['version'],
    );

    wp_enqueue_style(
        'wp-ai-client-demo-style',
        plugins_url( 'build/style-index.css', __DIR__ ),
        array(),
        $asset_file['version'],
    );
}
