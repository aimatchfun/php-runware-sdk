<?php

namespace AiMatchFun\PhpRunwareSDK;

use AIMatchFun\Runware\ControlMode;   

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