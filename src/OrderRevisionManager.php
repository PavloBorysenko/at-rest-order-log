<?php

namespace Supernova\AtRestOrderLog;

class OrderRevisionManager {

    private OrderRevisionDatabase $order_revision_database;

    public function __construct(OrderRevisionDatabase $order_revision_database) {
        $this->order_revision_database = $order_revision_database;
    }

    public function createOrderRevision( $order_id, $order = null ) {
        if ( ! $order ) {
            $order = wc_get_order( $order_id );
        }
    
        if ( ! $order ) {
            return false;
        }
        
        $snapshot = $this->getSnapshot( $order );
    
        $this->saveRevision( $order, $snapshot );
    
        return true;
    }

    public function getAllOrderRevisions( \WC_Order $order ) : array {
        $all_revisions = $this->order_revision_database->getRevisionsByOrderId( $order->get_id(), 20 );
        $revisions = $this->parseRevisions( $all_revisions );
        return $revisions;
    }   

    private function getSnapshot( \WC_Order $order ) : array {
        $snapshot = [
            'created_at' => gmdate('c', time()),
            'items'      => [],
            'billing'    => $order->get_address('billing'),
            'shipping'   => $order->get_address('shipping'),
            'totals'     => [
                'subtotal' => $order->get_subtotal(),
                'total'    => $order->get_total(),
            ],
            'custom_meta' => $order->get_meta_data()
        ];
    
        foreach ( $order->get_items() as $item_id => $item ) {
            $snapshot['items'][] = [
                'item_id'    => $item_id,
                'product_id' => $item->get_product_id(),
                'name'       => $item->get_name(),
                'qty'        => $item->get_quantity(),
                'subtotal'   => $item->get_subtotal(),
                'total'      => $item->get_total(),
                'meta'       => $item->get_meta_data(),
            ];
        }
        foreach ( $order->get_items( 'fee' ) as $item_id => $item ) {
            $snapshot['fees'][] = [
                'item_id'  => $item_id,
                'name'     => $item->get_name(),
                'total'    => $item->get_total(),
                'tax'      => $item->get_total_tax(),
                'tax_class'=> $item->get_tax_class(),
            ];
        }       
        return $snapshot;
    }
    private function saveRevision(\WC_Order $order, array $snapshot ) {
        $this->order_revision_database->saveRevision( 
            $order->get_id(), 
            get_current_user_id(), 
            $order->get_status(), 
            $snapshot 
        );
    }

    private function parseRevisions( array $all_revisions ) : array {
        // TODO: parse revisions
        return $all_revisions ;
    }
    
    public function getRevisionCount( int $order_id ) : int {
        return $this->order_revision_database->getRevisionCount( $order_id );
    }
}