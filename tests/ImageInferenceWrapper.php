<?php

declare(strict_types=1);

namespace Tests;

use AiMatchFun\PhpRunwareSDK\ImageInference;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use ReflectionClass;
use ReflectionMethod;

/**
 * Wrapper testável para ImageInference que permite injetar um mock HTTP client
 * 
 * Esta classe permite testar o ImageInference sem fazer chamadas reais à API
 * usando reflection para substituir o método privado post()
 */
class ImageInferenceWrapper extends ImageInference
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
        $reflection = new ReflectionClass(\AiMatchFun\PhpRunwareSDK\ImageInference::class);
        
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
                throw new \Exception("Runware API Error: " . $e->getResponse()->getBody()->getContents());
            } catch (\GuzzleHttp\Exception\ServerException $e) {
                throw new \Exception("Runware Server Error: " . $e->getResponse()->getBody()->getContents());
            } catch (\Exception $e) {
                throw new \Exception("Error connecting to Runware API: " . $e->getMessage());
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

    /**
     * Factory method para criar uma instância com mock handler
     */
    public static function withMockHandler(string $apiKey = 'test-api-key'): array
    {
        $mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($mockHandler);
        $mockClient = new Client(['handler' => $handlerStack]);

        $wrapper = new self($apiKey);
        $wrapper->setMockClient($mockClient);

        return [$wrapper, $mockHandler];
    }
}

