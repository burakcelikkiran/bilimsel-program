<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Excluded Routes
    |--------------------------------------------------------------------------
    |
    | Routes listed here are omitted from the inline @routes payload sent to
    | the browser. The frontend only needs named web/admin routes; excluding
    | API, debug, and tooling routes keeps the initial HTML payload small.
    |
    */

    'except' => [
        'api.*',
        'horizon*',
        'log-viewer.*',
        'l5-swagger.*',
        'docs',
        'sanctum.*',
        'boost*',
        'debug*',
    ],

];
