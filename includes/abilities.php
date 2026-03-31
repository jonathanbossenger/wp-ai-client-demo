<?php
/**
 * Ability registration functions for WP AI Client Demo.
 *
 * @package wp-ai-client-demo
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register custom ability categories for the WP AI Client Demo plugin.
 *
 * @return void
 */
function wp_ai_client_demo_register_ability_categories() {
	wp_register_ability_category(
		'wp-ai-client-demo',
		array(
			'label'       => __( 'WP AI Client Demo', 'wp-ai-client-demo' ),
			'description' => __( 'Abilities for the WP AI Client Demo.', 'wp-ai-client-demo' ),
		)
	);
}

/**
 * Register a custom ability to generate a post via AI.
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

