# WP AI Client Demo

A demonstration plugin showcasing the integration and capabilities of the WordPress AI Client library. This plugin demonstrates how to build AI-powered features in WordPress using both PHP and JavaScript.

## Description

WP AI Client Demo provides a practical example of integrating AI capabilities into WordPress. It demonstrates:

- Text generation using AI
- Image generation using AI
- Creating WordPress posts with AI-generated content and featured images
- Custom WordPress Abilities API integration
- Admin interface for interacting with AI features

## Requirements

- WordPress 6.0 or higher
- PHP 8.0 or higher
- Composer
- Node.js and npm (for building JavaScript assets)
- API credentials for an AI provider (configured through the WP AI Client)

## Installation

1. Clone or download this plugin to your WordPress plugins directory:
   ```bash
   cd wp-content/plugins
   git clone git@github.com:jonathanbossenger/wp-ai-client-demo.git
   ```

2. Install PHP dependencies:
   ```bash
   cd wp-ai-client-demo
   composer install
   ```

3. Install JavaScript dependencies and build assets:
   ```bash
   npm install
   npm run build
   ```

4. Activate the plugin through the WordPress admin interface or via WP-CLI:
   ```bash
   wp plugin activate wp-ai-client-demo
   ```

5. Configure your AI provider credentials as required by the WP AI Client library

## Usage

### Admin Interface

Access the demo tools page from the WordPress admin:

1. Navigate to **Tools > WP AI SDK Demo** in your WordPress admin
2. Use the interface to test AI capabilities

## Local AI Models

To use local AI models (Ollama) with this plugin, install the companion plugin:

**[WP Local Model Provider](https://github.com/jonathanbossenger/wp-local-model-provider)**

This plugin provides Ollama provider support for running AI models locally without cloud API keys.

### Migration Note

If you were previously using Ollama with this plugin, you'll need to:

1. Install and activate **WP Local Model Provider**
2. Configure your Ollama model in **Settings > Local AI Models**
3. The demo plugin will automatically detect and use the selected Ollama model

Your old Ollama configuration (`wp_ai_client_demo_ollama_model` option) will no longer be used. You can optionally clean it up:

```bash
wp option delete wp_ai_client_demo_ollama_model
```

### How It Works

When generating content, the plugin:

1. Checks if `wp-local-model-provider` is active and has a selected Ollama model
2. If yes, uses that model for text generation
3. If no, falls back to automatic provider selection (cloud providers)

This allows you to:
- Use Ollama for text generation (local, no API keys)
- Use cloud providers for image generation (Ollama doesn't support images)
- Switch between providers easily via the settings page

## License

GPL-2.0-or-later

## Author

Jonathan Bossenger
