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
 * @param string $prompt The prompt to guide content generation.
 *
 * @return mixed
 */
function wp_ai_client_generate_content( $prompt ) {
	$prompt = rtrim( $prompt );
	if ( ! str_ends_with( $prompt, '.' ) ) {
		$prompt .= '.';
	}
	$prompt .= ' Make sure the response uses WordPress Block Editor markup.';

	error_log( $prompt );

	try {
		return \WordPress\AI_Client\AI_Client::prompt( $prompt )
			->using_abilities()
			->generate_text();
	} catch ( Exception $e ) {
		error_log( $e );
		return new WP_Error( 'content_creation_error', 'Error message', $e->getMessage() );
	}
}

