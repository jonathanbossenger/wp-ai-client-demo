# Quick Start Guide - Local AI Models

Get up and running with Ollama in 5 minutes!

## Prerequisites

1. **Install Ollama**
   - Visit: https://ollama.com
   - Download and install for your operating system
   - Ollama starts automatically after installation

2. **Pull a Model**
   ```bash
   ollama pull llama3.2
   ```

   This downloads the llama3.2 model (~2GB). Wait for it to complete.

3. **Verify Ollama is Running**
   ```bash
   curl http://localhost:11434/api/tags
   ```

   Should return JSON with your installed models.

## Setup in WordPress

### Step 1: Configure Your Model
1. Login to WordPress admin
2. Navigate to **Settings > Local AI Models**
3. Select your preferred model from the dropdown (e.g., "llama3.2")
4. Click **Save Settings**

### Step 2: Test It Out
1. Navigate to **Tools > WP AI SDK Demo**
2. Enter a title (e.g., "My First AI Post")
3. Enter a prompt (e.g., "Write about the benefits of using WordPress")
4. Click **Generate Post**
5. Your post will be generated using your selected Ollama model!

## What Just Happened?

- Your WordPress plugin is now using **local AI** running on your computer
- No data is sent to cloud providers for text generation
- No API keys required
- Content is generated completely offline (if Ollama is local)

## Try Different Models

Want to experiment? Pull and try different models:

```bash
# Fast and lightweight
ollama pull phi

# Great for creative writing
ollama pull mistral

# Best for code/technical content
ollama pull codellama

# Balanced quality and speed
ollama pull llama3.2
```

Then return to **Settings > Local AI Models**, refresh the list, and select your new model!

## Important Notes

### Text Generation ✅
- **Works**: Blog posts, articles, descriptions, content
- **Uses**: Your selected Ollama model
- **Privacy**: Everything runs locally

### Image Generation ⚠️
- **Does NOT work**: Ollama doesn't support image generation
- **Fallback**: Plugin will use cloud providers (OpenAI) if configured
- **Requirement**: You need OpenAI API credentials for images

## Troubleshooting

**Can't see any models?**
```bash
# Check Ollama status
ollama list

# If empty, pull a model
ollama pull llama3.2
```

**"Cannot connect to Ollama" error?**
```bash
# Start Ollama manually
ollama serve
```

**Model not generating good content?**
- Try a different model (mistral is great for creative content)
- Some models are better at specific tasks
- See `OLLAMA-MODELS.md` for recommendations

## Next Steps

- Read `LOCAL-AI-SETTINGS.md` for detailed feature documentation
- Read `OLLAMA-MODELS.md` for model comparisons and recommendations
- Read `OLLAMA-INTEGRATION.md` for technical integration details

## Need Help?

1. Check that Ollama is running: `ollama list`
2. Verify WordPress can reach Ollama: Settings > Local AI Models (should show models)
3. Check WordPress debug log: `wp-content/debug.log` (enable WP_DEBUG first)
4. See `OLLAMA-INTEGRATION.md` troubleshooting section

---

**You're all set!** Start creating AI-powered content with local models.
