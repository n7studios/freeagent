<?php

/*
 * FreeAgent REST API Service Provider for Laravel
 *
 * Copyright (c) 2015 Agency Core.
 * Free to use under the MIT licence, for full details view the LICENCE file.
 *
 */

namespace FreeAgent\Laravel;

use Accounting\Abstracts\ServiceProvider as AccountingServiceProvider;

class ServiceProvider extends AccountingServiceProvider
{
    public $root    = __DIR__;
    public $service = 'freeagent';
    public $library = 'FreeAgent\Api';
}
