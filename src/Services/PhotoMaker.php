<?php

namespace AiMatchFun\PhpRunwareSDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use AiMatchFun\PhpRunwareSDK\OutputType;
use AiMatchFun\PhpRunwareSDK\OutputFormat;
use AiMatchFun\PhpRunwareSDK\RunwareModel;
use AiMatchFun\PhpRunwareSDK\Scheduler;
use Exception;
use InvalidArgumentException;

class PhotoMaker
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
    private int $clipSkip = 0;
    
    // PhotoMaker specific properties
    private array $inputImages = [];
    private string $style = 'photographic';
    private float $strength = 0.8;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Generates an image using PhotoMaker with reference images
     *
     * @return string The generated image data according to the specified output type
     * @throws Exception If API request fails or response is invalid
     */
    public function run(): string
    {
        $requestBody = $this->mountRequestBody();

        $response = $this->post($requestBody);

        return $this->handleResponse($response);
    }

    /**
     * Mounts the request body for PhotoMaker image generation
     *
     * @return array The request body
     */
    private function mountRequestBody(): array
    {
        if (empty($this->positivePrompt)) {
            throw new InvalidArgumentException("Positive prompt is required");
        }
        if (empty($this->inputImages)) {
            throw new InvalidArgumentException("Input images are required for PhotoMaker");
        }
        if (count($this->inputImages) > 4) {
            throw new InvalidArgumentException("PhotoMaker supports a maximum of 4 input images");
        }

        $requestBody = [
            'taskType' => 'imageInference',
            'taskUUID' => $this->generateUUID(),
            'outputType' => $this->outputType,
            'outputFormat' => $this->outputFormat,
            'positivePrompt' => $this->positivePrompt,
            'negativePrompt' => $this->negativePrompt,
            'height' => $this->height,
            'width' => $this->width,
            'model' => $this->model,
            'steps' => $this->steps,
            'checkNsfw' => $this->nsfw,
            'CFGScale' => $this->CFGScale,
            'clipSkip' => $this->clipSkip,
            'numberResults' => $this->numberResults,
            'scheduler' => $this->scheduler,
            'inputImages' => $this->inputImages,
            'style' => $this->style,
            'strength' => $this->strength,
            'includeCost' => true,
        ];

        return $requestBody;
    }

    /**
     * Adds an input image for PhotoMaker (up to 4 images)
     *
     * @param string $image The image UUID or URL
     * @return self
     * @throws InvalidArgumentException If maximum number of images is exceeded
     */
    public function addInputImage(string $image): self
    {
        if (count($this->inputImages) >= 4) {
            throw new InvalidArgumentException("PhotoMaker supports a maximum of 4 input images");
        }
        if (empty($image)) {
            throw new InvalidArgumentException("Input image cannot be empty");
        }
        
        $this->inputImages[] = $image;
        return $this;
    }

    /**
     * Sets multiple input images at once (up to 4 images)
     *
     * @param array $images Array of image UUIDs or URLs
     * @return self
     * @throws InvalidArgumentException If maximum number of images is exceeded
     */
    public function inputImages(array $images): self
    {
        if (count($images) > 4) {
            throw new InvalidArgumentException("PhotoMaker supports a maximum of 4 input images");
        }
        if (empty($images)) {
            throw new InvalidArgumentException("Input images array cannot be empty");
        }
        
        $this->inputImages = $images;
        return $this;
    }

    /**
     * Sets the style for PhotoMaker
     *
     * @param string $style The style to apply (e.g., 'photographic', 'anime', 'artistic', etc.)
     * @return self
     */
    public function style(string $style): self
    {
        $this->style = $style;
        return $this;
    }

    /**
     * Sets the strength of the PhotoMaker effect
     *
     * @param float $strength Strength value (between 0.0 and 1.0)
     * @return self
     * @throws InvalidArgumentException If strength value is invalid
     */
    public function strength(float $strength): self
    {
        if ($strength < 0.0 || $strength > 1.0) {
            throw new InvalidArgumentException('Strength must be between 0.0 and 1.0');
        }
        $this->strength = $strength;
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
     * Sets the CLIP skip value for text encoding
     *
     * @param int $skip Number of layers to skip (0-2)
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
     * Makes a POST request to the Runware API
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
     * Processes the API response and extracts the appropriate output format
     *
     * @param string $response The raw API response
     * @return string The processed image data in the requested format
     * @throws Exception If response processing fails or output type is not found
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
     * Generates an image using PhotoMaker asynchronously
     *
     * @return \GuzzleHttp\Promise\PromiseInterface The promise that will resolve to the generated image data
     * @throws Exception If API request fails or response is invalid
     */
    public function runAsync()
    {
        $requestBody = $this->mountRequestBody();
        return $this->postAsync($requestBody);
    }

    /**
     * Generates a UUID v4
     *
     * @return string The generated UUID
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
}
