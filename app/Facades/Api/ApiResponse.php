<?php

namespace App\Facades\Api;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \App\Responses\Api\ApiResponse
 */
class ApiResponse extends Facade
{
    const string FACADE_ACCESSOR = 'api-response-accessor';
    protected static function getFacadeAccessor()
    {
        return static::FACADE_ACCESSOR;
    }
}
