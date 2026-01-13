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
    // Simple page output; replace with your React mount node or server-rendered content.
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'WP AI SDK Demo', 'wp-ai-sdk-demo' ); ?></h1>
        <div id="wp-ai-sdk-demo-app">WP AI SDK Demo Content</div>
    </div>
    <?php
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

