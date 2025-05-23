<?php

namespace AIMatchFun\PhpRunwareSDK;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\RequestException;

class RunwareImageUpload
{
    public function run(string $imagePath): Response
    {
        $requestBody = [
            'taskType' => 'imageUpload',
            'taskUUID' => Uuid::uuid4()->toString(),
            'image' => base64_encode(file_get_contents($imagePath))
        ];

        try {
            $response = Http::post('https://api.runware.ai/v1', $requestBody);
            $response->throw();
            
            return $response;
        } catch (RequestException $e) {
            throw new \RuntimeException('Failed to upload image: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
