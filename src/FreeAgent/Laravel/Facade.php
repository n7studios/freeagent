<?php

/*
 * FreeAgent REST API Facade for Laravel
 *
 * Copyright (c) 2015 Agency Core.
 * Free to use under the MIT licence, for full details view the LICENCE file.
 *
 */

namespace FreeAgent\Laravel;

use Illuminate\Support\Facades\Facade as LaravelFacade;

class Facade extends LaravelFacade
{
    /**
     * Call FreeAgent API service
     *
     */
    protected static function getFacadeAccessor() {  return 'freeagent'; }
}