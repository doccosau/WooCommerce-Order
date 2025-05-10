<?php
/**
 * WCM Sync API
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * WCM_Sync_API Class
 */
class WCM_Sync_API {
    /**
     * Site URL
     *
     * @var string
     */
    private $site_url;
    
    /**
     * Consumer key
     *
     * @var string
     */
    private $consumer_key;
    
    /**
     * Consumer secret
     *
     * @var string
     */
    private $consumer_secret;
    
    /**
     * Constructor
     *
     * @param string $site_url
     * @param string $consumer_key
     * @param string $consumer_secret
     */
    public function __construct($site_url, $consumer_key, $consumer_secret) {
        $this->site_url = rtrim($site_url, '/');
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
    }
    
    /**
     * Get orders
     *
     * @param array $args
     * @return array|WP_Error
     */
    public function get_orders($args = array()) {
        $endpoint = '/wp-json/wc/v3/orders';
        return $this->make_request($endpoint, $args);
    }
    
    /**
     * Get products
     *
     * @param array $args
     * @return array|WP_Error
     */
    public function get_products($args = array()) {
        $endpoint = '/wp-json/wc/v3/products';
        return $this->make_request($endpoint, $args);
    }
    
    /**
     * Get customers
     *
     * @param array $args
     * @return array|WP_Error
     */
    public function get_customers($args = array()) {
        $endpoint = '/wp-json/wc/v3/customers';
        return $this->make_request($endpoint, $args);
    }
    
    /**
     * Get order
     *
     * @param int $order_id
     * @return array|WP_Error
     */
    public function get_order($order_id) {
        $endpoint = '/wp-json/wc/v3/orders/' . $order_id;
        return $this->make_request($endpoint);
    }
    
    /**
     * Get product
     *
     * @param int $product_id
     * @return array|WP_Error
     */
    public function get_product($product_id) {
        $endpoint = '/wp-json/wc/v3/products/' . $product_id;
        return $this->make_request($endpoint);
    }
    
    /**
     * Get customer
     *
     * @param int $customer_id
     * @return array|WP_Error
     */
    public function get_customer($customer_id) {
        $endpoint = '/wp-json/wc/v3/customers/' . $customer_id;
        return $this->make_request($endpoint);
    }
    
    /**
     * Update order
     *
     * @param int $order_id
     * @param array $data
     * @return array|WP_Error
     */
    public function update_order($order_id, $data) {
        $endpoint = '/wp-json/wc/v3/orders/' . $order_id;
        return $this->make_request($endpoint, $data, 'PUT');
    }
    
    /**
     * Update product
     *
     * @param int $product_id
     * @param array $data
     * @return array|WP_Error
     */
    public function update_product($product_id, $data) {
        $endpoint = '/wp-json/wc/v3/products/' . $product_id;
        return $this->make_request($endpoint, $data, 'PUT');
    }
    
    /**
     * Make API request
     *
     * @param string $endpoint
     * @param array $args
     * @param string $method
     * @return array|WP_Error
     */
    private function make_request($endpoint, $args = array(), $method = 'GET') {
        $url = $this->site_url . $endpoint;
        
        // Add query parameters for GET requests
        if ($method === 'GET' && !empty($args)) {
            $url = add_query_arg($args, $url);
        }
        
        $request_args = array(
            'method' => $method,
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($this->consumer_key . ':' . $this->consumer_secret),
                'Content-Type' => 'application/json',
            ),
        );
        
        // Add body for non-GET requests
        if ($method !== 'GET' && !empty($args)) {
            $request_args['body'] = json_encode($args);
        }
        
        $response = wp_remote_request($url, $request_args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code >= 400) {
            return new WP_Error(
                'wcm_api_error',
                sprintf(__('API Error: %s', 'wcm-data-sync'), $response_body),
                array('status' => $response_code)
            );
        }
        
        return json_decode($response_body, true);
    }
}
