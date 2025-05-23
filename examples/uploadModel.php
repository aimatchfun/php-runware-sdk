<?php 

require_once __DIR__ . '/../vendor/autoload.php';

use Daavelar\PhpRunwareSDK\UploadModel;

$uploadModel = new UploadModel('cbPA3O6uougZW1Rhvoyp80n86kzUte9V');

$uploadModel->upload([
    'model' => 'test-model',
    'file' => 'examples/test.jsonl',
]);