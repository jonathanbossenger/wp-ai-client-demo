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

const WP_AI_SDK_DEMO_DEV_MODE = false;

// Include the Composer autoloader.
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

add_action( 'wp_abilities_api_categories_init', 'wp_ai_sdk_demo_register_ability_categories' );
/**
 * Register custom ability categories for the WP AI SDK Demo plugin.
 *
 * @return void
 */
function wp_ai_sdk_demo_register_ability_categories() {
	wp_register_ability_category( 'wp-ai-sdk-demo', array(
		'label'       => __( 'WP AI SDK Demo', 'wp-ai-sdk-demo' ),
		'description' => __( 'Abilities for the WP AI SDK Demo.', 'wp-ai-sdk-demo' ),
	) );
}

add_action( 'wp_abilities_api_init', 'wp_ai_sdk_demo_register_generate_post_ability' );
/**
 * Register a custom ability to get site information.
 *
 * @return void
 */
function wp_ai_sdk_demo_register_generate_post_ability() {
	wp_register_ability( 'wp-ai-sdk-demo/generate-post', array(
		'label' => __( 'Generate a post via AI', 'wp-ai-sdk-demo' ),
		'description' => __( 'Based on a title and prompt, create an AI generated WordPress post.', 'wp-ai-sdk-demo' ),
		'category' => 'wp-ai-sdk-demo',
		'input_schema' => array(
			'type' => 'object',
			'properties' => array(
				'title' => array(
					'type' => 'string',
					'description' => 'The title of the post to be generated.'
				),
				'prompt' => array(
					'type' => 'string',
					'description' => 'The prompt to guide the post generation.'
				)
			),
		),
		'output_schema' => array(
			'type' => 'object',
			'properties' => array(
				'message' => array(
					'type' => 'string',
					'description' => 'A status message if the post was created successfully or not.'
				),
				'post_id' => array(
					'type' => 'integer',
					'description' => 'The ID of the newly created post.'
				)
			),
			'required' => array( 'message' )
		),
		'execute_callback' => 'wp_ai_sdk_demo_generate_post',
		'permission_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
		'meta' => array(
			'show_in_rest' => true,
		)
	));
}

/**
 * Generate a WordPress post using AI based on the provided title and prompt.
 *
 * @param $arguments
 *
 * @return array
 */
function wp_ai_sdk_demo_generate_post( $arguments ) {
	if ( WP_AI_SDK_DEMO_DEV_MODE ){
		return array(
			'message' => 'Post creation skipped in dev mode.',
		);
	}
	$content = wp_ai_sdk_generate_content( $arguments['prompt'] );

	return wp_ai_sdk_demo_create_post( $arguments['title'], $content );
}

/**
 * Generate content using the AI Client based on the provided prompt.
 *
 * @param $prompt
 *
 * @return mixed
 */
function wp_ai_sdk_generate_content( $prompt ) {
	$prompt = rtrim( $prompt );
	if ( ! str_ends_with( $prompt, '.' ) ) {
		$prompt .= '.';
	}
	$prompt .= " Make sure the response uses WordPress Block Editor markup.";

	return \WordPress\AI_Client\AI_Client::prompt( $prompt )->generateText();
}

/**
 * Generate an image using the AI Client based on the provided title.
 *
 * @param $title
 *
 * @return mixed
 */
function wp_ai_sdk_demo_create_image( $title ) {
	$image_prompt = "Create a relevant featured image for a blog post with the following title: " . $title . ". Provide the image in a URL format suitable for web display.";
	return \WordPress\AI_Client\AI_Client::prompt( $image_prompt )->generateImage();
}

/**
 * @param $title
 * @param $content
 *
 * @return array
 */
function wp_ai_sdk_demo_create_post( $title, $content ) {
	$post_id = wp_insert_post( array(
		'post_title'   => sanitize_text_field( $title ),
		'post_content' => $content,
		'post_status'  => 'draft',
		'post_type'    => 'post',
	) );
	if ( is_wp_error( $post_id ) ) {
		$message = 'Post creation failed.';
		return array(
			'message' => $message,
		);
	}
	$message = 'Post created successfully.';
	return array(
		'message' => $message,
		'post_id' => $post_id,
	);
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
	//WP_CLI::add_command( 'wp-ai-sdk-demo', 'wp_ai_sdk_demo_command' );
}

add_filter( 'wp_ai_client_default_request_timeout', 'wp_ai_sdk_demo_set_request_timeout' );
function wp_ai_sdk_demo_set_request_timeout( $timeout ) {
	return 60;
}

function wp_ai_sdk_demo_command( $args, $assoc_args ) {
	print_r( $args );
	print_r( $assoc_args );
	return;
	return wp_ai_sdk_demo_generate_post( $arguments );
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