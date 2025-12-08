# Guia de Testes - PHP Runware SDK

Este guia explica como testar o SDK sem fazer chamadas reais à API da Runware, evitando gastar créditos.

## Estrutura de Testes

Os testes estão organizados da seguinte forma:

- `TextToImageWrapper.php` - Classe wrapper que permite injetar um mock HTTP client
- `TextToImageWrapperTest.php` - Exemplos de testes usando o wrapper
- `Helpers/MocksRunwareApi.php` - Trait com helpers para criar mocks

## Como Usar

### Opção 1: Usando TextToImageWrapper (Recomendado)

```php
use Tests\TextToImageWrapper;
use GuzzleHttp\Psr7\Response;

// Criar instância com mock handler
[$textToImage, $mockHandler] = TextToImageWrapper::withMockHandler();

// Configurar resposta mockada
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

// Usar normalmente
$result = $textToImage
    ->positivePrompt('A beautiful sunset')
    ->negativePrompt('blur')
    ->width(512)
    ->height(512)
    ->outputType(OutputType::URL)
    ->run();

echo $result; // https://example.com/image.png
```

### Opção 2: Criar Mock Manualmente

```php
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TextToImageWrapper;

$mockHandler = new MockHandler();
$handlerStack = HandlerStack::create($mockHandler);
$mockClient = new Client(['handler' => $handlerStack]);

$textToImage = new TextToImageWrapper('test-api-key');
$textToImage->setMockClient($mockClient);

// Adicionar respostas mockadas
$mockHandler->append(new Response(200, [], json_encode([
    'data' => [['imageURL' => 'https://example.com/image.png']]
])));

$result = $textToImage
    ->positivePrompt('Test')
    ->negativePrompt('blur')
    ->run();
```

## Exemplos de Respostas Mockadas

### Resposta com URL
```php
$response = new Response(200, [], json_encode([
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
```

### Resposta com Base64
```php
$response = new Response(200, [], json_encode([
    'data' => [
        [
            'taskType' => 'imageInference',
            'taskUUID' => 'test-task-uuid',
            'imageUUID' => 'test-image-uuid',
            'imageBase64Data' => base64_encode('fake-image-data'),
        ]
    ]
]));
```

### Resposta de Erro
```php
$response = new Response(400, [], json_encode([
    'error' => 'Invalid API key'
]));
```

## Executando os Testes

```bash
# Executar todos os testes
vendor/bin/phpunit

# Executar um teste específico
vendor/bin/phpunit tests/TextToImageWrapperTest.php

# Com cobertura de código
vendor/bin/phpunit --coverage-text
```

## Notas Importantes

1. **Não use em produção**: O `TextToImageWrapper` é apenas para testes. Use `TextToImage` diretamente em produção.

2. **API Key**: Use qualquer string como API key nos testes, pois não será validada.

3. **Respostas Mockadas**: Certifique-se de que a estrutura da resposta mockada corresponda ao formato esperado pela API da Runware.

4. **Múltiplas Chamadas**: Você pode adicionar múltiplas respostas ao `MockHandler` e elas serão retornadas em ordem.

## Troubleshooting

### Erro: "API response does not contain data"
Certifique-se de que sua resposta mockada tenha a estrutura:
```json
{
  "data": [
    {
      "imageURL": "...",
      // outros campos
    }
  ]
}
```

### Erro: "Requested output type not found in response"
Certifique-se de incluir o campo correto na resposta:
- Para `OutputType::URL`: `imageURL`
- Para `OutputType::BASE64_DATA`: `imageBase64Data`
- Para `OutputType::DATA_URI`: `imageDataURI`

