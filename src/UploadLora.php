<?php

namespace Daavelar\PhpRunwareSDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Ramsey\Uuid\Uuid;
use InvalidArgumentException;

class UploadLora
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
     * Faz upload de um modelo LoRA para a Runware
     * 
     * @param array $params Parâmetros para o upload do modelo LoRA
     * @return string Resposta da API
     * @throws GuzzleException
     */
    public function upload(array $params): string
    {
        $defaultParams = [
            "taskType" => "modelUpload",
            "taskUUID" => Uuid::uuid4()->toString(),
            "category" => "lora",
            "architecture" => "flux1d",
            "format" => "safetensors",
            "private" => true,
            "defaultWeight" => 1.0
        ];

        $data = array_merge($defaultParams, $params);

        $requiredFields = ['air', 'uniqueIdentifier', 'name', 'version', 'downloadURL'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new InvalidArgumentException("O campo '$field' é obrigatório");
            }
        }

        $response = $this->client->post('/v1/tasks', [
            'json' => [$data],
        ]);

        return $response->getBody()->getContents();
    }
}