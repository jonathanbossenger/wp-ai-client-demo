<?php
/**
 * Plugin Name: WP AI Client Demo
 * Description: A demo plugin to showcase the integration of the WordPress AI Client.
 * Version: 1.0.0
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
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
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

		// Register the Ollama provider.
		wp_ai_client_demo_register_ollama_provider();
	}
}

/**
 * Register the Ollama provider with the AI Client.
 *
 * @return void
 */
function wp_ai_client_demo_register_ollama_provider() {
	// Check if the Ollama provider class exists.
	if ( ! class_exists( 'WpAiClientDemo\ProviderImplementations\Ollama\OllamaProvider' ) ) {
		return;
	}

	try {
		// Get the provider registry from the PHP AI Client.
		$registry = \WordPress\AiClient\AiClient::defaultRegistry();

		// Register the Ollama provider.
		$registry->registerProvider( \WpAiClientDemo\ProviderImplementations\Ollama\OllamaProvider::class );

		// Set no-auth authentication for Ollama (local server doesn't need API keys).
		$no_auth = new \WpAiClientDemo\ProviderImplementations\Ollama\NoAuthRequestAuthentication();
		$registry->setProviderRequestAuthentication( 'ollama', $no_auth );
	} catch ( Exception $e ) {
		// Log error if registration fails.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Failed to register Ollama provider: ' . $e->getMessage() );
		}
	}
}

add_filter( 'wp_ai_client_default_request_timeout', 'wp_ai_client_demo_set_request_timeout' );
/**
 * Set a custom request timeout for the AI Client.
 *
 * @return int
 */
function wp_ai_client_demo_set_request_timeout() {
	return 60;
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

add_action( 'admin_menu', 'wp_ai_client_demo_register_local_ai_settings' );
/**
 * Register the Local AI Model settings page.
 *
 * @return void
 */
function wp_ai_client_demo_register_local_ai_settings() {
	add_options_page(
		__( 'Local AI Models', 'wp-ai-client-demo' ),
		__( 'Local AI Models', 'wp-ai-client-demo' ),
		'manage_options',
		'wp-ai-client-demo-local-ai',
		'wp_ai_client_demo_local_ai_settings_page'
	);
}

add_action( 'admin_init', 'wp_ai_client_demo_register_local_ai_model_settings' );
/**
 * Register settings for Local AI Model.
 *
 * @return void
 */
function wp_ai_client_demo_register_local_ai_model_settings() {
	register_setting(
		'wp_ai_client_demo_local_ai',
		'wp_ai_client_demo_ollama_model',
		array(
			'type'              => 'string',
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	add_settings_section(
		'wp_ai_client_demo_local_ai_section',
		__( 'Ollama Model Selection', 'wp-ai-client-demo' ),
		'wp_ai_client_demo_local_ai_section_callback',
		'wp-ai-client-demo-local-ai'
	);

	add_settings_field(
		'wp_ai_client_demo_ollama_model',
		__( 'Select Ollama Model', 'wp-ai-client-demo' ),
		'wp_ai_client_demo_ollama_model_field_callback',
		'wp-ai-client-demo-local-ai',
		'wp_ai_client_demo_local_ai_section'
	);
}

/**
 * Settings section callback.
 *
 * @return void
 */
function wp_ai_client_demo_local_ai_section_callback() {
	echo '<p>' . esc_html__( 'Choose which Ollama model to use for AI-powered post generation.', 'wp-ai-client-demo' ) . '</p>';
}

/**
 * Ollama model field callback.
 *
 * @return void
 */
function wp_ai_client_demo_ollama_model_field_callback() {
	$selected_model = get_option( 'wp_ai_client_demo_ollama_model', '' );
	$models         = wp_ai_client_demo_get_ollama_models();

	if ( is_wp_error( $models ) ) {
		echo '<p class="description" style="color: #d63638;">';
		echo '<strong>' . esc_html__( 'Error:', 'wp-ai-client-demo' ) . '</strong> ';
		echo esc_html( $models->get_error_message() );
		echo '</p>';
		echo '<p class="description">' . esc_html__( 'Please ensure Ollama is running on http://localhost:11434', 'wp-ai-client-demo' ) . '</p>';
		return;
	}

	if ( empty( $models ) ) {
		echo '<p class="description">' . esc_html__( 'No Ollama models found. Please pull at least one model using: ollama pull llama2', 'wp-ai-client-demo' ) . '</p>';
		return;
	}

	echo '<select id="wp_ai_client_demo_ollama_model" name="wp_ai_client_demo_ollama_model">';
	echo '<option value="">' . esc_html__( '-- Select a model --', 'wp-ai-client-demo' ) . '</option>';

	foreach ( $models as $model ) {
		$selected = selected( $selected_model, $model['id'], false );
		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $model['id'] ),
			$selected,
			esc_html( $model['name'] )
		);
	}

	echo '</select>';
	echo '<p class="description">' . esc_html__( 'Select which local Ollama model to use for text generation.', 'wp-ai-client-demo' ) . '</p>';
}

/**
 * Get available Ollama models.
 *
 * @return array|WP_Error Array of models or WP_Error on failure.
 */
function wp_ai_client_demo_get_ollama_models() {
	// Try to get cached models first (cache for 5 minutes).
	$cached_models = get_transient( 'wp_ai_client_demo_ollama_models' );
	if ( false !== $cached_models ) {
		return $cached_models;
	}

	// Fetch models from Ollama API.
	$response = wp_remote_get( 'http://localhost:11434/api/tags' );

	if ( is_wp_error( $response ) ) {
		return new WP_Error(
			'ollama_connection_error',
			__( 'Cannot connect to Ollama. Please ensure it is running.', 'wp-ai-client-demo' )
		);
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $response_code ) {
		return new WP_Error(
			'ollama_api_error',
			sprintf(
				/* translators: %d: HTTP response code */
				__( 'Ollama API returned error code: %d', 'wp-ai-client-demo' ),
				$response_code
			)
		);
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( ! isset( $data['models'] ) || ! is_array( $data['models'] ) ) {
		return new WP_Error(
			'ollama_invalid_response',
			__( 'Invalid response from Ollama API.', 'wp-ai-client-demo' )
		);
	}

	// Format models for display.
	$models = array();
	foreach ( $data['models'] as $model ) {
		if ( isset( $model['name'] ) ) {
			$models[] = array(
				'id'   => $model['name'],
				'name' => $model['name'],
			);
		}
	}

	// Cache the results.
	set_transient( 'wp_ai_client_demo_ollama_models', $models, 5 * MINUTE_IN_SECONDS );

	return $models;
}

/**
 * Render the Local AI Model settings page.
 *
 * @return void
 */
function wp_ai_client_demo_local_ai_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Handle refresh action.
	if ( isset( $_GET['action'] ) && 'refresh' === $_GET['action'] && check_admin_referer( 'wp_ai_client_demo_refresh_models' ) ) {
		delete_transient( 'wp_ai_client_demo_ollama_models' );
		add_settings_error(
			'wp_ai_client_demo_messages',
			'wp_ai_client_demo_message',
			__( 'Model list refreshed successfully.', 'wp-ai-client-demo' ),
			'success'
		);
	}

	// Check if settings were updated.
	if ( isset( $_GET['settings-updated'] ) ) {
		add_settings_error(
			'wp_ai_client_demo_messages',
			'wp_ai_client_demo_message',
			__( 'Settings saved successfully.', 'wp-ai-client-demo' ),
			'success'
		);
	}

	settings_errors( 'wp_ai_client_demo_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'wp_ai_client_demo_local_ai' );
			do_settings_sections( 'wp-ai-client-demo-local-ai' );
			submit_button( __( 'Save Settings', 'wp-ai-client-demo' ) );
			?>
		</form>

		<div class="card">
			<h2><?php esc_html_e( 'About Ollama Models', 'wp-ai-client-demo' ); ?></h2>
			<p><?php esc_html_e( 'Ollama allows you to run large language models locally on your computer without requiring API keys or internet connectivity.', 'wp-ai-client-demo' ); ?></p>

			<h3><?php esc_html_e( 'How to get started:', 'wp-ai-client-demo' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Install Ollama from https://ollama.com', 'wp-ai-client-demo' ); ?></li>
				<li><?php esc_html_e( 'Pull a model: ollama pull llama3.2', 'wp-ai-client-demo' ); ?></li>
				<li><?php esc_html_e( 'Ensure Ollama is running (it starts automatically on most systems)', 'wp-ai-client-demo' ); ?></li>
				<li><?php esc_html_e( 'Select your preferred model above', 'wp-ai-client-demo' ); ?></li>
			</ol>

			<h3><?php esc_html_e( 'Recommended Models:', 'wp-ai-client-demo' ); ?></h3>
			<ul>
				<li><strong>llama3.2</strong> - <?php esc_html_e( 'Best balance of speed and quality', 'wp-ai-client-demo' ); ?></li>
				<li><strong>mistral</strong> - <?php esc_html_e( 'Great for creative content', 'wp-ai-client-demo' ); ?></li>
				<li><strong>codellama</strong> - <?php esc_html_e( 'Optimized for technical content', 'wp-ai-client-demo' ); ?></li>
				<li><strong>phi</strong> - <?php esc_html_e( 'Fastest, good for testing', 'wp-ai-client-demo' ); ?></li>
			</ul>

			<p>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'options-general.php?page=wp-ai-client-demo-local-ai&action=refresh' ), 'wp_ai_client_demo_refresh_models' ) ); ?>" class="button">
					<?php esc_html_e( 'Refresh Model List', 'wp-ai-client-demo' ); ?>
				</a>
			</p>
		</div>
	</div>
	<?php
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

	$asset_file = include plugin_dir_path( __FILE__ ) . 'build/index.asset.php';

	wp_enqueue_script(
		'wp-ai-client-demo-scripts',
		plugins_url( 'build/index.js', __FILE__ ),
		$asset_file['dependencies'],
		$asset_file['version'],
		true
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

	try {
		// Check if a specific Ollama model is selected.
		$selected_ollama_model = get_option( 'wp_ai_client_demo_ollama_model', '' );

		if ( ! empty( $selected_ollama_model ) && class_exists( 'WpAiClientDemo\ProviderImplementations\Ollama\OllamaProvider' ) ) {
			// Use the explicitly selected Ollama model.
			$model    = \WpAiClientDemo\ProviderImplementations\Ollama\OllamaProvider::model( $selected_ollama_model );
			$registry = \WordPress\AiClient\AiClient::defaultRegistry();
			$registry->bindModelDependencies( $model );

			return \WordPress\AI_Client\AI_Client::prompt( $prompt )
				->using_model( $model )
				->generate_text();
		}

		// Fall back to automatic provider/model selection.
		return \WordPress\AI_Client\AI_Client::prompt( $prompt )->generate_text();
	} catch ( Exception $e ) {
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
	try {
		return \WordPress\AI_Client\AI_Client::prompt( $image_prompt )->generate_image();
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
