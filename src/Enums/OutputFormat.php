<?php

namespace AiMatchFun\PhpRunwareSDK;

enum OutputFormat :string
{
    /**
     * JPEG/JPG image format (default)
     */
    case JPG = 'JPG';

    /**
     * PNG image format
     */
    case PNG = 'PNG';

    /**
     * WebP image format
     */
    case WEBP = 'WEBP';
} 