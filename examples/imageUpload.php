<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Daavelar\PhpRunwareSDK\Runware;

$runware = new Runware('your_api_key');

echo 'uploading image: ' . __DIR__ . '/sample.png' . PHP_EOL;
$imageUrl = $runware->imageUpload(__DIR__ . '/sample.png');

echo $imageUrl;