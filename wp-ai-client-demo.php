<?php
/**
 * Plugin Name: WP AI Client Demo
 * Description: A demo plugin to showcase the integration of the WordPress AI Client.
 * Version: 1.0.1
 * Author: Jonathan Bossenger
 * Plugin URI: https://github.com/jonathanbossenger/wp-ai-client-demo
 *
 * @package wp-ai-client-demo
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include the Composer autoloader.
if ( file_exists( __DIR__ . '/vendor/wordpress/wp-ai-client/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/wordpress/wp-ai-client/autoload.php';
}

// Initialize the AI Client when WordPress initializes.
add_action( 'init', 'wp_ai_client_demo_init' );
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

add_filter( 'wp_ai_client_default_request_timeout', 'wp_ai_client_demo_set_request_timeout' );
/**
 * Set a custom request timeout for the AI Client.
 *
 * @return int
 */
function wp_ai_client_demo_set_request_timeout() {
	return 120;
}

add_action( 'admin_menu', 'wp_ai_client_demo_register_tools_submenu' );
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

add_action( 'admin_enqueue_scripts', 'wp_ai_client_demo_admin_enqueue_scripts' );
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

    $asset_file = include plugin_dir_path( __FILE__ ) . 'build/index.asset.php';

    wp_enqueue_script_module(
        'wp-ai-client-demo-script',
        plugins_url( 'build/index.js', __FILE__ ),
        array( '@wordpress/abilities' ),
        $asset_file['version'],
    );
}

add_action( 'wp_abilities_api_categories_init', 'wp_ai_client_demo_register_ability_categories' );
/**
 * Register custom ability categories for the WP AI SDK Demo plugin.
 *
 * @return void
 */
function wp_ai_client_demo_register_ability_categories() {
	wp_register_ability_category(
		'wp-ai-client-demo',
		array(
			'label'       => __( 'WP AI SDK Demo', 'wp-ai-client-demo' ),
			'description' => __( 'Abilities for the WP AI SDK Demo.', 'wp-ai-client-demo' ),
		)
	);
}

add_action( 'wp_abilities_api_init', 'wp_ai_client_demo_register_generate_post_ability' );
/**
 * Register a custom ability to get site information.
 *
 * @return void
 */
function wp_ai_client_demo_register_generate_post_ability() {
	wp_register_ability(
		'wp-ai-client-demo/generate-post',
		array(
			'label'               => __( 'Generate a post via AI', 'wp-ai-client-demo' ),
			'description'         => __( 'Based on a title and prompt, create an AI generated WordPress post.', 'wp-ai-client-demo' ),
			'category'            => 'wp-ai-client-demo',
			'input_schema'        => array(
				'type'       => 'object',
				'properties' => array(
					'title'  => array(
						'type'        => 'string',
						'description' => 'The title of the post to be generated.',
					),
					'prompt'  => array(
						'type'        => 'string',
						'description' => 'The prompt to guide the post generation.',
					),
					'context' => array(
						'type'        => 'array',
						'description' => 'Optional list of post IDs to use as context for generation.',
						'items'       => array(
							'type' => 'integer',
						),
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
			'execute_callback'    => 'wp_ai_client_demo_generate_post',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'meta'                => array(
				'show_in_rest' => true,
			),
		)
	);
}
add_action( 'wp_abilities_api_init', 'wp_ai_client_demo_register_generate_writing_style_ability' );
/**
 * Register an ability to generate writing style instructions from existing posts.
 *
 * @return void
 */
function wp_ai_client_demo_register_generate_writing_style_ability() {
	wp_register_ability(
		'wp-ai-client-demo/generate-writing-style',
		array(
			'label'               => __( 'Generate writing style instructions', 'wp-ai-client-demo' ),
			'description'         => __( 'Analyze selected posts and generate a set of writing style instructions based on their content.', 'wp-ai-client-demo' ),
			'category'            => 'wp-ai-client-demo',
			'input_schema'        => array(
				'type'       => 'object',
				'properties' => array(
					'post_ids' => array(
						'type'        => 'array',
						'description' => 'List of post IDs to analyze for writing style.',
						'items'       => array(
							'type' => 'integer',
						),
					),
				),
				'required'   => array( 'post_ids' ),
			),
			'output_schema'       => array(
				'type'       => 'object',
				'properties' => array(
					'instructions' => array(
						'type'        => 'string',
						'description' => 'The generated writing style instructions.',
					),
				),
				'required'   => array( 'instructions' ),
			),
			'execute_callback'    => 'wp_ai_client_demo_generate_writing_style',
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
 * Generate writing style instructions based on the content of selected posts.
 *
 * @param array $arguments The arguments containing post_ids.
 *
 * @return array
 */
function wp_ai_client_demo_generate_writing_style( $arguments ) {
	$post_ids = $arguments['post_ids'] ?? array();

	if ( empty( $post_ids ) ) {
		return array(
			'instructions' => '',
		);
	}

	$post_contents = array();
	foreach ( $post_ids as $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'publish' !== $post->post_status ) {
			continue;
		}
		$text = wp_strip_all_tags( $post->post_content );
		$text = trim( $text );
		if ( ! empty( $text ) ) {
			$post_contents[] = $text;
		}
	}

	if ( empty( $post_contents ) ) {
		return array(
			'instructions' => '',
		);
	}

	$combined_content = implode( "\n\n---\n\n", $post_contents );

	$prompt = "Analyze the following blog posts and generate a concise set of writing style instructions that capture the author's tone, voice, sentence structure, vocabulary level, and any recurring stylistic patterns. The instructions should be usable as a guide for writing new content in the same style.\n\n" . $combined_content;

	try {
		$instructions = \WordPress\AI_Client\AI_Client::prompt( $prompt )->generate_text();
	} catch ( Exception $e ) {
		return array(
			'instructions' => 'Failed to generate writing style: ' . $e->getMessage(),
		);
	}

	return array(
		'instructions' => $instructions,
	);
}

/**
 * Generate a WordPress post using AI based on the provided title and prompt.
 *
 * @param array $arguments The arguments for post generation.
 *
 * @return array
 */
function wp_ai_client_demo_generate_post( $arguments ) {
	$context_post_ids = $arguments['context'] ?? array();
	$content          = wp_ai_client_generate_content( $arguments['prompt'], $context_post_ids );
	if ( is_wp_error( $content ) ) {
		return array(
			'message' => 'Post creation failed: ' . $content->get_error_message(),
		);
	}
	$image   = wp_ai_client_demo_create_image( $arguments['title'] );
	if ( is_wp_error( $image ) ) {
		return array(
			'message' => 'Post creation failed: ' . $image->get_error_message(),
		);
	}
    return wp_ai_client_demo_create_post( $arguments['title'], $content, $image );
}
/**
 * Generate content using the AI Client based on the provided prompt.
 *
 * @param string $prompt           The prompt to guide content generation.
 * @param array  $context_post_ids Optional list of post IDs to use as context.
 *
 * @return mixed
 */
function wp_ai_client_generate_content( $prompt, $context_post_ids = array() ) {
	$prompt = rtrim( $prompt );
	if ( ! str_ends_with( $prompt, '.' ) ) {
		$prompt .= '.';
	}
	$prompt .= ' Make sure the response uses WordPress Block Editor markup.';

	if ( ! empty( $context_post_ids ) ) {
		$ability = wp_get_ability( 'wp-ai-client-demo/generate-writing-style' );
		if ( $ability ) {
			$result = $ability->execute( array( 'post_ids' => $context_post_ids ) );
			if ( ! is_wp_error( $result ) && ! empty( $result['instructions'] ) ) {
				$prompt .= "\n\nUse the following writing style instructions when generating the content:\n" . $result['instructions'];
			}
		}
	}

    error_log($prompt);

	try {
		return \WordPress\AI_Client\AI_Client::prompt( $prompt )->generate_text();
	} catch ( Exception $e ) {
        error_log( $e );
		return new WP_Error( 'content_creation_error', 'Error message', $e->getMessage() );
	}
}
/**
 * Generate an image using the AI Client based on the provided title.
 *
 * @param string $title The post title to guide image generation.
 *
 * @return mixed
 */
function wp_ai_client_demo_create_image( $title ) {
	$image_prompt = 'Create a relevant featured image for a blog post with the following title: ' . $title . '.';
    $prompt = \WordPress\AI_Client\AI_Client::prompt( $image_prompt );
    if ( ! $prompt->is_supported_for_image_generation() ){
        return null;
    }
    try {
        return $prompt->generate_image();
    }catch ( Exception $e ) {
        return new WP_Error( 'image_creation_error', 'Error message', $e->getMessage() );
    }
}
/**
 * Create a WordPress post with the given title, content, and featured image.
 *
 * @param string $title The post title.
 * @param string $content The post content.
 * @param object $image The AI generated image object.
 *
 * @return array
 */
function wp_ai_client_demo_create_post( $title, $content, $image ) {
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
    if ( null === $image ) {
        $message = 'Post created successfully without featured image.';

        return array(
            'message' => $message,
            'post_id' => $post_id,
        );
    }
    $attachment_id = wp_ai_client_demo_image_to_media( $image, 'featured-image-' . sanitize_file_name( $title ), $post_id );
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
		'message'       => $message,
		'post_id'       => $post_id,
		'attachment_id' => $attachment_id,
	);
}
/**
 * Convert an AI generated image to a WordPress media attachment.
 *
 * @param object $image AI generated image object.
 * @param string $filename Optional. Desired filename for the image.
 * @param int    $post_id Optional. Post ID to attach the media to.
 *
 * @return int|WP_Error
 */
function wp_ai_client_demo_image_to_media( $image, $filename = null, $post_id = 0 ) {
	$base64_string = $image->getBase64Data();
	$mime          = $image->getMimeType();

	$data = base64_decode( $base64_string ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	if ( false === $data ) {
		return new WP_Error( 'invalid_base64', 'Base64 decode failed.' );
	}

	$mime_to_ext = array(
		'image/jpeg' => '.jpg',
		'image/png'  => '.png',
		'image/gif'  => '.gif',
		'image/webp' => '.webp',
	);
	$ext         = $mime_to_ext[ $mime ] ?? '.png';

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

	if ( file_put_contents( $file_path, $data ) === false ) { //phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		return new WP_Error( 'cannot_write_file', 'Failed to write file to disk.' );
	}

	$filetype = wp_check_filetype( $filename, null );

	$attachment = array(
		'guid'           => $upload['url'] . '/' . basename( $file_path ),
		'post_mime_type' => $filetype['type'] ?: ( $mime ?: 'image/png' ), //phpcs:ignore Universal.Operators.DisallowShortTernary.Found
		'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
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
