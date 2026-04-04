<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AmarWave Application Key
    |--------------------------------------------------------------------------
    |
    | The app_key for your AmarWave credential. Find it in the AmarWave
    | dashboard under App Keys.
    |
    */

    'app_key' => env('AMARWAVE_APP_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | AmarWave Application Secret
    |--------------------------------------------------------------------------
    |
    | Keep this value secret and never expose it to browser clients.
    |
    */

    'app_secret' => env('AMARWAVE_APP_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Cluster
    |--------------------------------------------------------------------------
    |
    | The cluster determines the API host automatically. Use 'default' for
    | the hosted AmarWave service (amarwave.com). Use 'local' for a local
    | self-hosted server. Set to null to configure host/port/ssl manually.
    |
    | Env: AMARWAVE_CLUSTER=default
    |
    | Available: default, local, eu, us, ap1, ap2
    |
    */

    'cluster' => env('AMARWAVE_CLUSTER', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Manual Connection (used only when cluster is null)
    |--------------------------------------------------------------------------
    |
    | If you self-host AmarWave on a custom domain, set cluster to null and
    | configure host, port, and ssl below.
    |
    */

    'ssl'  => (bool) env('AMARWAVE_SSL', false),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum number of seconds to wait for an API response.
    |
    */

    'timeout' => (int) env('AMARWAVE_TIMEOUT', 10),

];
