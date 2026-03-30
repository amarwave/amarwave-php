<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AmarWave Application Key
    |--------------------------------------------------------------------------
    |
    | The app_key for your AmarWave application. Find it in the AmarWave
    | dashboard under your application settings.
    |
    */

    'app_key' => env('AMARWAVE_APP_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | AmarWave Application Secret
    |--------------------------------------------------------------------------
    |
    | The app_secret for your AmarWave application. Keep this value secret
    | and never expose it to clients.
    |
    */

    'app_secret' => env('AMARWAVE_APP_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | AmarWave API Host
    |--------------------------------------------------------------------------
    |
    | The hostname of your AmarWave API server.
    |
    */

    'host' => env('AMARWAVE_HOST', 'localhost'),

    /*
    |--------------------------------------------------------------------------
    | AmarWave API Port
    |--------------------------------------------------------------------------
    |
    | The TCP port of your AmarWave API server.
    |
    */

    'port' => (int) env('AMARWAVE_PORT', 8000),

    /*
    |--------------------------------------------------------------------------
    | Use TLS / HTTPS
    |--------------------------------------------------------------------------
    |
    | Set to true to use HTTPS for API requests. Recommended for production.
    |
    */

    'ssl' => (bool) env('AMARWAVE_SSL', false),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum number of seconds to wait for an API response before timing out.
    |
    */

    'timeout' => (int) env('AMARWAVE_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | API Trigger Path
    |--------------------------------------------------------------------------
    |
    | The HTTP path for the AmarWave event trigger endpoint.
    | Change this only if you have customised the server route.
    |
    */

    'api_path' => env('AMARWAVE_API_PATH', '/api/v1/trigger'),

];
