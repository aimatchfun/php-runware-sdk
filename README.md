# PHP Runware

A PHP wrapper for the Runware AI API, allowing simple and efficient AI image generation.

## Installation

```bash
composer require aimatchfun/php-runware-sdk
```

## Basic Usage

### Text to Image

```php
use AiMatchFun\PhpRunwareSDK\TextToImage;
use AiMatchFun\PhpRunwareSDK\OutputType;
use AiMatchFun\PhpRunwareSDK\OutputFormat;
use AiMatchFun\PhpRunwareSDK\Scheduler;
use AiMatchFun\PhpRunwareSDK\RunwareModel;
use AiMatchFun\PhpRunwareSDK\PromptWeighting;

$textToImage = new TextToImage('your_api_key');

$result = $textToImage
    ->positivePrompt('A beautiful sunset over a calm ocean')
    ->height(768)
    ->width(1024)
    ->model(RunwareModel::REAL_DREAM_SDXL_PONY_14)
    ->steps(30)
    ->cfgScale(8.5)
    ->numberResults(1)
    ->outputType(OutputType::URL)
    ->outputFormat(OutputFormat::PNG)
    ->negativePrompt('low quality, blurred')
    ->run();
```

### Inpainting

Inpainting allows you to selectively edit specific areas of an image by providing a seed image and a mask image:

```php
use AiMatchFun\PhpRunwareSDK\Inpainting;
use AiMatchFun\PhpRunwareSDK\OutputType;
use AiMatchFun\PhpRunwareSDK\RunwareModel;

$inpainting = new Inpainting('your_api_key');

$result = $inpainting
    ->seedImage('59a2edc2-45e6-429f-be5f-7ded59b92046') // Image UUID or URL
    ->maskImage('5988e195-8100-4b91-b07c-c7096d0861aa') // Mask UUID or URL
    ->positivePrompt('a serene beach at sunset')
    ->negativePrompt('blur, distortion')
    ->strength(0.8) // Strength of the inpainting effect (0.0 to 1.0)
    ->maskMargin(64) // Extra context pixels around masked region (32-128)
    ->model(RunwareModel::REAL_DREAM_SDXL_PONY_14)
    ->width(1024)
    ->height(1024)
    ->outputType(OutputType::URL)
    ->run();
```

**Inpainting Parameters:**
- `seedImage(string $image)`: The original image you wish to edit (UUID, URL, base64, or data URI)
- `maskImage(string $image)`: Defines the area to be modified (UUID, URL, base64, or data URI)
  - White areas (255,255,255) indicate regions to be modified
  - Black areas (0,0,0) indicate regions to preserve
  - Gray values create partial modification
- `strength(float $strength)`: Strength of the inpainting effect (0.0 to 1.0, default: 0.8)
- `maskMargin(int $margin)`: Adds extra context pixels around masked region (32-128 pixels)

For more details about inpainting, see the [Runware Inpainting Documentation](https://runware.ai/docs/en/image-inference/inpainting).

### Image Upload

Image Upload allows you to upload images to the Runware API and receive an image UUID that can be used in other operations like inpainting:

```php
use AiMatchFun\PhpRunwareSDK\ImageUpload;

$imageUpload = new ImageUpload('your_api_key');

// Upload and get the image UUID
$imageUUID = $imageUpload
    ->image('https://example.com/image.png') // URL, base64, or data URI
    ->run();

// The returned UUID can be used in other operations
echo "Image uploaded with UUID: " . $imageUUID;
```

**Image Upload Parameters:**
- `image(string $image)`: The image to upload in one of these formats:
  - Public image URL: `https://example.com/image.png`
  - Base64 data URI: `data:image/png;base64,iVBORw0KGgo...`
  - Pure base64: `iVBORw0KGgo...`

For more details about image upload, see the [Runware Image Upload Documentation](https://runware.ai/docs/en/utilities/image-upload).

## Examples

### Image Upload Examples

#### Uploading from URL

```php
use AiMatchFun\PhpRunwareSDK\ImageUpload;

$imageUpload = new ImageUpload('your_api_key');

$imageUUID = $imageUpload
    ->image('https://example.com/my-image.png')
    ->run();

echo "Image UUID: " . $imageUUID;
```

#### Uploading Base64 Encoded Image

```php
use AiMatchFun\PhpRunwareSDK\ImageUpload;

$imageUpload = new ImageUpload('your_api_key');

// Using pure base64 string
$base64Image = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

$imageUUID = $imageUpload
    ->image($base64Image)
    ->run();

// Using data URI format
$dataURI = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

$imageUUID = $imageUpload
    ->image($dataURI)
    ->run();
```

#### Using Uploaded Image with Inpainting

```php
use AiMatchFun\PhpRunwareSDK\ImageUpload;
use AiMatchFun\PhpRunwareSDK\Inpainting;
use AiMatchFun\PhpRunwareSDK\RunwareModel;
use AiMatchFun\PhpRunwareSDK\OutputType;

// Step 1: Upload the image
$imageUpload = new ImageUpload('your_api_key');
$imageUUID = $imageUpload
    ->image('https://example.com/image.png')
    ->run();

// Step 2: Upload the mask image
$maskUUID = $imageUpload
    ->image('https://example.com/mask.png')
    ->run();

// Step 3: Use the uploaded images in inpainting
$inpainting = new Inpainting('your_api_key');
$result = $inpainting
    ->seedImage($imageUUID)      // Use the uploaded image UUID
    ->maskImage($maskUUID)        // Use the uploaded mask UUID
    ->positivePrompt('a beautiful blue sky')
    ->negativePrompt('blur, distortion')
    ->strength(0.8)
    ->model(RunwareModel::REAL_DREAM_SDXL_PONY_14)
    ->outputType(OutputType::URL)
    ->run();

echo "Inpainted image: " . $result;
```

### Inpainting Examples

#### Basic Inpainting

```php
use AiMatchFun\PhpRunwareSDK\Inpainting;
use AiMatchFun\PhpRunwareSDK\OutputType;
use AiMatchFun\PhpRunwareSDK\RunwareModel;

$inpainting = new Inpainting('your_api_key');

// Simple object removal
$result = $inpainting
    ->seedImage('image-uuid-or-url')
    ->maskImage('mask-uuid-or-url')
    ->positivePrompt('white wall')
    ->negativePrompt('blur, distortion')
    ->strength(0.8)
    ->model(RunwareModel::REAL_DREAM_SDXL_PONY_14)
    ->outputType(OutputType::URL)
    ->run();
```

#### Inpainting with Mask Margin for Enhanced Detail

```php
// Using maskMargin to enhance facial features
$result = $inpainting
    ->seedImage('image-uuid')
    ->maskImage('face-mask-uuid')
    ->positivePrompt('a detailed face with natural lighting')
    ->maskMargin(64) // Enhances detail by zooming into masked area
    ->strength(0.8)
    ->steps(40) // Higher steps for better detail
    ->cfgScale(8.0)
    ->run();
```

#### Inpainting with Different Strength Values

```php
// Low strength - subtle modification
$result = $inpainting
    ->seedImage('image-uuid')
    ->maskImage('mask-uuid')
    ->positivePrompt('red apple')
    ->strength(0.5) // More influence from original image
    ->run();

// High strength - complete replacement
$result = $inpainting
    ->seedImage('image-uuid')
    ->maskImage('mask-uuid')
    ->positivePrompt('blue sky with clouds')
    ->strength(0.9) // More creative deviation
    ->run();
```

### Text to Image Generation with Different Models

```php
use AiMatchFun\PhpRunwareSDK\TextToImage;
use AiMatchFun\PhpRunwareSDK\RunwareModel;

$textToImage = new TextToImage('your_api_key');

// Using Pony Realism model
$result = $textToImage
    ->positivePrompt('A serene mountain landscape at sunset')
    ->model(RunwareModel::PONY_REALISM)
    ->run();

// Using Goddess of Realism model
$result = $textToImage
    ->positivePrompt('A portrait of a young woman')
    ->model(RunwareModel::GODDESS_OF_REALISM)
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
use AiMatchFun\PhpRunwareSDK\Scheduler;

// Using DPM++ 2M scheduler
$result = $textToImage
    ->positivePrompt('A beautiful landscape')
    ->scheduler(Scheduler::DPM_PLUS_PLUS_2M)
    ->run();

// Using Euler scheduler
$result = $textToImage
    ->positivePrompt('A sci-fi scene')
    ->scheduler(Scheduler::EULER)
    ->run();

// Using Karras scheduler
$result = $textToImage
    ->positivePrompt('An artistic portrait')
    ->scheduler(Scheduler::DPM_PLUS_PLUS_KARRAS)
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
    ->model(RunwareModel::REAL_DREAM_SDXL_PONY_14)
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
use AiMatchFun\PhpRunwareSDK\RunwareModel;

// Using a specific model version with ENUM
$result = $textToImage
    ->positivePrompt('A beautiful landscape')
    ->model(RunwareModel::REAL_DREAM_SDXL_PONY_14)
    ->run();

// Using different model variants with ENUM
$result = $textToImage
    ->positivePrompt('A portrait photo')
    ->model(RunwareModel::REAL_DREAM_SDXL_PONY_11)
    ->run();

// Using model with string directly (AIR format)
$result = $textToImage
    ->positivePrompt('A fantasy scene')
    ->model('civitai:618692@691639') // Direct AIR string
    ->run();
```

**Note:** The `model()` method accepts both `RunwareModel` enum values and string values (AIR format). This allows you to use predefined models from the enum or specify custom models using their AIR identifier directly.

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

**TextToImage:**
- Height and width must be between 128 and 2048 and divisible by 64
- Steps must be between 1 and 100
- CFG Scale must be between 0 and 30
- Number of results must be between 1 and 20
- CLIP skip must be between 0 and 2
- TeaCache distance must be between 0 and 1

**Inpainting:**
- Height and width must be between 128 and 2048 and divisible by 64
- Steps must be between 1 and 100
- CFG Scale must be between 0 and 30
- Number of results must be between 1 and 20
- CLIP skip must be between 0 and 2
- Strength must be between 0.0 and 1.0
- Mask margin must be between 32 and 128
- Seed image and mask image are required

**Image Upload:**
- Image parameter is required and cannot be empty
- Accepts base64 encoded data URI, pure base64, or public image URLs

## Documentation

For more detailed information on the API, please refer to the [Runware API Documentation](https://runware.ai/docs).

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.