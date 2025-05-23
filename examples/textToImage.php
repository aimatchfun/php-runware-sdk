<?php
require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Ramsey\Uuid\Uuid;
use AIMatchFun\PhpRunwareSDK\ModelAir;
use AIMatchFun\PhpRunwareSDK\Scheduler;

$client = new Client();

$personalities = [
    'extrovert',
    'introvert',
    'adventurous',
    'analytical',
    'creative',
    'nurturing'
];

$interests = [
    'traveling, photography, hiking, nature exploration',
    'reading, writing, art galleries, museums',
    'cooking, food tasting, wine appreciation',
    'fitness, yoga, meditation, wellness',
    'gaming, anime, cosplay, technology',
    'music, concerts, playing instruments'
];

$promises = [];

foreach ($personalities as $personality) {
    foreach ($interests as $interest) {
        $prompt = "1girl, {$personality} personality, interested in {$interest}, modern clothing, natural lighting, candid pose";
        $filename = "{$personality}_" . substr(md5($interest), 0, 8);
        
        echo "Starting request for: {$personality} with interests in {$interest}" . PHP_EOL;
        
        $promises[$filename] = $client->postAsync('https://api.runware.ai/v1', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer cbPA3O6uougZW1Rhvoyp80n86kzUte9V'
            ],
            'json' => [[
                'taskType' => 'imageInference',
                'taskUUID' => Uuid::uuid4()->toString(),
                'outputType' => 'base64Data',
                'positivePrompt' => $prompt,
                'negativePrompt' => 'score_4, score_5, score_6, score_7, watermark, deformed eyes, bad face, ugly, bad quality, '
                . 'bad anatomy, six fingers, bad hands, bad eyes, twins, bilateral symmetry, child face, kid face',
                'checkNSFW' => false,
                'scheduler' => Scheduler::EULER_A->value,
                'height' => 512,
                'width' => 512,
                'model' => ModelAir::REALISM_BY_STABLE_YOGI->value,
                'clipSkip' => 0,
                'steps' => 30,
                'outputQuality' => 99,
                'CFGScale' => 6.5,
                'numberResults' => 1,
            ]]
        ])->then(
            function ($response) use ($filename) {
                $result = json_decode($response->getBody()->getContents(), true);
                $image = $result['data'][0]['imageBase64Data'];
                file_put_contents(__DIR__ . '/' . $filename . '.png', base64_decode($image));
                echo "Completed and saved: " . $filename . PHP_EOL;
            },
            function ($exception) use ($filename) {
                echo "Request failed for " . $filename . ": " . $exception->getMessage() . PHP_EOL;
            }
        );
    }
}

// Wait for all requests to complete
echo "Waiting for all requests to complete..." . PHP_EOL;
Promise\Utils::settle($promises)->wait();
echo "All requests completed!" . PHP_EOL;
