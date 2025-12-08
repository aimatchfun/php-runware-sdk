<?php

declare(strict_types=1);

namespace Tests;

use AiMatchFun\PhpRunwareSDK\ModelAir;
use AiMatchFun\PhpRunwareSDK\OutputFormat;
use AiMatchFun\PhpRunwareSDK\OutputType;
use AiMatchFun\PhpRunwareSDK\TextToImage;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class TextToImageTest extends TestCase
{
    private TextToImage $textToImage;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->textToImage = new TextToImage('test-api-key');
        $this->mockHandler = new MockHandler();
    }

    /**
     * Mock the HTTP client using reflection
     */
    private function mockHttpClient(Response $response): void
    {
        $handlerStack = HandlerStack::create($this->mockHandler);
        $mockClient = new Client(['handler' => $handlerStack]);
        
        $this->mockHandler->append($response);

        // Use reflection to replace the post method
        $reflection = new ReflectionClass($this->textToImage);
        $postMethod = $reflection->getMethod('post');
        $postMethod->setAccessible(true);

        // Create a closure that uses our mock client
        $originalPost = $postMethod->getClosure($this->textToImage);
        
        // Replace the post method to use our mock
        $textToImageReflection = new ReflectionClass($this->textToImage);
        $textToImageReflection->setStaticPropertyValue('mockClient', $mockClient);
    }

    public function testCanSetPositivePrompt(): void
    {
        $result = $this->textToImage->positivePrompt('A beautiful sunset');
        
        $this->assertSame($this->textToImage, $result);
    }

    public function testCanSetNegativePrompt(): void
    {
        $result = $this->textToImage->negativePrompt('blur, low quality');
        
        $this->assertSame($this->textToImage, $result);
    }

    public function testCanSetWidth(): void
    {
        $result = $this->textToImage->width(512);
        
        $this->assertSame($this->textToImage, $result);
    }

    public function testThrowsExceptionForInvalidWidth(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->textToImage->width(100); // Invalid: not divisible by 64
    }

    public function testCanSetHeight(): void
    {
        $result = $this->textToImage->height(768);
        
        $this->assertSame($this->textToImage, $result);
    }

    public function testCanSetModel(): void
    {
        $result = $this->textToImage->model(ModelAir::REAL_DREAM_SDXL_PONY_14);
        
        $this->assertSame($this->textToImage, $result);
    }

    public function testCanSetSteps(): void
    {
        $result = $this->textToImage->steps(30);
        
        $this->assertSame($this->textToImage, $result);
    }

    public function testThrowsExceptionForInvalidSteps(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->textToImage->steps(101); // Invalid: must be between 1 and 100
    }

    public function testCanSetCfgScale(): void
    {
        $result = $this->textToImage->cfgScale(7.5);
        
        $this->assertSame($this->textToImage, $result);
    }

    public function testCanSetOutputType(): void
    {
        $result = $this->textToImage->outputType(OutputType::URL);
        
        $this->assertSame($this->textToImage, $result);
    }

    public function testCanSetOutputFormat(): void
    {
        $result = $this->textToImage->outputFormat(OutputFormat::PNG);
        
        $this->assertSame($this->textToImage, $result);
    }

    public function testCanGenerateJsonConfiguration(): void
    {
        $this->textToImage
            ->positivePrompt('A beautiful landscape')
            ->negativePrompt('blur')
            ->width(512)
            ->height(512)
            ->steps(20);

        $json = $this->textToImage->toJson(true);
        
        $this->assertIsString($json);
        $this->assertStringContainsString('positivePrompt', $json);
        $this->assertStringContainsString('A beautiful landscape', $json);
    }

    public function testRunReturnsImageUrlWhenOutputTypeIsUrl(): void
    {
        // Este teste está incompleto e não pode ser facilmente testado sem mock
        // Use TextToImageWrapperTest ou TextToImageMockableTest para testes completos
        // Este teste foi removido para evitar chamadas reais à API
        $this->markTestSkipped('Use TextToImageWrapperTest para testes com mocks');
    }

    /**
     * Helper method to create a mock response
     */
    private function createMockResponse(array $data): Response
    {
        return new Response(200, [], json_encode($data));
    }
}

