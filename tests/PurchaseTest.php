<?php

use FreeAgent\Supplier;
use FreeAgent\Purchase;
use FreeAgent\Item;

class PurchaseTest extends TestCase
{    
    public function testCreate()
    {
        $suppliers = $this->api->find( 'supplier' );
        
        $purchase = new Purchase;
        $purchase->number   = 'PO-122';
        $purchase->issued   = '2015-07-02 22:44:00';
        $purchase->due      = '2015-08-02 22:44:00';
        $purchase->terms    = 30;
        $purchase->supplier = $suppliers[0];
        $purchase->currency = 'GBP';
        $purchase->notes    = 'Project #P123';
        
        $item = new Item;
        $item->description = 'Example';
        $item->quantity    = 10;
        $item->price       = 100;
        
        $purchase->items[] = $item;
        
        $purchase = $this->api->save( $purchase );
        
        $this->assertTrue( is_numeric($purchase->id) );
    }
    
    public function testFind()
    {
        $purchases = $this->api->find( 'purchase' );
        $this->assertTrue( count($purchases) > 0 );
    }
    
    public function testFindOne()
    {
        $purchases = $this->api->find( 'purchase' );
        $purchase  = $this->api->find( 'purchase', $purchases[0]->id );
        $this->assertTrue( is_a($purchase, '\Accounting\Interfaces\Model') );
    }
    
    public function testSearch()
    {
        $purchases = $this->api->find( 'purchase' );
        $purchases = $this->api->search( 'purchase', $purchases[0]->number );
        $this->assertTrue( count($purchases) > 0 );
    }
    
    public function testUpdate()
    {
        $purchases = $this->api->find( 'purchase' );
        $purchase  = $purchases[0];
        $purchase->due = $due = '2015-07-20 22:44:00';
        $this->api->save( $purchase );
        $purchase = $this->api->find( 'purchase', $purchase->id );
        $this->assertTrue( $purchase->due == '2015-07-20 00:00:00' );
    }
    
    public function testDelete()
    {
        $purchases = $this->api->find( 'purchase' );
        $purchase  = $this->api->delete( $purchases[0] );
        $this->assertTrue( $purchase );
    }
}