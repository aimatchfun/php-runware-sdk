<?php

/**
 * Exemplo de uso do TextToImageWrapper para testes sem chamar a API real
 * 
 * Este arquivo demonstra como usar o wrapper para testar sem gastar créditos da Runware
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Tests\TextToImageWrapper;
use AiMatchFun\PhpRunwareSDK\ModelAir;
use AiMatchFun\PhpRunwareSDK\OutputType;
use AiMatchFun\PhpRunwareSDK\OutputFormat;
use GuzzleHttp\Psr7\Response;

// Criar instância com mock handler
[$textToImage, $mockHandler] = TextToImageWrapper::withMockHandler('test-api-key');

// Configurar resposta mockada da API
$mockResponse = new Response(200, [], json_encode([
    'data' => [
        [
            'taskType' => 'imageInference',
            'taskUUID' => 'test-task-uuid-123',
            'imageUUID' => 'test-image-uuid-456',
            'imageURL' => 'https://example.com/generated-image.png',
            'seed' => 12345,
            'cost' => 0.5,
        ]
    ]
]));

$mockHandler->append($mockResponse);

// Usar normalmente - nenhuma chamada real será feita!
echo "=== Testando sem chamar a API real ===\n\n";

try {
    $result = $textToImage
        ->positivePrompt('A beautiful sunset over the ocean')
        ->negativePrompt('blur, low quality, distorted')
        ->width(512)
        ->height(512)
        ->model(ModelAir::REAL_DREAM_SDXL_PONY_14)
        ->steps(20)
        ->cfgScale(7.5)
        ->numberResults(1)
        ->outputType(OutputType::URL)
        ->outputFormat(OutputFormat::PNG)
        ->run();

    echo "✅ Sucesso! URL da imagem: $result\n";
    echo "\nNenhuma chamada real foi feita à API da Runware!\n";
} catch (\Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

// Exemplo: Testar múltiplas chamadas
echo "\n=== Testando múltiplas chamadas ===\n";

$mockHandler->append(new Response(200, [], json_encode([
    'data' => [['imageURL' => 'https://example.com/image2.png']]
])));

$result2 = $textToImage
    ->positivePrompt('A futuristic city')
    ->negativePrompt('blur')
    ->run();

echo "✅ Segunda imagem: $result2\n";

// Exemplo: Testar erro da API
echo "\n=== Testando tratamento de erro ===\n";

$mockHandler->append(new Response(400, [], json_encode([
    'error' => 'Invalid API key'
])));

try {
    $textToImage
        ->positivePrompt('Test')
        ->negativePrompt('blur')
        ->run();
    echo "❌ Deveria ter lançado exceção\n";
} catch (\Exception $e) {
    echo "✅ Erro capturado corretamente: " . $e->getMessage() . "\n";
}

