<?php

use App\Enums\MediaType;

return [
    MediaType::IMAGE->getValue() => [
        'mime_types' => [
            'image/jpeg',
            'image/gif',
            'image/png',
            'image/bmp',
            'image/svg+xml',
        ],
        'handler' => \App\Services\Media\ImageMediaHandler::class
    ],
];
