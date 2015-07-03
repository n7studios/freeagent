<?php

/*
 * PHP wrapper for FreeAgent REST API
 *
 * Copyright (c) 2015 Agency Core.
 * Free to use under the MIT licence, for full details view the LICENCE file.
 *
 */

namespace FreeAgent;

use Accounting\Abstracts\Supplier as AccountingSupplier;

class Supplier extends AccountingSupplier
{
    /*
     * Convert model to API compatible format
     *
     */
    public function encode()
    {
        $space = strpos($this->contact, ' ');
        $data  = array(
    	    'organisation_name' => $this->name,
    	    'first_name'        => trim( substr($this->contact, 0, $space) ),
            'last_name'         => trim( substr($this->contact, $space) ),
    	    'email'             => $this->email,
    	    'billing_email'     => $this->email,
    	    'phone_number'      => $this->phone,
    	    'address1'          => $this->address,
    	    'town'              => $this->town,
    	    'postcode'          => $this->postcode,
    	    'country'           => $this->country,
    	    'created_at'        => date( 'Y-m-d\TH:i:s' ),
    	    'updated_at'        => date( 'Y-m-d\TH:i:s' )
        );
        return [ 'contact' => $data ];
    }
    
    /*
     * Convert API response back to model
     *
     */
    public static function decode( $data )
    {
        $decode = array();
        $single = isset( $data->contact );
        $data   = $single ? [ $data->contact ] : $data->contacts;
        foreach ( $data as $object )
        {
            $class = __CLASS__;
            $supplier = new $class;
            $supplier->id       = basename( $object->url );
            $supplier->name     = $object->organisation_name;
            $supplier->contact  = $object->first_name . ' ' . $object->last_name;
            $supplier->email    = isset( $object->email )    ? $object->email    : null;
            $supplier->phone    = isset( $object->phone )    ? $object->phone    : null;
            $supplier->website  = isset( $object->website )  ? $object->website  : null;
            $supplier->address  = isset( $object->address1 ) ? $object->address1 : null;
            $supplier->town     = isset( $object->town )     ? $object->town     : null;
            $supplier->postcode = isset( $object->postcode ) ? $object->postcode : null;
            $supplier->country  = isset( $object->country )  ? $object->country  : null;
            $decode[] = $supplier;
        }
        return $single ? $decode[0] : $decode;
    }
    
    /*
     * Get unique id after object creationg
     *
     */
    public static function uid( \Accounting\Interfaces\Model $model, $data )
    {
        $model->id = basename( $data->contact->url );
        return $model;
    }
}