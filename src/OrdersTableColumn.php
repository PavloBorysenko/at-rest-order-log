<?php

namespace Supernova\AtRestOrderLog;

class OrdersTableColumn {

    private OrderRevisionManager $revision_manager;

    public function __construct(OrderRevisionManager $revision_manager) {
        $this->revision_manager = $revision_manager;
        
        add_filter('manage_woocommerce_page_wc-orders_columns', [$this, 'addRevisionColumn']);
        add_action('manage_woocommerce_page_wc-orders_custom_column', [$this, 'renderRevisionColumn'], 10, 2);
        
        add_filter('manage_edit-shop_order_columns', [$this, 'addRevisionColumn']);
        add_action('manage_shop_order_posts_custom_column', [$this, 'renderRevisionColumnLegacy'], 10, 2);
    }

    public function addRevisionColumn($columns) {
        $new_columns = [];
        
        foreach ($columns as $key => $column) {
            $new_columns[$key] = $column;
            
            if ($key === 'order_number') {
                $new_columns['order_revisions'] = __('Revisions', 'at-rest-order-log');
            }
        }
        
        if (!isset($new_columns['order_revisions'])) {
            $new_columns['order_revisions'] = __('Revisions', 'at-rest-order-log');
        }
        
        return $new_columns;
    }

    public function renderRevisionColumn($column_name, $order) {
        if ($column_name !== 'order_revisions') {
            return;
        }

        if (!($order instanceof \WC_Order)) {
            $order = wc_get_order($order);
        }

        if (!$order) {
            return;
        }

        $this->displayRevisionInfo($order);
    }

    public function renderRevisionColumnLegacy($column_name, $post_id) {
        if ($column_name !== 'order_revisions') {
            return;
        }

        $order = wc_get_order($post_id);
        
        if (!$order) {
            return;
        }

        $this->displayRevisionInfo($order);
    }

    private function displayRevisionInfo(\WC_Order $order) {
        $revisions = $this->revision_manager->getAllOrderRevisions($order);
        $count = count($revisions);

        if ($count > 0) {
            echo '<span class="order-revisions-badge" style="
                display: inline-block;
                padding: 3px 8px;
                background: #2271b1;
                color: white;
                border-radius: 3px;
                font-size: 11px;
                font-weight: 600;
            ">';
            printf(
                esc_html(_n('%d revision', '%d revisions', $count, 'at-rest-order-log')),
                $count
            );
            echo '</span>';
        } else {
            echo '<span style="color: #999; font-size: 11px;">' . esc_html__('No revisions', 'at-rest-order-log') . '</span>';
        }
    }
}