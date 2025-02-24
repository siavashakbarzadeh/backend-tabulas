<?php

namespace App\Facades\Api;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \App\Responses\Api\ApiResponse
 */
class ApiResponse extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'api-response-accessor';
    }
}
