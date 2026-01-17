<?php

namespace AiMatchFun\PhpRunwareSDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use AiMatchFun\PhpRunwareSDK\OutputType;
use AiMatchFun\PhpRunwareSDK\OutputFormat;
use AiMatchFun\PhpRunwareSDK\PromptWeighting;
use AiMatchFun\PhpRunwareSDK\RunwareModel;
use AiMatchFun\PhpRunwareSDK\RunwareResponse;
use AiMatchFun\PhpRunwareSDK\Scheduler;
use Exception;
use InvalidArgumentException;

class ImageInference
{
    private string $apiKey;
    private string $apiUrl = 'https://api.runware.ai/v1';
    private string $positivePrompt = '';
    private int $height = 1024;
    private int $width = 1024;
    private string $model = 'civitai:618692@691639';
    private int $steps = 20;
    private float $CFGScale = 7.0;
    private int $numberResults = 1;
    private string $outputType = OutputType::URL->value;
    private string $outputFormat = OutputFormat::JPG->value;
    private string $negativePrompt = '';
    private bool $nsfw = false;
    private string $scheduler = Scheduler::EULER_A->value;
    private string $promptWeighting = PromptWeighting::SD_EMBEDS->value;
    private bool $usePromptWeighting = false;
    private ?RunwareModel $vae = null;
    private ?Refiner $refiner = null;
    private array $embeddings = [];
    private array $controlNet = [];
    private array $ipAdapters = [];
    private array $images = [];
    private array $loras = [];
    private bool $teaCache = false;
    private float $teaCacheDistance = 0.5;
    private bool $deepCache = false;
    private int $deepCacheInterval = 3;
    private int $deepCacheBranchId = 0;
    private int $clipSkip = 0;
    private ?string $seedImage = null;
    private array $referenceImages = [];
    private array $disabledFields = [];

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Generates an image from the provided text prompt
     *
     * @return RunwareResponse The response containing image data, cost, seed, UUIDs, etc.
     * @throws Exception If API request fails or response is invalid
     */
    public function run(): RunwareResponse
    {
        $requestBody = $this->mountRequestBody();

        $response = $this->post($requestBody);

        return $this->handleResponse($response);
    }

    /**
     * Mounts the request body for the image generation
     *
     * @return array The request body
     */
    private function mountRequestBody(): array
    {
        if (empty($this->positivePrompt)) {
            throw new InvalidArgumentException("Positive prompt is required");
        }

        $requestBody = [
            'taskType' => 'imageInference',
            'taskUUID' => $this->generateUUID(),
            'outputType' => $this->outputType,
            'outputFormat' => $this->outputFormat,
            'positivePrompt' => $this->positivePrompt,
            'height' => $this->height,
            'width' => $this->width,
            'model' => $this->model,
            'checkNsfw' => $this->nsfw,
            'CFGScale' => $this->CFGScale,
            'clipSkip' => $this->clipSkip,
            'numberResults' => $this->numberResults,
            'scheduler' => $this->scheduler,
            'includeCost' => true,
            'steps' => $this->steps,
        ];

        if (!empty($this->negativePrompt)) {
            $requestBody['negativePrompt'] = $this->negativePrompt;
        }

        if (!empty($this->images)) {
            $requestBody['images'] = $this->images;
        }

        if (!empty($this->embeddings)) {
            $requestBody['embeddings'] = array_map(fn($embedding) => $embedding->value, $this->embeddings);
        }

        if (!empty($this->controlNet)) {
            $requestBody['controlNet'] = array_map(fn($control) => $control->value, $this->controlNet);
        }

        if (!empty($this->ipAdapters)) {
            $requestBody['ipAdapters'] = array_map(fn($adapter) => $adapter->value, $this->ipAdapters);
        }

        if (!empty($this->loras)) {
            $requestBody['lora'] = $this->loras;
        }

        $acceleratorOptions = [];

        if ($this->teaCache !== false) {
            $acceleratorOptions['teaCache'] = $this->teaCache;
        }

        if ($this->teaCacheDistance !== 0.5) {
            $acceleratorOptions['teaCacheDistance'] = $this->teaCacheDistance;
        }

        if ($this->deepCache !== false) {
            $acceleratorOptions['deepCache'] = $this->deepCache;
        }

        if ($this->deepCacheInterval !== 3) {
            $acceleratorOptions['deepCacheInterval'] = $this->deepCacheInterval;
        }

        if ($this->deepCacheBranchId !== 0) {
            $acceleratorOptions['deepCacheBranchId'] = $this->deepCacheBranchId;
        }

        if (!empty($acceleratorOptions)) {
            $requestBody['acceleratorOptions'] = $acceleratorOptions;
        }

        if ($this->usePromptWeighting) {
            $requestBody['promptWeighting'] = $this->promptWeighting;
        }

        if ($this->vae !== null) {
            $requestBody['vae'] = $this->vae->value;
        }

        if (!empty($this->seedImage)) {
            $requestBody['seedImage'] = $this->seedImage;
        }

        if (!empty($this->referenceImages)) {
            $requestBody['inputs'] = [
                'referenceImages' => $this->referenceImages
            ];
        }

        foreach ($this->disabledFields as $field) {
            unset($requestBody[$field]);
        }

        return $requestBody;
    }

    /**
     * Adds a refiner model to the image generation process
     *
     * @param Refiner $refiner The refiner model to add
     * @return self
     */
    public function addRefiner (Refiner $refiner): self
    {
        $this->refiner = $refiner;
        return $this;
    }

    /**
     * Adds an embedding model to the image generation process
     *
     * @param RunwareModel $embedding The embedding model to add
     * @return self
     */
    public function addEmbedding (RunwareModel $embedding): self
    {
        $this->embeddings[] = $embedding;
        return $this;
    }

    /**
     * Adds a ControlNet model to guide the image generation
     *
     * @param RunwareModel $controlNet The ControlNet model to add
     * @return self
     */
    public function addControlNet (RunwareModel $controlNet): self
    {
        $this->controlNet[] = $controlNet;
        return $this;
    }

    /**
     * Adds an IP-Adapter model for image conditioning
     *
     * @param RunwareModel $ipAdapter The IP-Adapter model to add
     * @return self
     */
    public function addIpAdapter (RunwareModel $ipAdapter): self
    {
        $this->ipAdapters[] = $ipAdapter;
        return $this;
    }

    /**
     * Sets the positive prompt for image generation
     *
     * @param string $prompt The positive prompt text
     * @return self
     */
    public function positivePrompt(string $prompt): self
    {
        $this->positivePrompt = $prompt;
        return $this;
    }

    public function scheduler(Scheduler $scheduler): self
    {
        $this->scheduler = $scheduler->value;
        return $this;
    }

    /**
     * Sets the width of the image to be generated
     *
     * @param int $width The width value (must be between 128 and 2048 and divisible by 64)
     * @return self
     * @throws InvalidArgumentException If width is invalid
     */
    public function width(int $width): self
    {
        if ($width < 128 || $width > 2048 || $width % 64 !== 0) {
            throw new InvalidArgumentException('Width must be between 128 and 2048 and divisible by 64');
        }
        $this->width = $width;
        return $this;
    }

    /**
     * Sets the height of the image to be generated
     *
     * @param int $height The height value (must be between 128 and 2048 and divisible by 64)
     * @return self
     * @throws InvalidArgumentException If height is invalid
     */
    public function height(int $height): self
    {
        if ($height < 128 || $height > 2048 || $height % 64 !== 0) {
            throw new InvalidArgumentException('Height must be between 128 and 2048 and divisible by 64');
        }
        $this->height = $height;
        return $this;
    }

    /**
     * Sets the model to be used for image generation
     *
     * @param RunwareModel|string $model The model enum or string
     * @return self
     */
    public function model(RunwareModel|string $model): self
    {
        $this->model = $model instanceof RunwareModel ? $model->value : $model;
        return $this;
    }

    /**
     * Sets the number of steps for image generation
     *
     * @param int $steps Number of steps (between 1 and 100)
     * @return self
     * @throws InvalidArgumentException If steps value is invalid
     */
    public function steps(int $steps): self
    {
        if ($steps < 1 || $steps > 100) {
            throw new InvalidArgumentException('Number of steps must be between 1 and 100');
        }
        $this->steps = $steps;
        return $this;
    }

    /**
     * Sets the CFG scale for image generation
     *
     * @param float $scale CFG scale value (between 0 and 30)
     * @return self
     * @throws InvalidArgumentException If scale value is invalid
     */
    public function cfgScale(float $scale): self
    {
        if ($scale < 0 || $scale > 30) {
            throw new InvalidArgumentException('CFG Scale must be between 0 and 30');
        }
        $this->CFGScale = $scale;
        return $this;
    }

    /**
     * Sets the number of results to be generated
     *
     * @param int $number Number of results (between 1 and 20)
     * @return self
     * @throws InvalidArgumentException If number is invalid
     */
    public function numberResults(int $number): self
    {
        if ($number < 1 || $number > 20) {
            throw new InvalidArgumentException('Number of results must be between 1 and 20');
        }
        $this->numberResults = $number;
        return $this;
    }

    /**
     * Sets the output type for the generated image
     *
     * @param OutputType $type The output type (URL, base64Data, dataURI)
     * @return self
     */
    public function outputType(OutputType $type): self
    {
        $this->outputType = $type->value;
        return $this;
    }

    /**
     * Sets the output format for the generated image
     *
     * @param OutputFormat $format The output format (JPG, PNG, WEBP)
     * @return self
     */
    public function outputFormat(OutputFormat $format): self
    {
        $this->outputFormat = $format->value;

        return $this;
    }

    /**
     * Sets whether NSFW content is allowed
     *
     * @param bool $nsfw True to allow NSFW content, false to disallow
     * @return self
     */
    public function nsfw(bool $nsfw): self
    {
        $this->nsfw = $nsfw;
        return $this;
    }

    /**
     * Sets the negative prompt for image generation
     *
     * @param string $prompt The negative prompt text
     * @return self
     */
    public function negativePrompt(string $prompt): self
    {
        $this->negativePrompt = $prompt;
        return $this;
    }

    /**
     * Adds a LoRA model to the generation process
     *
     * @param string $model The LoRA model identifier
     * @param float $weight The weight to apply to the LoRA model (default: 1.0)
     * @return self
     */
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
    public function teaCache(bool $enabled): self
    {
        $this->teaCache = $enabled;
        return $this;
    }

    /**
     * Set the TeaCache distance (aggressiveness)
     * @param float $distance Value between 0.0 (conservative) and 1.0 (aggressive)
     * @throws InvalidArgumentException if distance is not between 0 and 1
     */
    public function teaCacheDistance(float $distance): self
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
    public function deepCache(bool $enabled): self
    {
        $this->deepCache = $enabled;
        return $this;
    }

    /**
     * Set the DeepCache interval
     * @param int $interval Number of steps between each cache operation (minimum 1)
     * @throws InvalidArgumentException if interval is less than 1
     */
    public function deepCacheInterval(int $interval): self
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
    public function deepCacheBranchId(int $branchId): self
    {
        if ($branchId < 0) {
            throw new InvalidArgumentException('DeepCache branch ID must be at least 0');
        }
        $this->deepCacheBranchId = $branchId;
        return $this;
    }

    /**
     * Sets the CLIP skip value for text encoding
     *
     * @param int $skip Number of layers to skip (1-12)
     * @return self
     * @throws InvalidArgumentException If skip value is invalid
     */
    public function clipSkip(int $skip): self
    {
        if ($skip < 0 || $skip > 2) {
            throw new InvalidArgumentException('CLIP skip must be between 0 and 2');
        }
        $this->clipSkip = $skip;
        return $this;
    }

    /**
     * Sets the seed image for image generation
     *
     * @param string $image The image UUID or URL
     * @return self
     */
    public function seedImage(string $image): self
    {
        $this->seedImage = $image;
        return $this;
    }

    /**
     * Converts the current configuration to JSON format
     *
     * @param bool $prettyPrint Whether to format the JSON output (default: false)
     * @return string The JSON representation of the configuration
     */
    public function toJson($prettyPrint = false): string
    {
        $requestBody = $this->mountRequestBody();

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

    /**
     * Adds an image to the generation process
     *
     * @param string $image The image data or URL
     * @return self
     */
    public function addImage(string $image): self
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Adds a reference image for the generation process
     *
     * @param string $image The reference image URL or UUID
     * @return self
     */
    public function addReferenceImage(string $image): self
    {
        if (empty($image)) {
            throw new InvalidArgumentException("Reference image cannot be empty");
        }

        $this->referenceImages[] = $image;
        return $this;
    }

    /**
     * Sets multiple reference images at once
     *
     * @param array $images Array of reference image URLs or UUIDs
     * @return self
     */
    public function referenceImages(array $images): self
    {
        if (empty($images)) {
            throw new InvalidArgumentException("Reference images array cannot be empty");
        }

        $this->referenceImages = $images;
        return $this;
    }

    /**
     * Makes a POST request to the Runware API"204a9804-5714-43d6-a318-b8b6d4d2e0af"
     *
     * @param array $data The request data
     * @return string The API response
     * @throws Exception If the API request fails
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
     * Processes the API response and returns a RunwareResponse DTO
     *
     * @param string $response The raw API response
     * @return RunwareResponse The response DTO containing all image data
     * @throws Exception If response processing fails
     */
    private function handleResponse($response): RunwareResponse
    {
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error decoding JSON response: " . json_last_error_msg());
        }

        if (!isset($data['data']) || !is_array($data['data'])) {
            throw new Exception("API response does not contain data");
        }

        return RunwareResponse::fromArray($data);
    }

    /**
     * Sets the VAE model for image generation
     *
     * @param RunwareModel $vae The VAE model to use
     * @return self
     */
    public function vae(RunwareModel $vae): self
    {
        $this->vae = $vae;
        return $this;
    }

    /**
     * Makes a POST request to the Runware API asynchronously
     *
     * @param array $data The request data
     * @return \GuzzleHttp\Promise\PromiseInterface The promise that will resolve to the API response
     * @throws Exception If the API request fails
     */
    private function postAsync(array $data)
    {
        $client = new Client();

        return $client->postAsync($this->apiUrl, [
            'json' => [$data],
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey
            ]
        ])->then(
            function ($response) {
                return $this->handleResponse($response->getBody()->getContents());
            },
            function ($exception) {
                if ($exception instanceof ClientException) {
                    throw new Exception("Runware API Error: " . $exception->getResponse()->getBody()->getContents());
                } elseif ($exception instanceof ServerException) {
                    throw new Exception("Runware Server Error: " . $exception->getResponse()->getBody()->getContents());
                } else {
                    throw new Exception("Error connecting to Runware API: " . $exception->getMessage());
                }
            }
        );
    }

    /**
     * Generates an image from the provided text prompt asynchronously
     *
     * @return \GuzzleHttp\Promise\PromiseInterface The promise that will resolve to RunwareResponse
     * @throws Exception If API request fails or response is invalid
     */
    public function runAsync()
    {
        $requestBody = $this->mountRequestBody();
        return $this->postAsync($requestBody);
    }

    public function promptWeighting(PromptWeighting $weighting): self
    {
        $this->promptWeighting = $weighting->value;
        $this->usePromptWeighting = true;
        return $this;
    }

    /**
     * Disables specific fields from being included in the request body
     *
     * @param array|string ...$fields Field names to disable (can be passed as array or multiple arguments)
     * @return self
     */
    public function disableFields(array|string ...$fields): self
    {
        $fieldsToDisable = [];

        foreach ($fields as $field) {
            if (is_array($field)) {
                $fieldsToDisable = array_merge($fieldsToDisable, $field);
            } else {
                $fieldsToDisable[] = $field;
            }
        }

        $this->disabledFields = array_unique(array_merge($this->disabledFields, $fieldsToDisable));

        return $this;
    }
}