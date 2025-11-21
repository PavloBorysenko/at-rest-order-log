<?php

namespace Supernova\AtRestOrderLog;

class OrderRevisionDatabase {
    
    private string $table_name;
    private string $charset_collate;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'order_revisions';
        $this->charset_collate = $wpdb->get_charset_collate();
    }
    
    public function createTable() {
        global $wpdb;
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            order_status VARCHAR(50) NOT NULL DEFAULT '',
            revision_data LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            INDEX idx_order_id (order_id),
            INDEX idx_created_at (created_at),
            INDEX idx_order_user (order_id, user_id)
        ) ENGINE=InnoDB {$this->charset_collate};";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    public function saveRevision(int $order_id, int $user_id, string $order_status, array $revision_data) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->table_name,
            [
                'order_id'       => $order_id,
                'user_id'        => $user_id,
                'order_status'   => $order_status,
                'revision_data'  => wp_json_encode($revision_data),
                'created_at'     => current_time('mysql'),
            ],
            [
                '%d', // order_id
                '%d', // user_id
                '%s', // order_status
                '%s', // revision_data
                '%s', // created_at
            ]
        );
        
        if ($result === false) {
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    public function getRevisionsByOrderId(int $order_id, int $limit = 0) : array {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT id, order_id, user_id, order_status, revision_data, created_at
             FROM {$this->table_name}
             WHERE order_id = %d
             ORDER BY created_at DESC",
            $order_id
        );
        
        if ($limit > 0) {
            $sql .= $wpdb->prepare(" LIMIT %d", $limit);
        }
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        if (empty($results)) {
            return [];
        }
        
        // Decode JSON data for each revision
        foreach ($results as &$revision) {
            $revision['revision_data'] = json_decode($revision['revision_data'], true);
        }
        unset($revision);
        
        return $results;
    }
    
    public function getRevisionById(int $revision_id) : array {
        global $wpdb;
        
        $revision = $wpdb->get_row($wpdb->prepare(
            "SELECT id, order_id, user_id, order_status, revision_data, created_at
             FROM {$this->table_name}
             WHERE id = %d",
            $revision_id
        ), ARRAY_A);
        
        if ($revision) {
            $revision['revision_data'] = json_decode($revision['revision_data'], true);
        }
        
        return $revision ? $revision : [];
    }
    
    public function deleteRevisionsByOrderId(int $order_id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_name,
            ['order_id' => $order_id],
            ['%d']
        );
    }
    

    public function deleteRevisionById(int $revision_id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_name,
            ['id' => $revision_id],
            ['%d']
        );
    }
    
    public function getRevisionCount(int $order_id) : int {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE order_id = %d",
            $order_id
        ));
        
        return (int) $count;
    }

    public function dropTable() {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$this->table_name}");
    }
}

