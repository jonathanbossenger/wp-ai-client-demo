<?php
/**
 * Post generation and creation functions for WP AI Client Demo.
 *
 * @package wp-ai-client-demo
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate a WordPress post using AI based on the provided title and prompt.
 *
 * @param array $arguments The arguments for post generation.
 *
 * @return array
 */
function wp_ai_client_demo_generate_post( $arguments ) {
	$content = wp_ai_client_generate_content( $arguments['prompt'] );
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
