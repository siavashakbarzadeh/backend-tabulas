<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tabulas API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Tabulas Swagger API. This is used by the
    | TabulasApiService to make HTTP requests to the live endpoints.
    |
    */
    'api_base_url' => env('TABULAS_API_BASE_URL', 'https://svil-tabulas4.intra.senato.it'),

    /*
    |--------------------------------------------------------------------------
    | API Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for API requests.
    |
    */
    'timeout' => env('TABULAS_API_TIMEOUT', 30),
];
