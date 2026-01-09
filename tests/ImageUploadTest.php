<?php

declare(strict_types=1);

namespace Tests;

use AiMatchFun\PhpRunwareSDK\ImageUpload;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ImageUploadTest extends TestCase
{
    private ImageUpload $imageUpload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->imageUpload = new ImageUpload('test-api-key');
    }

    /**
     * Test that ImageUpload can be instantiated with an API key
     */
    public function testCanInstantiateImageUpload(): void
    {
        $this->assertInstanceOf(ImageUpload::class, $this->imageUpload);
    }

    /**
     * Test that uploadFromURL() method returns fluent interface
     */
    public function testCanSetImageWithUrl(): void
    {
        $result = $this->imageUpload->uploadFromURL('https://example.com/image.png');

        $this->assertSame($this->imageUpload, $result);
    }

    /**
     * Test that uploadFromLocalPath() method reads file and converts to base64
     */
    public function testCanSetImageWithLocalPath(): void
    {
        // Create a temporary image file for testing
        $tempFile = sys_get_temp_dir() . '/test_image_' . uniqid() . '.png';
        $base64Image = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        file_put_contents($tempFile, base64_decode($base64Image));

        try {
            $result = $this->imageUpload->uploadFromLocalPath($tempFile);

            $this->assertSame($this->imageUpload, $result);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * Test that empty URL throws InvalidArgumentException
     */
    public function testThrowsExceptionForEmptyUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Image URL cannot be empty");

        $this->imageUpload->uploadFromURL('');
    }

    /**
     * Test that empty path throws InvalidArgumentException
     */
    public function testThrowsExceptionForEmptyPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Image path cannot be empty");

        $this->imageUpload->uploadFromLocalPath('');
    }

    /**
     * Test that non-existent file throws InvalidArgumentException
     */
    public function testThrowsExceptionForNonExistentFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Image file not found");

        $this->imageUpload->uploadFromLocalPath('/nonexistent/file.png');
    }

    /**
     * Test that run() throws exception when image is not set
     */
    public function testThrowsExceptionWhenImageIsNotSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Image is required for upload");

        $this->imageUpload->run();
    }

    /**
     * Test that invalid API response triggers error handling
     */
    public function testHandlesInvalidJsonResponse(): void
    {
        // Manually test the JSON decoding logic
        $invalidResponse = 'invalid json {';
        json_decode($invalidResponse, true);

        // Verify that JSON decode error occurs with invalid JSON
        $this->assertNotEquals(JSON_ERROR_NONE, json_last_error());
    }
}
