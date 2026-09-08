<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SavvyCal API Key
    |--------------------------------------------------------------------------
    |
    | The key used to authenticate REST calls. Create one at:
    | https://savvycal.com/settings/developer
    |
    | When this is null the client falls back to config('services.savvycal.token').
    |
    */
    'token' => env('SAVVYCAL_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The number of seconds to wait for a response before giving up.
    |
    */
    'timeout' => (int) env('SAVVYCAL_TIMEOUT', 8),
];
