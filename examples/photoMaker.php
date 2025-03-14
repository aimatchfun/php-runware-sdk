<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Daavelar\PhpRunwareSDK\Runware;

$runware = new Runware('your_api_key');

$imageUrl = $runware->withWidth(512)
    ->withHeight(512)
    ->withSteps(20)
    ->withCFGScale(7.5)
    ->withNumberResults(1)
    ->withOutputType('URL')
    ->withOutputFormat('PNG')
    ->addImage("31630bb9-07f3-48b4-b187-d31aaf8c2b5e")
    ->photoMaker('a rusty and old yard');

echo $imageUrl;