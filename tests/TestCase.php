<?php

use FreeAgent\Api;

class TestCase extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {        
        $this->api = new Api;
        $this->api->setup( Settings::_( 'freeagent' ) );
        $this->api->sandbox( true );
    }
}