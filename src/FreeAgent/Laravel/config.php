<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FreeAgent API Config
    |--------------------------------------------------------------------------
    |
    | Configure the FreeAgent API Wrapper below.
    | Retrieve consumer and secret keys from FreeAgent Dev account https://dev.freeagent.com.
    | Use the debug and sandbox options to test your integration.
    | Use the database settings to fetch tokens from db not config.
    |
    */

    'tokens' => [
        'consumer' => 'your-consumer-key',
        'secret'   => 'your-secret-key',
    ],
    
    'redirect' => 'redirect-url-for-access-code', // e.g. http://domain.com/access-token
    
    'debug'   => false,
    'sandbox' => false,

    /*
     * Database link expects a table that has key:value style access.
     * Enter the name of the table to lookup.
     * Enter the index column that should be searched.
     * Enter the value column that data should be taken from.
     *
     * e.g. Settings Table
     *
     *   --------------------------------------------------------------------
     *   | id   | index                 | value                             |
     *   --------------------------------------------------------------------
     *   | 1    | api-tokens-consumer   | xxxxxxx-xxxxxxx-xxxxxxx-xxxxxxx   |
     *   --------------------------------------------------------------------
     *     
     */
    'database' => [
        'active' => false,     // set to true to enable db access
        'table'  => 'settings',
        'index'  => 'index',
        'value'  => 'value',
    ],

];
