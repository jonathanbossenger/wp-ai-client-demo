# Local AI Model Settings

This document describes the Local AI Model settings page feature added to the WP AI Client Demo plugin.

## Overview

The Local AI Model settings page allows users to easily select which Ollama model to use for AI-powered content generation without needing to modify code or understand complex provider configurations.

## Features

### 1. Settings Page Location
- **Path**: Settings > Local AI Models
- **Permission**: Requires `manage_options` capability (Administrators only)

### 2. Automatic Model Detection
- Fetches available models from your local Ollama installation (`http://localhost:11434/api/tags`)
- Displays models in a user-friendly dropdown select box
- Shows helpful error messages if Ollama is not running or no models are found

### 3. Model Selection
- Select any available Ollama model from the dropdown
- Settings are saved to the WordPress options table (`wp_ai_client_demo_ollama_model`)
- Selected model is automatically used for all text generation requests

### 4. Caching
- Model list is cached for 5 minutes to improve performance
- "Refresh Model List" button clears the cache and fetches fresh data
- Cache automatically expires after 5 minutes

### 5. User Guidance
The settings page includes:
- Instructions on how to install and set up Ollama
- Model recommendations (llama3.2, mistral, codellama, phi)
- Helpful information about model characteristics
- Clear error messages with troubleshooting steps

## How It Works

### User Flow
1. User installs Ollama and pulls at least one model (e.g., `ollama pull llama3.2`)
2. User navigates to **Settings > Local AI Models** in WordPress admin
3. Plugin automatically detects all available Ollama models
4. User selects their preferred model from dropdown
5. User clicks "Save Settings"
6. Selected model is now used for all AI content generation

### Technical Implementation

#### Model Fetching
```php
function wp_ai_client_demo_get_ollama_models()
```
- Fetches models from Ollama API endpoint: `http://localhost:11434/api/tags`
- Returns array of models with `id` and `name` fields
- Implements transient caching (5 minutes)
- Returns `WP_Error` on failure with helpful messages

#### Content Generation
```php
function wp_ai_client_generate_content( $prompt )
```
- Checks if an Ollama model is selected: `get_option( 'wp_ai_client_demo_ollama_model' )`
- If selected:
  - Uses explicit model instantiation: `OllamaProvider::model( $model_id )`
  - Binds model dependencies via registry
  - Generates text using the specified model
- If not selected:
  - Falls back to automatic provider/model selection
  - Works with any configured provider (OpenAI, Anthropic, etc.)

#### Settings Registration
- Settings group: `wp_ai_client_demo_local_ai`
- Option name: `wp_ai_client_demo_ollama_model`
- Type: string (stores model ID, e.g., "llama3.2:3b")
- Sanitization: `sanitize_text_field()`

## Benefits

### For Users
1. **No Code Changes Required** - Select models through a simple UI
2. **Easy Model Switching** - Change models anytime without technical knowledge
3. **Clear Feedback** - Helpful error messages guide troubleshooting
4. **Privacy-First** - Use local models without sending data to cloud providers

### For Developers
1. **Automatic Integration** - Model selection is transparent to other code
2. **Graceful Fallback** - Still works with cloud providers if Ollama isn't configured
3. **Standard WordPress Patterns** - Uses options API, Settings API, admin pages
4. **Cached Performance** - Avoids repeated API calls during browsing

## Troubleshooting

### "Cannot connect to Ollama"
**Cause**: Ollama server is not running
**Solution**:
```bash
# Start Ollama (it usually runs automatically)
ollama serve
```

### "No models found"
**Cause**: No models have been pulled to Ollama
**Solution**:
```bash
# Pull a recommended model
ollama pull llama3.2
```

### Model not appearing in list
**Cause**: Model list cache needs refreshing
**Solution**: Click the "Refresh Model List" button on the settings page

### Selected model not working
**Cause**: Model may have been removed from Ollama
**Solution**:
1. Check available models: `ollama list`
2. Pull the model again if needed: `ollama pull <model-name>`
3. Refresh the model list in WordPress

## Code References

### Main Implementation Files
- **Settings Page**: `wp-ai-sdk-demo.php` lines 93-322
- **Content Generation**: `wp-ai-sdk-demo.php` lines 446-474
- **Ollama Provider**: `includes/ProviderImplementations/Ollama/`

### Key Functions
- `wp_ai_client_demo_register_local_ai_settings()` - Registers settings page
- `wp_ai_client_demo_register_local_ai_model_settings()` - Registers settings and fields
- `wp_ai_client_demo_get_ollama_models()` - Fetches models from Ollama API
- `wp_ai_client_demo_local_ai_settings_page()` - Renders settings page
- `wp_ai_client_generate_content()` - Uses selected model for generation

### WordPress Options
- `wp_ai_client_demo_ollama_model` - Stores selected model ID

### Transients
- `wp_ai_client_demo_ollama_models` - Caches model list (5 minutes)

## Future Enhancements

Potential improvements for future versions:

1. **Model Performance Metrics** - Show speed/quality ratings for each model
2. **Model Size Display** - Show disk space used by each model
3. **Batch Operations** - Pull/remove multiple models from WordPress
4. **Model Testing** - Quick test interface to try models before setting as default
5. **Per-Ability Model Selection** - Different models for different content types
6. **Model Health Check** - Verify selected model is still available before generation
7. **Multiple Provider Support** - Extend to support other local providers (LM Studio, etc.)

## Related Documentation

- `OLLAMA-INTEGRATION.md` - Complete Ollama provider setup and integration guide
- `OLLAMA-MODELS.md` - Model recommendations and characteristics
- `CLAUDE.md` - Full plugin architecture and development guide
