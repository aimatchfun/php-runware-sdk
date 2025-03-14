<?php

namespace Daavelar\PhpRunwareSDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Exception;
use InvalidArgumentException;

class Runware
{
    private string $apiKey;
    private string $apiUrl = 'https://api.runware.ai/v1';
    private int $height = 512;
    private int $width = 512;
    private string $model = 'runware:default';
    private int $steps = 20;
    private float $CFGScale = 7.0;
    private int $numberResults = 1;
    private string $outputType = 'URL';
    private string $outputFormat = 'JPG';
    private string $negativePrompt = '';
    private array $loras = [];
    private bool $nsfw = true;
    private array $images = [];

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Define a largura da imagem a ser gerada
     */
    public function withWidth(int $width): self
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Define a altura da imagem a ser gerada
     */
    public function withHeight(int $height): self
    {
        if ($height < 128 || $height > 2048 || $height % 64 !== 0) {
            throw new InvalidArgumentException('Height must be between 128 and 2048 and divisible by 64');
        }
        $this->height = $height;
        return $this;
    }

    /**
     * Define o modelo a ser usado
     */
    public function model(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Define o número de passos para geração
     */
    public function withSteps(int $steps): self
    {
        if ($steps < 1 || $steps > 100) {
            throw new InvalidArgumentException('Number of steps must be between 1 and 100');
        }
        $this->steps = $steps;
        return $this;
    }

    /**
     * Define a escala CFG
     */
    public function withCFGScale(float $scale): self
    {
        if ($scale < 0 || $scale > 30) {
            throw new InvalidArgumentException('CFG Scale must be between 0 and 30');
        }
        $this->CFGScale = $scale;
        return $this;
    }

    /**
     * Define o número de resultados a serem gerados
     */
    public function withNumberResults(int $number): self
    {
        if ($number < 1 || $number > 20) {
            throw new InvalidArgumentException('Number of results must be between 1 and 20');
        }
        $this->numberResults = $number;
        return $this;
    }

    /**
     * Define o tipo de saída (URL, base64Data, dataURI)
     */
    public function withOutputType(string $type): self
    {
        $validTypes = ['URL', 'base64Data', 'dataURI'];
        if (!in_array($type, $validTypes)) {
            throw new InvalidArgumentException('Output type must be one of the following: ' . implode(', ', $validTypes));
        }
        $this->outputType = $type;
        return $this;
    }

    /**
     * Define o formato de saída (JPG, PNG, WEBP)
     */
    public function withOutputFormat(string $format): self
    {
        $validFormats = ['JPG', 'PNG', 'WEBP'];
        if (!in_array($format, $validFormats)) {
            throw new InvalidArgumentException('Output format must be one of the following: ' . implode(', ', $validFormats));
        }
        $this->outputFormat = $format;
        return $this;
    }

    public function withNsfw(bool $nsfw): self
    {
        $this->nsfw = $nsfw;
        return $this;
    }

    /**
     * Define o prompt negativo
     */
    public function withNegativePrompt(string $prompt): self
    {
        $this->negativePrompt = $prompt;
        return $this;
    }

    public function addLora(string $model, float $weight = 1.0): self
    {
        $this->loras[] = [
            'model' => $model,
            'weight' => $weight
        ];
        return $this;
    }

    /**
     * Gera uma imagem a partir de um texto
     */
    public function textToImage(string $text): string
    {
        $requestBody = [
            'taskType' => 'imageInference',
            'taskUUID' => $this->generateUUID(),
            'outputType' => $this->outputType,
            'outputFormat' => $this->outputFormat,
            'positivePrompt' => $text,
            'negativePrompt' => $this->negativePrompt,
            'height' => $this->height,
            'checkNSFW' => $this->nsfw,
            'width' => $this->width,
            'model' => $this->model,
            'steps' => $this->steps,
            'CFGScale' => $this->CFGScale,
            'numberResults' => $this->numberResults,
        ];

        foreach ($this->loras as $lora) {
            if (!isset($requestBody['lora'])) {
                $requestBody['lora'] = [];
            }
            $requestBody['lora'][] = [
                'model' => $lora['model'],
                'weight' => $lora['weight'],
            ];
        }

        $response = $this->post($requestBody);

        return $this->handleResponse($response);
    }

    public function toJson($text, $prettyPrint = false): string
    {
        $requestBody = [
            'taskType' => 'imageInference',
            'taskUUID' => $this->generateUUID(),
            'outputType' => $this->outputType,
            'outputFormat' => $this->outputFormat,
            'positivePrompt' => $text,
            'negativePrompt' => $this->negativePrompt,
            'height' => $this->height,
            'checkNSFW' => $this->nsfw,
            'width' => $this->width,
            'model' => $this->model,
            'steps' => $this->steps,
            'CFGScale' => $this->CFGScale,
            'numberResults' => $this->numberResults,
        ];

        foreach ($this->loras as $lora) {
            if (!isset($requestBody['lora'])) {
                $requestBody['lora'] = []       ;
            }
            $requestBody['lora'][] = [
                'model' => $lora['model'],
                'weight' => $lora['weight'],
            ];
        }

        if ($prettyPrint) {
            return json_encode($requestBody, JSON_PRETTY_PRINT);
        }

        return json_encode($requestBody);
    }

    /**
     * Realiza inpainting em uma imagem
     */
    public function inpainting(string $text, string $seedImage, string $maskImage, float $strength = 0.8): string
    {
        $response = $this->post([
            "taskType" => "imageInference",
            "taskUUID" => $this->generateUUID(),
            "outputType" => $this->outputType,
            "outputFormat" => $this->outputFormat,
            "positivePrompt" => $text,
            "negativePrompt" => $this->negativePrompt,
            "seedImage" => $seedImage,
            "maskImage" => $maskImage,
            "strength" => $strength,
            "height" => $this->height,
            "width" => $this->width,
            "model" => $this->model,
            "steps" => $this->steps,
            "CFGScale" => $this->CFGScale,
            "numberResults" => $this->numberResults
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Realiza outpainting em uma imagem
     */
    public function outpainting(string $text, string $seedImage, array $outpaintingOptions, float $strength = 0.8): string
    {
        $response = $this->post([
            "taskType" => "imageInference",
            "taskUUID" => $this->generateUUID(),
            "outputType" => $this->outputType,
            "outputFormat" => $this->outputFormat,
            "positivePrompt" => $text,
            "negativePrompt" => $this->negativePrompt,
            "seedImage" => $seedImage,
            "outpainting" => $outpaintingOptions,
            "strength" => $strength,
            "height" => $this->height,
            "width" => $this->width,
            "model" => $this->model,
            "steps" => $this->steps,
            "CFGScale" => $this->CFGScale,
            "numberResults" => $this->numberResults
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Gera um UUID v4
     */
    private function generateUUID(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function addImage(string $image): self
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Realiza uma requisição POST para a API
     */
    private function post(array $data)
    {
        $client = new Client();

        try {
            $response = $client->post($this->apiUrl, [
                'json' => [$data],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->apiKey
                ]
            ]);

            return $response->getBody()->getContents();
        } catch (ClientException $e) {
            throw new Exception("Runware API Error: " . $e->getResponse()->getBody()->getContents());
        } catch (ServerException $e) {
            throw new Exception("Runware Server Error: " . $e->getResponse()->getBody()->getContents());
        } catch (Exception $e) {
            throw new Exception("Error connecting to Runware API: " . $e->getMessage());
        }
    }

    /**
     * Processa a resposta da API
     */
    private function handleResponse($response)
    {
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error decoding JSON response: " . json_last_error_msg());
        }

        if (!isset($data['data'][0])) {
            throw new Exception("API response does not contain data");
        }

        $result = $data['data'][0];

        if ($this->outputType === 'URL' && isset($result['imageURL'])) {
            return $result['imageURL'];
        } elseif ($this->outputType === 'base64Data' && isset($result['imageBase64Data'])) {
            return $result['imageBase64Data'];
        } elseif ($this->outputType === 'dataURI' && isset($result['imageDataURI'])) {
            return $result['imageDataURI'];
        }

        throw new Exception("Requested output type not found in response");
    }

    public function imageUpload(string $imagePath)
    {
        $requestBody = [
            'taskType' => 'imageUpload',
            'taskUUID' => $this->generateUUID(),
            'image' => base64_encode(file_get_contents($imagePath))
        ];

        $response = $this->post($requestBody);

        return $response;
    }

    /**
     * Gera imagens personalizadas usando a tecnologia PhotoMaker
     * 
     * @param string $positivePrompt Texto descritivo para guiar a geração da imagem

     * @return string Resposta da API no formato configurado
     */
    public function photoMaker(string $positivePrompt): string
    {
        if ($this->images === []) {
            throw new Exception("No images were provided");
        }

        $response = $this->post([
            "taskType" => "photoMaker",
            'model' => 'civitai:133005@288982',
            "taskUUID" => $this->generateUUID(),
            "inputImages" => $this->images,
            "style" => 'Photographic',
            "strength" => 30,
            "positivePrompt" => $positivePrompt,
            "height" => $this->height,
            "width" => $this->width,
            "steps" => $this->steps,
            "CFGScale" => $this->CFGScale,
            "outputFormat" => $this->outputFormat,
            "numberResults" => $this->numberResults
        ]);

        return $this->handleResponse($response);
    }
}