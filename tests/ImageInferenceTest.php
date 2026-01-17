<?php

declare(strict_types=1);

namespace Tests;

use AiMatchFun\PhpRunwareSDK\RunwareModel;
use AiMatchFun\PhpRunwareSDK\OutputFormat;
use AiMatchFun\PhpRunwareSDK\OutputType;
use AiMatchFun\PhpRunwareSDK\ImageInference;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class ImageInferenceTest extends TestCase
{
    private ImageInference $imageInference;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->imageInference = new ImageInference('test-api-key');
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
        $reflection = new ReflectionClass($this->imageInference);
        $postMethod = $reflection->getMethod('post');
        $postMethod->setAccessible(true);

        // Create a closure that uses our mock client
        $originalPost = $postMethod->getClosure($this->imageInference);
        
        // Replace the post method to use our mock
        $imageInferenceReflection = new ReflectionClass($this->imageInference);
        $imageInferenceReflection->setStaticPropertyValue('mockClient', $mockClient);
    }

    public function testCanSetPositivePrompt(): void
    {
        $result = $this->imageInference->positivePrompt('A beautiful sunset');
        
        $this->assertSame($this->imageInference, $result);
    }

    public function testCanSetNegativePrompt(): void
    {
        $result = $this->imageInference->negativePrompt('blur, low quality');
        
        $this->assertSame($this->imageInference, $result);
    }

    public function testCanSetWidth(): void
    {
        $result = $this->imageInference->width(512);
        
        $this->assertSame($this->imageInference, $result);
    }

    public function testThrowsExceptionForInvalidWidth(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->imageInference->width(100); // Invalid: not divisible by 64
    }

    public function testCanSetHeight(): void
    {
        $result = $this->imageInference->height(768);
        
        $this->assertSame($this->imageInference, $result);
    }

    public function testCanSetModel(): void
    {
        $result = $this->imageInference->model(RunwareModel::REAL_DREAM_SDXL_PONY_14);
        
        $this->assertSame($this->imageInference, $result);
    }

    public function testCanSetSteps(): void
    {
        $result = $this->imageInference->steps(30);
        
        $this->assertSame($this->imageInference, $result);
    }

    public function testThrowsExceptionForInvalidSteps(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->imageInference->steps(101); // Invalid: must be between 1 and 100
    }

    public function testCanSetCfgScale(): void
    {
        $result = $this->imageInference->cfgScale(7.5);
        
        $this->assertSame($this->imageInference, $result);
    }

    public function testCanSetOutputType(): void
    {
        $result = $this->imageInference->outputType(OutputType::URL);
        
        $this->assertSame($this->imageInference, $result);
    }

    public function testCanSetOutputFormat(): void
    {
        $result = $this->imageInference->outputFormat(OutputFormat::PNG);
        
        $this->assertSame($this->imageInference, $result);
    }

    public function testCanGenerateJsonConfiguration(): void
    {
        $this->imageInference
            ->positivePrompt('A beautiful landscape')
            ->negativePrompt('blur')
            ->width(512)
            ->height(512)
            ->steps(20);

        $json = $this->imageInference->toJson(true);
        
        $this->assertIsString($json);
        $this->assertStringContainsString('positivePrompt', $json);
        $this->assertStringContainsString('A beautiful landscape', $json);
    }

    public function testRunReturnsImageUrlWhenOutputTypeIsUrl(): void
    {
        // Este teste está incompleto e não pode ser facilmente testado sem mock
        // Use ImageInferenceWrapperTest ou ImageInferenceMockableTest para testes completos
        // Este teste foi removido para evitar chamadas reais à API
        $this->markTestSkipped('Use ImageInferenceWrapperTest para testes com mocks');
    }

    /**
     * Helper method to create a mock response
     */
    private function createMockResponse(array $data): Response
    {
        return new Response(200, [], json_encode($data));
    }
}

