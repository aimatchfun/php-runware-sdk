<?php

declare(strict_types=1);

namespace Tests;

use AiMatchFun\PhpRunwareSDK\RunwareModel;
use AiMatchFun\PhpRunwareSDK\OutputFormat;
use AiMatchFun\PhpRunwareSDK\OutputType;
use AiMatchFun\PhpRunwareSDK\TextToImage;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * Testable version of TextToImage that allows HTTP client injection
 */
class TextToImageMockable extends TextToImage
{
    private ?Client $mockClient = null;

    public function setMockClient(Client $client): void
    {
        $this->mockClient = $client;
    }

    /**
     * Sobrescreve o método run para usar o mock client
     * Como o método post é privado na classe pai, precisamos usar reflection
     */
    public function run(): string
    {
        if ($this->mockClient === null) {
            // Se não há mock, usar comportamento padrão
            return parent::run();
        }

        // Usar reflection para acessar métodos e propriedades privadas
        $reflection = new ReflectionClass(\AiMatchFun\PhpRunwareSDK\TextToImage::class);
        
        // Acessar propriedades privadas da classe pai
        $apiUrlProperty = $reflection->getProperty('apiUrl');
        $apiUrlProperty->setAccessible(true);
        $apiUrl = $apiUrlProperty->getValue($this);

        $apiKeyProperty = $reflection->getProperty('apiKey');
        $apiKeyProperty->setAccessible(true);
        $apiKey = $apiKeyProperty->getValue($this);

        // Criar closure que usa nosso mock client
        $mockClient = $this->mockClient;

        // Criar função post mockada
        $mockPost = function(array $data) use ($mockClient, $apiUrl, $apiKey) {
            try {
                $response = $mockClient->post($apiUrl, [
                    'json' => [$data],
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $apiKey
                    ]
                ]);

                return $response->getBody()->getContents();
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                throw new Exception("Runware API Error: " . $e->getResponse()->getBody()->getContents());
            } catch (\GuzzleHttp\Exception\ServerException $e) {
                throw new Exception("Runware Server Error: " . $e->getResponse()->getBody()->getContents());
            } catch (Exception $e) {
                throw new Exception("Error connecting to Runware API: " . $e->getMessage());
            }
        };

        // Usar o método mountRequestBody da classe pai
        $mountRequestBodyMethod = $reflection->getMethod('mountRequestBody');
        $mountRequestBodyMethod->setAccessible(true);
        $requestBody = $mountRequestBodyMethod->invoke($this);

        // Chamar nosso método post mockado
        $response = $mockPost($requestBody);

        // Usar handleResponse da classe pai
        $handleResponseMethod = $reflection->getMethod('handleResponse');
        $handleResponseMethod->setAccessible(true);
        
        return $handleResponseMethod->invoke($this, $response);
    }
}

class TextToImageMockableTest extends TestCase
{
    private TextToImageMockable $textToImage;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->textToImage = new TextToImageMockable('test-api-key');
        $this->mockHandler = new MockHandler();
        
        $handlerStack = HandlerStack::create($this->mockHandler);
        $mockClient = new Client(['handler' => $handlerStack]);
        $this->textToImage->setMockClient($mockClient);
    }

    public function testRunReturnsImageUrlWhenOutputTypeIsUrl(): void
    {
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

        $this->mockHandler->append($mockResponse);

        // Configure the textToImage instance
        $result = $this->textToImage
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

        $this->mockHandler->append($mockResponse);

        $result = $this->textToImage
            ->positivePrompt('A beautiful landscape')
            ->negativePrompt('blur')
            ->outputType(OutputType::BASE64_DATA)
            ->run();

        $this->assertEquals($base64Data, $result);
    }

    public function testRunThrowsExceptionOnApiError(): void
    {
        $mockResponse = new Response(400, [], json_encode([
            'error' => 'Invalid API key'
        ]));

        $this->mockHandler->append($mockResponse);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Runware API Error');

        $this->textToImage
            ->positivePrompt('A beautiful sunset')
            ->negativePrompt('blur')
            ->run();
    }

    public function testRunThrowsExceptionWhenResponseIsInvalid(): void
    {
        $mockResponse = new Response(200, [], json_encode([
            'data' => [] // Empty data array
        ]));

        $this->mockHandler->append($mockResponse);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('API response does not contain data');

        $this->textToImage
            ->positivePrompt('A beautiful sunset')
            ->negativePrompt('blur')
            ->run();
    }

    public function testCanChainMultipleMethods(): void
    {
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

        $this->mockHandler->append($mockResponse);

        $result = $this->textToImage
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

    public function testCanAddLora(): void
    {
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

        $this->mockHandler->append($mockResponse);

        $result = $this->textToImage
            ->positivePrompt('A beautiful portrait')
            ->negativePrompt('blur')
            ->addLora('civitai:927305@1037996', 1.0)
            ->addLora('civitai:368139@411375', 0.8)
            ->run();

        $this->assertEquals('https://example.com/image.png', $result);
    }
}

