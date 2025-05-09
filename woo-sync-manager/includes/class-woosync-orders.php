<?php
/**
 * WooSync Orders
 * 
 * Đồng bộ đơn hàng từ các website WooCommerce con
 */

// Đảm bảo không truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

class WooSync_Orders {
    /**
     * Core instance
     */
    private $core;
    
    /**
     * Logger instance
     */
    private $logger;
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Khởi tạo class
     */
    public function __construct($core, $logger, $database) {
        $this->core = $core;
        $this->logger = $logger;
        $this->database = $database;
    }
    
    /**
     * Đồng bộ đơn hàng từ một website
     */
    public function sync_orders($site_id, $args = array()) {
        $defaults = array(
            'per_page' => 20,
            'page' => 1,
            'sync_id' => '',
            'max_pages' => 10, // Giới hạn số trang để tránh quá tải
            'status' => 'any', // any, processing, completed, etc.
            'after' => '', // Đồng bộ đơn hàng sau ngày này (YYYY-MM-DD)
            'before' => '' // Đồng bộ đơn hàng trước ngày này (YYYY-MM-DD)
        );
        
        $args = wp_parse_args($args, $defaults);
        $sync_id = $args['sync_id'];
        
        $site = $this->core->get_site($site_id);
        
        if (!$site) {
            $this->logger->log("Không tìm thấy website: {$site_id}", 'error');
            
            if (!empty($sync_id)) {
                $this->core->update_sync_status($sync_id, 'error', "Không tìm thấy website: {$site_id}");
            }
            
            return array(
                'success' => false,
                'message' => "Không tìm thấy website: {$site_id}"
            );
        }
        
        $this->logger->log("Bắt đầu đồng bộ đơn hàng từ {$site['name']} ({$site_id})");
        
        $total_orders = 0;
        $synced_count = 0;
        $error_count = 0;
        $current_page = $args['page'];
        $max_pages = $args['max_pages'];
        
        try {
            // Lấy tổng số đơn hàng
            $count_result = $this->get_orders_count($site_id, array(
                'status' => $args['status'],
                'after' => $args['after'],
                'before' => $args['before']
            ));
            
            if (!$count_result['success']) {
                throw new Exception($count_result['message']);
            }
            
            $total_orders = $count_result['count'];
            
            if (!empty($sync_id)) {
                $this->core->update_sync_progress($sync_id, 0, $total_orders);
            }
            
            $this->logger->log("Tổng số đơn hàng: {$total_orders}");
            
            // Tính toán số trang
            $total_pages = ceil($total_orders / $args['per_page']);
            
            // Giới hạn số trang nếu cần
            if ($max_pages > 0 && $total_pages > $max_pages) {
                $total_pages = $max_pages;
                $this->logger->log("Giới hạn số trang đồng bộ: {$max_pages}");
            }
            
            $processed_count = 0;
            
            // Lặp qua từng trang đơn hàng
            while ($current_page <= $total_pages) {
                $this->logger->log("Đồng bộ đơn hàng trang {$current_page}/{$total_pages}");
                
                // Lấy đơn hàng từ trang hiện tại
                $orders_result = $this->get_orders_page($site_id, array(
                    'per_page' => $args['per_page'],
                    'page' => $current_page,
                    'status' => $args['status'],
                    'after' => $args['after'],
                    'before' => $args['before']
                ));
                
                if (!$orders_result['success']) {
                    $this->logger->log("Lỗi lấy đơn hàng trang {$current_page}: " . $orders_result['message'], 'error');
                    $error_count++;
                    
                    if (!empty($sync_id)) {
                        $this->core->increment_sync_error($sync_id, "Lỗi lấy đơn hàng trang {$current_page}: " . $orders_result['message']);
                    }
                    
                    // Tiếp tục với trang tiếp theo
                    $current_page++;
                    continue;
                }
                
                $orders = $orders_result['orders'];
                $page_count = count($orders);
                
                $this->logger->log("Đã lấy {$page_count} đơn hàng từ trang {$current_page}");
                
                // Lưu từng đơn hàng vào cơ sở dữ liệu
                foreach ($orders as $order) {
                    $processed_count++;
                    
                    try {
                        // Lấy thêm thông tin chi tiết đơn hàng nếu cần
                        $order_detail = $this->get_order_detail($site_id, $order['id']);
                        
                        if ($order_detail['success']) {
                            $order = $order_detail['order'];
                        }
                        
                        $saved = $this->save_order($site_id, $order);
                        
                        if ($saved) {
                            $synced_count++;
                            
                            if (!empty($sync_id)) {
                                $this->core->increment_sync_success($sync_id);
                            }
                        } else {
                            $error_count++;
                            
                            if (!empty($sync_id)) {
                                $this->core->increment_sync_error($sync_id, "Không thể lưu đơn hàng: {$order['id']}");
                            }
                        }
                    } catch (Exception $e) {
                        $this->logger->log("Lỗi lưu đơn hàng {$order['id']}: " . $e->getMessage(), 'error');
                        $error_count++;
                        
                        if (!empty($sync_id)) {
                            $this->core->increment_sync_error($sync_id, "Lỗi lưu đơn hàng {$order['id']}: " . $e->getMessage());
                        }
                    }
                    
                    // Cập nhật tiến trình
                    if (!empty($sync_id)) {
                        $this->core->update_sync_progress($sync_id, $processed_count, $total_orders);
                    }
                }
                
                // Chuyển sang trang tiếp theo
                $current_page++;
                
                // Tạm dừng giữa các trang để tránh quá tải
                if ($current_page <= $total_pages) {
                    sleep(1);
                }
            }
            
            $this->logger->log("Hoàn tất đồng bộ đơn hàng từ {$site['name']} ({$site_id}). Đã đồng bộ: {$synced_count}, Lỗi: {$error_count}");
            
            return array(
                'success' => true,
                'message' => "Đã đồng bộ {$synced_count} đơn hàng, {$error_count} lỗi",
                'synced' => $synced_count,
                'errors' => $error_count,
                'total' => $total_orders
            );
            
        } catch (Exception $e) {
            $this->logger->log("Lỗi đồng bộ đơn hàng từ {$site['name']} ({$site_id}): " . $e->getMessage(), 'error');
            
            if (!empty($sync_id)) {
                $this->core->update_sync_status($sync_id, 'error', $e->getMessage());
            }
            
            return array(
                'success' => false,
                'message' => $e->getMessage(),
                'synced' => $synced_count,
                'errors' => $error_count,
                'total' => $total_orders
            );
        }
    }
    
    /**
     * Lấy số lượng đơn hàng từ website
     */
    private function get_orders_count($site_id, $args = array()) {
        $defaults = array(
            'status' => 'any',
            'after' => '',
            'before' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $api = new WooSync_API($this->logger);
        
        $params = array(
            'per_page' => 1
        );
        
        // Thêm tham số lọc nếu có
        if ($args['status'] !== 'any') {
            $params['status'] = $args['status'];
        }
        
        if (!empty($args['after'])) {
            $params['after'] = $args['after'];
        }
        
        if (!empty($args['before'])) {
            $params['before'] = $args['before'];
        }
        
        $result = $api->get($site_id, 'orders', $params);
        
        if (!$result['success']) {
            return array(
                'success' => false,
                'message' => $result['message']
            );
        }
        
        // Lấy tổng số đơn hàng từ header
        $total_orders = isset($result['headers']['X-WP-Total']) ? intval($result['headers']['X-WP-Total']) : 0;
        
        return array(
            'success' => true,
            'count' => $total_orders
        );
    }
    
    /**
     * Lấy một trang đơn hàng từ website
     */
    private function get_orders_page($site_id, $args = array()) {
        $defaults = array(
            'per_page' => 20,
            'page' => 1,
            'status' => 'any',
            'after' => '',
            'before' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $api = new WooSync_API($this->logger);
        
        $params = array(
            'per_page' => $args['per_page'],
            'page' => $args['page']
        );
        
        // Thêm tham số lọc nếu có
        if ($args['status'] !== 'any') {
            $params['status'] = $args['status'];
        }
        
        if (!empty($args['after'])) {
            $params['after'] = $args['after'];
        }
        
        if (!empty($args['before'])) {
            $params['before'] = $args['before'];
        }
        
        $result = $api->get($site_id, 'orders', $params);
        
        if (!$result['success']) {
            return array(
                'success' => false,
                'message' => $result['message']
            );
        }
        
        return array(
            'success' => true,
            'orders' => $result['data']
        );
    }
    
    /**
     * Lấy chi tiết đơn hàng
     */
    private function get_order_detail($site_id, $order_id) {
        $api = new WooSync_API($this->logger);
        
        $result = $api->get($site_id, "orders/{$order_id}");
        
        if (!$result['success']) {
            return array(
                'success' => false,
                'message' => $result['message']
            );
        }
        
        return array(
            'success' => true,
            'order' => $result['data']
        );
    }
    
    /**
     * Lưu đơn hàng vào cơ sở dữ liệu
     */
    private function save_order($site_id, $order) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'woosync_orders';
        
        // Kiểm tra đơn hàng đã tồn tại chưa
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE site_id = %s AND order_id = %d",
                $site_id,
                $order['id']
            )
        );
        
        // Chuẩn bị dữ liệu
        $data = array(
            'site_id' => $site_id,
            'order_id' => $order['id'],
            'parent_id' => $order['parent_id'],
            'number' => $order['number'],
            'order_key' => $order['order_key'],
            'created_via' => $order['created_via'],
            'version' => $order['version'],
            'status' => $order['status'],
            'currency' => $order['currency'],
            'date_created' => $order['date_created'],
            'date_modified' => $order['date_modified'],
            'discount_total' => $order['discount_total'],
            'discount_tax' => $order['discount_tax'],
            'shipping_total' => $order['shipping_total'],
            'shipping_tax' => $order['shipping_tax'],
            'cart_tax' => $order['cart_tax'],
            'total' => $order['total'],
            'total_tax' => $order['total_tax'],
            'prices_include_tax' => $order['prices_include_tax'] ? 1 : 0,
            'customer_id' => $order['customer_id'],
            'customer_ip_address' => $order['customer_ip_address'],
            'customer_user_agent' => $order['customer_user_agent'],
            'customer_note' => $order['customer_note'],
            'billing' => json_encode($order['billing']),
            'shipping' => json_encode($order['shipping']),
            'payment_method' => $order['payment_method'],
            'payment_method_title' => $order['payment_method_title'],
            'transaction_id' => $order['transaction_id'],
            'date_paid' => $order['date_paid'],
            'date_completed' => $order['date_completed'],
            'cart_hash' => $order['cart_hash'],
            'line_items' => json_encode($order['line_items']),
            'tax_lines' => json_encode($order['tax_lines']),
            'shipping_lines' => json_encode($order['shipping_lines']),
            'fee_lines' => json_encode($order['fee_lines']),
            'coupon_lines' => json_encode($order['coupon_lines']),
            'refunds' => json_encode($order['refunds']),
            'meta_data' => json_encode($order['meta_data']),
            'last_synced' => current_time('mysql')
        );
        
        $format = array(
            '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
            '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s'
        );
        
        if ($existing) {
            // Cập nhật đơn hàng
            $result = $wpdb->update(
                $table_name,
                $data,
                array('id' => $existing->id),
                $format,
                array('%d')
            );
        } else {
            // Thêm đơn hàng mới
            $result = $wpdb->insert(
                $table_name,
                $data,
                $format
            );
        }
        
        if ($result === false) {
            $this->logger->log("Lỗi SQL khi lưu đơn hàng {$order['id']}: " . $wpdb->last_error, 'error');
            return false;
        }
        
        return true;
    }
    
    /**
     * Lấy đơn hàng từ cơ sở dữ liệu
     */
    public function get_order($order_id, $site_id = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'woosync_orders';
        
        if (!empty($site_id)) {
            $query = $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE order_id = %d AND site_id = %s",
                $order_id,
                $site_id
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE order_id = %d",
                $order_id
            );
        }
        
        $order = $wpdb->get_row($query);
        
        if (!$order) {
            return false;
        }
        
        // Chuyển đổi các trường JSON thành mảng
        $json_fields = array(
            'billing', 'shipping', 'line_items', 'tax_lines', 'shipping_lines',
            'fee_lines', 'coupon_lines', 'refunds', 'meta_data'
        );
        
        foreach ($json_fields as $field) {
            if (isset($order->$field)) {
                $order->$field = json_decode($order->$field, true);
            }
        }
        
        return $order;
    }
    
    /**
     * Lấy danh sách đơn hàng từ cơ sở dữ liệu
     */
    public function get_orders($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'per_page' => 20,
            'page' => 1,
            'site_id' => '',
            'status' => '',
            'customer_id' => '',
            'search' => '',
            'after' => '',
            'before' => '',
            'orderby' => 'date_created',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'woosync_orders';
        
        $where = array();
        $where_format = array();
        
        if (!empty($args['site_id'])) {
            $where[] = 'site_id = %s';
            $where_format[] = $args['site_id'];
        }
        
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $where_format[] = $args['status'];
        }
        
        if (!empty($args['customer_id'])) {
            $where[] = 'customer_id = %d';
            $where_format[] = $args['customer_id'];
        }
        
        if (!empty($args['search'])) {
            $where[] = '(number LIKE %s OR billing LIKE %s)';
            $where_format[] = '%' . $wpdb->esc_like($args['search']) . '%';
            $where_format[] = '%' . $wpdb->esc_like($args['search']) . '%';
        }
        
        if (!empty($args['after'])) {
            $where[] = 'date_created >= %s';
            $where_format[] = $args['after'];
        }
        
        if (!empty($args['before'])) {
            $where[] = 'date_created <= %s';
            $where_format[] = $args['before'];
        }
        
        // Xây dựng câu truy vấn WHERE
        $where_clause = '';
        if (!empty($where)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where);
        }
        
        // Xây dựng câu truy vấn ORDER BY
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        if (!$orderby) {
            $orderby = 'date_created DESC';
        }
        
        // Tính toán LIMIT và OFFSET
        $per_page = intval($args['per_page']);
        $page = intval($args['page']);
        $offset = ($page - 1) * $per_page;
        
        // Đếm tổng số đơn hàng
        $count_query = "SELECT COUNT(*) FROM {$table_name} {$where_clause}";
        
        if (!empty($where_format)) {
            $count_query = $wpdb->prepare($count_query, $where_format);
        }
        
        $total = $wpdb->get_var($count_query);
        
        // Lấy danh sách đơn hàng
        $query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY {$orderby} LIMIT %d OFFSET %d";
        
        $query_args = $where_format;
        $query_args[] = $per_page;
        $query_args[] = $offset;
        
        $orders = $wpdb->get_results($wpdb->prepare($query, $query_args));
        
        // Chuyển đổi các trường JSON thành mảng
        $json_fields = array(
            'billing', 'shipping', 'line_items', 'tax_lines', 'shipping_lines',
            'fee_lines', 'coupon_lines', 'refunds', 'meta_data'
        );
        
        foreach ($orders as &$order) {
            foreach ($json_fields as $field) {
                if (isset($order->$field)) {
                    $order->$field = json_decode($order->$field, true);
                }
            }
        }
        
        return array(
            'orders' => $orders,
            'total' => intval($total),
            'pages' => ceil($total / $per_page)
        );
    }
    
    /**
     * Xóa đơn hàng từ cơ sở dữ liệu
     */
    public function delete_order($order_id, $site_id = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'woosync_orders';
        
        if (!empty($site_id)) {
            $result = $wpdb->delete(
                $table_name,
                array(
                    'order_id' => $order_id,
                    'site_id' => $site_id
                ),
                array('%d', '%s')
            );
        } else {
            $result = $wpdb->delete(
                $table_name,
                array('order_id' => $order_id),
                array('%d')
            );
        }
        
        return $result !== false;
    }
    
    /**
     * Xóa tất cả đơn hàng của một website
     */
    public function delete_all_orders($site_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'woosync_orders';
        
        $result = $wpdb->delete(
            $table_name,
            array('site_id' => $site_id),
            array('%s')
        );
        
        return $result !== false;
    }
}
