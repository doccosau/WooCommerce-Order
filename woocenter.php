<?php
/**
 * Plugin Name: WooCenter - Quản lý đa site WooCommerce
 * Plugin URI: https://example.com/woocenter
 * Description: Hệ thống quản lý tập trung nhiều website WooCommerce
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: woocenter
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.4
 */

// Đảm bảo không truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

// Định nghĩa hằng số
define('WOOCENTER_VERSION', '1.0.0');
define('WOOCENTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WOOCENTER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WOOCENTER_PLUGIN_FILE', __FILE__);

// Tải các file cần thiết
require_once WOOCENTER_PLUGIN_DIR . 'includes/class-woo-api-manager.php';
require_once WOOCENTER_PLUGIN_DIR . 'includes/class-woo-data-sync.php';
require_once WOOCENTER_PLUGIN_DIR . 'includes/class-woo-database.php';

/**
 * Class chính của plugin
 */
class WooCenter {
    /**
     * Instance của class
     */
    private static $instance = null;

    /**
     * Khởi tạo class
     */
    private function __construct() {
        // Khởi tạo database
        new Woo_Database();
        
        // Đăng ký hook
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Đăng ký cron job
        add_action('woocenter_sync_cron', array($this, 'run_scheduled_sync'));
        
        // Đăng ký hook kích hoạt và hủy kích hoạt plugin
        register_activation_hook(WOOCENTER_PLUGIN_FILE, array($this, 'activate_plugin'));
        register_deactivation_hook(WOOCENTER_PLUGIN_FILE, array($this, 'deactivate_plugin'));
    }

    /**
     * Lấy instance của class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    /**
     * Đăng ký menu admin
     */
    public function register_admin_menu() {
        add_menu_page(
            'WooCenter',
            'WooCenter',
            'manage_options',
            'woocenter',
            array($this, 'render_dashboard_page'),
            'dashicons-store',
            30
        );
        
        add_submenu_page(
            'woocenter',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'woocenter',
            array($this, 'render_dashboard_page')
        );
        
        add_submenu_page(
            'woocenter',
            'API Settings',
            'API Settings',
            'manage_options',
            'woocenter-api-settings',
            array($this, 'render_api_settings_page')
        );
        
        add_submenu_page(
            'woocenter',
            'Sync Data',
            'Sync Data',
            'manage_options',
            'woocenter-sync-data',
            array($this, 'render_sync_data_page')
        );
    }

    /**
     * Render trang dashboard
     */
    public function render_dashboard_page() {
        include WOOCENTER_PLUGIN_DIR . 'admin/dashboard.php';
    }

    /**
     * Render trang cài đặt API
     */
    public function render_api_settings_page() {
        include WOOCENTER_PLUGIN_DIR . 'admin/api-settings.php';
    }

    /**
     * Render trang đồng bộ dữ liệu
     */
    public function render_sync_data_page() {
        include WOOCENTER_PLUGIN_DIR . 'admin/sync-data.php';
    }

    /**
     * Enqueue scripts và styles cho admin
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'woocenter') !== false) {
            wp_enqueue_style('woocenter-admin', WOOCENTER_PLUGIN_URL . 'assets/css/admin.css', array(), WOOCENTER_VERSION);
            wp_enqueue_script('woocenter-admin', WOOCENTER_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), WOOCENTER_VERSION, true);
        }
    }

    /**
     * Chạy đồng bộ theo lịch trình
     */
    public function run_scheduled_sync() {
        $data_sync = new Woo_Data_Sync();
        
        // Kiểm tra các loại dữ liệu cần đồng bộ
        $sync_products = get_option('woocenter_sync_products', '1');
        $sync_orders = get_option('woocenter_sync_orders', '1');
        $sync_customers = get_option('woocenter_sync_customers', '1');
        
        // Lấy danh sách website
        $api_manager = new Woo_API_Manager();
        $sites = $api_manager->get_all_sites();
        
        foreach ($sites as $site_id => $site) {
            if ($site['status'] === 'active') {
                // Đồng bộ sản phẩm
                if ($sync_products === '1') {
                    $data_sync->sync_products($site_id);
                }
                
                // Đồng bộ đơn hàng
                if ($sync_orders === '1') {
                    $data_sync->sync_orders($site_id);
                }
                
                // Đồng bộ khách hàng
                if ($sync_customers === '1') {
                    $data_sync->sync_customers($site_id);
                }
                
                // Cập nhật thời gian đồng bộ
                $api_manager->update_last_sync($site_id);
            }
        }
    }

    /**
     * Kích hoạt plugin
     */
    public function activate_plugin() {
        // Lên lịch cron job
        $interval = get_option('woocenter_sync_interval', 'daily');
        
        if (!wp_next_scheduled('woocenter_sync_cron')) {
            wp_schedule_event(time(), $interval, 'woocenter_sync_cron');
        }
    }

    /**
     * Hủy kích hoạt plugin
     */
    public function deactivate_plugin() {
        // Hủy lịch cron job
        wp_clear_scheduled_hook('woocenter_sync_cron');
    }
}

// Khởi tạo plugin
function woocenter() {
    return WooCenter::get_instance();
}

// Chạy plugin
woocenter();
