<?php
/**
 * WooCommerce API Manager
 * 
 * Quản lý kết nối API với các website WooCommerce con
 */
class Woo_API_Manager {
    /**
     * Danh sách các website đã kết nối
     */
    private $connected_sites = array();

    /**
     * Khởi tạo class
     */
    public function __construct() {
        $this->load_connected_sites();
    }

    /**
     * Tải danh sách các website đã kết nối từ cơ sở dữ liệu
     */
    private function load_connected_sites() {
        $sites = get_option('woocenter_connected_sites', array());
        
        if (!empty($sites) && is_array($sites)) {
            $this->connected_sites = $sites;
        }
    }

    /**
     * Lưu danh sách các website đã kết nối vào cơ sở dữ liệu
     */
    private function save_connected_sites() {
        update_option('woocenter_connected_sites', $this->connected_sites);
    }

    /**
     * Thêm một website mới
     * 
     * @param string $name Tên website
     * @param string $url URL của website
     * @param string $consumer_key Consumer Key
     * @param string $consumer_secret Consumer Secret
     * @return bool Trạng thái thêm website
     */
    public function add_site($name, $url, $consumer_key, $consumer_secret) {
        if (empty($name) || empty($url) || empty($consumer_key) || empty($consumer_secret)) {
            return false;
        }

        // Kiểm tra kết nối trước khi thêm
        $test_connection = $this->test_connection($url, $consumer_key, $consumer_secret);
        if (!$test_connection['success']) {
            return false;
        }

        $site_id = sanitize_title($name) . '_' . time();
        
        $this->connected_sites[$site_id] = array(
            'id' => $site_id,
            'name' => sanitize_text_field($name),
            'url' => esc_url_raw(trailingslashit($url)),
            'consumer_key' => sanitize_text_field($consumer_key),
            'consumer_secret' => sanitize_text_field($consumer_secret),
            'last_sync' => '',
            'status' => 'active'
        );

        $this->save_connected_sites();
        return true;
    }

    /**
     * Cập nhật thông tin website
     * 
     * @param string $site_id ID của website
     * @param array $data Dữ liệu cập nhật
     * @return bool Trạng thái cập nhật
     */
    public function update_site($site_id, $data) {
        if (!isset($this->connected_sites[$site_id])) {
            return false;
        }

        $site = $this->connected_sites[$site_id];

        if (isset($data['name'])) {
            $site['name'] = sanitize_text_field($data['name']);
        }

        if (isset($data['url'])) {
            $site['url'] = esc_url_raw(trailingslashit($data['url']));
        }

        if (isset($data['consumer_key'])) {
            $site['consumer_key'] = sanitize_text_field($data['consumer_key']);
        }

        if (isset($data['consumer_secret'])) {
            $site['consumer_secret'] = sanitize_text_field($data['consumer_secret']);
        }

        if (isset($data['status'])) {
            $site['status'] = sanitize_text_field($data['status']);
        }

        $this->connected_sites[$site_id] = $site;
        $this->save_connected_sites();
        return true;
    }

    /**
     * Xóa một website
     * 
     * @param string $site_id ID của website
     * @return bool Trạng thái xóa
     */
    public function delete_site($site_id) {
        if (!isset($this->connected_sites[$site_id])) {
            return false;
        }

        unset($this->connected_sites[$site_id]);
        $this->save_connected_sites();
        return true;
    }

    /**
     * Lấy thông tin một website
     * 
     * @param string $site_id ID của website
     * @return array|bool Thông tin website hoặc false nếu không tìm thấy
     */
    public function get_site($site_id) {
        if (!isset($this->connected_sites[$site_id])) {
            return false;
        }

        return $this->connected_sites[$site_id];
    }

    /**
     * Lấy danh sách tất cả các website
     * 
     * @return array Danh sách website
     */
    public function get_all_sites() {
        return $this->connected_sites;
    }

    /**
     * Kiểm tra kết nối với website
     * 
     * @param string $url URL của website
     * @param string $consumer_key Consumer Key
     * @param string $consumer_secret Consumer Secret
     * @return array Kết quả kiểm tra
     */
    public function test_connection($url, $consumer_key, $consumer_secret) {
        $api_url = trailingslashit($url) . 'wp-json/wc/v3/system_status';
        
        $response = wp_remote_get($api_url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($consumer_key . ':' . $consumer_secret)
            )
        ));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code !== 200) {
            return array(
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $response_code
            );
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data)) {
            return array(
                'success' => false,
                'message' => 'Không thể đọc dữ liệu từ API'
            );
        }

        return array(
            'success' => true,
            'message' => 'Kết nối thành công',
            'data' => $data
        );
    }

    /**
     * Cập nhật thời gian đồng bộ cuối cùng
     * 
     * @param string $site_id ID của website
     * @return bool Trạng thái cập nhật
     */
    public function update_last_sync($site_id) {
        if (!isset($this->connected_sites[$site_id])) {
            return false;
        }

        $this->connected_sites[$site_id]['last_sync'] = current_time('mysql');
        $this->save_connected_sites();
        return true;
    }

    /**
     * Thực hiện yêu cầu GET đến API
     * 
     * @param string $site_id ID của website
     * @param string $endpoint Endpoint API
     * @param array $params Tham số truy vấn
     * @return array Kết quả yêu cầu
     */
    public function get($site_id, $endpoint, $params = array()) {
        $site = $this->get_site($site_id);
        
        if (!$site) {
            return array(
                'success' => false,
                'message' => 'Website không tồn tại'
            );
        }

        $api_url = $site['url'] . 'wp-json/wc/v3/' . $endpoint;
        
        if (!empty($params)) {
            $api_url = add_query_arg($params, $api_url);
        }

        $response = wp_remote_get($api_url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($site['consumer_key'] . ':' . $site['consumer_secret'])
            ),
            'timeout' => 30
        ));

        return $this->handle_response($response);
    }

    /**
     * Thực hiện yêu cầu POST đến API
     * 
     * @param string $site_id ID của website
     * @param string $endpoint Endpoint API
     * @param array $data Dữ liệu gửi đi
     * @return array Kết quả yêu cầu
     */
    public function post($site_id, $endpoint, $data = array()) {
        $site = $this->get_site($site_id);
        
        if (!$site) {
            return array(
                'success' => false,
                'message' => 'Website không tồn tại'
            );
        }

        $api_url = $site['url'] . 'wp-json/wc/v3/' . $endpoint;
        
        $response = wp_remote_post($api_url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($site['consumer_key'] . ':' . $site['consumer_secret']),
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data),
            'timeout' => 30
        ));

        return $this->handle_response($response);
    }

    /**
     * Thực hiện yêu cầu PUT đến API
     * 
     * @param string $site_id ID của website
     * @param string $endpoint Endpoint API
     * @param array $data Dữ liệu gửi đi
     * @return array Kết quả yêu cầu
     */
    public function put($site_id, $endpoint, $data = array()) {
        $site = $this->get_site($site_id);
        
        if (!$site) {
            return array(
                'success' => false,
                'message' => 'Website không tồn tại'
            );
        }

        $api_url = $site['url'] . 'wp-json/wc/v3/' . $endpoint;
        
        $response = wp_remote_request($api_url, array(
            'method' => 'PUT',
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($site['consumer_key'] . ':' . $site['consumer_secret']),
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data),
            'timeout' => 30
        ));

        return $this->handle_response($response);
    }

    /**
     * Thực hiện yêu cầu DELETE đến API
     * 
     * @param string $site_id ID của website
     * @param string $endpoint Endpoint API
     * @param array $params Tham số truy vấn
     * @return array Kết quả yêu cầu
     */
    public function delete($site_id, $endpoint, $params = array()) {
        $site = $this->get_site($site_id);
        
        if (!$site) {
            return array(
                'success' => false,
                'message' => 'Website không tồn tại'
            );
        }

        $api_url = $site['url'] . 'wp-json/wc/v3/' . $endpoint;
        
        if (!empty($params)) {
            $api_url = add_query_arg($params, $api_url);
        }

        $response = wp_remote_request($api_url, array(
            'method' => 'DELETE',
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($site['consumer_key'] . ':' . $site['consumer_secret'])
            ),
            'timeout' => 30
        ));

        return $this->handle_response($response);
    }

    /**
     * Xử lý phản hồi từ API
     * 
     * @param array|WP_Error $response Phản hồi từ wp_remote_*
     * @return array Kết quả xử lý
     */
    private function handle_response($response) {
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($response_code < 200 || $response_code >= 300) {
            $error_message = isset($data['message']) ? $data['message'] : 'Lỗi không xác định';
            
            return array(
                'success' => false,
                'message' => 'Lỗi API: ' . $error_message,
                'code' => $response_code
            );
        }

        return array(
            'success' => true,
            'data' => $data
        );
    }

    /**
     * Lấy danh sách sản phẩm từ website
     * 
     * @param string $site_id ID của website
     * @param array $params Tham số truy vấn
     * @return array Danh sách sản phẩm
     */
    public function get_products($site_id, $params = array()) {
        return $this->get($site_id, 'products', $params);
    }

    /**
     * Lấy danh sách đơn hàng từ website
     * 
     * @param string $site_id ID của website
     * @param array $params Tham số truy vấn
     * @return array Danh sách đơn hàng
     */
    public function get_orders($site_id, $params = array()) {
        return $this->get($site_id, 'orders', $params);
    }

    /**
     * Lấy danh sách khách hàng từ website
     * 
     * @param string $site_id ID của website
     * @param array $params Tham số truy vấn
     * @return array Danh sách khách hàng
     */
    public function get_customers($site_id, $params = array()) {
        return $this->get($site_id, 'customers', $params);
    }

    /**
     * Lấy thông tin báo cáo từ website
     * 
     * @param string $site_id ID của website
     * @param string $report_type Loại báo cáo
     * @param array $params Tham số truy vấn
     * @return array Thông tin báo cáo
     */
    public function get_reports($site_id, $report_type, $params = array()) {
        return $this->get($site_id, 'reports/' . $report_type, $params);
    }
}
