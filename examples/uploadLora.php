<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Daavelar\PhpRunwareSDK\UploadLora;
use Ramsey\Uuid\Uuid;

$uploader = new UploadLora('your_api_key');

$resultado = $uploader->upload([
    'air' => 'aimatch:1@1',
    'uniqueIdentifier' => Uuid::uuid4()->toString(),
    'name' => 'OC - Alice Dantas',
    'version' => '1.0',
    'downloadURL' => 'https://huggingface.co/diegueradev/alice-dantas/blob/main/alice-dantas.safetensors',
    'tags' => ['character', 'anime', 'artstyle'],
    'positiveTriggerWords' => 'alice dantas, anime, artstyle',
    'shortDescription' => 'Alice Dantas, personagem de anime',
    'comment' => 'Uso interno apenas'
]);

echo $resultado;