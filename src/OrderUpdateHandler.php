<?php

namespace Supernova\AtRestOrderLog;

class OrderUpdateHandler {
    private OrderRevisionManager $order_manager;
    
    public function __construct(OrderRevisionManager $order_manager) {
        $this->order_manager = $order_manager;
        
        add_action( 'woocommerce_process_shop_order_meta', [$this, 'createRevisionBeforeUpdate'], 1, 2 );
    }

    public function createRevisionBeforeUpdate( $order_id, $post = null ) {
    
        
        if ( ! $this->isUpdateButtonClicked( $order_id ) ) {
            return;
        }

        $this->processRevision( $order_id );
    }

    private function processRevision( $order_id ) {
        static $processed_orders = array();
        
        if ( isset( $processed_orders[ $order_id ] ) ) {
            return;
        }
    
        wp_cache_delete( $order_id, 'orders' );
        
        if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
            wp_cache_delete( $order_id, 'wc-orders' );
        }
        
        $old_order = wc_get_order( $order_id );
        
        if ( ! $old_order ) {
            return;
        }
    
        if ( $old_order->is_paid() ) {
            return;
        }
    

        $processed_orders[ $order_id ] = true;
        
        $this->order_manager->createOrderRevision( $order_id, $old_order );
    }

    private function isUpdateButtonClicked( $order_id ) : bool {
        if ( ! $order_id ) {
            return false;
        }
    
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return false;
        }
    
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return false;
        }
    
        if ( wp_is_post_revision( $order_id ) || wp_is_post_autosave( $order_id ) ) {
            return false;
        }
    
        if ( ! current_user_can( 'edit_post', $order_id ) ) {
            return false;
        }
    
        if ( isset($_GET['action']) && $_GET['action'] === 'atrest_resend_invoice_pdf' ) {
            return false;
        }
    
        if ( ! isset( $_POST['action'] ) ) {
            return false;
        }
    
        $action = sanitize_text_field( wp_unslash( $_POST['action'] ) );
        
        if ( $action !== 'edit_order' ) {
            return false;
        }
    
        if ( ! isset( $_POST['_wpnonce'] ) || ! isset( $_POST['post_ID'] ) ) {
            return false;
        }
    
        if ( (int) $_POST['post_ID'] !== $order_id ) {
            return false;
        }
    
        return true;
    }
}