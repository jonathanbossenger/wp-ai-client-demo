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
   git clone <repository-url> wp-ai-client-demo
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

## License

GPL-2.0-or-later

## Author

Jonathan Bossenger

## Support

For issues and questions:
- GitHub Issues: https://github.com/jonathanbossenger/wp-ai-sdk-demo/issues
- Documentation: See the WP AI Client documentation for more details on the underlying library

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
