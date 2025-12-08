<?php

namespace AiMatchFun\PhpRunwareSDK;

class ControlNet
{
    public string $model;
    public string $guideImage;
    public string $weight;
    public string $startStep;
    public string $endStep;
    public int $startStepPercentage;
    public int $endStepPercentage;
    public ControlMode $controlMode;
}