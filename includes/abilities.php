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
 * Register an ability to get the saved writing style instructions.
 *
 * @return void
 */
function wp_ai_client_demo_register_get_writing_style_ability() {
	wp_register_ability(
		'wp-ai-client-demo/get-writing-style',
		array(
			'label'               => __( 'Get writing style instructions', 'wp-ai-client-demo' ),
			'description'         => __( 'Retrieve the saved writing style instructions to guide content generation.', 'wp-ai-client-demo' ),
			'category'            => 'wp-ai-client-demo',
			'input_schema'        => array(
				'type'       => 'object',
				'properties' => array(),
			),
			'output_schema'       => array(
				'type'       => 'object',
				'properties' => array(
					'instructions' => array(
						'type'        => 'string',
						'description' => 'The saved writing style instructions.',
					),
				),
				'required'   => array( 'instructions' ),
			),
			'execute_callback'    => 'wp_ai_client_demo_get_writing_style',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'meta'                => array(
				'show_in_rest' => true,
			),
		)
	);
}
