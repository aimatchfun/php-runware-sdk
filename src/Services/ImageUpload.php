<?php

namespace AiMatchFun\PhpRunwareSDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Exception;
use InvalidArgumentException;

class ImageUpload
{
    private string $apiKey;
    private string $apiUrl = 'https://api.runware.ai/v1';
    private string $image = '';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Sets the image to upload from a local file path
     *
     * Reads the file from the local filesystem and converts it to base64 format.
     *
     * @param string $path The local file path to the image
     * @return self
     * @throws InvalidArgumentException If the path is empty or file doesn't exist
     * @throws Exception If file cannot be read
     */
    public function uploadFromLocalPath(string $path): self
    {
        if (empty($path)) {
            throw new InvalidArgumentException("Image path cannot be empty");
        }

        if (!file_exists($path)) {
            throw new InvalidArgumentException("Image file not found: {$path}");
        }

        if (!is_readable($path)) {
            throw new Exception("Image file is not readable: {$path}");
        }

        $fileContents = file_get_contents($path);
        if ($fileContents === false) {
            throw new Exception("Failed to read image file: {$path}");
        }

        $base64 = base64_encode($fileContents);
        $this->image = $base64;

        return $this;
    }

    /**
     * Sets the image to upload from a URL
     *
     * @param string $url The public URL of the image
     * @return self
     * @throws InvalidArgumentException If the URL is empty
     */
    public function uploadFromURL(string $url): self
    {
        if (empty($url)) {
            throw new InvalidArgumentException("Image URL cannot be empty");
        }

        $this->image = $url;
        return $this;
    }

    /**
     * Uploads the image to Runware
     *
     * @return string The image UUID returned by the API
     * @throws Exception If API request fails or response is invalid
     * @throws InvalidArgumentException If required parameters are missing
     */
    public function run(): string
    {
        $requestBody = $this->mountRequestBody();
        $response = $this->post($requestBody);
        return $this->handleResponse($response);
    }

    /**
     * Uploads the image to Runware asynchronously
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws InvalidArgumentException If required parameters are missing
     */
    public function runAsync(): \GuzzleHttp\Promise\PromiseInterface
    {
        $requestBody = $this->mountRequestBody();
        return $this->postAsync($requestBody);
    }

    /**
     * Mounts the request body for the image upload
     *
     * @return array The request body
     * @throws InvalidArgumentException If image is not set
     */
    private function mountRequestBody(): array
    {
        if (empty($this->image)) {
            throw new InvalidArgumentException("Image is required for upload");
        }

        return [
            'taskType' => 'imageUpload',
            'taskUUID' => $this->generateUUID(),
            'image' => $this->image,
        ];
    }

    /**
     * Sends a POST request to the API
     *
     * @param array $data The request data
     * @return string The JSON response from the API
     * @throws Exception If the request fails
     */
    private function post(array $data): string
    {
        $client = new Client();

        try {
            $response = $client->post($this->apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ],
                'body' => json_encode([$data]),
            ]);

            return $response->getBody()->getContents();
        } catch (ClientException $e) {
            throw new Exception("Client error: " . $e->getMessage(), 0, $e);
        } catch (ServerException $e) {
            throw new Exception("Server error: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Sends an asynchronous POST request to the API
     *
     * @param array $data The request data
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    private function postAsync(array $data): \GuzzleHttp\Promise\PromiseInterface
    {
        $client = new Client();

        return $client->postAsync($this->apiUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
            'body' => json_encode([$data]),
        ])->then(function ($response) {
            return $this->handleResponse($response->getBody()->getContents());
        });
    }

    /**
     * Handles the API response and extracts the image UUID
     *
     * @param string $response The JSON response from the API
     * @return string The image UUID
     * @throws Exception If response format is invalid or contains an error
     */
    private function handleResponse(string $response): string
    {
        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to decode API response: " . json_last_error_msg());
        }

        if (!isset($decoded['data']) || !is_array($decoded['data'])) {
            throw new Exception("Invalid response format: missing 'data' field");
        }

        $data = $decoded['data'];

        if (!isset($data['imageUUID'])) {
            throw new Exception("Invalid response format: missing 'imageUUID' in response data");
        }

        return $data['imageUUID'];
    }

    /**
     * Generates a UUID v4
     *
     * @return string The generated UUID
     */
    private function generateUUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
