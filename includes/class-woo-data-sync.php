<?php
/**
 * WooCommerce Data Sync
 * 
 * Đồng bộ dữ liệu từ các website WooCommerce con
 */
class Woo_Data_Sync {
    /**
     * API Manager
     */
    private $api_manager;

    /**
     * Khởi tạo class
     */
    public function __construct() {
        $this->api_manager = new Woo_API_Manager();
    }

    /**
     * Đồng bộ sản phẩm từ một website
     * 
     * @param string $site_id ID của website
     * @param array $params Tham số truy vấn
     * @return array Kết quả đồng bộ
     */
    public function sync_products($site_id, $params = array()) {
        $default_params = array(
            'per_page' => 100,
            'page' => 1
        );
        
        $params = wp_parse_args($params, $default_params);
        
        $result = $this->api_manager->get_products($site_id, $params);
        
        if (!$result['success']) {
            return $result;
        }
        
        $products = $result['data'];
        $synced_count = 0;
        
        foreach ($products as $product) {
            // Lưu sản phẩm vào cơ sở dữ liệu trung tâm
            $saved = $this->save_product($site_id, $product);
            
            if ($saved) {
                $synced_count++;
            }
        }
        
        // Cập nhật thời gian đồng bộ
        $this->api_manager->update_last_sync($site_id);
        
        return array(
            'success' => true,
            'message' => sprintf('Đã đồng bộ %d sản phẩm', $synced_count),
            'count' => $synced_count,
            'total' => count($products)
        );
    }

    /**
     * Lưu sản phẩm vào cơ sở dữ liệu trung tâm
     * 
     * @param string $site_id ID của website
     * @param array $product Dữ liệu sản phẩm
     * @return bool Trạng thái lưu
     */
    private function save_product($site_id, $product) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'woocenter_products';
        
        // Kiểm tra sản phẩm đã tồn tại chưa
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE site_id = %s AND product_id = %d",
                $site_id,
                $product['id']
            )
        );
        
        $data = array(
            'site_id' => $site_id,
            'product_id' => $product['id'],
            'name' => $product['name'],
            'sku' => $product['sku'],
            'price' => $product['price'],
            'regular_price' => $product['regular_price'],
            'sale_price' => $product['sale_price'],
            'status' => $product['status'],
            'stock_status' => $product['stock_status'],
            'stock_quantity' => $product['stock_quantity'],
            'categories' => json_encode($this->extract_categories($product)),
            'images' => json_encode($this->extract_images($product)),
            'data' => json_encode($product),
            'last_updated' => current_time('mysql')
        );
        
        $format = array(
            '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s'
        );
        
        if ($existing) {
            // Cập nhật sản phẩm
            $result = $wpdb->update(
                $table_name,
                $data,
                array('id' => $existing->id),
                $format,
                array('%d')
            );
        } else {
            // Thêm sản phẩm mới
            $result = $wpdb->insert(
                $table_name,
                $data,
                $format
            );
        }
        
        return $result !== false;
    }

    /**
     * Trích xuất danh mục từ sản phẩm
     * 
     * @param array $product Dữ liệu sản phẩm
     * @return array Danh sách danh mục
     */
    private function extract_categories($product) {
        $categories = array();
        
        if (isset($product['categories']) && is_array($product['categories'])) {
            foreach ($product['categories'] as $category) {
                $categories[] = array(
                    'id' => $category['id'],
                    'name' => $category['name'],
                    'slug' => $category['slug']
                );
            }
        }
        
        return $categories;
    }

    /**
     * Trích xuất hình ảnh từ sản phẩm
     * 
     * @param array $product Dữ liệu sản phẩm
     * @return array Danh sách hình ảnh
     */
    private function extract_images($product) {
        $images = array();
        
        if (isset($product['images']) && is_array($product['images'])) {
            foreach ($product['images'] as $image) {
                $images[] = array(
                    'id' => $image['id'],
                    'src' => $image['src'],
                    'alt' => $image['alt']
                );
            }
        }
        
        return $images;
    }

    /**
     * Đồng bộ đơn hàng từ một website
     * 
     * @param string $site_id ID của website
     * @param array $params Tham số truy vấn
     * @return array Kết quả đồng bộ
     */
    public function sync_orders($site_id, $params = array()) {
        $default_params = array(
            'per_page' => 100,
            'page' => 1
        );
        
        $params = wp_parse_args($params, $default_params);
        
        $result = $this->api_manager->get_orders($site_id, $params);
        
        if (!$result['success']) {
            return $result;
        }
        
        $orders = $result['data'];
        $synced_count = 0;
        
        foreach ($orders as $order) {
            // Lưu đơn hàng vào cơ sở dữ liệu trung tâm
            $saved = $this->save_order($site_id, $order);
            
            if ($saved) {
                $synced_count++;
            }
        }
        
        // Cập nhật thời gian đồng bộ
        $this->api_manager->update_last_sync($site_id);
        
        return array(
            'success' => true,
            'message' => sprintf('Đã đồng bộ %d đơn hàng', $synced_count),
            'count' => $synced_count,
            'total' => count($orders)
        );
    }

    /**
     * Lưu đơn hàng vào cơ sở dữ liệu trung tâm
     * 
     * @param string $site_id ID của website
     * @param array $order Dữ liệu đơn hàng
     * @return bool Trạng thái lưu
     */
    private function save_order($site_id, $order) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'woocenter_orders';
        
        // Kiểm tra đơn hàng đã tồn tại chưa
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE site_id = %s AND order_id = %d",
                $site_id,
                $order['id']
            )
        );
        
        $data = array(
            'site_id' => $site_id,
            'order_id' => $order['id'],
            'number' => $order['number'],
            'status' => $order['status'],
            'date_created' => $order['date_created'],
            'total' => $order['total'],
            'customer_id' => $order['customer_id'],
            'billing' => json_encode($order['billing']),
            'shipping' => json_encode($order['shipping']),
            'payment_method' => $order['payment_method'],
            'payment_method_title' => $order['payment_method_title'],
            'line_items' => json_encode($order['line_items']),
            'data' => json_encode($order),
            'last_updated' => current_time('mysql')
        );
        
        $format = array(
            '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
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
        
        return $result !== false;
    }

    /**
     * Đồng bộ khách hàng từ một website
     * 
     * @param string $site_id ID của website
     * @param array $params Tham số truy vấn
     * @return array Kết quả đồng bộ
     */
    public function sync_customers($site_id, $params = array()) {
        $default_params = array(
            'per_page' => 100,
            'page' => 1
        );
        
        $params = wp_parse_args($params, $default_params);
        
        $result = $this->api_manager->get_customers($site_id, $params);
        
        if (!$result['success']) {
            return $result;
        }
        
        $customers = $result['data'];
        $synced_count = 0;
        
        foreach ($customers as $customer) {
            // Lưu khách hàng vào cơ sở dữ liệu trung tâm
            $saved = $this->save_customer($site_id, $customer);
            
            if ($saved) {
                $synced_count++;
            }
        }
        
        // Cập nhật thời gian đồng bộ
        $this->api_manager->update_last_sync($site_id);
        
        return array(
            'success' => true,
            'message' => sprintf('Đã đồng bộ %d khách hàng', $synced_count),
            'count' => $synced_count,
            'total' => count($customers)
        );
    }

    /**
     * Lưu khách hàng vào cơ sở dữ liệu trung tâm
     * 
     * @param string $site_id ID của website
     * @param array $customer Dữ liệu khách hàng
     * @return bool Trạng thái lưu
     */
    private function save_customer($site_id, $customer) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'woocenter_customers';
        
        // Kiểm tra khách hàng đã tồn tại chưa
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE site_id = %s AND customer_id = %d",
                $site_id,
                $customer['id']
            )
        );
        
        $data = array(
            'site_id' => $site_id,
            'customer_id' => $customer['id'],
            'email' => $customer['email'],
            'first_name' => $customer['first_name'],
            'last_name' => $customer['last_name'],
            'username' => $customer['username'],
            'billing' => json_encode($customer['billing']),
            'shipping' => json_encode($customer['shipping']),
            'is_paying_customer' => $customer['is_paying_customer'] ? 1 : 0,
            'avatar_url' => $customer['avatar_url'],
            'data' => json_encode($customer),
            'last_updated' => current_time('mysql')
        );
        
        $format = array(
            '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s'
        );
        
        if ($existing) {
            // Cập nhật khách hàng
            $result = $wpdb->update(
                $table_name,
                $data,
                array('id' => $existing->id),
                $format,
                array('%d')
            );
        } else {
            // Thêm khách hàng mới
            $result = $wpdb->insert(
                $table_name,
                $data,
                $format
            );
        }
        
        return $result !== false;
    }

    /**
     * Đồng bộ tất cả dữ liệu từ một website
     * 
     * @param string $site_id ID của website
     * @return array Kết quả đồng bộ
     */
    public function sync_all($site_id) {
        $results = array();
        
        // Đồng bộ sản phẩm
        $products_result = $this->sync_products($site_id);
        $results['products'] = $products_result;
        
        // Đồng bộ đơn hàng
        $orders_result = $this->sync_orders($site_id);
        $results['orders'] = $orders_result;
        
        // Đồng bộ khách hàng
        $customers_result = $this->sync_customers($site_id);
        $results['customers'] = $customers_result;
        
        return $results;
    }

    /**
     * Đồng bộ tất cả dữ liệu từ tất cả các website
     * 
     * @return array Kết quả đồng bộ
     */
    public function sync_all_sites() {
        $sites = $this->api_manager->get_all_sites();
        $results = array();
        
        foreach ($sites as $site_id => $site) {
            if ($site['status'] === 'active') {
                $results[$site_id] = $this->sync_all($site_id);
            }
        }
        
        return $results;
    }
}
