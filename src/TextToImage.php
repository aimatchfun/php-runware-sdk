<?php

namespace AIMatchFun\PhpRunwareSDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Exception;
use InvalidArgumentException;
use Daavelar\PhpRunwareSDK\OutputType;
use Daavelar\PhpRunwareSDK\OutputFormat;
use Daavelar\PhpRunwareSDK\PromptWeighting;
use Daavelar\PhpRunwareSDK\ControlNet;
use Daavelar\PhpRunwareSDK\FluxScheduler;
use Daavelar\PhpRunwareSDK\ModelAir;

class TextToImage
{
    private string $apiKey;
    private string $apiUrl = 'https://api.runware.ai/v1';
    private int $height = 512;
    private int $width = 512;
    private string $model = 'runware:default';
    private int $steps = 20;
    private float $CFGScale = 7.0;
    private int $numberResults = 1;
    private string $outputType = OutputType::URL;
    private string $outputFormat = OutputFormat::JPG;
    private string $negativePrompt = '';
    private bool $nsfw = true;
    private string $scheduler = FluxScheduler::DPM_PLUS_PLUS_2M->value;
    private string $promptWeighting = PromptWeighting::SD_EMBEDS;
    private ModelAir $vae = '';
    private Embedding $embeddings = null;
    private ControlNet $controlNet = null;
    private Refiner $refiner = null;
    private string $ipAdapters = null;
    private array $images = [];
    private array $loras = [];
    private bool $teaCache = false;
    private float $teaCacheDistance = 0.5;
    private bool $deepCache = false;
    private int $deepCacheInterval = 3;
    private int $deepCacheBranchId = 0;

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
    public function modelAir(string $model): self
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
    public function withOutputType(OutputType $type): self
    {
        $this->outputType = $type;
        return $this;
    }

    /**
     * Define o formato de saída (JPG, PNG, WEBP)
     */
    public function withOutputFormat(OutputFormat $format): self
    {
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
     * Enable or disable TeaCache feature for transformer-based models
     * @param bool $enabled Whether to enable TeaCache
     */
    public function withTeaCache(bool $enabled): self
    {
        $this->teaCache = $enabled;
        return $this;
    }

    /**
     * Set the TeaCache distance (aggressiveness)
     * @param float $distance Value between 0.0 (conservative) and 1.0 (aggressive)
     * @throws InvalidArgumentException if distance is not between 0 and 1
     */
    public function withTeaCacheDistance(float $distance): self
    {
        if ($distance < 0 || $distance > 1) {
            throw new InvalidArgumentException('TeaCache distance must be between 0 and 1');
        }
        $this->teaCacheDistance = $distance;
        return $this;
    }

    /**
     * Enable or disable DeepCache feature for UNet-based models
     * @param bool $enabled Whether to enable DeepCache
     */
    public function withDeepCache(bool $enabled): self
    {
        $this->deepCache = $enabled;
        return $this;
    }

    /**
     * Set the DeepCache interval
     * @param int $interval Number of steps between each cache operation (minimum 1)
     * @throws InvalidArgumentException if interval is less than 1
     */
    public function withDeepCacheInterval(int $interval): self
    {
        if ($interval < 1) {
            throw new InvalidArgumentException('DeepCache interval must be at least 1');
        }
        $this->deepCacheInterval = $interval;
        return $this;
    }

    /**
     * Set the DeepCache branch ID
     * @param int $branchId Branch ID for caching processes (minimum 0)
     * @throws InvalidArgumentException if branchId is less than 0
     */
    public function withDeepCacheBranchId(int $branchId): self
    {
        if ($branchId < 0) {
            throw new InvalidArgumentException('DeepCache branch ID must be at least 0');
        }
        $this->deepCacheBranchId = $branchId;
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
            'outputType' => OutputType::BASE64_DATA,
            'positivePrompt' => $text,
            'negativePrompt' => $this->negativePrompt,
            'height' => $this->height,
            'checkNSFW' => $this->nsfw,
            'width' => $this->width,
            'model' => $this->model,
            'steps' => $this->steps,
            'vae' => $this->vae,
            'scheduler' => $this->scheduler,
            'CFGScale' => $this->CFGScale,
            'clipSkip' => $this->clipSkip,
            'promptWeighting' => $this->promptWeighting,
            'numberResults' => $this->numberResults,
            'acceleratorOptions' => [
                'teaCache' => $this->teaCache,
                'teaCacheDistance' => $this->teaCacheDistance,
                'deepCache' => $this->deepCache,
                'deepCacheInterval' => $this->deepCacheInterval,
                'deepCacheBranchId' => $this->deepCacheBranchId,
            ],
            'refiner' => $this->refiner,
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
            'acceleratorOptions' => [
                'teaCache' => $this->teaCache,
                'teaCacheDistance' => $this->teaCacheDistance,
                'deepCache' => $this->deepCache,
                'deepCacheInterval' => $this->deepCacheInterval,
                'deepCacheBranchId' => $this->deepCacheBranchId,
            ],
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
}