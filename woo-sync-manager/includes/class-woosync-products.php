<?php
/**
 * WooSync Products
 * 
 * Đồng bộ sản phẩm từ các website WooCommerce con
 */

// Đảm bảo không truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

class WooSync_Products {
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
     * Đồng bộ sản phẩm từ một website
     */
    public function sync_products($site_id, $args = array()) {
        $defaults = array(
            'per_page' => 20,
            'page' => 1,
            'sync_id' => '',
            'max_pages' => 10, // Giới hạn số trang để tránh quá tải
            'include_variations' => true,
            'include_images' => true,
            'include_attributes' => true,
            'include_categories' => true,
            'include_tags' => true
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
        
        $this->logger->log("Bắt đầu đồng bộ sản phẩm từ {$site['name']} ({$site_id})");
        
        $total_products = 0;
        $synced_count = 0;
        $error_count = 0;
        $current_page = $args['page'];
        $max_pages = $args['max_pages'];
        
        try {
            // Lấy tổng số sản phẩm
            $count_result = $this->get_products_count($site_id);
            
            if (!$count_result['success']) {
                throw new Exception($count_result['message']);
            }
            
            $total_products = $count_result['count'];
            
            if (!empty($sync_id)) {
                $this->core->update_sync_progress($sync_id, 0, $total_products);
            }
            
            $this->logger->log("Tổng số sản phẩm: {$total_products}");
            
            // Tính toán số trang
            $total_pages = ceil($total_products / $args['per_page']);
            
            // Giới hạn số trang nếu cần
            if ($max_pages > 0 && $total_pages > $max_pages) {
                $total_pages = $max_pages;
                $this->logger->log("Giới hạn số trang đồng bộ: {$max_pages}");
            }
            
            $processed_count = 0;
            
            // Lặp qua từng trang sản phẩm
            while ($current_page <= $total_pages) {
                $this->logger->log("Đồng bộ sản phẩm trang {$current_page}/{$total_pages}");
                
                // Lấy sản phẩm từ trang hiện tại
                $products_result = $this->get_products_page($site_id, array(
                    'per_page' => $args['per_page'],
                    'page' => $current_page,
                    'include_variations' => $args['include_variations'],
                    'include_images' => $args['include_images'],
                    'include_attributes' => $args['include_attributes'],
                    'include_categories' => $args['include_categories'],
                    'include_tags' => $args['include_tags']
                ));
                
                if (!$products_result['success']) {
                    $this->logger->log("Lỗi lấy sản phẩm trang {$current_page}: " . $products_result['message'], 'error');
                    $error_count++;
                    
                    if (!empty($sync_id)) {
                        $this->core->increment_sync_error($sync_id, "Lỗi lấy sản phẩm trang {$current_page}: " . $products_result['message']);
                    }
                    
                    // Tiếp tục với trang tiếp theo
                    $current_page++;
                    continue;
                }
                
                $products = $products_result['products'];
                $page_count = count($products);
                
                $this->logger->log("Đã lấy {$page_count} sản phẩm từ trang {$current_page}");
                
                // Lưu từng sản phẩm vào cơ sở dữ liệu
                foreach ($products as $product) {
                    $processed_count++;
                    
                    try {
                        $saved = $this->save_product($site_id, $product);
                        
                        if ($saved) {
                            $synced_count++;
                            
                            if (!empty($sync_id)) {
                                $this->core->increment_sync_success($sync_id);
                            }
                        } else {
                            $error_count++;
                            
                            if (!empty($sync_id)) {
                                $this->core->increment_sync_error($sync_id, "Không thể lưu sản phẩm: {$product['id']}");
                            }
                        }
                    } catch (Exception $e) {
                        $this->logger->log("Lỗi lưu sản phẩm {$product['id']}: " . $e->getMessage(), 'error');
                        $error_count++;
                        
                        if (!empty($sync_id)) {
                            $this->core->increment_sync_error($sync_id, "Lỗi lưu sản phẩm {$product['id']}: " . $e->getMessage());
                        }
                    }
                    
                    // Cập nhật tiến trình
                    if (!empty($sync_id)) {
                        $this->core->update_sync_progress($sync_id, $processed_count, $total_products);
                    }
                }
                
                // Chuyển sang trang tiếp theo
                $current_page++;
                
                // Tạm dừng giữa các trang để tránh quá tải
                if ($current_page <= $total_pages) {
                    sleep(1);
                }
            }
            
            $this->logger->log("Hoàn tất đồng bộ sản phẩm từ {$site['name']} ({$site_id}). Đã đồng bộ: {$synced_count}, Lỗi: {$error_count}");
            
            return array(
                'success' => true,
                'message' => "Đã đồng bộ {$synced_count} sản phẩm, {$error_count} lỗi",
                'synced' => $synced_count,
                'errors' => $error_count,
                'total' => $total_products
            );
            
        } catch (Exception $e) {
            $this->logger->log("Lỗi đồng bộ sản phẩm từ {$site['name']} ({$site_id}): " . $e->getMessage(), 'error');
            
            if (!empty($sync_id)) {
                $this->core->update_sync_status($sync_id, 'error', $e->getMessage());
            }
            
            return array(
                'success' => false,
                'message' => $e->getMessage(),
                'synced' => $synced_count,
                'errors' => $error_count,
                'total' => $total_products
            );
        }
    }
    
    /**
     * Lấy số lượng sản phẩm từ website
     */
    private function get_products_count($site_id) {
        $api = new WooSync_API($this->logger);
        
        $result = $api->get($site_id, 'products', array(
            'per_page' => 1
        ));
        
        if (!$result['success']) {
            return array(
                'success' => false,
                'message' => $result['message']
            );
        }
        
        // Lấy tổng số sản phẩm từ header
        $total_products = isset($result['headers']['X-WP-Total']) ? intval($result['headers']['X-WP-Total']) : 0;
        
        return array(
            'success' => true,
            'count' => $total_products
        );
    }
    
    /**
     * Lấy một trang sản phẩm từ website
     */
    private function get_products_page($site_id, $args = array()) {
        $defaults = array(
            'per_page' => 20,
            'page' => 1,
            'include_variations' => true,
            'include_images' => true,
            'include_attributes' => true,
            'include_categories' => true,
            'include_tags' => true
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $api = new WooSync_API($this->logger);
        
        $params = array(
            'per_page' => $args['per_page'],
            'page' => $args['page'],
            'status' => 'publish,draft,pending'
        );
        
        $result = $api->get($site_id, 'products', $params);
        
        if (!$result['success']) {
            return array(
                'success' => false,
                'message' => $result['message']
            );
        }
        
        $products = $result['data'];
        
        // Lấy thêm thông tin biến thể nếu cần
        if ($args['include_variations']) {
            foreach ($products as $key => $product) {
                if ($product['type'] === 'variable') {
                    $variations_result = $this->get_product_variations($site_id, $product['id']);
                    
                    if ($variations_result['success']) {
                        $products[$key]['variations_data'] = $variations_result['variations'];
                    }
                }
            }
        }
        
        return array(
            'success' => true,
            'products' => $products
        );
    }
    
    /**
     * Lấy biến thể của sản phẩm
     */
    private function get_product_variations($site_id, $product_id) {
        $api = new WooSync_API($this->logger);
        
        $result = $api->get($site_id, "products/{$product_id}/variations", array(
            'per_page' => 100
        ));
        
        if (!$result['success']) {
            return array(
                'success' => false,
                'message' => $result['message']
            );
        }
        
        return array(
            'success' => true,
            'variations' => $result['data']
        );
    }
    
    /**
     * Lưu sản phẩm vào cơ sở dữ liệu
     */
    private function save_product($site_id, $product) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'woosync_products';
        
        // Kiểm tra sản phẩm đã tồn tại chưa
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE site_id = %s AND product_id = %d",
                $site_id,
                $product['id']
            )
        );
        
        // Chuẩn bị dữ liệu
        $data = array(
            'site_id' => $site_id,
            'product_id' => $product['id'],
            'name' => $product['name'],
            'slug' => $product['slug'],
            'type' => $product['type'],
            'status' => $product['status'],
            'featured' => $product['featured'] ? 1 : 0,
            'catalog_visibility' => $product['catalog_visibility'],
            'description' => $product['description'],
            'short_description' => $product['short_description'],
            'sku' => $product['sku'],
            'price' => $product['price'],
            'regular_price' => $product['regular_price'],
            'sale_price' => $product['sale_price'],
            'date_on_sale_from' => $product['date_on_sale_from'],
            'date_on_sale_to' => $product['date_on_sale_to'],
            'virtual' => $product['virtual'] ? 1 : 0,
            'downloadable' => $product['downloadable'] ? 1 : 0,
            'downloads' => json_encode($product['downloads']),
            'download_limit' => $product['download_limit'],
            'download_expiry' => $product['download_expiry'],
            'tax_status' => $product['tax_status'],
            'tax_class' => $product['tax_class'],
            'manage_stock' => $product['manage_stock'] ? 1 : 0,
            'stock_quantity' => $product['stock_quantity'],
            'stock_status' => $product['stock_status'],
            'backorders' => $product['backorders'],
            'backorders_allowed' => $product['backorders_allowed'] ? 1 : 0,
            'backordered' => $product['backordered'] ? 1 : 0,
            'sold_individually' => $product['sold_individually'] ? 1 : 0,
            'weight' => $product['weight'],
            'dimensions' => json_encode(array(
                'length' => $product['dimensions']['length'],
                'width' => $product['dimensions']['width'],
                'height' => $product['dimensions']['height']
            )),
            'shipping_required' => $product['shipping_required'] ? 1 : 0,
            'shipping_taxable' => $product['shipping_taxable'] ? 1 : 0,
            'shipping_class' => $product['shipping_class'],
            'shipping_class_id' => $product['shipping_class_id'],
            'reviews_allowed' => $product['reviews_allowed'] ? 1 : 0,
            'average_rating' => $product['average_rating'],
            'rating_count' => $product['rating_count'],
            'related_ids' => json_encode($product['related_ids']),
            'upsell_ids' => json_encode($product['upsell_ids']),
            'cross_sell_ids' => json_encode($product['cross_sell_ids']),
            'parent_id' => $product['parent_id'],
            'purchase_note' => $product['purchase_note'],
            'categories' => json_encode($product['categories']),
            'tags' => json_encode($product['tags']),
            'images' => json_encode($product['images']),
            'attributes' => json_encode($product['attributes']),
            'default_attributes' => json_encode($product['default_attributes']),
            'variations' => json_encode(isset($product['variations']) ? $product['variations'] : array()),
            'variations_data' => json_encode(isset($product['variations_data']) ? $product['variations_data'] : array()),
            'menu_order' => $product['menu_order'],
            'meta_data' => json_encode($product['meta_data']),
            'permalink' => $product['permalink'],
            'date_created' => $product['date_created'],
            'date_modified' => $product['date_modified'],
            'last_synced' => current_time('mysql')
        );
        
        $format = array(
            '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d',
            '%s', '%d', '%d', '%s', '%s', '%d', '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%d', '%s', '%d',
            '%d', '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
            '%s', '%d', '%s', '%s', '%s', '%s'
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
        
        if ($result === false) {
            $this->logger->log("Lỗi SQL khi lưu sản phẩm {$product['id']}: " . $wpdb->last_error, 'error');
            return false;
        }
        
        return true;
    }
    
    /**
     * Lấy sản phẩm từ cơ sở dữ liệu
     */
    public function get_product($product_id, $site_id = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'woosync_products';
        
        if (!empty($site_id)) {
            $query = $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE product_id = %d AND site_id = %s",
                $product_id,
                $site_id
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE product_id = %d",
                $product_id
            );
        }
        
        $product = $wpdb->get_row($query);
        
        if (!$product) {
            return false;
        }
        
        // Chuyển đổi các trường JSON thành mảng
        $json_fields = array(
            'downloads', 'dimensions', 'related_ids', 'upsell_ids', 'cross_sell_ids',
            'categories', 'tags', 'images', 'attributes', 'default_attributes',
            'variations', 'variations_data', 'meta_data'
        );
        
        foreach ($json_fields as $field) {
            if (isset($product->$field)) {
                $product->$field = json_decode($product->$field, true);
            }
        }
        
        return $product;
    }
    
    /**
     * Lấy danh sách sản phẩm từ cơ sở dữ liệu
     */
    public function get_products($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'per_page' => 20,
            'page' => 1,
            'site_id' => '',
            'status' => '',
            'type' => '',
            'category' => '',
            'search' => '',
            'orderby' => 'date_created',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'woosync_products';
        
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
        
        if (!empty($args['type'])) {
            $where[] = 'type = %s';
            $where_format[] = $args['type'];
        }
        
        if (!empty($args['search'])) {
            $where[] = '(name LIKE %s OR sku LIKE %s)';
            $where_format[] = '%' . $wpdb->esc_like($args['search']) . '%';
            $where_format[] = '%' . $wpdb->esc_like($args['search']) . '%';
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
        
        // Đếm tổng số sản phẩm
        $count_query = "SELECT COUNT(*) FROM {$table_name} {$where_clause}";
        
        if (!empty($where_format)) {
            $count_query = $wpdb->prepare($count_query, $where_format);
        }
        
        $total = $wpdb->get_var($count_query);
        
        // Lấy danh sách sản phẩm
        $query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY {$orderby} LIMIT %d OFFSET %d";
        
        $query_args = $where_format;
        $query_args[] = $per_page;
        $query_args[] = $offset;
        
        $products = $wpdb->get_results($wpdb->prepare($query, $query_args));
        
        // Chuyển đổi các trường JSON thành mảng
        $json_fields = array(
            'downloads', 'dimensions', 'related_ids', 'upsell_ids', 'cross_sell_ids',
            'categories', 'tags', 'images', 'attributes', 'default_attributes',
            'variations', 'variations_data', 'meta_data'
        );
        
        foreach ($products as &$product) {
            foreach ($json_fields as $field) {
                if (isset($product->$field)) {
                    $product->$field = json_decode($product->$field, true);
                }
            }
        }
        
        return array(
            'products' => $products,
            'total' => intval($total),
            'pages' => ceil($total / $per_page)
        );
    }
    
    /**
     * Xóa sản phẩm từ cơ sở dữ liệu
     */
    public function delete_product($product_id, $site_id = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'woosync_products';
        
        if (!empty($site_id)) {
            $result = $wpdb->delete(
                $table_name,
                array(
                    'product_id' => $product_id,
                    'site_id' => $site_id
                ),
                array('%d', '%s')
            );
        } else {
            $result = $wpdb->delete(
                $table_name,
                array('product_id' => $product_id),
                array('%d')
            );
        }
        
        return $result !== false;
    }
    
    /**
     * Xóa tất cả sản phẩm của một website
     */
    public function delete_all_products($site_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'woosync_products';
        
        $result = $wpdb->delete(
            $table_name,
            array('site_id' => $site_id),
            array('%s')
        );
        
        return $result !== false;
    }
}
