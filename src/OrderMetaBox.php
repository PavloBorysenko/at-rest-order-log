<?php

namespace Supernova\AtRestOrderLog;

class OrderMetaBox {

    private OrderRevisionManager $order_manager;
    public function __construct(OrderRevisionManager $order_manager) {
        $this->order_manager = $order_manager;
        add_action( 'add_meta_boxes', [$this, 'addOrderRevisionsMetaBox'] );
        add_action( 'add_meta_boxes_shop_order', [$this, 'addOrderRevisionsMetaBox'], 20 );
        add_action( 'admin_enqueue_scripts', [$this, 'addCss'] );
    }

    public function addOrderRevisionsMetaBox() {
        if ( function_exists( 'wc_get_page_screen_id' ) ) {
            $screen_id = wc_get_page_screen_id( 'shop-order' );
        } else {
            $screen_id = 'shop_order';
        }
        
        add_meta_box(
            'at-rest-order-revisions',
            esc_html__( 'Order Revision History', 'at-rest-order-log' ),
            [$this, 'renderOrderRevisionsMetaBox'],
            $screen_id,
            'normal',
            'default'
        );
    }
    public function renderOrderRevisionsMetaBox( $post_or_order  ) {

        $order = ( $post_or_order instanceof WC_Order ) ? $post_or_order : wc_get_order( $post_or_order );
    
        if ( ! $order ) {
            return;
        }

        $revisions = $this->order_manager->getAllOrderRevisions( $order );
    
        if ( empty( $revisions ) ) {
           
            require_once AT_REST_ORDER_LOG_DIR . '/views/order-revisions-meta-box-empty.php';
            return;
        }
        
        require_once AT_REST_ORDER_LOG_DIR . '/views/order-revisions-meta-box.php';
    }
    public function addCss() {
        $screen = get_current_screen();
        
        if ( ! $screen ) {
            return;
        }
        
        $allowed_screens = ['shop_order', 'woocommerce_page_wc-orders'];
        
        if ( in_array( $screen->id, $allowed_screens, true ) ) {
            wp_enqueue_style( 'at-rest-order-log-revision', AT_REST_ORDER_LOG_URL . '/assets/css/revision.css' );
        }
    }
}