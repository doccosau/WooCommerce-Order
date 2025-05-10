<?php
/**
 * Plugin Name: WCM Roles
 * Plugin URI: https://example.com/wcm-roles
 * Description: Phân quyền người dùng cho hệ thống quản lý WooCommerce
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: wcm-roles
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('WCM_ROLES_VERSION', '1.0.0');
define('WCM_ROLES_FILE', __FILE__);
define('WCM_ROLES_DIR', plugin_dir_path(__FILE__));
define('WCM_ROLES_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class WCM_Roles {
    /**
     * Constructor
     */
    public function __construct() {
        // Register activation and deactivation hooks
        register_activation_hook(WCM_ROLES_FILE, array($this, 'activate'));
        register_deactivation_hook(WCM_ROLES_FILE, array($this, 'deactivate'));
        
        // Load plugin files
        $this->includes();
        
        // Initialize hooks
        $this->init_hooks();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create custom roles
        $this->create_roles();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Do nothing
    }
    
    /**
     * Include required files
     */
    private function includes() {
        // Core classes
        require_once WCM_ROLES_DIR . 'includes/class-wcm-roles-manager.php';
        require_once WCM_ROLES_DIR . 'includes/class-wcm-roles-capabilities.php';
        
        // Admin classes
        if (is_admin()) {
            require_once WCM_ROLES_DIR . 'includes/admin/class-wcm-roles-admin.php';
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Add admin menu
        add_action('admin_menu', array($this, 'admin_menu'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Filter user capabilities
        add_filter('user_has_cap', array($this, 'filter_user_caps'), 10, 3);
    }
    
    /**
     * Create custom roles
     */
    private function create_roles() {
        // Get roles manager
        $roles_manager = new WCM_Roles_Manager();
        
        // Create WCM Admin role
        $roles_manager->add_role('wcm_admin', __('WCM Admin', 'wcm-roles'), array(
            'read' => true,
            'wcm_view_dashboard' => true,
            'wcm_manage_orders' => true,
            'wcm_manage_products' => true,
            'wcm_manage_customers' => true,
            'wcm_view_reports' => true,
            'wcm_manage_coupons' => true,
            'wcm_manage_settings' => true,
            'wcm_manage_sites' => true,
            'wcm_manage_users' => true,
        ));
        
        // Create WCM Manager role
        $roles_manager->add_role('wcm_manager', __('WCM Manager', 'wcm-roles'), array(
            'read' => true,
            'wcm_view_dashboard' => true,
            'wcm_manage_orders' => true,
            'wcm_manage_products' => true,
            'wcm_manage_customers' => true,
            'wcm_view_reports' => true,
            'wcm_manage_coupons' => true,
        ));
        
        // Create WCM Staff role
        $roles_manager->add_role('wcm_staff', __('WCM Staff', 'wcm-roles'), array(
            'read' => true,
            'wcm_view_dashboard' => true,
            'wcm_view_orders' => true,
            'wcm_view_products' => true,
            'wcm_view_customers' => true,
            'wcm_view_reports' => true,
        ));
    }
    
    /**
     * Add admin menu
     */
    public function admin_menu() {
        add_menu_page(
            __('WCM Roles', 'wcm-roles'),
            __('WCM Roles', 'wcm-roles'),
            'manage_options',
            'wcm-roles',
            array($this, 'admin_page'),
            'dashicons-groups',
            40
        );
        
        add_submenu_page(
            'wcm-roles',
            __('Manage Roles', 'wcm-roles'),
            __('Manage Roles', 'wcm-roles'),
            'manage_options',
            'wcm-roles',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'wcm-roles',
            __('Add New Role', 'wcm-roles'),
            __('Add New Role', 'wcm-roles'),
            'manage_options',
            'wcm-roles-add',
            array($this, 'add_role_page')
        );
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        include WCM_ROLES_DIR . 'includes/admin/views/html-admin-page.php';
    }
    
    /**
     * Add role page
     */
    public function add_role_page() {
        include WCM_ROLES_DIR . 'includes/admin/views/html-add-role-page.php';
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'wcm-roles') !== false) {
            wp_enqueue_style('wcm-roles-admin', WCM_ROLES_URL . 'assets/css/admin.css', array(), WCM_ROLES_VERSION);
            wp_enqueue_script('wcm-roles-admin', WCM_ROLES_URL . 'assets/js/admin.js', array('jquery'), WCM_ROLES_VERSION, true);
            
            wp_localize_script('wcm-roles-admin', 'wcm_roles', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wcm_roles_nonce'),
                'i18n' => array(
                    'confirm_delete' => __('Bạn có chắc chắn muốn xóa vai trò này?', 'wcm-roles'),
                    'confirm_reset' => __('Bạn có chắc chắn muốn đặt lại vai trò này về mặc định?', 'wcm-roles'),
                )
            ));
        }
    }
    
    /**
     * Filter user capabilities
     *
     * @param array $allcaps
     * @param array $caps
     * @param array $args
     * @return array
     */
    public function filter_user_caps($allcaps, $caps, $args) {
        // Get capabilities manager
        $capabilities = new WCM_Roles_Capabilities();
        
        // Filter capabilities
        return $capabilities->filter_caps($allcaps, $caps, $args);
    }
}

// Initialize the plugin
new WCM_Roles();
