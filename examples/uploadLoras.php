<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Daavelar\PhpRunwareSDK\UploadLora;
use Ramsey\Uuid\Uuid;

$uploader = new UploadLora('your_api_key');

$models = [
    [
        'version' => '1.0',
        'id' => '1',
        'name' => 'Alice Dantas',
        'downloadURL' => 'https://storage.googleapis.com/aimatch-loras/alice-dantas.safetensors'
    ],
    [
        'version' => '1.0',
        'id' => '2',
        'name' => 'Alicia Silva',
        'downloadURL' => 'https://storage.googleapis.com/aimatch-loras/alicia-silva.safetensors'
    ],
    [
        'version' => '1.0',
        'id' => '3',
        'name' => 'Bruna Santos',
        'downloadURL' => 'https://storage.googleapis.com/aimatch-loras/bruna-santos.safetensors'
    ],
    [
        'version' => '1.0',
        'id' => '4',
        'name' => 'Erika Matos',
        'downloadURL' => 'https://storage.googleapis.com/aimatch-loras/erika-matos.safetensors'
    ],
    [
        'version' => '1.0',
        'id' => '5',
        'name' => 'Jennifer Silva',
        'downloadURL' => 'https://storage.googleapis.com/aimatch-loras/jennifer-silva.safetensors'
    ],
    [
        'version' => '1.0',
        'id' => '6',
        'name' => 'Jennifer Stone',
        'downloadURL' => 'https://storage.googleapis.com/aimatch-loras/jennifer-stone.safetensors'
    ],
    [
        'version' => '1.0',
        'id' => '7',
        'name' => 'Luiza Gomes',
        'downloadURL' => 'https://storage.googleapis.com/aimatch-loras/luiza-gomes.safetensors'
    ],
    [
        'version' => '1.0',
        'id' => '8',
        'name' => 'Marisa Alves',
        'downloadURL' => 'https://storage.googleapis.com/aimatch-loras/marisa-alves.safetensors'
    ],
    [
        'version' => '1.0',
        'id' => '9',
        'name' => 'Thuanny Campos',
        'downloadURL' => 'https://storage.googleapis.com/aimatch-loras/thuanny-campos.safetensors'
    ]
];

foreach ($models as $model) {
    echo $uploader->upload([
        'air' => "aimatch:{$model['id']}@{$model['version']}",
        'uniqueIdentifier' => Uuid::uuid4()->toString(),
        'name' => $model['name'],
        'version' => $model['version'],
        'downloadURL' => $model['downloadURL'],
        'tags' => ['character'],
        'positiveTriggerWords' => str_replace(' ', '', strtolower($model['name'])),
        'shortDescription' => $model['name'] . ' AI generated character',
        'comment' => 'Use exclusive for internal purposes only'
    ]);
}