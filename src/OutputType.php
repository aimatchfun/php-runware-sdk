<?php

namespace AIMatchFun\PhpRunwareSDK;

enum OutputType: string
{
    /**
     * The image is returned as a base64-encoded string
     */
    case BASE64_DATA = 'base64Data';

    /**
     * The image is returned as a data URI string
     */
    case DATA_URI = 'dataURI';

    /**
     * The image is returned as a URL string (default)
     */
    case URL = 'URL';
} 