<?php

declare(strict_types=1);

namespace Tests\Helpers;

use AiMatchFun\PhpRunwareSDK\TextToImage;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use ReflectionClass;
use ReflectionMethod;

/**
 * Trait para facilitar o mock de chamadas HTTP no TextToImage
 */
trait MocksRunwareApi
{
    /**
     * Cria uma instância de TextToImage com um mock HTTP client
     */
    protected function createTextToImageWithMock(string $apiKey = 'test-api-key'): array
    {
        $mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($mockHandler);
        $mockClient = new Client(['handler' => $handlerStack]);

        $textToImage = new TextToImage($apiKey);

        // Use reflection to inject mock client
        $reflection = new ReflectionClass($textToImage);
        
        // Create a wrapper that intercepts HTTP calls
        return [$textToImage, $mockHandler, $mockClient];
    }

    /**
     * Cria uma resposta mockada da API Runware
     */
    protected function createMockApiResponse(array $imageData): Response
    {
        return new Response(200, [], json_encode([
            'data' => [$imageData]
        ]));
    }

    /**
     * Cria uma resposta mockada de sucesso com URL
     */
    protected function createMockSuccessResponseWithUrl(string $url = 'https://example.com/image.png'): Response
    {
        return $this->createMockApiResponse([
            'taskType' => 'imageInference',
            'taskUUID' => 'test-task-uuid-' . uniqid(),
            'imageUUID' => 'test-image-uuid-' . uniqid(),
            'imageURL' => $url,
            'seed' => 12345,
            'cost' => 0.5,
        ]);
    }

    /**
     * Cria uma resposta mockada de sucesso com base64
     */
    protected function createMockSuccessResponseWithBase64(string $base64Data = null): Response
    {
        $data = $base64Data ?? base64_encode('fake-image-data');
        
        return $this->createMockApiResponse([
            'taskType' => 'imageInference',
            'taskUUID' => 'test-task-uuid-' . uniqid(),
            'imageUUID' => 'test-image-uuid-' . uniqid(),
            'imageBase64Data' => $data,
        ]);
    }

    /**
     * Cria uma resposta mockada de erro da API
     */
    protected function createMockErrorResponse(int $statusCode = 400, string $errorMessage = 'Invalid API key'): Response
    {
        return new Response($statusCode, [], json_encode([
            'error' => $errorMessage
        ]));
    }
}

