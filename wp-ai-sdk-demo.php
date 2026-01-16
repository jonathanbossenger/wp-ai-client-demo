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
const WP_AI_SDK_DEMO_LOGGER_ENABLED = false;

// Include the Composer autoloader.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

add_action( 'wp_abilities_api_categories_init', 'wp_ai_sdk_demo_register_ability_categories' );
/**
 * Register custom ability categories for the WP AI SDK Demo plugin.
 *
 * @return void
 */
function wp_ai_sdk_demo_register_ability_categories() {
	wp_register_ability_category(
		'wp-ai-sdk-demo',
		array(
			'label'       => __( 'WP AI SDK Demo', 'wp-ai-sdk-demo' ),
			'description' => __( 'Abilities for the WP AI SDK Demo.', 'wp-ai-sdk-demo' ),
		)
	);
}

add_action( 'wp_abilities_api_init', 'wp_ai_sdk_demo_register_generate_post_ability' );
/**
 * Register a custom ability to get site information.
 *
 * @return void
 */
function wp_ai_sdk_demo_register_generate_post_ability() {
	wp_register_ability(
		'wp-ai-sdk-demo/generate-post',
		array(
			'label'               => __( 'Generate a post via AI', 'wp-ai-sdk-demo' ),
			'description'         => __( 'Based on a title and prompt, create an AI generated WordPress post.', 'wp-ai-sdk-demo' ),
			'category'            => 'wp-ai-sdk-demo',
			'input_schema'        => array(
				'type'       => 'object',
				'properties' => array(
					'title'  => array(
						'type'        => 'string',
						'description' => 'The title of the post to be generated.',
					),
					'prompt' => array(
						'type'        => 'string',
						'description' => 'The prompt to guide the post generation.',
					),
				),
			),
			'output_schema'       => array(
				'type'       => 'object',
				'properties' => array(
					'message' => array(
						'type'        => 'string',
						'description' => 'A status message if the post was created successfully or not.',
					),
					'post_id' => array(
						'type'        => 'integer',
						'description' => 'The ID of the newly created post.',
					),
				),
				'required'   => array( 'message' ),
			),
			'execute_callback'    => 'wp_ai_sdk_demo_generate_post',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'meta'                => array(
				'show_in_rest' => true,
			),
		)
	);
}

/**
 * Generate a WordPress post using AI based on the provided title and prompt.
 *
 * @param $arguments
 *
 * @return array
 */
function wp_ai_sdk_demo_generate_post( $arguments ) {
	if ( WP_AI_SDK_DEMO_DEV_MODE ) {
		return array(
			'message' => 'Post creation skipped in dev mode.',
		);
	}
	$content = wp_ai_sdk_generate_content( $arguments['prompt'] );
	$image = wp_ai_sdk_demo_create_image( $arguments['title'] );

	return wp_ai_sdk_demo_create_post( $arguments['title'], $content, $image );
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
	$prompt .= ' Make sure the response uses WordPress Block Editor markup.';

	$content = \WordPress\AI_Client\AI_Client::prompt( $prompt )->generate_text();
	if ( WP_AI_SDK_DEMO_LOGGER_ENABLED ) {
		error_log( 'Generated Content: ' . print_r( $content, true ) );
	}
	return $content;
}

/**
 * Generate an image using the AI Client based on the provided title.
 *
 * @param $title
 *
 * @return mixed
 */
function wp_ai_sdk_demo_create_image( $title ) {
	$image_prompt = 'Create a relevant featured image for a blog post with the following title: ' . $title . '. Provide the image in a URL format suitable for web display.';
	$image = \WordPress\AI_Client\AI_Client::prompt( $image_prompt )->generate_image();
	if ( WP_AI_SDK_DEMO_LOGGER_ENABLED ) {
		error_log( 'Generated Image: ' . print_r( $image, true ) );
	}
	return $image;
}

/**
 * @param $title
 * @param $content
 * @param $image
 *
 * @return array
 */
function wp_ai_sdk_demo_create_post( $title, $content, $image ) {
	$post_id = wp_insert_post(
		array(
			'post_title'   => sanitize_text_field( $title ),
			'post_content' => $content,
			'post_status'  => 'draft',
			'post_type'    => 'post',
		)
	);
	if ( is_wp_error( $post_id ) ) {
		$message = 'Post creation failed.';
		return array(
			'message' => $message,
		);
	}
	$attachment_id = wp_ai_sdk_demo_image_to_media( $image, 'featured-image-' . sanitize_file_name( $title ), $post_id );
	if ( is_wp_error( $attachment_id ) ) {
		$message = 'Post created, but featured image upload failed.';
		return array(
			'message' => $message,
			'post_id' => $post_id,
		);
	}
	set_post_thumbnail( $post_id, $attachment_id );
	$message = 'Post and image created successfully.';
	return array(
		'message' => $message,
		'post_id' => $post_id,
		'attachment_id' => $attachment_id,
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
}

add_filter( 'wp_ai_client_default_request_timeout', 'wp_ai_sdk_demo_set_request_timeout' );
function wp_ai_sdk_demo_set_request_timeout( $timeout ) {
	return 60;
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
	$asset_file = include plugin_dir_path( __FILE__ ) . 'build/index.asset.php';

	wp_enqueue_script(
		'wp-ai-sdk-demo-scripts',
		plugins_url( 'build/index.js', __FILE__ ),
		$asset_file['dependencies'],
		$asset_file['version']
	);
}

function wp_ai_sdk_demo_image_to_media( $image, $filename = null, $post_id = 0 ) {

	$base64_string = $image->getBase64Data();
	$mime          = $image->getMimeType();

	$data = base64_decode( $base64_string );
	if ( $data === false ) {
		return new WP_Error( 'invalid_base64', 'Base64 decode failed.' );
	}

	$mime_to_ext = array(
		'image/jpeg' => '.jpg',
		'image/png'  => '.png',
		'image/gif'  => '.gif',
		'image/webp' => '.webp',
	);
	$ext = $mime_to_ext[ $mime ] ?? '.png';

	if ( ! $filename ) {
		$filename = 'image-' . time() . $ext;
	} elseif ( pathinfo( $filename, PATHINFO_EXTENSION ) === '' ) {
		$filename .= $ext;
	}

	$upload = wp_upload_dir();
	if ( wp_mkdir_p( $upload['path'] ) ) {
		$file_path = $upload['path'] . '/' . $filename;
	} else {
		$file_path = $upload['basedir'] . '/' . $filename;
	}

	if ( file_put_contents( $file_path, $data ) === false ) {
		return new WP_Error( 'cannot_write_file', 'Failed to write file to disk.' );
	}

	$filetype = wp_check_filetype( $filename, null );

	$attachment = array(
		'guid'           => $upload['url'] . '/' . basename( $file_path ),
		'post_mime_type' => $filetype['type'] ?: ( $mime ?: 'image/png' ),
		'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
		'post_content'   => '',
		'post_status'    => 'inherit'
	);

	$attach_id = wp_insert_attachment( $attachment, $file_path, $post_id );
	if ( is_wp_error( $attach_id ) ) {
		return $attach_id;
	}

	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	return $attach_id;
}
