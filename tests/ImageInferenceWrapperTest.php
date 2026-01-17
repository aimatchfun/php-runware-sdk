<?php

declare(strict_types=1);

namespace Tests;

use AiMatchFun\PhpRunwareSDK\RunwareModel;
use AiMatchFun\PhpRunwareSDK\OutputFormat;
use AiMatchFun\PhpRunwareSDK\OutputType;
use Exception;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ImageInferenceWrapperTest extends TestCase
{
    public function testRunReturnsImageUrlWhenOutputTypeIsUrl(): void
    {
        [$imageInference, $mockHandler] = ImageInferenceWrapper::withMockHandler();

        // Mock API response
        $mockResponse = new Response(200, [], json_encode([
            'data' => [
                [
                    'taskType' => 'imageInference',
                    'taskUUID' => 'test-task-uuid',
                    'imageUUID' => 'test-image-uuid',
                    'imageURL' => 'https://example.com/image.png',
                    'seed' => 12345,
                    'cost' => 0.5,
                ]
            ]
        ]));

        $mockHandler->append($mockResponse);

        // Configure and run
        $result = $imageInference
            ->positivePrompt('A beautiful sunset')
            ->negativePrompt('blur')
            ->width(512)
            ->height(512)
            ->model(RunwareModel::REAL_DREAM_SDXL_PONY_14)
            ->steps(20)
            ->cfgScale(7.5)
            ->numberResults(1)
            ->outputType(OutputType::URL)
            ->outputFormat(OutputFormat::PNG)
            ->run();

        $this->assertEquals('https://example.com/image.png', $result);
    }

    public function testRunReturnsBase64DataWhenOutputTypeIsBase64(): void
    {
        [$imageInference, $mockHandler] = ImageInferenceWrapper::withMockHandler();

        $base64Data = base64_encode('fake-image-data');

        $mockResponse = new Response(200, [], json_encode([
            'data' => [
                [
                    'taskType' => 'imageInference',
                    'taskUUID' => 'test-task-uuid',
                    'imageUUID' => 'test-image-uuid',
                    'imageBase64Data' => $base64Data,
                ]
            ]
        ]));

        $mockHandler->append($mockResponse);

        $result = $imageInference
            ->positivePrompt('A beautiful landscape')
            ->negativePrompt('blur')
            ->outputType(OutputType::BASE64_DATA)
            ->run();

        $this->assertEquals($base64Data, $result);
    }

    public function testRunThrowsExceptionOnApiError(): void
    {
        [$imageInference, $mockHandler] = ImageInferenceWrapper::withMockHandler();

        $mockResponse = new Response(400, [], json_encode([
            'error' => 'Invalid API key'
        ]));

        $mockHandler->append($mockResponse);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Runware API Error');

        $imageInference
            ->positivePrompt('A beautiful sunset')
            ->negativePrompt('blur')
            ->run();
    }

    public function testCanChainMultipleMethods(): void
    {
        [$imageInference, $mockHandler] = ImageInferenceWrapper::withMockHandler();

        $mockResponse = new Response(200, [], json_encode([
            'data' => [
                [
                    'taskType' => 'imageInference',
                    'taskUUID' => 'test-task-uuid',
                    'imageUUID' => 'test-image-uuid',
                    'imageURL' => 'https://example.com/image.png',
                ]
            ]
        ]));

        $mockHandler->append($mockResponse);

        $result = $imageInference
            ->positivePrompt('A beautiful sunset')
            ->negativePrompt('blur, low quality')
            ->width(1024)
            ->height(768)
            ->model(RunwareModel::REAL_DREAM_SDXL_PONY_14)
            ->steps(30)
            ->cfgScale(8.5)
            ->numberResults(2)
            ->outputType(OutputType::URL)
            ->outputFormat(OutputFormat::PNG)
            ->nsfw(false)
            ->run();

        $this->assertEquals('https://example.com/image.png', $result);
    }
}

