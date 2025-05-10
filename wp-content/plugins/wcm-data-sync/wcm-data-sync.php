<?php
/**
 * Plugin Name: WCM Data Sync
 * Plugin URI: https://example.com/wcm-data-sync
 * Description: Đồng bộ dữ liệu từ các site WooCommerce con về hệ thống quản lý trung tâm
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: wcm-data-sync
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('WCM_SYNC_VERSION', '1.0.0');
define('WCM_SYNC_FILE', __FILE__);
define('WCM_SYNC_DIR', plugin_dir_path(__FILE__));
define('WCM_SYNC_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class WCM_Data_Sync {
    /**
     * Constructor
     */
    public function __construct() {
        // Register activation and deactivation hooks
        register_activation_hook(WCM_SYNC_FILE, array($this, 'activate'));
        register_deactivation_hook(WCM_SYNC_FILE, array($this, 'deactivate'));
        
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
        require_once WCM_SYNC_DIR . 'includes/class-wcm-sync-install.php';
        WCM_Sync_Install::install();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('wcm_sync_data');
    }
    
    /**
     * Include required files
     */
    private function includes() {
        // Core classes
        require_once WCM_SYNC_DIR . 'includes/class-wcm-sync-api.php';
        require_once WCM_SYNC_DIR . 'includes/class-wcm-sync-orders.php';
        require_once WCM_SYNC_DIR . 'includes/class-wcm-sync-products.php';
        require_once WCM_SYNC_DIR . 'includes/class-wcm-sync-customers.php';
        
        // Admin classes
        if (is_admin()) {
            require_once WCM_SYNC_DIR . 'includes/admin/class-wcm-sync-admin.php';
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Register custom post types
        add_action('init', array($this, 'register_post_types'));
        
        // Schedule sync events
        add_action('wcm_sync_data', array($this, 'sync_data'));
        
        // Register REST API endpoints
        add_action('rest_api_init', array($this, 'register_api_endpoints'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'admin_menu'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Register custom post types
     */
    public function register_post_types() {
        // Register 'wcm_site' post type for storing connected sites
        register_post_type('wcm_site', array(
            'labels' => array(
                'name' => __('Connected Sites', 'wcm-data-sync'),
                'singular_name' => __('Connected Site', 'wcm-data-sync'),
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
     * Sync data from connected sites
     */
    public function sync_data() {
        // Get all connected sites
        $sites = get_posts(array(
            'post_type' => 'wcm_site',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));
        
        foreach ($sites as $site) {
            $site_url = get_post_meta($site->ID, 'site_url', true);
            $consumer_key = get_post_meta($site->ID, 'consumer_key', true);
            $consumer_secret = get_post_meta($site->ID, 'consumer_secret', true);
            
            if (!empty($site_url) && !empty($consumer_key) && !empty($consumer_secret)) {
                // Initialize API connection
                $api = new WCM_Sync_API($site_url, $consumer_key, $consumer_secret);
                
                // Sync orders
                $orders_sync = new WCM_Sync_Orders($api, $site->ID);
                $orders_sync->sync();
                
                // Sync products
                $products_sync = new WCM_Sync_Products($api, $site->ID);
                $products_sync->sync();
                
                // Sync customers
                $customers_sync = new WCM_Sync_Customers($api, $site->ID);
                $customers_sync->sync();
                
                // Log sync completion
                update_post_meta($site->ID, 'last_sync', current_time('mysql'));
            }
        }
    }
    
    /**
     * Register REST API endpoints
     */
    public function register_api_endpoints() {
        register_rest_route('wcm/v1', '/sync', array(
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
        $site_id = $request->get_param('site_id');
        
        if (!empty($site_id)) {
            // Sync specific site
            $site = get_post($site_id);
            
            if ($site && $site->post_type === 'wcm_site') {
                $site_url = get_post_meta($site->ID, 'site_url', true);
                $consumer_key = get_post_meta($site->ID, 'consumer_key', true);
                $consumer_secret = get_post_meta($site->ID, 'consumer_secret', true);
                
                if (!empty($site_url) && !empty($consumer_key) && !empty($consumer_secret)) {
                    // Initialize API connection
                    $api = new WCM_Sync_API($site_url, $consumer_key, $consumer_secret);
                    
                    // Sync orders
                    $orders_sync = new WCM_Sync_Orders($api, $site->ID);
                    $orders_result = $orders_sync->sync();
                    
                    // Sync products
                    $products_sync = new WCM_Sync_Products($api, $site->ID);
                    $products_result = $products_sync->sync();
                    
                    // Sync customers
                    $customers_sync = new WCM_Sync_Customers($api, $site->ID);
                    $customers_result = $customers_sync->sync();
                    
                    // Log sync completion
                    update_post_meta($site->ID, 'last_sync', current_time('mysql'));
                    
                    return array(
                        'success' => true,
                        'message' => sprintf(__('Đã đồng bộ dữ liệu từ site %s', 'wcm-data-sync'), $site->post_title),
                        'data' => array(
                            'orders' => $orders_result,
                            'products' => $products_result,
                            'customers' => $customers_result,
                        )
                    );
                }
            }
            
            return new WP_Error('invalid_site', __('Site không hợp lệ', 'wcm-data-sync'), array('status' => 400));
        } else {
            // Sync all sites
            $this->sync_data();
            
            return array(
                'success' => true,
                'message' => __('Đã đồng bộ dữ liệu từ tất cả các site', 'wcm-data-sync')
            );
        }
    }
    
    /**
     * Add admin menu
     */
    public function admin_menu() {
        add_menu_page(
            __('WCM Data Sync', 'wcm-data-sync'),
            __('WCM Data Sync', 'wcm-data-sync'),
            'manage_options',
            'wcm-data-sync',
            array($this, 'admin_page'),
            'dashicons-update',
            30
        );
        
        add_submenu_page(
            'wcm-data-sync',
            __('Connected Sites', 'wcm-data-sync'),
            __('Connected Sites', 'wcm-data-sync'),
            'manage_options',
            'edit.php?post_type=wcm_site'
        );
        
        add_submenu_page(
            'wcm-data-sync',
            __('Add New Site', 'wcm-data-sync'),
            __('Add New Site', 'wcm-data-sync'),
            'manage_options',
            'post-new.php?post_type=wcm_site'
        );
        
        add_submenu_page(
            'wcm-data-sync',
            __('Settings', 'wcm-data-sync'),
            __('Settings', 'wcm-data-sync'),
            'manage_options',
            'wcm-data-sync-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        include WCM_SYNC_DIR . 'includes/admin/views/html-admin-page.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        include WCM_SYNC_DIR . 'includes/admin/views/html-settings-page.php';
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'wcm-data-sync') !== false) {
            wp_enqueue_style('wcm-sync-admin', WCM_SYNC_URL . 'assets/css/admin.css', array(), WCM_SYNC_VERSION);
            wp_enqueue_script('wcm-sync-admin', WCM_SYNC_URL . 'assets/js/admin.js', array('jquery'), WCM_SYNC_VERSION, true);
            
            wp_localize_script('wcm-sync-admin', 'wcm_sync', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'rest_url' => rest_url('wcm/v1/sync'),
                'nonce' => wp_create_nonce('wp_rest'),
                'i18n' => array(
                    'syncing' => __('Đang đồng bộ...', 'wcm-data-sync'),
                    'sync_complete' => __('Đồng bộ hoàn tất!', 'wcm-data-sync'),
                    'sync_error' => __('Lỗi đồng bộ!', 'wcm-data-sync'),
                )
            ));
        }
    }
}

// Initialize the plugin
new WCM_Data_Sync();
