# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

WordPress admin plugin demonstrating AI integration via the `wordpress/wp-ai-client` Composer package. Registers WordPress Abilities (server-side callable actions) and provides a React-based admin UI under Tools > WP AI SDK Demo.

## Build Commands

- `npm start` — Start dev build with watch mode (uses @wordpress/scripts)
- `npm run build` — Production build
- `composer install` — Install PHP dependencies (wp-ai-client library)

No test suite or linter scripts are configured. PHP coding standards package (`wpcs`) is installed as a dev dependency but has no configured command.

## Architecture

**PHP backend** (`includes/`) registers three WordPress Abilities:
- `wp-ai-client-demo/generate-post` — generates AI text + image, creates a draft post
- `wp-ai-client-demo/generate-writing-style` — analyzes selected posts to extract writing style, saves to `wp_ai_client_demo_writing_style` option
- `wp-ai-client-demo/get-writing-style` — retrieves saved writing style

**Data flow:** React UI → `executeAbility()` → PHP ability callback → AI Client → WordPress post/attachment creation → response to UI.

**PHP file responsibilities:**
- `ai-client.php` — initializes the AI_Client class, sets 120s request timeout
- `abilities.php` — registers ability categories and abilities with schemas
- `content.php` — AI text generation, writing style analysis/storage
- `post.php` — orchestrates post creation (content + image + wp_insert_post)
- `image.php` — AI image generation, base64→media attachment conversion
- `admin.php` — admin menu registration, script/style enqueueing

**React frontend** (`src/`):
- `index.js` — entry point, mounts `<SettingsPage />` into `#wp-ai-client-demo-app`
- `components/settings-page.jsx` — TabPanel UI with "Generate Post" and "Writing Style" tabs; uses `@wordpress/abilities` module dynamically imported, `@wordpress/components`, and `@wordpress/data` for REST queries

## Key Details

- Main branch is `trunk` (not `main`)
- Build output goes to `build/` — compiled by @wordpress/scripts, generates `index.js`, `index.asset.php` (dependency manifest), and CSS files
- The plugin autoloads the vendor directory in the main plugin file before including any other files
- Abilities use WordPress's `wp_abilities_api_init` and `wp_abilities_api_categories_init` hooks
- Admin scripts are enqueued as WordPress script modules (not classic wp_enqueue_script)
- Requires WordPress 7.0+, PHP 8.0+
