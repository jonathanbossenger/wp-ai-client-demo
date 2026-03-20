<?php
/**
 * Content generation functions for WP AI Client Demo.
 *
 * @package wp-ai-client-demo
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
