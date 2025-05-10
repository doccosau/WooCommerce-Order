<?php
/**
 * WCM Sync Orders
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * WCM_Sync_Orders Class
 */
class WCM_Sync_Orders {
    /**
     * API instance
     *
     * @var WCM_Sync_API
     */
    private $api;
    
    /**
     * Site ID
     *
     * @var int
     */
    private $site_id;
    
    /**
     * Constructor
     *
     * @param WCM_Sync_API $api
     * @param int $site_id
     */
    public function __construct($api, $site_id) {
        $this->api = $api;
        $this->site_id = $site_id;
    }
    
    /**
     * Sync orders
     *
     * @return array
     */
    public function sync() {
        global $wpdb;
        
        $result = array(
            'synced' => 0,
            'updated' => 0,
            'errors' => 0,
        );
        
        // Get orders from the last X days
        $days = get_option('wcm_sync_orders_days', 30);
        $after = date('Y-m-d\TH:i:s', strtotime("-{$days} days"));
        
        // Get orders from API
        $page = 1;
        $per_page = 50;
        $more_pages = true;
        
        while ($more_pages) {
            $orders = $this->api->get_orders(array(
                'after' => $after,
                'page' => $page,
                'per_page' => $per_page,
            ));
            
            if (is_wp_error($orders)) {
                // Log error
                $this->log_sync('orders', 'error', 0, array(
                    'error' => $orders->get_error_message(),
                ));
                
                $result['errors']++;
                break;
            }
            
            if (empty($orders)) {
                $more_pages = false;
                continue;
            }
            
            foreach ($orders as $order) {
                // Check if order exists
                $existing_order = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}wcm_orders WHERE order_id = %d AND site_id = %d",
                        $order['id'],
                        $this->site_id
                    )
                );
                
                if ($existing_order) {
                    // Update existing order
                    $updated = $wpdb->update(
                        "{$wpdb->prefix}wcm_orders",
                        array(
                            'status' => $order['status'],
                            'total' => $order['total'],
                            'date_modified' => $order['date_modified_gmt'],
                            'order_data' => json_encode($order),
                        ),
                        array(
                            'order_id' => $order['id'],
                            'site_id' => $this->site_id,
                        )
                    );
                    
                    if ($updated) {
                        $result['updated']++;
                    } else {
                        $result['errors']++;
                    }
                } else {
                    // Insert new order
                    $inserted = $wpdb->insert(
                        "{$wpdb->prefix}wcm_orders",
                        array(
                            'order_id' => $order['id'],
                            'site_id' => $this->site_id,
                            'customer_id' => $order['customer_id'],
                            'status' => $order['status'],
                            'total' => $order['total'],
                            'date_created' => $order['date_created_gmt'],
                            'date_modified' => $order['date_modified_gmt'],
                            'order_data' => json_encode($order),
                        )
                    );
                    
                    if ($inserted) {
                        $result['synced']++;
                    } else {
                        $result['errors']++;
                    }
                }
            }
            
            // Check if we need to fetch more pages
            if (count($orders) < $per_page) {
                $more_pages = false;
            } else {
                $page++;
            }
        }
        
        // Log sync completion
        $this->log_sync('orders', 'complete', $result['synced'] + $result['updated'], $result);
        
        return $result;
    }
    
    /**
     * Log sync
     *
     * @param string $sync_type
     * @param string $status
     * @param int $items_synced
     * @param array $data
     */
    private function log_sync($sync_type, $status, $items_synced, $data = array()) {
        global $wpdb;
        
        $wpdb->insert(
            "{$wpdb->prefix}wcm_sync_logs",
            array(
                'site_id' => $this->site_id,
                'sync_type' => $sync_type,
                'status' => $status,
                'items_synced' => $items_synced,
                'date_synced' => current_time('mysql'),
                'log_data' => !empty($data) ? json_encode($data) : null,
            )
        );
    }
}
