# Guia de Testes - Como Testar Sem Gastar Créditos da Runware

Este guia explica como testar o `php-runware-sdk` e `laravel-runware` sem fazer chamadas reais à API da Runware, evitando gastar seus créditos.

## 📦 Para php-runware-sdk

### Método Recomendado: TextToImageWrapper

Use a classe `TextToImageWrapper` que permite injetar um mock HTTP client:

```php
use Tests\TextToImageWrapper;
use AiMatchFun\PhpRunwareSDK\ModelAir;
use AiMatchFun\PhpRunwareSDK\OutputType;
use GuzzleHttp\Psr7\Response;

// 1. Criar instância com mock handler
[$textToImage, $mockHandler] = TextToImageWrapper::withMockHandler('test-api-key');

// 2. Configurar resposta mockada
$mockResponse = new Response(200, [], json_encode([
    'data' => [
        [
            'taskType' => 'imageInference',
            'taskUUID' => 'test-task-uuid',
            'imageUUID' => 'test-image-uuid',
            'imageURL' => 'https://example.com/image.png',
            'seed' => 12345,
            'cost' => 0.5,
        ]
    ]
]));

$mockHandler->append($mockResponse);

// 3. Usar normalmente - nenhuma chamada real será feita!
$result = $textToImage
    ->positivePrompt('A beautiful sunset')
    ->negativePrompt('blur')
    ->width(512)
    ->height(512)
    ->model(ModelAir::REAL_DREAM_SDXL_PONY_14)
    ->outputType(OutputType::URL)
    ->run();

echo $result; // https://example.com/image.png
```

### Exemplo Completo de Teste PHPUnit

```php
<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\TextToImageWrapper;
use AiMatchFun\PhpRunwareSDK\ModelAir;
use AiMatchFun\PhpRunwareSDK\OutputType;
use GuzzleHttp\Psr7\Response;

class TextToImageTest extends TestCase
{
    public function testImageGeneration()
    {
        [$textToImage, $mockHandler] = TextToImageWrapper::withMockHandler();

        $mockHandler->append(new Response(200, [], json_encode([
            'data' => [['imageURL' => 'https://example.com/image.png']]
        ])));

        $result = $textToImage
            ->positivePrompt('A beautiful sunset')
            ->negativePrompt('blur')
            ->outputType(OutputType::URL)
            ->run();

        $this->assertEquals('https://example.com/image.png', $result);
    }
}
```

## 🎨 Para laravel-runware

### Opção 1: Mock do Facade (Mais Simples)

```php
use Runware;

// Em um teste PHPUnit com Orchestra Testbench
\Runware::shouldReceive('positivePrompt')
    ->once()
    ->with('A beautiful sunset')
    ->andReturnSelf();

\Runware::shouldReceive('negativePrompt')
    ->once()
    ->with('blur')
    ->andReturnSelf();

\Runware::shouldReceive('run')
    ->once()
    ->andReturn('https://example.com/image.png');

$result = \Runware::positivePrompt('A beautiful sunset')
    ->negativePrompt('blur')
    ->run();
```

### Opção 2: Mock HTTP Client (Mais Realista)

```php
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TextToImageWrapper;

// Criar mock handler
$mockHandler = new MockHandler();
$handlerStack = HandlerStack::create($mockHandler);
$mockClient = new Client(['handler' => $handlerStack]);

// Criar instância com mock
$textToImage = new TextToImageWrapper('test-api-key');
$textToImage->setMockClient($mockClient);

// Registrar no container Laravel
app()->instance('runware', $textToImage);

// Configurar resposta mockada
$mockHandler->append(new Response(200, [], json_encode([
    'data' => [['imageURL' => 'https://example.com/image.png']]
])));

// Usar o facade normalmente
$result = \Runware::positivePrompt('A beautiful sunset')
    ->negativePrompt('blur')
    ->run();
```

## 📝 Estrutura de Respostas Mockadas

### Resposta de Sucesso com URL

```php
new Response(200, [], json_encode([
    'data' => [
        [
            'taskType' => 'imageInference',
            'taskUUID' => 'test-task-uuid',
            'imageUUID' => 'test-image-uuid',
            'imageURL' => 'https://example.com/image.png',
            'seed' => 12345,
            'cost' => 0.5,
        ]
    ]
]))
```

### Resposta com Base64

```php
new Response(200, [], json_encode([
    'data' => [
        [
            'taskType' => 'imageInference',
            'taskUUID' => 'test-task-uuid',
            'imageUUID' => 'test-image-uuid',
            'imageBase64Data' => base64_encode('fake-image-data'),
        ]
    ]
]))
```

### Resposta de Erro

```php
new Response(400, [], json_encode([
    'error' => 'Invalid API key'
]))
```

## 🚀 Executando os Testes

### php-runware-sdk

```bash
cd php-runware-sdk
vendor/bin/phpunit tests/
```

### laravel-runware

```bash
cd laravel-runware
vendor/bin/phpunit tests/
```

## ⚠️ Notas Importantes

1. **Não use em produção**: O `TextToImageWrapper` é apenas para testes. Use `TextToImage` diretamente em produção.

2. **API Key nos testes**: Use qualquer string como API key nos testes (ex: `'test-api-key'`), pois não será validada.

3. **Estrutura da resposta**: Certifique-se de que a resposta mockada tenha a estrutura correta com `data[0]` contendo os campos necessários.

4. **Múltiplas chamadas**: Você pode adicionar múltiplas respostas ao `MockHandler` e elas serão retornadas em ordem.

5. **Campos obrigatórios**: Dependendo do `outputType`, certifique-se de incluir:
   - `imageURL` para `OutputType::URL`
   - `imageBase64Data` para `OutputType::BASE64_DATA`
   - `imageDataURI` para `OutputType::DATA_URI`

## 📚 Arquivos de Referência

- `tests/TextToImageWrapper.php` - Classe wrapper para testes
- `tests/TextToImageWrapperTest.php` - Exemplos de testes
- `tests/example-usage.php` - Exemplos práticos de uso
- `tests/README.md` - Documentação detalhada

## 🐛 Troubleshooting

### Erro: "API response does not contain data"
Certifique-se de que sua resposta mockada tenha `data[0]`:

```json
{
  "data": [
    {
      "imageURL": "..."
    }
  ]
}
```

### Erro: "Requested output type not found in response"
Inclua o campo correto na resposta conforme o `outputType` configurado.

### O mock não está funcionando
Certifique-se de:
1. Usar `TextToImageWrapper` ao invés de `TextToImage` diretamente
2. Chamar `setMockClient()` antes de `run()`
3. Adicionar a resposta mockada ao `MockHandler` antes de chamar `run()`

