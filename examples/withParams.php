<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Daavelar\PhpRunwareSDK\Runware;

$runware = new Runware('uZ7zT4r8xZW55R1YBYArySPqkiQrFc3O');

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
    ->textToImage('1girl, solo, in a room, dark, underwear, big breasts, long hair, blue eyes, looking at viewer, cg, 8k, high quality, photo realistic');

echo $imageUrl;