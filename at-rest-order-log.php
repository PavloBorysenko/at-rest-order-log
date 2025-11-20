<?php
/**
 * Plugin Name: At Rest Order Log
 * Description: A plugin to log order changes
 * Version: 1.0.1
 * Author: Na-Gora
 */

define('AT_REST_ORDER_LOG_DIR', plugin_dir_path(__FILE__));
define('AT_REST_ORDER_LOG_URL', plugin_dir_url(__FILE__));

require_once AT_REST_ORDER_LOG_DIR . '/src/OrderRevisionManager.php';
require_once AT_REST_ORDER_LOG_DIR . '/src/OrderMetaBox.php';
require_once AT_REST_ORDER_LOG_DIR . '/src/OrderUpdateHandler.php';
require_once AT_REST_ORDER_LOG_DIR . '/src/OrdersTableColumn.php';

$revision_manager = new \Supernova\AtRestOrderLog\OrderRevisionManager();
new \Supernova\AtRestOrderLog\OrderMetaBox( $revision_manager );
new \Supernova\AtRestOrderLog\OrderUpdateHandler( $revision_manager );
new \Supernova\AtRestOrderLog\OrdersTableColumn( $revision_manager );

