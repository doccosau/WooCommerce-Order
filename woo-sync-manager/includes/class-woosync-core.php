<?php
/**
 * WooSync Core
 * 
 * Lớp cơ sở cho plugin đồng bộ
 */

// Đảm bảo không truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

class WooSync_Core {
    /**
     * Logger instance
     */
    private $logger;
    
    /**
     * API instance
     */
    private $api;
    
    /**
     * Sync processes
     */
    private $sync_processes = array();
    
    /**
     * Khởi tạo class
     */
    public function __construct($logger, $api) {
        $this->logger = $logger;
        $this->api = $api;
        
        // Tải các tiến trình đồng bộ đã lưu
        $this->sync_processes = get_option('woosync_processes', array());
    }
    
    /**
     * Lấy danh sách website đã kết nối
     */
    public function get_connected_sites() {
        return get_option('woocenter_connected_sites', array());
    }
    
    /**
     * Lấy thông tin một website
     */
    public function get_site($site_id) {
        $sites = $this->get_connected_sites();
        
        if (isset($sites[$site_id])) {
            return $sites[$site_id];
        }
        
        return false;
    }
    
    /**
     * Cập nhật thời gian đồng bộ cuối cùng
     */
    public function update_last_sync($site_id) {
        $sites = $this->get_connected_sites();
        
        if (isset($sites[$site_id])) {
            $sites[$site_id]['last_sync'] = current_time('mysql');
            update_option('woocenter_connected_sites', $sites);
            return true;
        }
        
        return false;
    }
    
    /**
     * Bắt đầu tiến trình đồng bộ
     */
    public function start_sync_process($site_id, $sync_type = 'all') {
        $sync_id = 'sync_' . uniqid();
        
        $this->sync_processes[$sync_id] = array(
            'id' => $sync_id,
            'site_id' => $site_id,
            'type' => $sync_type,
            'status' => 'running',
            'start_time' => current_time('mysql'),
            'end_time' => '',
            'progress' => 0,
            'total' => 0,
            'processed' => 0,
            'success' => 0,
            'errors' => 0,
            'error_messages' => array(),
            'results' => array()
        );
        
        update_option('woosync_processes', $this->sync_processes);
        
        // Bắt đầu đồng bộ trong background
        $this->schedule_background_sync($sync_id);
        
        return $sync_id;
    }
    
    /**
     * Lên lịch đồng bộ trong background
     */
    private function schedule_background_sync($sync_id) {
        if (!isset($this->sync_processes[$sync_id])) {
            return false;
        }
        
        $process = $this->sync_processes[$sync_id];
        $site_id = $process['site_id'];
        $sync_type = $process['type'];
        
        // Sử dụng WP Cron để chạy đồng bộ trong background
        wp_schedule_single_event(time(), 'woosync_background_sync', array(
            'sync_id' => $sync_id,
            'site_id' => $site_id,
            'sync_type' => $sync_type
        ));
        
        // Đăng ký hook cho sự kiện background sync
        add_action('woosync_background_sync', array($this, 'process_background_sync'), 10, 3);
        
        return true;
    }
    
    /**
     * Xử lý đồng bộ trong background
     */
    public function process_background_sync($sync_id, $site_id, $sync_type) {
        if (!isset($this->sync_processes[$sync_id])) {
            return;
        }
        
        $this->logger->log("Bắt đầu đồng bộ background (ID: {$sync_id}, Site: {$site_id}, Type: {$sync_type})");
        
        $site = $this->get_site($site_id);
        
        if (!$site) {
            $this->update_sync_status($sync_id, 'error', 'Website không tồn tại');
            return;
        }
        
        try {
            // Thực hiện đồng bộ dựa trên loại
            switch ($sync_type) {
                case 'products':
                    $this->sync_products_background($sync_id, $site_id);
                    break;
                    
                case 'orders':
                    $this->sync_orders_background($sync_id, $site_id);
                    break;
                    
                case 'customers':
                    $this->sync_customers_background($sync_id, $site_id);
                    break;
                    
                case 'all':
                default:
                    $this->sync_all_background($sync_id, $site_id);
                    break;
            }
            
            // Cập nhật thời gian đồng bộ
            $this->update_last_sync($site_id);
            
            // Đánh dấu hoàn thành
            $this->update_sync_status($sync_id, 'completed');
            
        } catch (Exception $e) {
            $this->logger->log("Lỗi đồng bộ background: " . $e->getMessage(), 'error');
            $this->update_sync_status($sync_id, 'error', $e->getMessage());
        }
    }
    
    /**
     * Đồng bộ sản phẩm trong background
     */
    private function sync_products_background($sync_id, $site_id) {
        $products_sync = new WooSync_Products($this, $this->logger, new WooSync_Database());
        $result = $products_sync->sync_products($site_id, array('sync_id' => $sync_id));
        
        $this->sync_processes[$sync_id]['results']['products'] = $result;
        update_option('woosync_processes', $this->sync_processes);
    }
    
    /**
     * Đồng bộ đơn hàng trong background
     */
    private function sync_orders_background($sync_id, $site_id) {
        $orders_sync = new WooSync_Orders($this, $this->logger, new WooSync_Database());
        $result = $orders_sync->sync_orders($site_id, array('sync_id' => $sync_id));
        
        $this->sync_processes[$sync_id]['results']['orders'] = $result;
        update_option('woosync_processes', $this->sync_processes);
    }
    
    /**
     * Đồng bộ khách hàng trong background
     */
    private function sync_customers_background($sync_id, $site_id) {
        $customers_sync = new WooSync_Customers($this, $this->logger, new WooSync_Database());
        $result = $customers_sync->sync_customers($site_id, array('sync_id' => $sync_id));
        
        $this->sync_processes[$sync_id]['results']['customers'] = $result;
        update_option('woosync_processes', $this->sync_processes);
    }
    
    /**
     * Đồng bộ tất cả trong background
     */
    private function sync_all_background($sync_id, $site_id) {
        // Đồng bộ sản phẩm
        $this->sync_products_background($sync_id, $site_id);
        
        // Đồng bộ đơn hàng
        $this->sync_orders_background($sync_id, $site_id);
        
        // Đồng bộ khách hàng
        $this->sync_customers_background($sync_id, $site_id);
    }
    
    /**
     * Cập nhật trạng thái đồng bộ
     */
    public function update_sync_status($sync_id, $status, $error_message = '') {
        if (!isset($this->sync_processes[$sync_id])) {
            return false;
        }
        
        $this->sync_processes[$sync_id]['status'] = $status;
        
        if ($status === 'completed' || $status === 'error') {
            $this->sync_processes[$sync_id]['end_time'] = current_time('mysql');
        }
        
        if ($status === 'error' && !empty($error_message)) {
            $this->sync_processes[$sync_id]['error_messages'][] = $error_message;
            $this->sync_processes[$sync_id]['errors']++;
        }
        
        update_option('woosync_processes', $this->sync_processes);
        
        return true;
    }
    
    /**
     * Cập nhật tiến trình đồng bộ
     */
    public function update_sync_progress($sync_id, $processed, $total) {
        if (!isset($this->sync_processes[$sync_id])) {
            return false;
        }
        
        $this->sync_processes[$sync_id]['processed'] = $processed;
        $this->sync_processes[$sync_id]['total'] = $total;
        $this->sync_processes[$sync_id]['progress'] = ($total > 0) ? round(($processed / $total) * 100) : 0;
        
        update_option('woosync_processes', $this->sync_processes);
        
        return true;
    }
    
    /**
     * Tăng số lượng thành công
     */
    public function increment_sync_success($sync_id) {
        if (!isset($this->sync_processes[$sync_id])) {
            return false;
        }
        
        $this->sync_processes[$sync_id]['success']++;
        update_option('woosync_processes', $this->sync_processes);
        
        return true;
    }
    
    /**
     * Tăng số lượng lỗi
     */
    public function increment_sync_error($sync_id, $error_message = '') {
        if (!isset($this->sync_processes[$sync_id])) {
            return false;
        }
        
        $this->sync_processes[$sync_id]['errors']++;
        
        if (!empty($error_message)) {
            $this->sync_processes[$sync_id]['error_messages'][] = $error_message;
        }
        
        update_option('woosync_processes', $this->sync_processes);
        
        return true;
    }
    
    /**
     * Lấy trạng thái đồng bộ
     */
    public function get_sync_status($sync_id) {
        if (!isset($this->sync_processes[$sync_id])) {
            return false;
        }
        
        return $this->sync_processes[$sync_id];
    }
    
    /**
     * Lấy danh sách tiến trình đồng bộ
     */
    public function get_sync_processes($limit = 10, $status = '') {
        $processes = $this->sync_processes;
        
        // Sắp xếp theo thời gian bắt đầu (mới nhất trước)
        uasort($processes, function($a, $b) {
            return strtotime($b['start_time']) - strtotime($a['start_time']);
        });
        
        // Lọc theo trạng thái nếu có
        if (!empty($status)) {
            $processes = array_filter($processes, function($process) use ($status) {
                return $process['status'] === $status;
            });
        }
        
        // Giới hạn số lượng
        if ($limit > 0) {
            $processes = array_slice($processes, 0, $limit);
        }
        
        return $processes;
    }
    
    /**
     * Xóa tiến trình đồng bộ
     */
    public function delete_sync_process($sync_id) {
        if (!isset($this->sync_processes[$sync_id])) {
            return false;
        }
        
        unset($this->sync_processes[$sync_id]);
        update_option('woosync_processes', $this->sync_processes);
        
        return true;
    }
    
    /**
     * Xóa tất cả tiến trình đồng bộ
     */
    public function delete_all_sync_processes() {
        $this->sync_processes = array();
        update_option('woosync_processes', $this->sync_processes);
        
        return true;
    }
    
    /**
     * Xóa tiến trình đồng bộ cũ
     */
    public function cleanup_old_sync_processes($days = 7) {
        $cutoff_time = strtotime("-{$days} days");
        
        foreach ($this->sync_processes as $sync_id => $process) {
            $start_time = strtotime($process['start_time']);
            
            if ($start_time < $cutoff_time) {
                unset($this->sync_processes[$sync_id]);
            }
        }
        
        update_option('woosync_processes', $this->sync_processes);
        
        return true;
    }
}
