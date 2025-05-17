<?php
/**
 * WooCommerce Database
 * 
 * Quản lý cơ sở dữ liệu cho hệ thống
 */
class Woo_Database {
    /**
     * Khởi tạo class
     */
    public function __construct() {
        // Đăng ký hook kích hoạt plugin
        register_activation_hook(WOOCENTER_PLUGIN_FILE, array($this, 'create_tables'));
    }

    /**
     * Tạo các bảng cơ sở dữ liệu
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Bảng sản phẩm
        $table_products = $wpdb->prefix . 'woocenter_products';
        
        $sql_products = "CREATE TABLE $table_products (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            site_id varchar(50) NOT NULL,
            product_id bigint(20) NOT NULL,
            name varchar(255) NOT NULL,
            sku varchar(100) DEFAULT '',
            price varchar(20) DEFAULT '',
            regular_price varchar(20) DEFAULT '',
            sale_price varchar(20) DEFAULT '',
            status varchar(20) DEFAULT 'publish',
            stock_status varchar(20) DEFAULT 'instock',
            stock_quantity int(11) DEFAULT 0,
            categories longtext DEFAULT NULL,
            images longtext DEFAULT NULL,
            data longtext DEFAULT NULL,
            last_updated datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY site_product (site_id,product_id)
        ) $charset_collate;";
        
        // Bảng đơn hàng
        $table_orders = $wpdb->prefix . 'woocenter_orders';
        
        $sql_orders = "CREATE TABLE $table_orders (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            site_id varchar(50) NOT NULL,
            order_id bigint(20) NOT NULL,
            number varchar(100) DEFAULT '',
            status varchar(20) DEFAULT '',
            date_created datetime DEFAULT NULL,
            total varchar(20) DEFAULT '',
            customer_id bigint(20) DEFAULT 0,
            billing longtext DEFAULT NULL,
            shipping longtext DEFAULT NULL,
            payment_method varchar(100) DEFAULT '',
            payment_method_title varchar(100) DEFAULT '',
            line_items longtext DEFAULT NULL,
            data longtext DEFAULT NULL,
            last_updated datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY site_order (site_id,order_id)
        ) $charset_collate;";
        
        // Bảng khách hàng
        $table_customers = $wpdb->prefix . 'woocenter_customers';
        
        $sql_customers = "CREATE TABLE $table_customers (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            site_id varchar(50) NOT NULL,
            customer_id bigint(20) NOT NULL,
            email varchar(100) DEFAULT '',
            first_name varchar(100) DEFAULT '',
            last_name varchar(100) DEFAULT '',
            username varchar(100) DEFAULT '',
            billing longtext DEFAULT NULL,
            shipping longtext DEFAULT NULL,
            is_paying_customer tinyint(1) DEFAULT 0,
            avatar_url varchar(255) DEFAULT '',
            data longtext DEFAULT  DEFAULT 0,
            avatar_url varchar(255) DEFAULT '',
            data longtext DEFAULT NULL,
            last_updated datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY site_customer (site_id,customer_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_products);
        dbDelta($sql_orders);
        dbDelta($sql_customers);
    }

    /**
     * Xóa các bảng cơ sở dữ liệu
     */
    public function drop_tables() {
        global $wpdb;
        
        $table_products = $wpdb->prefix . 'woocenter_products';
        $table_orders = $wpdb->prefix . 'woocenter_orders';
        $table_customers = $wpdb->prefix . 'woocenter_customers';
        
        $wpdb->query("DROP TABLE IF EXISTS $table_products");
        $wpdb->query("DROP TABLE IF EXISTS $table_orders");
        $wpdb->query("DROP TABLE IF EXISTS $table_customers");
    }
}
