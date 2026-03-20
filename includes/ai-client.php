<?php
/**
 * AI Client functions for WP AI Client Demo.
 *
 * @package wp-ai-client-demo
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Initialize any plugin functionality
 *
 * @return void
 */
function wp_ai_client_demo_init() {
    if ( class_exists( 'WordPress\AI_Client\AI_Client' ) ) {
        \WordPress\AI_Client\AI_Client::init();
    }
}

/**
 * Set a custom request timeout for the AI Client.
 *
 * @return int
 */
function wp_ai_client_demo_set_request_timeout() {
    return 120;
}
