<?php

namespace Daavelar\PhpRunwareSDK;

use Daavelar\Runware\ControlMode;   

enum ControlNet: string
{
    public string $modelAir;
    public string $guideImage;
    public string $weight;
    public string $startStep;
    public string $endStep;
    public int $startStepPercentage;
    public int $endStepPercentage;
    public ControlMode $controlMode;
}