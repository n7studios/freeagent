<?php

/*
 * PHP wrapper for FreeAgent REST API
 *
 * Copyright (c) 2015 Agency Core.
 * Free to use under the MIT licence, for full details view the LICENCE file.
 *
 */

namespace FreeAgent;

use Accounting\Abstracts\Credit as AccountingCredit;
use Accounting\Traits\Dated;

class Credit extends AccountingCredit
{
    use Dated;
    
    /*
     * Convert model to API compatible format
     *
     */
    public function encode()
    {
        $data  = array(
        	'dated_on'              => self::apiDate( $this->issued ),
        	'due_on'                => self::apiDate( $this->due ),
        	'payment_terms_in_days' => $this->terms,
        	'contact'               => isset( $this->customer->id ) ? $this->customer->id : $this->customerId,
        	'comments'              => $this->notes,
        	'invoice_items'         => array()
        );
        foreach ( $this->items as $item )
        {
        	$data['invoice_items'][] = array(
            	'description' => $item->description,
            	'quantity'    => $item->quantity,
            	'item_type'   => 'Hours',
            	'price'       => $item->price > 0 ? 0 - $item->price : $item->price,
        	);
        }
        return [ 'invoice' => $data ];
    }
    
    /*
     * Convert API response back to model
     *
     */
    public static function decode( $data )
    {
        $decode = array();
        $single = isset( $data->invoice );
        $data   = $single ? [ $data->invoice ] : $data->invoices;
        foreach ( $data as $object )
        {
            $class = __CLASS__;
            $credit = new $class;
            $credit->id         = basename( $object->url );
            $credit->number     = $object->reference;
            $credit->issued     = self::dbDate( $object->dated_on );
        	$credit->due        = self::dbDate( $object->due_on );
        	$credit->terms      = $object->payment_terms_in_days;
            $credit->customerId = $object->contact;
            $credit->notes      = $object->comments;
            $credit->status     = $object->status;
            $credit->total      = $object->total_value;
            $credit->items      = [];
            if ( isset($object->invoice_items) )
            {
                foreach ( $object->invoice_items as $invoiceItem )
                {
                	$item = new Item;
                	$item->description = $invoiceItem->description;
                	$item->quantity    = $invoiceItem->quantity;
                	$item->price       = $invoiceItem->price;	
                	$invoice->items[] = $item;
                }
            }
            $decode[] = $credit;
        }
        return $single ? $decode[0] : $decode;
    }
    
    /*
     * Get unique id after object creationg
     *
     */
    public static function uid( \Accounting\Interfaces\Model $model, $data )
    {
        $model->id = basename( $data->invoice->url );
        $model->number = $data->invoice->reference;
        return $model;
    }
}