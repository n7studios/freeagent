<?php

/*
 * PHP wrapper for FreeAgent REST API
 *
 * Copyright (c) 2015 Agency Core.
 * Free to use under the MIT licence, for full details view the LICENCE file.
 *
 */

namespace FreeAgent;

use Accounting\Abstracts\Customer as AccountingCustomer;

class Customer extends AccountingCustomer
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
            // Not all contacts will have a first and/or last name, if none was specified
            $contact = '';
            if ( isset( $object->first_name ) ) {
                $contact .= $object->first_name;
            }
            if ( isset( $object->last_name ) ) {
                $contact .= ( ! empty( $contact ) ? ' ' : '' ) . $object->last_name;
            }

            $class = __CLASS__;
            $customer = new $class;
            $customer->id       = basename( $object->url );
            $customer->name     = $object->organisation_name;
            $customer->contact  = $contact;
            $customer->email    = isset( $object->email )    ? $object->email    : null;
            $customer->phone    = isset( $object->phone )    ? $object->phone    : null;
            $customer->website  = isset( $object->website )  ? $object->website  : null;
            $customer->address  = isset( $object->address1 ) ? $object->address1 : null;
            $customer->town     = isset( $object->town )     ? $object->town     : null;
            $customer->postcode = isset( $object->postcode ) ? $object->postcode : null;
            $customer->country  = isset( $object->country )  ? $object->country  : null;
            $decode[] = $customer;
        }
        return $single ? $decode[0] : $decode;
    }
    
    /*
     * Get unique id after object creation
     *
     */
    public static function uid( \Accounting\Interfaces\Model $model, $data )
    {
        $model->id = basename( $data->contact->url );
        return $model;
    }
}