<?php

namespace Daavelar\PhpRunwareSDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Ramsey\Uuid\Uuid;
use InvalidArgumentException;

class UploadModel
{
    private string $apiKey;
    private Client $client;
    private string $baseUrl = 'https://api.runware.ai';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);
    }

    /**
     * Faz upload de um modelo para a Runware
     * 
     * @param array $params Parâmetros para o upload do modelo
     * @return string Resposta da API
     * @throws GuzzleException
     */
    public function upload($air, $uniqueIdentifier, $name, $version, $downloadURL, $architecture = 'pony'): string
    {
        $params = [
            "air" => $air,
            "uniqueIdentifier" => $uniqueIdentifier,
            "name" => $name,
            "version" => $version,
            "downloadURL" => $downloadURL,
            "taskType" => "modelUpload",
            "taskUUID" => Uuid::uuid4()->toString(),
            "category" => "checkpoint",
            "type" => "base",
            "architecture" => $architecture,
            "format" => "safetensors",
            "private" => true,
            "defaultCFG" => 3.5,
            "defaultSteps" => 35,
            "defaultScheduler" => "DPM++ 2M",
            "defaultStrength" => 1.0,
        ];

        $response = $this->client->post('/v1/tasks', [
            'json' => [$params],
        ]);

        return $response->getBody()->getContents();
    }
}
