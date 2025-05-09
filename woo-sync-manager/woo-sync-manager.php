<?php
/**
 * Plugin Name: WooSync Manager
 * Plugin URI: https://example.com/woosync-manager
 * Description: Plugin đồng bộ dữ liệu từ các website WooCommerce con về hệ thống quản lý trung tâm
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: woosync-manager
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.4
 */

// Đảm bảo không truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

// Định nghĩa hằng số
define('WOOSYNC_VERSION', '1.0.0');
define('WOOSYNC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WOOSYNC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WOOSYNC_PLUGIN_FILE', __FILE__);

// Tải các file cần thiết
require_once WOOSYNC_PLUGIN_DIR . 'includes/class-woosync-core.php';
require_once WOOSYNC_PLUGIN_DIR . 'includes/class-woosync-products.php';
require_once WOOSYNC_PLUGIN_DIR . 'includes/class-woosync-orders.php';
require_once WOOSYNC_PLUGIN_DIR . 'includes/class-woosync-customers.php';
require_once WOOSYNC_PLUGIN_DIR . 'includes/class-woosync-logger.php';
require_once WOOSYNC_PLUGIN_DIR . 'includes/class-woosync-database.php';
require_once WOOSYNC_PLUGIN_DIR . 'includes/class-woosync-api.php';

/**
 * Class chính của plugin
 */
class WooSync_Manager {
    /**
     * Instance của class
     */
    private static $instance = null;

    /**
     * Core class
     */
    private $core;

    /**
     * Products class
     */
    private $products;

    /**
     * Orders class
     */
    private $orders;

    /**
     * Customers class
     */
    private $customers;

    /**
     * Logger class
     */
    private $logger;

    /**
     * Database class
     */
    private $database;

    /**
     * API class
     */
    private $api;

    /**
     * Khởi tạo class
     */
    private function __construct() {
        // Khởi tạo các class
        $this->logger = new WooSync_Logger();
        $this->database = new WooSync_Database();
        $this->api = new WooSync_API($this->logger);
        $this->core = new WooSync_Core($this->logger, $this->api);
        $this->products = new WooSync_Products($this->core, $this->logger, $this->database);
        $this->orders = new WooSync_Orders($this->core, $this->logger, $this->database);
        $this->customers = new WooSync_Customers($this->core, $this->logger, $this->database);
        
        // Đăng ký hook
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_woosync_sync_now', array($this, 'ajax_sync_now'));
        add_action('wp_ajax_woosync_get_sync_status', array($this, 'ajax_get_sync_status'));
        
        // Đăng ký cron job
        add_action('woosync_scheduled_sync', array($this, 'run_scheduled_sync'));
        
        // Đăng ký hook kích hoạt và hủy kích hoạt plugin
        register_activation_hook(WOOSYNC_PLUGIN_FILE, array($this, 'activate_plugin'));
        register_deactivation_hook(WOOSYNC_PLUGIN_FILE, array($this, 'deactivate_plugin'));
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
            'WooSync Manager',
            'WooSync',
            'manage_options',
            'woosync-manager',
            array($this, 'render_dashboard_page'),
            'dashicons-update',
            58
        );
        
        add_submenu_page(
            'woosync-manager',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'woosync-manager',
            array($this, 'render_dashboard_page')
        );
        
        add_submenu_page(
            'woosync-manager',
            'Đồng bộ sản phẩm',
            'Sản phẩm',
            'manage_options',
            'woosync-products',
            array($this, 'render_products_page')
        );
        
        add_submenu_page(
            'woosync-manager',
            'Đồng bộ đơn hàng',
            'Đơn hàng',
            'manage_options',
            'woosync-orders',
            array($this, 'render_orders_page')
        );
        
        add_submenu_page(
            'woosync-manager',
            'Đồng bộ khách hàng',
            'Khách hàng',
            'manage_options',
            'woosync-customers',
            array($this, 'render_customers_page')
        );
        
        add_submenu_page(
            'woosync-manager',
            'Cài đặt',
            'Cài đặt',
            'manage_options',
            'woosync-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'woosync-manager',
            'Nhật ký',
            'Nhật ký',
            'manage_options',
            'woosync-logs',
            array($this, 'render_logs_page')
        );
    }

    /**
     * Render trang dashboard
     */
    public function render_dashboard_page() {
        include WOOSYNC_PLUGIN_DIR . 'admin/dashboard.php';
    }

    /**
     * Render trang sản phẩm
     */
    public function render_products_page() {
        include WOOSYNC_PLUGIN_DIR . 'admin/products.php';
    }

    /**
     * Render trang đơn hàng
     */
    public function render_orders_page() {
        include WOOSYNC_PLUGIN_DIR . 'admin/orders.php';
    }

    /**
     * Render trang khách hàng
     */
    public function render_customers_page() {
        include WOOSYNC_PLUGIN_DIR . 'admin/customers.php';
    }

    /**
     * Render trang cài đặt
     */
    public function render_settings_page() {
        include WOOSYNC_PLUGIN_DIR . 'admin/settings.php';
    }

    /**
     * Render trang nhật ký
     */
    public function render_logs_page() {
        include WOOSYNC_PLUGIN_DIR . 'admin/logs.php';
    }

    /**
     * Enqueue scripts và styles cho admin
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'woosync') !== false) {
            wp_enqueue_style('woosync-admin', WOOSYNC_PLUGIN_URL . 'assets/css/admin.css', array(), WOOSYNC_VERSION);
            wp_enqueue_script('woosync-admin', WOOSYNC_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), WOOSYNC_VERSION, true);
            
            wp_localize_script('woosync-admin', 'woosyncData', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('woosync_nonce'),
                'syncInProgress' => __('Đồng bộ đang diễn ra...', 'woosync-manager'),
                'syncComplete' => __('Đồng bộ hoàn tất!', 'woosync-manager'),
                'syncError' => __('Lỗi đồng bộ!', 'woosync-manager')
            ));
        }
    }

    /**
     * AJAX handler cho đồng bộ ngay
     */
    public function ajax_sync_now() {
        // Kiểm tra nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'woosync_nonce')) {
            wp_send_json_error(array('message' => 'Lỗi bảo mật'));
        }
        
        // Kiểm tra quyền
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Không có quyền thực hiện'));
        }
        
        $site_id = isset($_POST['site_id']) ? sanitize_text_field($_POST['site_id']) : '';
        $sync_type = isset($_POST['sync_type']) ? sanitize_text_field($_POST['sync_type']) : 'all';
        
        // Bắt đầu đồng bộ
        $sync_id = $this->core->start_sync_process($site_id, $sync_type);
        
        if ($sync_id) {
            wp_send_json_success(array(
                'sync_id' => $sync_id,
                'message' => 'Đã bắt đầu đồng bộ'
            ));
        } else {
            wp_send_json_error(array('message' => 'Không thể bắt đầu đồng bộ'));
        }
    }

    /**
     * AJAX handler cho lấy trạng thái đồng bộ
     */
    public function ajax_get_sync_status() {
        // Kiểm tra nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'woosync_nonce')) {
            wp_send_json_error(array('message' => 'Lỗi bảo mật'));
        }
        
        // Kiểm tra quyền
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Không có quyền thực hiện'));
        }
        
        $sync_id = isset($_POST['sync_id']) ? sanitize_text_field($_POST['sync_id']) : '';
        
        if (empty($sync_id)) {
            wp_send_json_error(array('message' => 'ID đồng bộ không hợp lệ'));
        }
        
        $status = $this->core->get_sync_status($sync_id);
        
        wp_send_json_success($status);
    }

    /**
     * Chạy đồng bộ theo lịch trình
     */
    public function run_scheduled_sync() {
        $this->logger->log('Bắt đầu đồng bộ theo lịch trình');
        
        $settings = get_option('woosync_settings', array());
        $sync_products = isset($settings['sync_products']) ? $settings['sync_products'] : true;
        $sync_orders = isset($settings['sync_orders']) ? $settings['sync_orders'] : true;
        $sync_customers = isset($settings['sync_customers']) ? $settings['sync_customers'] : true;
        
        // Lấy danh sách website
        $sites = $this->core->get_connected_sites();
        
        foreach ($sites as $site) {
            if ($site['status'] === 'active') {
                $site_id = $site['id'];
                
                // Đồng bộ sản phẩm
                if ($sync_products) {
                    $this->logger->log("Đồng bộ sản phẩm từ {$site['name']} ({$site_id})");
                    $this->products->sync_products($site_id);
                }
                
                // Đồng bộ đơn hàng
                if ($sync_orders) {
                    $this->logger->log("Đồng bộ đơn hàng từ {$site['name']} ({$site_id})");
                    $this->orders->sync_orders($site_id);
                }
                
                // Đồng bộ khách hàng
                if ($sync_customers) {
                    $this->logger->log("Đồng bộ khách hàng từ {$site['name']} ({$site_id})");
                    $this->customers->sync_customers($site_id);
                }
                
                // Cập nhật thời gian đồng bộ
                $this->core->update_last_sync($site_id);
            }
        }
        
        $this->logger->log('Hoàn tất đồng bộ theo lịch trình');
    }

    /**
     * Kích hoạt plugin
     */
    public function activate_plugin() {
        // Tạo bảng cơ sở dữ liệu
        $this->database->create_tables();
        
        // Lên lịch cron job
        $settings = get_option('woosync_settings', array());
        $interval = isset($settings['sync_interval']) ? $settings['sync_interval'] : 'daily';
        
        if (!wp_next_scheduled('woosync_scheduled_sync')) {
            wp_schedule_event(time(), $interval, 'woosync_scheduled_sync');
        }
        
        // Tạo thư mục logs
        $log_dir = WOOSYNC_PLUGIN_DIR . 'logs';
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        
        // Tạo file .htaccess để bảo vệ thư mục logs
        $htaccess_file = $log_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "Order deny,allow\nDeny from all";
            file_put_contents($htaccess_file, $htaccess_content);
        }
        
        // Tạo file index.php trống để bảo vệ thư mục logs
        $index_file = $log_dir . '/index.php';
        if (!file_exists($index_file)) {
            file_put_contents($index_file, '<?php // Silence is golden');
        }
    }

    /**
     * Hủy kích hoạt plugin
     */
    public function deactivate_plugin() {
        // Hủy lịch cron job
        wp_clear_scheduled_hook('woosync_scheduled_sync');
    }
}

// Khởi tạo plugin
function woosync_manager() {
    return WooSync_Manager::get_instance();
}

// Chạy plugin
woosync_manager();
