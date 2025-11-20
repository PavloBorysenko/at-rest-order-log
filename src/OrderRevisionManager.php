<?php

namespace Supernova\AtRestOrderLog;

class OrderRevisionManager {
    public function createOrderRevision( $order_id, $order = null ) {
        if ( ! $order ) {
            $order = wc_get_order( $order_id );
        }
    
        if ( ! $order ) {
            return false;
        }
        
        $key = $this->generateKey();
    
        $snapshot = $this->getSnapshot( $order );
    
        $this->saveRevision( $order, $key, $snapshot );
    
        return true;
    }

    public function getAllOrderRevisions( \WC_Order $order ) : array {
        $meta_data = $order->get_meta_data();
        $revisions = $this->parseRevisions( $meta_data );
        return $revisions;
    }   

    private function generateKey() : string {
        return "_revision/" . time() . "/" . wp_generate_password(12, false);
    }
    private function getSnapshot( \WC_Order $order ) : array {
        $snapshot = [
            'order_id'   => $order->get_id(),
            'created_at' => gmdate('c', time()),
            'status'     => $order->get_status(),
            'user_id'    => get_current_user_id(),
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
    private function saveRevision(\WC_Order $order, string $key, array $snapshot ) {
        $order->add_meta_data( $key, wp_json_encode($snapshot), false );
        $order->save();
    }

    private function parseRevisions( array $meta_data ) : array {
        $revisions = [];
        foreach ( $meta_data as $meta ) {
            $key = $meta->key;
            if ( strpos( $key, '_revision/' ) === 0 ) {
                $data = json_decode( $meta->value, true );
                if ( $data ) {
                    preg_match( '/_revision\/(\d+)\//', $key, $matches );
                    $timestamp = isset( $matches[1] ) ? (int) $matches[1] : 0;
                    $revisions[ $timestamp ] = [
                        'key'       => $key,
                        'timestamp' => $timestamp,
                        'data'      => $data,
                    ];
                }
            }
        }
        krsort( $revisions );
        return $revisions;
    }
}