<?php
/**
 * WCM Sync Install
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * WCM_Sync_Install Class
 */
class WCM_Sync_Install {
    /**
     * Install the plugin
     */
    public static function install() {
        self::create_tables();
        self::create_options();
        self::schedule_events();
    }
    
    /**
     * Create custom tables
     */
    private static function create_tables() {
        global $wpdb;
        
        $wpdb->hide_errors();
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        $collate = '';
        
        if ($wpdb->has_cap('collation')) {
            $collate = $wpdb->get_charset_collate();
        }
        
        // Orders table
        $tables = "
        CREATE TABLE {$wpdb->prefix}wcm_orders (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            site_id bigint(20) NOT NULL,
            customer_id bigint(20) NOT NULL,
            status varchar(100) NOT NULL,
            total decimal(19,4) NOT NULL DEFAULT 0,
            date_created datetime NOT NULL,
            date_modified datetime NOT NULL,
            order_data longtext NOT NULL,
            PRIMARY KEY (id),
            KEY order_id (order_id),
            KEY site_id (site_id),
            KEY customer_id (customer_id),
            KEY status (status),
            KEY date_created (date_created)
        ) $collate;
        
        CREATE TABLE {$wpdb->prefix}wcm_products (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            site_id bigint(20) NOT NULL,
            name varchar(255) NOT NULL,
            sku varchar(100) NOT NULL,
            price decimal(19,4) NOT NULL DEFAULT 0,
            regular_price decimal(19,4) NOT NULL DEFAULT 0,
            sale_price decimal(19,4) DEFAULT NULL,
            stock_quantity int(11) DEFAULT NULL,
            stock_status varchar(100) NOT NULL,
            product_type varchar(100) NOT NULL,
            date_created datetime NOT NULL,
            date_modified datetime NOT NULL,
            product_data longtext NOT NULL,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY site_id (site_id),
            KEY sku (sku),
            KEY stock_status (stock_status),
            KEY product_type (product_type)
        ) $collate;
        
        CREATE TABLE {$wpdb->prefix}wcm_customers (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            customer_id bigint(20) NOT NULL,
            site_id bigint(20) NOT NULL,
            email varchar(255) NOT NULL,
            first_name varchar(255) NOT NULL,
            last_name varchar(255) NOT NULL,
            username varchar(100) NOT NULL,
            date_created datetime NOT NULL,
            date_modified datetime NOT NULL,
            customer_data longtext NOT NULL,
            PRIMARY KEY (id),
            KEY customer_id (customer_id),
            KEY site_id (site_id),
            KEY email (email)
        ) $collate;
        
        CREATE TABLE {$wpdb->prefix}wcm_sync_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            site_id bigint(20) NOT NULL,
            sync_type varchar(100) NOT NULL,
            status varchar(100) NOT NULL,
            items_synced int(11) NOT NULL DEFAULT 0,
            date_synced datetime NOT NULL,
            log_data longtext DEFAULT NULL,
            PRIMARY KEY (id),
            KEY site_id (site_id),
            KEY sync_type (sync_type),
            KEY status (status),
            KEY date_synced (date_synced)
        ) $collate;
        ";
        
        dbDelta($tables);
    }
    
    /**
     * Create default options
     */
    private static function create_options() {
        add_option('wcm_sync_interval', 'hourly');
        add_option('wcm_sync_orders_days', 30);
        add_option('wcm_sync_products_limit', 100);
        add_option('wcm_sync_customers_limit', 100);
    }
    
    /**
     * Schedule events
     */
    private static function schedule_events() {
        $interval = get_option('wcm_sync_interval', 'hourly');
        
        if (!wp_next_scheduled('wcm_sync_data')) {
            wp_schedule_event(time(), $interval, 'wcm_sync_data');
        }
    }
}
