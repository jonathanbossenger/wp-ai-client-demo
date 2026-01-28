# AI Agent Instructions: Generate Ollama Provider Implementation

You are tasked with creating a complete Ollama provider implementation for the WordPress PHP AI Client. Follow these specifications exactly.

## Task Overview

Create a new provider that enables the PHP AI Client to work with local Ollama models. The implementation must follow the existing provider architecture patterns.

## Implementation Requirements

### 1. Create Provider Class

**File**: `src/ProviderImplementations/Ollama/OllamaProvider.php`

Generate a class extending `AbstractApiProvider` with these specifications:

```php
<?php
namespace WordPress\AiClient\ProviderImplementations\Ollama;

use WordPress\AiClient\Common\Exception\RuntimeException;
use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiProvider;
use WordPress\AiClient\Providers\ApiBasedImplementation\ListModelsApiBasedProviderAvailability;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Contracts\ProviderAvailabilityInterface;
use WordPress\AiClient\Providers\DTO\ProviderMetadata;
use WordPress\AiClient\Providers\Enums\ProviderTypeEnum;
use WordPress\AiClient\Providers\Http\Enums\RequestAuthenticationMethod;
use WordPress\AiClient\Providers\Models\Contracts\ModelInterface;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;

class OllamaProvider extends AbstractApiProvider
{
    protected static function baseUrl(): string
    {
        return 'http://localhost:11434';
    }

    protected static function createModel(
        ModelMetadata $modelMetadata,
        ProviderMetadata $providerMetadata
    ): ModelInterface {
        $capabilities = $modelMetadata->getSupportedCapabilities();
        foreach ($capabilities as $capability) {
            if ($capability->isTextGeneration()) {
                return new OllamaTextGenerationModel($modelMetadata, $providerMetadata);
            }
        }

        throw new RuntimeException(
            'Unsupported model capabilities: ' . implode(', ', $capabilities)
        );
    }

    protected static function createProviderMetadata(): ProviderMetadata
    {
        return new ProviderMetadata(
            'ollama',
            'Ollama',
            ProviderTypeEnum::server(),
            null,
            RequestAuthenticationMethod::none()
        );
    }

    protected static function createProviderAvailability(): ProviderAvailabilityInterface
    {
        return new ListModelsApiBasedProviderAvailability(
            static::modelMetadataDirectory()
        );
    }

    protected static function createModelMetadataDirectory(): ModelMetadataDirectoryInterface
    {
        return new OllamaModelMetadataDirectory();
    }
}
```

Follow the exact pattern from existing providers like `OpenAiProvider` [1](#2-0)  and `AnthropicProvider` [2](#2-1) .

### 2. Create Model Metadata Directory

**File**: `src/ProviderImplementations/Ollama/OllamaModelMetadataDirectory.php`

Generate a class extending `AbstractApiBasedModelMetadataDirectory`:

```php
<?php
namespace WordPress\AiClient\ProviderImplementations\Ollama;

use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiBasedModelMetadataDirectory;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\DTO\Response;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\Http\Exception\ResponseException;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;

class OllamaModelMetadataDirectory extends AbstractApiBasedModelMetadataDirectory
{
    protected function createRequest(HttpMethodEnum $method, string $path, array $headers = [], $data = null): Request
    {
        return new Request(
            $method,
            OllamaProvider::url($path),
            $headers,
            $data
        );
    }

    protected function parseResponseToModelMetadataList(Response $response): array
    {
        $responseData = $response->getData();
        if (!isset($responseData['models']) || !$responseData['models']) {
            throw ResponseException::fromMissingData('Ollama', 'models');
        }

        $modelsMetadata = [];
        foreach ($responseData['models'] as $model) {
            $modelsMetadata[] = new ModelMetadata(
                $model['model'],
                $model['name'],
                [CapabilityEnum::textGeneration()],
                []
            );
        }

        return $modelsMetadata;
    }

    protected function getModelsApiPath(): string
    {
        return '/api/tags';
    }
}
```

### 3. Create Text Generation Model

**File**: `src/ProviderImplementations/Ollama/OllamaTextGenerationModel.php`

Generate a class extending `AbstractOpenAiCompatibleTextGenerationModel`:

```php
<?php
namespace WordPress\AiClient\ProviderImplementations\Ollama;

use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\DTO\ProviderMetadata;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleTextGenerationModel;

class OllamaTextGenerationModel extends AbstractOpenAiCompatibleTextGenerationModel
{
    protected function getApiPath(): string
    {
        return '/api/generate';
    }

    protected function prepareRequestData(array $messages, array $options): array
    {
        return [
            'model' => $this->modelMetadata->getId(),
            'prompt' => $this->formatMessagesForOllama($messages),
            'stream' => false,
            'options' => $this->mapOptionsToOllamaFormat($options)
        ];
    }

    private function formatMessagesForOllama(array $messages): string
    {
        $prompt = '';
        foreach ($messages as $message) {
            $role = $message['role'] ?? 'user';
            $content = $message['content'] ?? '';
            $prompt .= "{$role}: {$content}\n";
        }
        return $prompt;
    }

    private function mapOptionsToOllamaFormat(array $options): array
    {
        $ollamaOptions = [];
        if (isset($options['temperature'])) {
            $ollamaOptions['temperature'] = $options['temperature'];
        }
        if (isset($options['max_tokens'])) {
            $ollamaOptions['num_predict'] = $options['max_tokens'];
        }
        return $ollamaOptions;
    }
}
```

Use the `AbstractOpenAiCompatibleTextGenerationModel` [3](#2-2)  as the base since Ollama provides OpenAI-compatible endpoints.

### 4. Create Authentication Handler

**File**: `src/ProviderImplementations/Ollama/OllamaNoAuthentication.php`

Generate a no-op authentication class:

```php
<?php
namespace WordPress\AiClient\ProviderImplementations\Ollama;

use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\Contracts\RequestAuthenticationInterface;

class OllamaNoAuthentication implements RequestAuthenticationInterface
{
    public function authenticateRequest(Request $request): Request
    {
        return $request; // No authentication needed for local Ollama
    }
}
```

## Integration Instructions

### Registration Code

Generate the provider registration code:

```php
use WordPress\AiClient\Providers\ProviderRegistry;
use WordPress\AiClient\Providers\Http\HttpTransporter;

$registry = new ProviderRegistry();
$registry->registerProvider(\WordPress\AiClient\ProviderImplementations\Ollama\OllamaProvider::class);

// Set HTTP transporter
$registry->setHttpTransporter(new HttpTransporter($psrClient, $psrFactory, $psrFactory));

// Usage example
if ($registry->isProviderConfigured('ollama')) {
    $model = $registry->getProviderModel('ollama', 'llama2');
    $result = $model->generateTextResult($messages);
}
```

Follow the registration pattern from `ProviderRegistry::registerProvider()` [4](#2-3) .

## Technical Specifications

### Provider Type
Use `ProviderTypeEnum::server()` since Ollama runs as a local server [5](#2-4) .

### API Endpoints
- Model discovery: `GET /api/tags`
- Text generation: `POST /api/generate`
- Base URL: `http://localhost:11434`

### Authentication
Use `RequestAuthenticationMethod::none()` as local Ollama requires no authentication.

### Model Capabilities
Assume text generation capability for all discovered models unless otherwise specified.

## Validation Requirements

Ensure the implementation:
1. Implements `ProviderInterface` [6](#2-5)
2. Follows the abstract provider pattern from `AbstractProvider` [7](#2-6)
3. Uses the HTTP transport layer properly via `AbstractApiBasedModel` [8](#2-7)
4. Supports the architecture requirement for server-side AI [9](#2-8)

## Notes

- The implementation must handle cases where Ollama server is not running
- Error handling should follow the project's custom exception patterns
- All new code requires corresponding unit tests
- Follow the coding standards specified in `CONTRIBUTING.md` [10](#2-9)

Wiki pages you might want to explore:
- [Overview (WordPress/php-ai-client)](/wiki/WordPress/php-ai-client#1)
- [Core Architecture (WordPress/php-ai-client)](/wiki/WordPress/php-ai-client#3)

### Citations

**File:** src/ProviderImplementations/OpenAi/OpenAiProvider.php (L23-103)
```php
class OpenAiProvider extends AbstractApiProvider
{
    /**
     * {@inheritDoc}
     *
     * @since 0.2.0
     */
    protected static function baseUrl(): string
    {
        return 'https://api.openai.com/v1';
    }

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    protected static function createModel(
        ModelMetadata $modelMetadata,
        ProviderMetadata $providerMetadata
    ): ModelInterface {
        $capabilities = $modelMetadata->getSupportedCapabilities();
        foreach ($capabilities as $capability) {
            if ($capability->isTextGeneration()) {
                return new OpenAiTextGenerationModel($modelMetadata, $providerMetadata);
            }
            if ($capability->isImageGeneration()) {
                return new OpenAiImageGenerationModel($modelMetadata, $providerMetadata);
            }
            if ($capability->isTextToSpeechConversion()) {
                // TODO: Implement OpenAiTextToSpeechConversionModel.
                throw new RuntimeException(
                    'OpenAI text to speech conversion model class is not yet implemented.'
                );
            }
        }

        throw new RuntimeException(
            'Unsupported model capabilities: ' . implode(', ', $capabilities)
        );
    }

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    protected static function createProviderMetadata(): ProviderMetadata
    {
        return new ProviderMetadata(
            'openai',
            'OpenAI',
            ProviderTypeEnum::cloud(),
            'https://platform.openai.com/api-keys',
            RequestAuthenticationMethod::apiKey()
        );
    }

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    protected static function createProviderAvailability(): ProviderAvailabilityInterface
    {
        // Check valid API access by attempting to list models.
        return new ListModelsApiBasedProviderAvailability(
            static::modelMetadataDirectory()
        );
    }

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    protected static function createModelMetadataDirectory(): ModelMetadataDirectoryInterface
    {
        return new OpenAiModelMetadataDirectory();
    }
}
```

**File:** src/ProviderImplementations/Anthropic/AnthropicProvider.php (L23-90)
```php
class AnthropicProvider extends AbstractApiProvider
{
    /**
     * {@inheritDoc}
     *
     * @since 0.2.0
     */
    protected static function baseUrl(): string
    {
        return 'https://api.anthropic.com/v1';
    }

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    protected static function createModel(
        ModelMetadata $modelMetadata,
        ProviderMetadata $providerMetadata
    ): ModelInterface {
        $capabilities = $modelMetadata->getSupportedCapabilities();
        foreach ($capabilities as $capability) {
            if ($capability->isTextGeneration()) {
                return new AnthropicTextGenerationModel($modelMetadata, $providerMetadata);
            }
        }

        throw new RuntimeException(
            'Unsupported model capabilities: ' . implode(', ', $capabilities)
        );
    }

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    protected static function createProviderMetadata(): ProviderMetadata
    {
        return new ProviderMetadata(
            'anthropic',
            'Anthropic',
            ProviderTypeEnum::cloud(),
            'https://console.anthropic.com/settings/keys',
            RequestAuthenticationMethod::apiKey()
        );
    }

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    protected static function createProviderAvailability(): ProviderAvailabilityInterface
    {
        // Check valid API access by attempting to list models.
        return new ListModelsApiBasedProviderAvailability(
            static::modelMetadataDirectory()
        );
    }

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    protected static function createModelMetadataDirectory(): ModelMetadataDirectoryInterface
```

**File:** src/Providers/OpenAiCompatibleImplementation/AbstractOpenAiCompatibleTextGenerationModel.php (L66-90)
```php
abstract class AbstractOpenAiCompatibleTextGenerationModel extends AbstractApiBasedModel implements
    TextGenerationModelInterface
{
    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    final public function generateTextResult(array $prompt): GenerativeAiResult
    {
        $httpTransporter = $this->getHttpTransporter();

        $params = $this->prepareGenerateTextParams($prompt);

        $request = $this->createRequest(
            HttpMethodEnum::POST(),
            'chat/completions',
            ['Content-Type' => 'application/json'],
            $params
        );

        // Add authentication credentials to the request.
        $request = $this->getRequestAuthentication()->authenticateRequest($request);

        // Send and process the request.
```

**File:** src/Providers/ProviderRegistry.php (L62-113)
```php
    public function registerProvider(string $className): void
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException(
                sprintf('Provider class does not exist: %s', $className)
            );
        }

        // Validate that class implements ProviderInterface
        if (!is_subclass_of($className, ProviderInterface::class)) {
            throw new InvalidArgumentException(
                sprintf('Provider class must implement %s: %s', ProviderInterface::class, $className)
            );
        }

        $metadata = $className::metadata();

        if (!$metadata instanceof ProviderMetadata) {
            throw new InvalidArgumentException(
                sprintf('Provider must return ProviderMetadata from metadata() method: %s', $className)
            );
        }

        // If there is already a HTTP transporter instance set, hook it up to the provider as needed.
        try {
            $httpTransporter = $this->getHttpTransporter();
            $this->setHttpTransporterForProvider($className, $httpTransporter);
        } catch (RuntimeException $e) {
            /*
             * If this fails, it's okay. There is no defined sequence between setting the HTTP transporter in the
             * registry and registering providers in it, so it might be that the transporter is set later. It will be
             * hooked up then.
             * Therefore we can simply ignore this exception.
             */
        }

        // Hook up the request authentication instance, using a default if not set.
        if (!isset($this->providerAuthenticationInstances[$className])) {
            $defaultProviderAuthentication = $this->createDefaultProviderRequestAuthentication(
                $className
            );
            if ($defaultProviderAuthentication !== null) {
                $this->providerAuthenticationInstances[$className] = $defaultProviderAuthentication;
            }
        }
        if (isset($this->providerAuthenticationInstances[$className])) {
            $this->setRequestAuthenticationForProvider($className, $this->providerAuthenticationInstances[$className]);
        }

        $this->registeredIdsToClassNames[$metadata->getId()] = $className;
        $this->registeredClassNamesToIds[$className] = $metadata->getId();
    }
```

**File:** docs/ARCHITECTURE.md (L896-900)
```markdown
        class ProviderTypeEnum {
            CLOUD
            SERVER
            CLIENT
        }
```

**File:** src/Providers/Contracts/ProviderInterface.php (L20-60)
```php
interface ProviderInterface
{
    /**
     * Gets provider metadata.
     *
     * @since 0.1.0
     *
     * @return ProviderMetadata Provider metadata.
     */
    public static function metadata(): ProviderMetadata;

    /**
     * Creates a model instance.
     *
     * @since 0.1.0
     *
     * @param string $modelId Model identifier.
     * @param ?ModelConfig $modelConfig Model configuration.
     * @return ModelInterface Model instance.
     * @throws InvalidArgumentException If model not found or configuration invalid.
     */
    public static function model(string $modelId, ?ModelConfig $modelConfig = null): ModelInterface;

    /**
     * Gets provider availability checker.
     *
     * @since 0.1.0
     *
     * @return ProviderAvailabilityInterface Provider availability checker.
     */
    public static function availability(): ProviderAvailabilityInterface;

    /**
     * Gets model metadata directory.
     *
     * @since 0.1.0
     *
     * @return ModelMetadataDirectoryInterface Model metadata directory.
     */
    public static function modelMetadataDirectory(): ModelMetadataDirectoryInterface;
}
```

**File:** src/Providers/AbstractProvider.php (L20-60)
```php
abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @var array<string, ProviderMetadata> Cache for provider metadata per class.
     */
    private static array $metadataCache = [];

    /**
     * @var array<string, ProviderAvailabilityInterface> Cache for provider availability per class.
     */
    private static array $availabilityCache = [];

    /**
     * @var array<string, ModelMetadataDirectoryInterface> Cache for model metadata directory per class.
     */
    private static array $modelMetadataDirectoryCache = [];

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    final public static function metadata(): ProviderMetadata
    {
        $className = static::class;
        if (!isset(self::$metadataCache[$className])) {
            self::$metadataCache[$className] = static::createProviderMetadata();
        }
        return self::$metadataCache[$className];
    }

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    final public static function model(string $modelId, ?ModelConfig $modelConfig = null): ModelInterface
    {
        $providerMetadata = static::metadata();
        $modelMetadata = static::modelMetadataDirectory()->getModelMetadata($modelId);

```

**File:** src/Providers/ApiBasedImplementation/AbstractApiBasedModel.php (L25-60)
```php
abstract class AbstractApiBasedModel implements
    ApiBasedModelInterface,
    WithHttpTransporterInterface,
    WithRequestAuthenticationInterface
{
    use WithHttpTransporterTrait;
    use WithRequestAuthenticationTrait;

    /**
     * @var ModelMetadata The metadata for the model.
     */
    private ModelMetadata $metadata;

    /**
     * @var ProviderMetadata The metadata for the model's provider.
     */
    private ProviderMetadata $providerMetadata;

    /**
     * @var ModelConfig The configuration for the model.
     */
    private ModelConfig $config;

    /**
     * @var RequestOptions|null The request options for HTTP transport.
     */
    private ?RequestOptions $requestOptions = null;

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @param ModelMetadata $metadata The metadata for the model.
     * @param ProviderMetadata $providerMetadata The metadata for the model's provider.
     */
```

**File:** docs/REQUIREMENTS.md (L33-33)
```markdown
* MUST support any kinds of AI implementation, i.e. cloud-based AI, server-side AI, client-side AI.
```

**File:** AGENTS.md (L20-27)
```markdown
All code in this project MUST adhere to the coding standards, naming conventions, and documentation standards outlined in the `CONTRIBUTING.md` file. Any agent working on this project MUST read and follow the guidelines in that file before starting any work.

Key constraints include:

*   PHP 7.4 as the minimum required version.
*   PER Coding Style (extending PSR-12).
*   Strict type hinting for all parameters, return values, and properties.

```
