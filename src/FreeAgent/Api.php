<?php

/*
 * PHP wrapper for FreeAgent REST API
 *
 * Copyright (c) 2015 Agency Core.
 * Free to use under the MIT licence, for full details view the LICENCE file.
 *
 */

namespace FreeAgent;

use Accounting\Abstracts\Api as AccountingApi;
use Accounting\Traits\AccessTokens;
use Accounting\Traits\Debugger;
use Accounting\Traits\Inflect;
use Accounting\Traits\StandardResponse;

class Api extends AccountingApi
{
    /*
     * Register traits
     *
     */
    use AccessTokens, StandardResponse, Debugger, Inflect;

    /*
     * API variables
     *
     */
    public $api   = 'https://api.freeagent.com/v2/';
    public $apiSB = 'https://api.sandbox.freeagent.com/v2/';
    public $auth  = 'approve_app';
    public $token = 'token_endpoint';
    
    public $endpoints = [
        'customer' => 'contacts',
        'supplier' => 'contacts',
        'purchase' => 'bills',
        'invoice'  => 'invoices',
        'credit'   => 'invoices',
        'category' => 'categories'
    ];
    
    /*
     * Private tokens
     *
     */
    private $_consumer;
    private $_secret;
    private $_access;
    private $_refresh;

    /*
     * Fetch the API and set the refresh token
     *
     */    
    public function fetch( $refresh = null )
    {
        if ( $refresh ) $this->setup([ 'refresh' => $refresh ]);
        return $this;
    }
    
    /*
     * Set up the wrapper
     *
     */
    public function setup( array $tokens = array() )
    {
        if ( isset($tokens['consumer']) ) $this->_consumer = $tokens['consumer'];
        if ( isset($tokens['secret']) )   $this->_secret   = $tokens['secret'];
        if ( isset($tokens['refresh']) )  $this->_refresh  = $tokens['refresh'];
    }

    /*
     * Configure the wrapper
     *
     */
    public function auxiliary( array $params = array() )
    {
        // currently not in use
        return null;
    }

    /*
     * Locate this class
     *
     */
    protected function locate()
    {
        return __NAMESPACE__;
    }

    /*
     * Fetch tokens
     *
     */
    public function tokens()
    {
        return [
            'consumer' => $this->_consumer,
            'secret'   => $this->_secret,
            'access'   => $this->_access,
            'refresh'  => $this->_refresh,
        ];
    }

    /*
     * Fetch API url
     *
     */
    public function url( $endpoint = '' )
    {
        $url = $this->sandbox ? $this->apiSB : $this->api;
        return $url . $endpoint;
    }

    /*
     * Handle raw requests
     *
     */
    public function request( $endpoint, $verb, array $params = array(), $token = true )
    {
        // open curl
        $curl = curl_init();
        $headers = array('Accept: application/json');
        
        // token auth for all calls and basic auth for token calls
        if ( $token ) {
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: Bearer ' . $this->_access;
            $headers[] = 'User-Agent: Agency Core';
            $post = json_encode( $params );
        } else {
            curl_setopt($curl, CURLOPT_USERPWD, $this->_consumer . ":" . $this->_secret);
            $post = http_build_query( $params );
        }
        
        // prepare request
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                
        // choose http method and set options
        switch ($verb)
        {
            case 'GET':
                $url = empty( $params ) ? $endpoint : $endpoint . '?' . $post;
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_HTTPGET, true);
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_URL, $endpoint);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
                break;
            case 'POST':
                curl_setopt($curl, CURLOPT_URL, $endpoint);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_URL, $endpoint);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        
        // make request and get status code
        $data   = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $time   = curl_getinfo($curl, CURLINFO_TOTAL_TIME);
        $type   = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

        // close curl
        curl_close($curl);
        
		// handle any errors
        if ( $status >= 400 )
        {
            $this->debugger( $endpoint, $status, $params, $data );
            $this->error( isset( $request->data->errors ) ? $request->data->errors->error->message : 'Server Error', $status );
        }
        
        // wrap up response
        return $this->response( json_decode($data), $status, $time );
    }
    
    /*
     * Internal front end to request function
     *
     */
    public function call( $endpoint, $verb, array $data = array() )
    {
        // refresh access token
        $this->_access = $this->refresh( $this->_refresh );
        // make request
        return $this->request( $this->url( $endpoint ), $verb, $data );
    }
    
    /**
     * Find one or more models
     *
     */
    public function find( $type, $id = null )
    {
        // prepare endpoint
        $endpoint = $this->endpoints[$type];
        $endpoint = $id ? $endpoint . '/' . $id : $endpoint;
        // for single invoices, included items
        if ( $type == 'invoice' && $id ) $endpoint .= '?nested_invoice_items=true';
        // attempt request
        try {
            $response = $this->call( $endpoint, 'GET' );
        } catch ( \Exception $exception ) {
            // handle 404 exceptions
            if ( $exception->getCode() == 404 )
                return false;
            else
                throw $exception;
        }
        // prepare response
        $class = $this->inflect( $type );
        return $class::decode( $response->data );
    }

    /**
     * Find qualified resource location
     *
     */
    public function resource( \Accounting\Interfaces\Model $model )
    {
        if ( $model->id )
        {
            $endpoint = $this->detector( $model );
            return $this->url( $endpoint . '/' . $model->id );
        }
        return false;
    }

    /**
     * Search for models
     *
     */
    public function search( $type, $name )
    {
        $data  = $this->find( $type );
        $fetch = array();
        foreach ( $data as $object )
        {
            $search = ( $type == 'invoice' || $type == 'purchase' || $type == 'credit' ) ? $object->number : $object->name;
            if ( preg_match( "/$name/i", $search ) )
            {
                $fetch[] = $object;
            }
        }
        return empty( $fetch ) ? false : $fetch;
    }

    /**
     * Save a model
     *
     */
    public function save( \Accounting\Interfaces\Model $model )
    {
        // prepare endpoint and select verb
        $endpoint  = $this->detector( $model );
        $endpoint  = $model->id ? $endpoint . '/' . $model->id : $endpoint; // use model id (url if we have it)
        $verb      = $model->id ? 'PUT' : 'POST';
        // we need to handle bill categories, which is a FreeAgent only thing
        if ( property_exists( $model, 'category') && !$model->category ) {
            $model->category = $this->url( 'categories/280' );
        }
        // make request
        $response  = $this->call( $endpoint, $verb, $model->encode() );
        // grab id
        $class = $this->inflect( $model );
        // some FreeAgent PUT requests return no data
        if ( $response->data )
        {
            $model = $class::uid( $model, $response->data );
        }
        return $model;
    }

    /**
     * Delete a model
     *
     */
    public function delete( \Accounting\Interfaces\Model $model )
    {
        if ( $model->id )
        {
            $endpoint  = $this->detector( $model );
            // attempt to delete
            try {
                $this->call( $endpoint . '/' . $model->id, 'DELETE' );
            } catch ( \Exception $exception ) {
                // skip if 403 exceptions is thrown
                if ( $exception->getCode() == 403 )
                    return false;
                else
                    throw $exception;
            }
            return true;
        }
        return false;
    }
}