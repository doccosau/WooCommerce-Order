<?php
/**
 * Plugin Name: WCM Reports
 * Plugin URI: https://example.com/wcm-reports
 * Description: Báo cáo thống kê từ nhiều site WooCommerce
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: wcm-reports
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('WCM_REPORTS_VERSION', '1.0.0');
define('WCM_REPORTS_FILE', __FILE__);
define('WCM_REPORTS_DIR', plugin_dir_path(__FILE__));
define('WCM_REPORTS_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class WCM_Reports {
    /**
     * Constructor
     */
    public function __construct() {
        // Load plugin files
        $this->includes();
        
        // Initialize hooks
        $this->init_hooks();
    }
    
    /**
     * Include required files
     */
    private function includes() {
        // Core classes
        require_once WCM_REPORTS_DIR . 'includes/class-wcm-reports-sales.php';
        require_once WCM_REPORTS_DIR . 'includes/class-wcm-reports-products.php';
        require_once WCM_REPORTS_DIR . 'includes/class-wcm-reports-customers.php';
        require_once WCM_REPORTS_DIR . 'includes/class-wcm-reports-export.php';
        
        // Admin classes
        if (is_admin()) {
            require_once WCM_REPORTS_DIR . 'includes/admin/class-wcm-reports-admin.php';
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Register shortcodes
        add_shortcode('wcm_reports', array($this, 'reports_shortcode'));
        
        // Register AJAX handlers
        add_action('wp_ajax_wcm_get_report_data', array($this, 'get_report_data'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Reports shortcode
     */
    public function reports_shortcode($atts) {
        $atts = shortcode_atts(array(
            'type' => 'sales',
            'period' => '30days',
            'site_id' => 0,
        ), $atts, 'wcm_reports');
        
        ob_start();
        
        switch ($atts['type']) {
            case 'sales':
                $reports = new WCM_Reports_Sales();
                $reports->display($atts);
                break;
                
            case 'products':
                $reports = new WCM_Reports_Products();
                $reports->display($atts);
                break;
                
            case 'customers':
                $reports = new WCM_Reports_Customers();
                $reports->display($atts);
                break;
                
            default:
                echo '<p>' . __('Loại báo cáo không hợp lệ.', 'wcm-reports') . '</p>';
                break;
        }
        
        return ob_get_clean();
    }
    
    /**
     * Get report data via AJAX
     */
    public function get_report_data() {
        check_ajax_referer('wcm_reports_nonce', 'nonce');
        
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'sales';
        $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : '30days';
        $site_id = isset($_POST['site_id']) ? intval($_POST['site_id']) : 0;
        
        switch ($type) {
            case 'sales':
                $reports = new WCM_Reports_Sales();
                $data = $reports->get_data($period, $site_id);
                break;
                
            case 'products':
                $reports = new WCM_Reports_Products();
                $data = $reports->get_data($period, $site_id);
                break;
                
            case 'customers':
                $reports = new WCM_Reports_Customers();
                $data = $reports->get_data($period, $site_id);
                break;
                
            default:
                wp_send_json_error(array(
                    'message' => __('Loại báo cáo không hợp lệ.', 'wcm-reports')
                ));
                break;
        }
        
        wp_send_json_success($data);
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style('wcm-reports', WCM_REPORTS_URL . 'assets/css/wcm-reports.css', array(), WCM_REPORTS_VERSION);
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js', array(), '4.3.0', true);
        wp_enqueue_script('wcm-reports', WCM_REPORTS_URL . 'assets/js/wcm-reports.js', array('jquery', 'chart-js'), WCM_REPORTS_VERSION, true);
        
        wp_localize_script('wcm-reports', 'wcm_reports', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wcm_reports_nonce'),
            'i18n' => array(
                'loading' => __('Đang tải dữ liệu...', 'wcm-reports'),
                'error' => __('Đã xảy ra lỗi. Vui lòng thử lại.', 'wcm-reports'),
            )
        ));
    }
}

// Initialize the plugin
new WCM_Reports();
