# PHP Runware

A PHP wrapper for the Runware AI API, allowing simple and efficient AI image generation.

## Installation

```bash
composer require runware/php-runware
```

## Usage

```php
use Runware\Runware;

$runware = new Runware('your_api_key');

$imageUrl = $runware
    ->withHeight(768)
    ->withWidth(1024)
    ->model('runware:realistic')
    ->withSteps(30)
    ->withCFGScale(8.5)
    ->withNumberResults(4)
    ->withOutputType('URL')
    ->withOutputFormat('PNG')
    ->withNegativePrompt('low quality, blurred')
    ->textToImage('A beautiful sunset over a calm ocean');

$transformedImageUrl = $runware
    ->withHeight(512)
    ->withWidth(512)
    ->withSteps(25)
    ->imageToImage(
        'A beautiful sunset over a calm ocean', 
        'https://url-da-imagem-original.jpg',
        0.7
    );

$inpaintedImageUrl = $runware
    ->inpainting(
        'A beautiful sunset over a calm ocean',
        'https://url-da-imagem-original.jpg',
        'https://url-da-mascara.jpg',
        0.8
    );

$outpaintedImageUrl = $runware
    ->outpainting(
        'A beautiful sunset over a calm ocean',
        'https://url-da-imagem-original.jpg',
        [
            'top' => 256,
            'right' => 256,
            'bottom' => 0,
            'left' => 0
        ],
        0.8
    );
```

## Documentation

For more detailed information on the API, please refer to the [Runware API Documentation](https://runware.ai/docs).

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.