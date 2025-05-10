<?php
/**
 * WCM TikTok API
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * WCM_TikTok_API Class
 */
class WCM_TikTok_API {
    /**
     * App key
     *
     * @var string
     */
    private $app_key;
    
    /**
     * App secret
     *
     * @var string
     */
    private $app_secret;
    
    /**
     * Access token
     *
     * @var string
     */
    private $access_token;
    
    /**
     * Shop ID
     *
     * @var string
     */
    private $shop_id;
    
    /**
     * API base URL
     *
     * @var string
     */
    private $api_url = 'https://open-api.tiktokglobalshop.com';
    
    /**
     * Constructor
     *
     * @param string $app_key
     * @param string $app_secret
     * @param string $access_token
     * @param string $shop_id
     */
    public function __construct($app_key, $app_secret, $access_token, $shop_id) {
        $this->app_key = $app_key;
        $this->app_secret = $app_secret;
        $this->access_token = $access_token;
        $this->shop_id = $shop_id;
    }
    
    /**
     * Get orders
     *
     * @param array $params
     * @return array|WP_Error
     */
    public function get_orders($params = array()) {
        $endpoint = '/api/orders/search';
        
        $default_params = array(
            'page_size' => 50,
            'page_number' => 1,
            'sort_by' => 'create_time',
            'sort_type' => 2, // Descending
        );
        
        $params = wp_parse_args($params, $default_params);
        
        return $this->make_request($endpoint, $params);
    }
    
    /**
     * Get order details
     *
     * @param string $order_id
     * @return array|WP_Error
     */
    public function get_order_details($order_id) {
        $endpoint = '/api/orders/detail/query';
        
        $params = array(
            'order_id' => $order_id,
        );
        
        return $this->make_request($endpoint, $params);
    }
    
    /**
     * Get products
     *
     * @param array $params
     * @return array|WP_Error
     */
    public function get_products($params = array()) {
        $endpoint = '/api/products/search';
        
        $default_params = array(
            'page_size' => 50,
            'page_number' => 1,
        );
        
        $params = wp_parse_args($params, $default_params);
        
        return $this->make_request($endpoint, $params);
    }
    
    /**
     * Get product details
     *
     * @param string $product_id
     * @return array|WP_Error
     */
    public function get_product_details($product_id) {
        $endpoint = '/api/products/details';
        
        $params = array(
            'product_id' => $product_id,
        );
        
        return $this->make_request($endpoint, $params);
    }
    
    /**
     * Make API request

## 7. Tạo file JavaScript cho theme
