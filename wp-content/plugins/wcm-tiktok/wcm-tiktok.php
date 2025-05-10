<?php
/**
 * Plugin Name: WCM TikTok Shop
 * Plugin URI: https://example.com/wcm-tiktok
 * Description: Tích hợp TikTok Shop với hệ thống quản lý WooCommerce
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: wcm-tiktok
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('WCM_TIKTOK_VERSION', '1.0.0');
define('WCM_TIKTOK_FILE', __FILE__);
define('WCM_TIKTOK_DIR', plugin_dir_path(__FILE__));
define('WCM_TIKTOK_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class WCM_TikTok {
    /**
     * Constructor
     */
    public function __construct() {
        // Register activation and deactivation hooks
        register_activation_hook(WCM_TIKTOK_FILE, array($this, 'activate'));
        register_deactivation_hook(WCM_TIKTOK_FILE, array($this, 'deactivate'));
        
        // Load plugin files
        $this->includes();
        
        // Initialize hooks
        $this->init_hooks();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create custom tables
        require_once WCM_TIKTOK_DIR . 'includes/class-wcm-tiktok-install.php';
        WCM_TikTok_Install::install();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('wcm_tiktok_sync');
    }
    
    /**
     * Include required files
     */
    private function includes() {
        // Core classes
        require_once WCM_TIKTOK_DIR . 'includes/class-wcm-tiktok-api.php';
        require_once WCM_TIKTOK_DIR . 'includes/class-wcm-tiktok-orders.php';
        require_once WCM_TIKTOK_DIR . 'includes/class-wcm-tiktok-products.php';
        
        // Admin classes
        if (is_admin()) {
            require_once WCM_TIKTOK_DIR . 'includes/admin/class-wcm-tiktok-admin.php';
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Register custom post types
        add_action('init', array($this, 'register_post_types'));
        
        // Schedule sync events
        add_action('wcm_tiktok_sync', array($this, 'sync_data'));
        
        // Register REST API endpoints
        add_action('rest_api_init', array($this, 'register_api_endpoints'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'admin_menu'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Register shortcodes
        add_shortcode('wcm_tiktok_orders', array($this, 'tiktok_orders_shortcode'));
        add_shortcode('wcm_tiktok_products', array($this, 'tiktok_products_shortcode'));
    }
    
    /**
     * Register custom post types
     */
    public function register_post_types() {
        // Register 'wcm_tiktok_shop' post type for storing TikTok Shop accounts
        register_post_type('wcm_tiktok_shop', array(
            'labels' => array(
                'name' => __('TikTok Shops', 'wcm-tiktok'),
                'singular_name' => __('TikTok Shop', 'wcm-tiktok'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => array('title', 'custom-fields'),
            'capability_type' => 'post',
            'capabilities' => array(
                'create_posts' => 'manage_options',
            ),
            'map_meta_cap' => true,
        ));
    }
    
    /**
     * Sync data from TikTok Shop
     */
    public function sync_data() {
        // Get all TikTok Shop accounts
        $shops = get_posts(array(
            'post_type' => 'wcm_tiktok_shop',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));
        
        foreach ($shops as $shop) {
            $app_key = get_post_meta($shop->ID, 'app_key', true);
            $app_secret = get_post_meta($shop->ID, 'app_secret', true);
            $access_token = get_post_meta($shop->ID, 'access_token', true);
            $shop_id = get_post_meta($shop->ID, 'shop_id', true);
            
            if (!empty($app_key) && !empty($app_secret) && !empty($access_token) && !empty($shop_id)) {
                // Initialize API connection
                $api = new WCM_TikTok_API($app_key, $app_secret, $access_token, $shop_id);
                
                // Sync orders
                $orders_sync = new WCM_TikTok_Orders($api, $shop->ID);
                $orders_sync->sync();
                
                // Sync products
                $products_sync = new WCM_TikTok_Products($api, $shop->ID);
                $products_sync->sync();
                
                // Log sync completion
                update_post_meta($shop->ID, 'last_sync', current_time('mysql'));
            }
        }
    }
    
    /**
     * Register REST API endpoints
     */
    public function register_api_endpoints() {
        register_rest_route('wcm/v1', '/tiktok/sync', array(
            'methods' => 'POST',
            'callback' => array($this, 'api_sync_data'),
            'permission_callback' => function() {
                return current_user_can('manage_options');
            }
        ));
    }
    
    /**
     * API endpoint for manual sync
     */
    public function api_sync_data($request) {
        $shop_id = $request->get_param('shop_id');
        
        if (!empty($shop_id)) {
            // Sync specific shop
            $shop = get_post($shop_id);
            
            if ($shop && $shop->post_type === 'wcm_tiktok_shop') {
                $app_key = get_post_meta($shop->ID, 'app_key', true);
                $app_secret = get_post_meta($shop->ID, 'app_secret', true);
                $access_token = get_post_meta($shop->ID, 'access_token', true);
                $tiktok_shop_id = get_post_meta($shop->ID, 'shop_id', true);
                
                if (!empty($app_key) && !empty($app_secret) && !empty($access_token) && !empty($tiktok_shop_id)) {
                    // Initialize API connection
                    $api = new WCM_TikTok_API($app_key, $app_secret, $access_token, $tiktok_shop_id);
                    
                    // Sync orders
                    $orders_sync = new WCM_TikTok_Orders($api, $shop->ID);
                    $orders_result = $orders_sync->sync();
                    
                    // Sync products
                    $products_sync = new WCM_TikTok_Products($api, $shop->ID);
                    $products_result = $products_sync->sync();
                    
                    // Log sync completion
                    update_post_meta($shop->ID, 'last_sync', current_time('mysql'));
                    
                    return array(
                        'success' => true,
                        'message' => sprintf(__('Đã đồng bộ dữ liệu từ TikTok Shop %s', 'wcm-tiktok'), $shop->post_title),
                        'data' => array(
                            'orders' => $orders_result,
                            'products' => $products_result,
                        )
                    );
                }
            }
            
            return new WP_Error('invalid_shop', __('TikTok Shop không hợp lệ', 'wcm-tiktok'), array('status' => 400));
        } else {
            // Sync all shops
            $this->sync_data();
            
            return array(
                'success' => true,
                'message' => __('Đã đồng bộ dữ liệu từ tất cả các TikTok Shop', 'wcm-tiktok')
            );
        }
    }
    
    /**
     * Add admin menu
     */
    public function admin_menu() {
        add_menu_page(
            __('WCM TikTok', 'wcm-tiktok'),
            __('WCM TikTok', 'wcm-tiktok'),
            'manage_options',
            'wcm-tiktok',
            array($this, 'admin_page'),
            'dashicons-share',
            35
        );
        
        add_submenu_page(
            'wcm-tiktok',
            __('TikTok Shops', 'wcm-tiktok'),
            __('TikTok Shops', 'wcm-tiktok'),
            'manage_options',
            'edit.php?post_type=wcm_tiktok_shop'
        );
        
        add_submenu_page(
            'wcm-tiktok',
            __('Add New Shop', 'wcm-tiktok'),
            __('Add New Shop', 'wcm-tiktok'),
            'manage_options',
            'post-new.php?post_type=wcm_tiktok_shop'
        );
        
        add_submenu_page(
            'wcm-tiktok',
            __('Settings', 'wcm-tiktok'),
            __('Settings', 'wcm-tiktok'),
            'manage_options',
            'wcm-tiktok-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        include WCM_TIKTOK_DIR . 'includes/admin/views/html-admin-page.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        include WCM_TIKTOK_DIR . 'includes/admin/views/html-settings-page.php';
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'wcm-tiktok') !== false) {
            wp_enqueue_style('wcm-tiktok-admin', WCM_TIKTOK_URL . 'assets/css/admin.css', array(), WCM_TIKTOK_VERSION);
            wp_enqueue_script('wcm-tiktok-admin', WCM_TIKTOK_URL . 'assets/js/admin.js', array('jquery'), WCM_TIKTOK_VERSION, true);
            
            wp_localize_script('wcm-tiktok-admin', 'wcm_tiktok', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'rest_url' => rest_url('wcm/v1/tiktok/sync'),
                'nonce' => wp_create_nonce('wp_rest'),
                'i18n' => array(
                    'syncing' => __('Đang đồng bộ...', 'wcm-tiktok'),
                    'sync_complete' => __('Đồng bộ hoàn tất!', 'wcm-tiktok'),
                    'sync_error' => __('Lỗi đồng bộ!', 'wcm-tiktok'),
                )
            ));
        }
    }
    
    /**
     * TikTok orders shortcode
     */
    public function tiktok_orders_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 20,
            'status' => '',
            'shop_id' => 0,
        ), $atts, 'wcm_tiktok_orders');
        
        ob_start();
        
        $orders = new WCM_TikTok_Orders(null, $atts['shop_id']);
        $orders->display_orders($atts);
        
        return ob_get_clean();
    }
    
    /**
     * TikTok products shortcode
     */
    public function tiktok_products_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 20,
            'status' => '',
            'shop_id' => 0,
        ), $atts, 'wcm_tiktok_products');
        
        ob_start();
        
        $products = new WCM_TikTok_Products(null, $atts['shop_id']);
        $products->display_products($atts);
        
        return ob_get_clean();
    }
}

// Initialize the plugin
new WCM_TikTok();
