<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Daavelar\PhpRunwareSDK\UploadModel;
use Ramsey\Uuid\Uuid;

$uploader = new UploadModel('your_api_key');

$resultado = $uploader->upload([
    'air' => 'air-of-your-model',
    'uniqueIdentifier' => Uuid::uuid4()->toString(),
    'name' => 'name-of-your-model',
    'version' => '1.0',
    'downloadURL' => 'public-url-to-your-model',
    'tags' => ['character', 'anime', 'artstyle'],
    'positiveTriggerWords' => 'positive-trigger-words',
    'shortDescription' => 'short-description-of-your-model',
    'comment' => 'comment-of-your-model'
]);

echo $resultado;