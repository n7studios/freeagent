<?php

/*
 * PHP wrapper for FreeAgent REST API
 *
 * Copyright (c) 2015 Agency Core.
 * Free to use under the MIT licence, for full details view the LICENCE file.
 *
 */

namespace FreeAgent;

use Accounting\Abstracts\Purchase as AccountingPurchase;
use Accounting\Traits\Dated;

class Purchase extends AccountingPurchase
{
    use Dated;
    
    /*
     * Special fields just for FreeAgent purchases (bills)
     *
     */    
    public $category;
    
    /*
     * Convert model to API compatible format
     *
     */
    public function encode()
    {
        $total = 0;
        foreach ( $this->items as $item )
        {
            $cost = $item->quantity * $item->price;
            $total += $cost;
        }
        $data = array(
            'reference'      => $this->number,
        	'dated_on'       => self::apiDate( $this->issued ),
        	'due_on'         => self::apiDate( $this->due ),
        	'contact'        => isset( $this->supplier->id ) ? $this->supplier->id : $this->supplierId,
            'category'       => $this->category, // default for now is sundries! registered in the save method
            'sales_tax_rate' => $this->tax,
            'total_value'    => $total
        );

        return [ 'bill' => $data ];
    }
    
    /*
     * Convert API response back to model
     *
     */
    public static function decode( $data )
    {
        $decode = array();
        $single = isset( $data->bill );
        $data   = $single ? [ $data->bill ] : $data->bills;
        foreach ( $data as $object )
        {
            $class = __CLASS__;
            $purchase = new $class;
            $purchase->id         = basename( $object->url );
            $purchase->number     = $object->reference;
            $purchase->issued     = self::dbDate( $object->dated_on );
        	$purchase->due        = self::dbDate( $object->due_on );
            $purchase->supplierId = $object->contact;
            $purchase->category   = $object->category;
            $purchase->status     = $object->status;
            $purchase->total      = $object->total_value;
            $decode[] = $purchase;
        }
        return $single ? $decode[0] : $decode;
    }
    
    /*
     * Get unique id after object creationg
     *
     */
    public static function uid( \Accounting\Interfaces\Model $model, $data )
    {
        $model->id = basename( $data->bill->url );
        $model->number = $data->bill->reference;
        return $model;
    }
}