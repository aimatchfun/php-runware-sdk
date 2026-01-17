<?php

namespace AiMatchFun\PhpRunwareSDK;

class RunwareResponse
{
    /**
     * @param array<RunwareImageResponse> $images Array of image responses
     */
    public function __construct(
        public readonly array $images = []
    ) {
    }

    /**
     * Creates a RunwareResponse from the API response array
     *
     * @param array $data The API response data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $images = [];
        
        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $imageData) {
                $images[] = RunwareImageResponse::fromArray($imageData);
            }
        }

        return new self($images);
    }

    /**
     * Gets the first image response
     *
     * @return RunwareImageResponse|null
     */
    public function first(): ?RunwareImageResponse
    {
        return $this->images[0] ?? null;
    }

    /**
     * Gets all image responses
     *
     * @return array<RunwareImageResponse>
     */
    public function all(): array
    {
        return $this->images;
    }

    /**
     * Gets the count of images
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->images);
    }

    /**
     * Gets the image data from the first image based on output type
     *
     * @param string $outputType The output type (URL, base64Data, dataURI)
     * @return string|null The image data or null if not found
     */
    public function getImageData(string $outputType): ?string
    {
        $first = $this->first();
        return $first?->getImageData($outputType);
    }

    /**
     * Gets an item at a specific index
     *
     * @param int $index The index of the item
     * @return RunwareImageResponse|null The image response at the index or null if not found
     */
    public function get(int $index): ?RunwareImageResponse
    {
        return $this->images[$index] ?? null;
    }

    /**
     * Gets an item at a specific index (alias for get)
     *
     * @param int $index The index of the item
     * @return RunwareImageResponse|null The image response at the index or null if not found
     */
    public function eq(int $index): ?RunwareImageResponse
    {
        return $this->get($index);
    }
}

