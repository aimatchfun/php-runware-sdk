<?php

namespace AiMatchFun\PhpRunwareSDK;

class RunwareImageResponse
{
    public function __construct(
        public readonly string $taskType,
        public readonly string $imageUUID,
        public readonly string $taskUUID,
        public readonly ?int $seed = null,
        public readonly ?string $imageURL = null,
        public readonly ?string $imageBase64Data = null,
        public readonly ?string $imageDataURI = null,
        public readonly ?float $cost = null,
    ) {
    }

    /**
     * Creates a RunwareImageResponse from an array
     *
     * @param array $data The image data array
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            taskType: $data['taskType'] ?? '',
            imageUUID: $data['imageUUID'] ?? '',
            taskUUID: $data['taskUUID'] ?? '',
            seed: $data['seed'] ?? null,
            imageURL: $data['imageURL'] ?? null,
            imageBase64Data: $data['imageBase64Data'] ?? null,
            imageDataURI: $data['imageDataURI'] ?? null,
            cost: $data['cost'] ?? null,
        );
    }

    /**
     * Gets the image data based on the output type
     *
     * @param string $outputType The output type (URL, base64Data, dataURI)
     * @return string|null The image data or null if not found
     */
    public function getImageData(string $outputType): ?string
    {
        return match ($outputType) {
            'URL' => $this->imageURL,
            'base64Data' => $this->imageBase64Data,
            'dataURI' => $this->imageDataURI,
            default => null,
        };
    }
}

