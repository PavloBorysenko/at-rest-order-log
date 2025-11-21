<?php
/**
 * Plugin Name: At Rest Order Log
 * Description: A plugin to log order changes
 * Version: 1.0.1
 * Author: Na-Gora
 */

define('AT_REST_ORDER_LOG_DIR', plugin_dir_path(__FILE__));
define('AT_REST_ORDER_LOG_URL', plugin_dir_url(__FILE__));

require_once AT_REST_ORDER_LOG_DIR . '/src/OrderRevisionDatabase.php';
require_once AT_REST_ORDER_LOG_DIR . '/src/OrderRevisionManager.php';
require_once AT_REST_ORDER_LOG_DIR . '/src/OrderMetaBox.php';
require_once AT_REST_ORDER_LOG_DIR . '/src/OrderUpdateHandler.php';
require_once AT_REST_ORDER_LOG_DIR . '/src/OrdersTableColumn.php';

// Activation hook - create database table
register_activation_hook(__FILE__, 'at_rest_order_log_activate');

function at_rest_order_log_activate() {
    $database = new \Supernova\AtRestOrderLog\OrderRevisionDatabase();
    $database->createTable();
    
    // Save plugin version for future updates
    update_option('at_rest_order_log_version', '1.0.1');
}

$order_revision_database = new \Supernova\AtRestOrderLog\OrderRevisionDatabase();
$revision_manager = new \Supernova\AtRestOrderLog\OrderRevisionManager( $order_revision_database );
new \Supernova\AtRestOrderLog\OrderMetaBox( $revision_manager );
new \Supernova\AtRestOrderLog\OrderUpdateHandler( $revision_manager );
new \Supernova\AtRestOrderLog\OrdersTableColumn( $revision_manager );

