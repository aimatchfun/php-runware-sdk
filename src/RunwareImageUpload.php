<?php

namespace Daavelar\PhpRunwareSDK;

class RunwareImageUpload
{
    public function run(string $imagePath)
    {
        $requestBody = [
            'taskType' => 'imageUpload',
            'taskUUID' => $this->generateUUID(),
            'image' => base64_encode(file_get_contents($imagePath))
        ];

        $response = $this->post($requestBody);

        return $response;
    }
}
