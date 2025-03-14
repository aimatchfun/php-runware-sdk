<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Daavelar\PhpRunwareSDK\Runware;

$runware = new Runware('your_api_key');

$imageUrl = $runware
    ->withHeight(512)
    ->withWidth(512)
    ->model('runware:101@1')
    ->withSteps(40)
    ->withCFGScale(7.5)
    ->withNsfw(true)
    ->addLora('civitai:128568@747534')
    ->withNumberResults(1)
    ->withOutputType('URL')
    ->withOutputFormat('PNG')
    ->withNegativePrompt('low quality, blurred')
    ->textToImage('pretty woman with short white hair, busty breasts, oiled body, oiledskin, alicedantas');
echo $imageUrl;