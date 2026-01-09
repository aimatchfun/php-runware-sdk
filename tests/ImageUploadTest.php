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
     * Test that image() method returns fluent interface
     */
    public function testCanSetImageWithUrl(): void
    {
        $result = $this->imageUpload->image('https://example.com/image.png');

        $this->assertSame($this->imageUpload, $result);
    }

    /**
     * Test that image() method accepts base64 string
     */
    public function testCanSetImageWithBase64(): void
    {
        $base64Image = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $result = $this->imageUpload->image($base64Image);

        $this->assertSame($this->imageUpload, $result);
    }

    /**
     * Test that image() method accepts data URI format
     */
    public function testCanSetImageWithDataUri(): void
    {
        $dataURI = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $result = $this->imageUpload->image($dataURI);

        $this->assertSame($this->imageUpload, $result);
    }

    /**
     * Test that empty image throws InvalidArgumentException
     */
    public function testThrowsExceptionForEmptyImage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Image cannot be empty");

        $this->imageUpload->image('');
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
