<?php
/**
 * Plugin Name: WCM Orders
 * Plugin URI: https://example.com/wcm-orders
 * Description: Quản lý đơn hàng từ nhiều site WooCommerce
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: wcm-orders
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('WCM_ORDERS_VERSION', '1.0.0');
define('WCM_ORDERS_FILE', __FILE__);
define('WCM_ORDERS_DIR', plugin_dir_path(__FILE__));
define('WCM  __FILE__);
define('WCM_ORDERS_DIR', plugin_dir_path(__FILE__));
define('WCM_ORDERS_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class WCM_Orders {
    /**
     * Constructor
     */
    public function __construct() {
        // Register activation hook
        register_activation_hook(WCM_ORDERS_FILE, array($this, 'activate'));
        
        // Load plugin files
        $this->includes();
        
        // Initialize hooks
        $this->init_hooks();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create custom tables if needed
    }
    
    /**
     * Include required files
     */
    private function includes() {
        // Core classes
        require_once WCM_ORDERS_DIR . 'includes/class-wcm-orders-list.php';
        require_once WCM_ORDERS_DIR . 'includes/class-wcm-order-details.php';
        require_once WCM_ORDERS_DIR . 'includes/class-wcm-order-actions.php';
        
        // Admin classes
        if (is_admin()) {
            require_once WCM_ORDERS_DIR . 'includes/admin/class-wcm-orders-admin.php';
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Register shortcodes
        add_shortcode('wcm_orders', array($this, 'orders_shortcode'));
        add_shortcode('wcm_order_details', array($this, 'order_details_shortcode'));
        
        // Register AJAX handlers
        add_action('wp_ajax_wcm_update_order_status', array($this, 'update_order_status'));
        add_action('wp_ajax_wcm_filter_orders', array($this, 'filter_orders'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Orders shortcode
     */
    public function orders_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 20,
            'status' => '',
            'site_id' => 0,
        ), $atts, 'wcm_orders');
        
        ob_start();
        
        $orders_list = new WCM_Orders_List();
        $orders_list->display($atts);
        
        return ob_get_clean();
    }
    
    /**
     * Order details shortcode
     */
    public function order_details_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts, 'wcm_order_details');
        
        if (empty($atts['id'])) {
            return '<p>' . __('Order ID is required.', 'wcm-orders') . '</p>';
        }
        
        ob_start();
        
        $order_details = new WCM_Order_Details();
        $order_details->display($atts['id']);
        
        return ob_get_clean();
    }
    
    /**
     * Update order status
     */
    public function update_order_status() {
        check_ajax_referer('wcm_orders_nonce', 'nonce');
        
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $site_id = isset($_POST['site_id']) ? intval($_POST['site_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        
        if (empty($order_id) || empty($site_id) || empty($status)) {
            wp_send_json_error(array(
                'message' => __('Invalid parameters.', 'wcm-orders')
            ));
        }
        
        $order_actions = new WCM_Order_Actions();
        $result = $order_actions->update_status($order_id, $site_id, $status);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => $result->get_error_message()
            ));
        }
        
        wp_send_json_success(array(
            'message' => __('Order status updated successfully.', 'wcm-orders')
        ));
    }
    
    /**
     * Filter orders
     */
    public function filter_orders() {
        check_ajax_referer('wcm_orders_nonce', 'nonce');
        
        $filters = isset($_POST['filters']) ? $_POST['filters'] : array();
        
        $orders_list = new WCM_Orders_List();
        $orders = $orders_list->get_orders($filters);
        
        wp_send_json_success(array(
            'orders' => $orders
        ));
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style('wcm-orders', WCM_ORDERS_URL . 'assets/css/wcm-orders.css', array(), WCM_ORDERS_VERSION);
        wp_enqueue_script('wcm-orders', WCM_ORDERS_URL . 'assets/js/wcm-orders.js', array('jquery'), WCM_ORDERS_VERSION, true);
        
        wp_localize_script('wcm-orders', 'wcm_orders', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wcm_orders_nonce'),
            'i18n' => array(
                'confirm_status_change' => __('Bạn có chắc chắn muốn thay đổi trạng thái đơn hàng?', 'wcm-orders'),
                'loading' => __('Đang xử lý...', 'wcm-orders'),
                'error' => __('Đã xảy ra lỗi. Vui lòng thử lại.', 'wcm-orders'),
            )
        ));
    }
}

// Initialize the plugin
new WCM_Orders();
