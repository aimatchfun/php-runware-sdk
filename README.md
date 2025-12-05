# PHP Runware

A PHP wrapper for the Runware AI API, allowing simple and efficient AI image generation.

## Installation

```bash
composer require aimatchfun/php-runware-sdk
```

## Basic Usage

```php
use AiMatchFun\PhpRunwareSDK\TextToImage;
use AiMatchFun\PhpRunwareSDK\OutputType;
use AiMatchFun\PhpRunwareSDK\OutputFormat;
use AiMatchFun\PhpRunwareSDK\FluxScheduler;
use AiMatchFun\PhpRunwareSDK\ModelAir;
use AiMatchFun\PhpRunwareSDK\PromptWeighting;

$textToImage = new TextToImage('your_api_key');

$result = $textToImage
    ->positivePrompt('A beautiful sunset over a calm ocean')
    ->height(768)
    ->width(1024)
    ->modelAir(ModelAir::REAL_DREAM_SDXL_PONY_14)
    ->steps(30)
    ->cfgScale(8.5)
    ->numberResults(1)
    ->outputType(OutputType::URL)
    ->outputFormat(OutputFormat::PNG)
    ->negativePrompt('low quality, blurred')
    ->run();
```

## Examples

### Text to Image Generation with Different Models

```php
use AiMatchFun\PhpRunwareSDK\TextToImage;
use AiMatchFun\PhpRunwareSDK\ModelAir;

$textToImage = new TextToImage('your_api_key');

// Using Pony Realism model
$result = $textToImage
    ->positivePrompt('A serene mountain landscape at sunset')
    ->modelAir(ModelAir::PONY_REALISM)
    ->run();

// Using Goddess of Realism model
$result = $textToImage
    ->positivePrompt('A portrait of a young woman')
    ->modelAir(ModelAir::GODDESS_OF_REALISM)
    ->run();
```

### Output Types and Formats

```php
use AiMatchFun\PhpRunwareSDK\OutputType;
use AiMatchFun\PhpRunwareSDK\OutputFormat;

// Get result as URL
$result = $textToImage
    ->positivePrompt('A cyberpunk city at night')
    ->outputType(OutputType::URL)
    ->outputFormat(OutputFormat::PNG)
    ->run();

// Get result as base64 data
$result = $textToImage
    ->positivePrompt('An abstract painting')
    ->outputType(OutputType::BASE64_DATA)
    ->outputFormat(OutputFormat::JPG)
    ->run();

// Get result as data URI
$result = $textToImage
    ->positivePrompt('A fantasy landscape')
    ->outputType(OutputType::DATA_URI)
    ->outputFormat(OutputFormat::WEBP)
    ->run();
```

### Using Different Schedulers

```php
use AiMatchFun\PhpRunwareSDK\FluxScheduler;

// Using DPM++ 2M scheduler
$result = $textToImage
    ->positivePrompt('A beautiful landscape')
    ->scheduler(FluxScheduler::DPM_PLUS_PLUS_2M)
    ->run();

// Using Euler scheduler
$result = $textToImage
    ->positivePrompt('A sci-fi scene')
    ->scheduler(FluxScheduler::EULER)
    ->run();

// Using Karras scheduler
$result = $textToImage
    ->positivePrompt('An artistic portrait')
    ->scheduler(FluxScheduler::DPM_PLUS_PLUS_KARRAS)
    ->run();
```

### Advanced Configuration

```php
use AiMatchFun\PhpRunwareSDK\PromptWeighting;

// Using advanced settings with prompt weighting
$result = $textToImage
    ->positivePrompt('A magical forest scene')
    ->negativePrompt('blurry, low quality, distorted')
    ->height(1024)
    ->width(1024)
    ->steps(30)
    ->cfgScale(7.5)
    ->numberResults(4)
    ->outputType(OutputType::URL)
    ->outputFormat(OutputFormat::PNG)
    ->modelAir(ModelAir::REAL_DREAM_SDXL_PONY_14)
    ->promptWeighting(PromptWeighting::COMPEL)
    ->run();

// Using acceleration features
$result = $textToImage
    ->positivePrompt('A fantasy landscape')
    ->teaCache(true)
    ->teaCacheDistance(0.5)
    ->deepCache(true)
    ->deepCacheInterval(3)
    ->run();
```

### Using LoRA Models

```php
// Using multiple LoRA models with different weights
$result = $textToImage
    ->positivePrompt('A beautiful portrait')
    ->addLora('civitai:927305@1037996', 1.0)  // First LoRA with weight 1.0
    ->addLora('civitai:368139@411375', 0.8)   // Second LoRA with weight 0.8
    ->addLora('civitai:888213@486749', 0.5)   // Third LoRA with weight 0.5
    ->run();

// Using a single LoRA model
$result = $textToImage
    ->positivePrompt('A fantasy character')
    ->addLora('civitai:927305@1037996', 0.7)  // Using LoRA with weight 0.7
    ->run();
```

### Using Custom Models

```php
use AiMatchFun\PhpRunwareSDK\ModelAir;

// Using a specific model version
$result = $textToImage
    ->positivePrompt('A beautiful landscape')
    ->modelAir(ModelAir::REAL_DREAM_SDXL_PONY_14)
    ->run();

// Using different model variants
$result = $textToImage
    ->positivePrompt('A portrait photo')
    ->modelAir(ModelAir::REAL_DREAM_SDXL_PONY_11)
    ->run();
```

### Uploading LoRA Models

```php
use AiMatchFun\PhpRunwareSDK\UploadLora;
use Ramsey\Uuid\Uuid;

$uploader = new UploadLora('your_api_key');

$result = $uploader->upload([
    'air' => 'air-of-your-model',
    'uniqueIdentifier' => Uuid::uuid4()->toString(),
    'name' => 'name-of-your-model',
    'version' => '1.0',
    'downloadURL' => 'public-url-to-your-model',
    'tags' => ['character', 'anime', 'artstyle'],
    'positiveTriggerWords' => 'positive-trigger-words',
    'shortDescription' => 'short-description-of-your-model',
    'comment' => 'comment-of-your-model'
]);
```

### Uploading Custom Models

```php
use AiMatchFun\PhpRunwareSDK\UploadModel;
use Ramsey\Uuid\Uuid;

$uploader = new UploadModel('your_api_key');

$result = $uploader->upload([
    'air' => 'air-of-your-model',
    'uniqueIdentifier' => Uuid::uuid4()->toString(),
    'name' => 'name-of-your-model',
    'version' => '1.0',
    'downloadURL' => 'public-url-to-your-model',
    'tags' => ['character', 'anime', 'artstyle'],
    'positiveTriggerWords' => 'positive-trigger-words',
    'shortDescription' => 'short-description-of-your-model',
    'comment' => 'comment-of-your-model'
]);
```

The upload methods accept the following parameters:
- `air`: Unique identifier for your model in the Runware system
- `uniqueIdentifier`: A UUID v4 string to identify your upload
- `name`: Name of your model
- `version`: Version string of your model
- `downloadURL`: Public URL where the model can be downloaded
- `tags`: Array of relevant tags for categorizing the model
- `positiveTriggerWords`: Keywords that activate or work well with the model
- `shortDescription`: Brief description of what the model does
- `comment`: Additional notes or comments about the model

### Error Handling

```php
use Exception;

try {
    $result = $textToImage
        ->positivePrompt('A beautiful landscape')
        ->run();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Parameter Validation

The SDK includes built-in validation for all parameters:

- Height and width must be between 128 and 2048 and divisible by 64
- Steps must be between 1 and 100
- CFG Scale must be between 0 and 30
- Number of results must be between 1 and 20
- CLIP skip must be between 0 and 2
- TeaCache distance must be between 0 and 1

## Documentation

For more detailed information on the API, please refer to the [Runware API Documentation](https://runware.ai/docs).

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.