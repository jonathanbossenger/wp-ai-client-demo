<?php
/**
 * Plugin Name: WP AI SDK Demo
 * Description: A demo plugin to showcase the integration of the WordPress AI SDK.
 * Version: 1.0.0
 * Author: Jonathan Bossenger
 * Plugin URI: https://github.com/jonathanbossenger/wp-ai-sdk-demo
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include the Composer autoloader.
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

// Initialize the AI Client when WordPress initializes.
add_action( 'init', 'wp_ai_sdk_demo_init' );
/**
 * Initialize any plugin functionality
 *
 * @return void
 */
function wp_ai_sdk_demo_init() {
    if ( class_exists( 'WordPress\AI_Client\AI_Client' ) ) {
        \WordPress\AI_Client\AI_Client::init();
    }
}

add_action( 'admin_menu', 'wp_ai_sdk_demo_register_tools_submenu' );
/**
 * Register the WP AI SDK Demo Tools submenu page.
 *
 * @return void
 */
function wp_ai_sdk_demo_register_tools_submenu() {
    add_submenu_page(
        'tools.php',
        'WP AI SDK Demo',
        'WP AI SDK Demo',
        'manage_options',
        'wp-ai-sdk-demo-tools',
        'wp_ai_sdk_demo_tools_page_callback'
    );
}

function wp_ai_sdk_demo_tools_page_callback() {
    printf(
        '<div class="wrap" id="wp-ai-sdk-demo-app">%s</div>',
        esc_html__( 'Loading…', 'wp-ai-sdk-demo' )
    );
}

add_action( 'admin_enqueue_scripts', 'wp_ai_sdk_demo_admin_enqueue_scripts' );
/**
 * Enqueue Editor assets.
 */
function wp_ai_sdk_demo_admin_enqueue_scripts() {
    $screen = get_current_screen();
    if ( $screen->id !== 'tools_page_wp-ai-sdk-demo-tools' ) {
        return;
    }
    $asset_file = include( plugin_dir_path( __FILE__ ) . 'build/index.asset.php');

    wp_enqueue_script(
        'wp-ai-sdk-demo-scripts',
        plugins_url( 'build/index.js', __FILE__ ),
        $asset_file['dependencies'],
        $asset_file['version']
    );
}

