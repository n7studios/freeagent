<?php

$base = dirname( __DIR__ );

require_once $base . '/vendor/autoload.php';
require_once $base . '/../settings.php';

$api = new FreeAgent\Api;
$api->setup( Settings::_( 'freeagent' ) );
$api->sandbox( true );

$access = new FreeAgent\Access( $api );

$redirect = 'http://' .$_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

if ( isset($_GET['code']) )
{
    var_dump( $access->token( $_GET['code'], array( 'redirect' => $redirect ) ) );
}
else
{
    $access->link( $redirect );
}