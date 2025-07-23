<?php

namespace AiMatchFun\PhpRunwareSDK;

enum ControlMode: string
{
    /**
     * Prompt is more important in guiding image generation
     */
    case PROMPT = 'prompt';

    /**
     * ControlNet is more important in guiding image generation
     */
    case CONTROLNET = 'controlnet';

    /**
     * Balanced approach between prompt and ControlNet guidance
     */
    case BALANCED = 'balanced';
}

