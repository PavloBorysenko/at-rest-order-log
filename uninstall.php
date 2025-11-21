<?php
/**
 * Uninstall script for At Rest Order Log plugin
 * 
 * IMPORTANT: This file is executed ONLY when the plugin is DELETED, NOT when deactivated.
 * 
 * WordPress execution flow:
 * 1. Deactivate plugin - data is preserved, this file is NOT executed
 * 2. Delete plugin - this file runs and removes all data
 * 
 * This file removes:
 * - Custom database table (wp_order_revisions)
 * - Plugin options (if any)
 * - Cached data
 */

// This security check ensures the file is only executed during proper uninstall
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Include the database class
require_once plugin_dir_path(__FILE__) . 'src/OrderRevisionDatabase.php';

// Create instance and drop the table
$database = new \Supernova\AtRestOrderLog\OrderRevisionDatabase();
$database->dropTable();

// Optionally: Clean up any plugin options if we add them in the future
// delete_option('at_rest_order_log_version');
// delete_option('at_rest_order_log_settings');

// Clear any cached data
wp_cache_flush();

