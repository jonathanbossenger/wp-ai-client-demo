# Ollama Provider Integration Guide

This guide explains how to integrate the Ollama provider into the WP AI Client Demo plugin.

## Overview

The Ollama provider enables the plugin to use local Ollama models for AI generation tasks. Ollama runs as a local server (typically at `http://localhost:11434`) and requires no authentication, making it ideal for development and self-hosted scenarios.

## Prerequisites

1. **Ollama Server Running**: Ensure Ollama is installed and running locally
   ```bash
   # Check if Ollama is running
   curl http://localhost:11434/api/tags
   ```

2. **PHP AI Client with Ollama Provider**: The underlying `wordpress/php-ai-client` library must include the Ollama provider implementation in the `src/ProviderImplementations/Ollama/` directory

## Implementation Steps

### 1. Register the Ollama Provider

Add Ollama provider registration to the plugin's initialization function in `wp-ai-sdk-demo.php`:

```php
/**
 * Initialize any plugin functionality
 *
 * @return void
 */
function wp_ai_client_demo_init() {
	if ( class_exists( 'WordPress\AI_Client\AI_Client' ) ) {
		\WordPress\AI_Client\AI_Client::init();

		// Register the Ollama provider
		wp_ai_client_demo_register_ollama_provider();
	}
}

/**
 * Register the Ollama provider with the AI Client.
 *
 * @return void
 */
function wp_ai_client_demo_register_ollama_provider() {
	// Check if the Ollama provider class exists
	if ( ! class_exists( 'WpAiClientDemo\ProviderImplementations\Ollama\OllamaProvider' ) ) {
		return;
	}

	try {
		// Get the provider registry from the PHP AI Client
		$registry = \WordPress\AiClient\AiClient::defaultRegistry();

		// Register the Ollama provider
		$registry->registerProvider( \WpAiClientDemo\ProviderImplementations\Ollama\OllamaProvider::class );

		// Set no-auth authentication for Ollama (local server doesn't need API keys)
		$no_auth = new \WpAiClientDemo\ProviderImplementations\Ollama\NoAuthRequestAuthentication();
		$registry->setProviderRequestAuthentication( 'ollama', $no_auth );
	} catch ( Exception $e ) {
		// Log error if registration fails
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Failed to register Ollama provider: ' . $e->getMessage() );
		}
	}
}
```

### 2. Configure Ollama Model Selection

The plugin provides a dedicated settings page for selecting your Ollama model:

1. Navigate to **Settings > Local AI Models** in WordPress admin
2. The page will automatically detect all available Ollama models on your system
3. Select your preferred model from the dropdown
4. Click **Save Settings**

**Note**: Unlike cloud providers (OpenAI, Anthropic), Ollama requires no API key since it runs locally without authentication.

**Available Features**:
- Automatic model detection from your local Ollama installation
- Model list caching (refreshed every 5 minutes)
- Manual refresh option to update the model list
- Helpful information about recommended models

### 3. Test Ollama Integration

After registration, test the integration:

1. Ensure Ollama server is running with at least one model pulled:
   ```bash
   ollama pull llama3.2
   ```

2. Navigate to **Settings > Local AI Models** in WordPress admin
3. Select your preferred Ollama model from the dropdown
4. Save your settings
5. Navigate to **Tools > WP AI SDK Demo** in WordPress admin
6. Try generating a post using the demo interface - it will automatically use your selected Ollama model for text generation

### 4. Handle Ollama-Specific Errors

Add error handling for common Ollama issues:

```php
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
		return \WordPress\AI_Client\AI_Client::prompt( $prompt )->generate_text();
	} catch ( Exception $e ) {
		$error_message = $e->getMessage();

		// Provide helpful error messages for Ollama-specific issues
		if ( strpos( $error_message, 'Connection refused' ) !== false ) {
			$error_message = 'Cannot connect to Ollama. Please ensure Ollama is running at http://localhost:11434';
		} elseif ( strpos( $error_message, 'model not found' ) !== false ) {
			$error_message = 'Model not found in Ollama. Please pull the model first using: ollama pull <model-name>';
		}

		return new WP_Error( 'content_creation_error', 'Error generating content', $error_message );
	}
}
```

## Provider Architecture

### Ollama Provider Components

The Ollama provider consists of four main classes:

1. **OllamaProvider** (`OllamaProvider.php`)
   - Extends `AbstractApiProvider`
   - Base URL: `http://localhost:11434`
   - Authentication: None (local server)
   - Provider type: `SERVER` (not cloud-based)

2. **OllamaModelMetadataDirectory** (`OllamaModelMetadataDirectory.php`)
   - Discovers available models via `GET /api/tags`
   - Returns model metadata with text generation capability

3. **OllamaTextGenerationModel** (`OllamaTextGenerationModel.php`)
   - Extends `AbstractOpenAiCompatibleTextGenerationModel`
   - Uses `POST /api/generate` endpoint
   - Formats prompts for Ollama's API format

4. **OllamaNoAuthentication** (`OllamaNoAuthentication.php`)
   - Implements `RequestAuthenticationInterface`
   - No-op authentication (returns request unchanged)

### API Endpoints

- **Model Discovery**: `GET http://localhost:11434/api/tags`
  - Returns list of available models

- **Text Generation**: `POST http://localhost:11434/api/generate`
  - Request format:
    ```json
    {
      "model": "llama2",
      "prompt": "Your prompt here",
      "stream": false,
      "options": {
        "temperature": 0.7,
        "num_predict": 1000
      }
    }
    ```

### Image Generation Limitation

**Important**: The Ollama provider only supports text generation. If your plugin attempts to generate images using Ollama, it will fail.

To handle this:

```php
/**
 * Generate an image using the AI Client based on the provided title.
 *
 * @param string $title The post title to guide image generation.
 *
 * @return mixed
 */
function wp_ai_client_demo_create_image( $title ) {
	// Check if current provider supports image generation
	$provider_metadata = \WordPress\AI_Client\AI_Client::get_current_provider_metadata();

	if ( $provider_metadata->getId() === 'ollama' ) {
		return new WP_Error(
			'unsupported_capability',
			'Image generation is not supported by Ollama. Please use OpenAI or another provider for image generation.'
		);
	}

	$image_prompt = 'Create a relevant featured image for a blog post with the following title: ' . $title . '.';
	try {
		return \WordPress\AI_Client\AI_Client::prompt( $image_prompt )->generate_image();
	} catch ( Exception $e ) {
		return new WP_Error( 'image_creation_error', 'Error message', $e->getMessage() );
	}
}
```

## Configuration Options

### Custom Ollama URL

If Ollama runs on a different host or port, users can configure it through the AI Client settings or via a filter:

```php
add_filter( 'wp_ai_client_ollama_base_url', 'wp_ai_client_demo_custom_ollama_url' );
/**
 * Set a custom Ollama base URL.
 *
 * @return string
 */
function wp_ai_client_demo_custom_ollama_url() {
	return 'http://192.168.1.100:11434'; // Remote Ollama server
}
```

### Request Timeout

Ollama can be slower than cloud providers, especially with larger models. Adjust the timeout:

```php
add_filter( 'wp_ai_client_default_request_timeout', 'wp_ai_client_demo_set_request_timeout' );
/**
 * Set a custom request timeout for the AI Client.
 *
 * @return int
 */
function wp_ai_client_demo_set_request_timeout() {
	return 120; // 2 minutes for slower local models
}
```

## Troubleshooting

### Common Issues

1. **Connection Refused**
   - **Cause**: Ollama server not running
   - **Solution**: Start Ollama: `ollama serve`

2. **Model Not Found**
   - **Cause**: Requested model not pulled
   - **Solution**: Pull model: `ollama pull llama2`

3. **Slow Response Times**
   - **Cause**: Large models on slower hardware
   - **Solution**: Use smaller models (e.g., `mistral:7b` instead of `llama2:70b`) or increase timeout

4. **Provider Not Available**
   - **Cause**: Ollama provider classes not included in PHP AI Client
   - **Solution**: Ensure `wordpress/php-ai-client` version includes Ollama implementation

### Debugging

Enable WordPress debug logging to see Ollama-related errors:

```php
// In wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Check logs at `wp-content/debug.log` for Ollama registration and request errors.

## Testing Ollama Integration

### Manual Testing

1. Start Ollama with a model:
   ```bash
   ollama pull llama2
   ollama serve
   ```

2. Test API directly:
   ```bash
   curl -X POST http://localhost:11434/api/generate -d '{
     "model": "llama2",
     "prompt": "Write a short blog post about WordPress.",
     "stream": false
   }'
   ```

3. Test through WordPress plugin:
   - Go to Tools > WP AI SDK Demo
   - Select Ollama provider
   - Generate a test post

### Automated Testing

Create a test callback to verify Ollama availability:

```php
/**
 * Check if Ollama is available and responding.
 *
 * @return bool|WP_Error
 */
function wp_ai_client_demo_check_ollama_availability() {
	$response = wp_remote_get( 'http://localhost:11434/api/tags' );

	if ( is_wp_error( $response ) ) {
		return new WP_Error(
			'ollama_unavailable',
			'Ollama server is not responding. Please ensure it is running.'
		);
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( ! isset( $data['models'] ) || empty( $data['models'] ) ) {
		return new WP_Error(
			'no_models',
			'No models found in Ollama. Please pull at least one model.'
		);
	}

	return true;
}
```

## Best Practices

1. **Graceful Degradation**: Always check if Ollama is available before using it
2. **User Feedback**: Provide clear error messages when Ollama is not configured properly
3. **Model Selection**: Let users choose which Ollama model to use (different models have different capabilities)
4. **Timeouts**: Set appropriate timeouts for local models (they can be slower than cloud APIs)
5. **Capability Checking**: Verify the current provider supports the required capability before attempting operations

## Security Considerations

1. **Local Only**: By default, Ollama should only be accessible from localhost
2. **No Authentication**: Ollama has no built-in authentication, so don't expose it publicly
3. **Network Configuration**: If accessing Ollama remotely, use proper network security (VPN, SSH tunnels, etc.)

## References

- Ollama Documentation: https://github.com/ollama/ollama
- PHP AI Client Architecture: See `ollama-provider.md` for provider implementation details
- WordPress AI Client: `wordpress/wp-ai-client` package documentation
