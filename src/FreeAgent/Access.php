<?php

/*
 * OAuth token wrapper for FreeAgent REST API
 *
 * Copyright (c) 2015 Agency Core.
 * Free to use under the MIT licence, for full details view the LICENCE file.
 *
 */

namespace FreeAgent;

use Accounting\Interfaces\Access as AccountingAccess;

class Access implements AccountingAccess
{
    protected $api;
    
    /*
     * Prepare the FreeAgent API
     *
     */
    public function __construct( \Accounting\Abstracts\Api $api )
    {
        $this->api = $api;
    }
    
    /*
     * Get authentication link
     *
     */
    public function link( $redirect, $echo = true )
    {
        // prepare params
        $token = $this->api->tokens();
        $params = array(
            'client_id'     => $token['consumer'],
            'response_type' => 'code',
            'redirect_uri'  => $redirect
        );
        // create auth url
        $url = $this->api->url( $this->api->auth ) . '?' . http_build_query( $params );
        if ( $echo )
            echo '<a href="' . $url . '">Link to FreeAgent</a>';
        else
            return $url;
    }
    
    /*
     * Get refresh token (which can be traded for an access token)
     *
     */
    public function token( $code, array $options = array() )
    {
        // prepare params
        $params = array(
            'grant_type' => 'authorization_code',
            'code'       => $code
        );
        // add redirect if required
        if ( isset($options['redirect']) ) {
            $params['redirect_uri'] = $options['redirect'];
        }
        // get request auth token
        $request = $this->api->request( $this->api->url( $this->api->token ), 'POST', $params, false);
        if ( $request->status === 200 )
            return $request->data->refresh_token;
        else
            return false;
    }

    /*
     * Refresh the access token
     *
     * Provide the refresh $token and retrieve a new access token
     *
     */
    public function refresh( $token )
    {
        // prepare params
        $params = array(
            'grant_type'    => 'refresh_token',
            'refresh_token' => $token
        );
        // request access token
        $request = $this->api->request( $this->api->url( $this->api->token ), 'POST', $params, false);
        if ( $request->status === 200 )
            return $request->data->access_token;
        else
            return false;
    }
}