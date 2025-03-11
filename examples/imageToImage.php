<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Daavelar\PhpRunwareSDK\Runware;

$runware = new Runware('your_api_key');

$imageUrl = $runware
    ->withHeight(512)
    ->withWidth(512)
    ->model('civitai:372465@534642')
    ->withSteps(20)
    ->withCFGScale(7.5)
    ->withNumberResults(5)
    ->withOutputType('URL')
    ->withOutputFormat('PNG')
    ->withNegativePrompt('low quality, blurred')
    ->imageToImage('1girl, solo, in a room, long hair, blue eyes, looking at viewer, cg, 8k, high quality, photo realistic', 'https://i.pinimg.com/originals/00/00/00/00000000000000000000000000000000.jpg', 0.8);

echo $imageUrl;