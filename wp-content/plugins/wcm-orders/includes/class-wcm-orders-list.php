<?php
/**
 * WCM Orders List
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * WCM_Orders_List Class
 */
class WCM_Orders_List {
    /**
     * Get orders
     *
     * @param array $filters
     * @return array
     */
    public function get_orders($filters = array()) {
        global $wpdb;
        
        $defaults = array(
            'limit' => 20,
            'offset' => 0,
            'status' => '',
            'site_id' => 0,
            'customer_id' => 0,
            'date_from' => '',
            'date_to' => '',
            'search' => '',
            'orderby' => 'date_created',
            'order' => 'DESC',
        );
        
        $args = wp_parse_args($filters, $defaults);
        
        // Build query
        $query = "SELECT o.*, s.post_title as site_name 
                 FROM {$wpdb->prefix}wcm_orders o
                 LEFT JOIN {$wpdb->posts} s ON o.site_id = s.ID";
        
        $where = array();
        $where_args = array();
        
        // Filter by status
        if (!empty($args['status'])) {
            $where[] = "o.status = %s";
            $where_args[] = $args['status'];
        }
        
        // Filter by site
        if (!empty($args['site_id'])) {
            $where[] = "o.site_id = %d";
            $where_args[] = $args['site_id'];
        }
        
        // Filter by customer
        if (!empty($args['customer_id'])) {
            $where[] = "o.customer_id = %d";
            $where_args[] = $args['customer_id'];
        }
        
        // Filter by date range
        if (!empty($args['date_from'])) {
            $where[] = "o.date_created >= %s";
            $where_args[] = $args['date_from'];
        }
        
        if (!empty($args['date_to'])) {
            $where[] = "o.date_created <= %s";
            $where_args[] = $args['date_to'];
        }
        
        // Search
        if (!empty($args['search'])) {
            $where[] = "(o.order_data LIKE %s)";
            $where_args[] = '%' . $wpdb->esc_like($args['search']) . '%';
        }
        
        // Add WHERE clause if we have conditions
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
        
        // Order by
        $valid_orderby = array('date_created', 'total', 'status');
        $orderby = in_array($args['orderby'], $valid_orderby) ? $args['orderby'] : 'date_created';
        
        $valid_order = array('ASC', 'DESC');
        $order = in_array(strtoupper($args['order']), $valid_order) ? strtoupper($args['order']) : 'DESC';
        
        $query .= " ORDER BY o.{$orderby} {$order}";
        
        // Limit
        $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        
        // Prepare the query with all arguments
        $prepared_query = !empty($where_args) ? $wpdb->prepare($query, $where_args) : $query;
        
        // Get results
        $results = $wpdb->get_results($prepared_query);
        
        // Process results
        $orders = array();
        
        foreach ($results as $result) {
            $order_data = json_decode($result->order_data, true);
            
            $orders[] = array(
                'id' => $result->id,
                'order_id' => $result->order_id,
                'site_id' => $result->site_id,
                'site_name' => $result->site_name,
                'customer_id' => $result->customer_id,
                'customer_name' => isset($order_data['billing']['first_name']) ? $order_data['billing']['first_name'] . ' ' . $order_data['billing']['last_name'] : '',
                'status' => $result->status,
                'total' => $result->total,
                'date_created' => $result->date_created,
                'items_count' => isset($order_data['line_items']) ? count($order_data['line_items']) : 0,
                'payment_method' => isset($order_data['payment_method_title']) ? $order_data['payment_method_title'] : '',
                'order_data' => $order_data,
            );
        }
        
        return $orders;
    }
    
    /**
     * Get total orders count
     *
     * @param array $filters
     * @return int
     */
    public function get_orders_count($filters = array()) {
        global $wpdb;
        
        $defaults = array(
            'status' => '',
            'site_id' => 0,
            'customer_id' => 0,
            'date_from' => '',
            'date_to' => '',
            'search' => '',
        );
        
        $args = wp_parse_args($filters, $defaults);
        
        // Build query
        $query = "SELECT COUNT(*) FROM {$wpdb->prefix}wcm_orders o";
        
        $where = array();
        $where_args = array();
        
        // Filter by status
        if (!empty($args['status'])) {
            $where[] = "o.status = %s";
            $where_args[] = $args['status'];
        }
        
        // Filter by site
        if (!empty($args['site_id'])) {
            $where[] = "o.site_id = %d";
            $where_args[] = $args['site_id'];
        }
        
        // Filter by customer
        if (!empty($args['customer_id'])) {
            $where[] = "o.customer_id = %d";
            $where_args[] = $args['customer_id'];
        }
        
        // Filter by date range
        if (!empty($args['date_from'])) {
            $where[] = "o.date_created >= %s";
            $where_args[] = $args['date_from'];
        }
        
        if (!empty($args['date_to'])) {
            $where[] = "o.date_created <= %s";
            $where_args[] = $args['date_to'];
        }
        
        // Search
        if (!empty($args['search'])) {
            $where[] = "(o.order_data LIKE %s)";
            $where_args[] = '%' . $wpdb->esc_like($args['search']) . '%';
        }
        
        // Add WHERE clause if we have conditions
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
        
        // Prepare the query with all arguments
        $prepared_query = !empty($where_args) ? $wpdb->prepare($query, $where_args) : $query;
        
        // Get count
        return $wpdb->get_var($prepared_query);
    }
    
    /**
     * Display orders list
     *
     * @param array $args
     */
    public function display($args = array()) {
        $defaults = array(
            'limit' => 20,
            'status' => '',
            'site_id' => 0,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Get orders
        $orders = $this->get_orders($args);
        
        // Get sites for filter
        $sites = get_posts(array(
            'post_type' => 'wcm_site',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));
        
        // Get order statuses
        $statuses = array(
            'pending' => __('Chờ thanh toán', 'wcm-orders'),
            'processing' => __('Đang xử lý', 'wcm-orders'),
            'on-hold' => __('Tạm giữ', 'wcm-orders'),
            'completed' => __('Hoàn thành', 'wcm-orders'),
            'cancelled' => __('Đã hủy', 'wcm-orders'),
            'refunded' => __('Hoàn tiền', 'wcm-orders'),
            'failed' => __('Thất bại', 'wcm-orders'),
        );
        
        // Display filters
        ?>
        <div class="wcm-orders-filters">
            <form id="wcm-orders-filter-form" class="row g-3">
                <div class="col-md-3">
                    <label for="filter-status" class="form-label"><?php _e('Trạng thái', 'wcm-orders'); ?></label>
                    <select id="filter-status" name="status" class="form-select">
                        <option value=""><?php _e('Tất cả trạng thái', 'wcm-orders'); ?></option>
                        <?php foreach ($statuses as $status_key => $status_label) : ?>
                            <option value="<?php echo esc_attr($status_key); ?>" <?php selected($args['status'], $status_key); ?>>
                                <?php echo esc_html($status_label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="filter-site" class="form-label"><?php _e('Website', 'wcm-orders'); ?></label>
                    <select id="filter-site" name="site_id" class="form-select">
                        <option value=""><?php _e('Tất cả website', 'wcm-orders'); ?></option>
                        <?php foreach ($sites as $site) : ?>
                            <option value="<?php echo esc_attr($site->ID); ?>" <?php selected($args['site_id'], $site->ID); ?>>
                                <?php echo esc_html($site->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="filter-date" class="form-label"><?php _e('Thời gian', 'wcm-orders'); ?></label>
                    <input type="text" id="filter-date" name="date_range" class="form-control wcm-daterange" placeholder="<?php _e('Chọn khoảng thời gian', 'wcm-orders'); ?>">
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><?php _e('Lọc', 'wcm-orders'); ?></button>
                </div>
            </form>
        </div>
        
        <div class="wcm-orders-list mt-4">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th><?php _e('Đơn hàng', 'wcm-orders'); ?></th>
                            <th><?php _e('Ngày', 'wcm-orders'); ?></th>
                            <th><?php _e('Trạng thái', 'wcm-orders'); ?></th>
                            <th><?php _e('Khách hàng', 'wcm-orders'); ?></th>
                            <th><?php _e('Tổng', 'wcm-orders'); ?></th>
                            <th><?php _e('Website', 'wcm-orders'); ?></th>
                            <th><?php _e('Thao tác', 'wcm-orders'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)) : ?>
                            <tr>
                                <td colspan="7" class="text-center"><?php _e('Không có đơn hàng nào.', 'wcm-orders'); ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($orders as $order) : ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url(home_url('/order/' . $order['id'])); ?>" class="fw-bold">
                                            #<?php echo esc_html($order['order_id']); ?>
                                        </a>
                                        <div class="small text-muted">
                                            <?php echo sprintf(_n('%s sản phẩm', '%s sản phẩm', $order['items_count'], 'wcm-orders'), $order['items_count']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order['date_created'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo esc_attr($this->get_status_color($order['status'])); ?>">
                                            <?php echo esc_html($statuses[$order['status']] ?? $order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo esc_html($order['customer_name']); ?>
                                    </td>
                                    <td>
                                        <?php echo wc_price($order['total']); ?>
                                        <div class="small text-muted">
                                            <?php echo esc_html($order['payment_method']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo esc_html($order['site_name']); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo esc_url(home_url('/order/' . $order['id'])); ?>" class="btn btn-sm btn-outline-primary">
                                                <?php _e('Xem', 'wcm-orders'); ?>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span class="visually-hidden"><?php _e('Thao tác', 'wcm-orders'); ?></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <?php foreach ($statuses as $status_key => $status_label) : ?>
                                                    <?php if ($status_key !== $order['status']) : ?>
                                                        <li>
                                                            <a class="dropdown-item wcm-update-status" href="#" 
                                                               data-order-id="<?php echo esc_attr($order['order_id']); ?>"
                                                               data-site-id="<?php echo esc_attr($order['site_id']); ?>"
                                                               data-status="<?php echo esc_attr($status_key); ?>">
                                                                <?php echo sprintf(__('Chuyển sang %s', 'wcm-orders'), $status_label); ?>
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item" href="<?php echo esc_url(home_url('/order/' . $order['id'] . '/print')); ?>" target="_blank">
                                                        <?php _e('In đơn hàng', 'wcm-orders'); ?>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php
            // Pagination
            $total_orders = $this->get_orders_count(array(
                'status' => $args['status'],
                'site_id' => $args['site_id'],
            ));
            
            $total_pages = ceil($total_orders / $args['limit']);
            
            if ($total_pages > 1) {
                echo '<nav aria-label="' . __('Phân trang đơn hàng', 'wcm-orders') . '">';
                echo '<ul class="pagination justify-content-center">';
                
                $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
                
                // Previous page
                if ($current_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="' . esc_url(add_query_arg('paged', $current_page - 1)) . '">' . __('Trước', 'wcm-orders') . '</a></li>';
                } else {
                    echo '<li class="page-item disabled"><span class="page-link">' . __('Trước', 'wcm-orders') . '</span></li>';
                }
                
                // Page numbers
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="' . esc_url(add_query_arg('paged', 1)) . '">1</a></li>';
                    if ($start_page > 2) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    if ($i == $current_page) {
                        echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                    } else {
                        echo '<li class="page-item"><a class="page-link" href="' . esc_url(add_query_arg('paged', $i)) . '">' . $i . '</a></li>';
                    }
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo '<li class="page-item"><a class="page-link" href="' . esc_url(add_query_arg('paged', $total_pages)) . '">' . $total_pages . '</a></li>';
                }
                
                // Next page
                if ($current_page < $total_pages) {
                    echo '<li class="page-item"><a class="page-link" href="' . esc_url(add_query_arg('paged', $current_page + 1)) . '">' . __('Tiếp', 'wcm-orders') . '</a></li>';
                } else {
                    echo '<li class="page-item disabled"><span class="page-link">' . __('Tiếp', 'wcm-orders') . '</span></li>';
                }
                
                echo '</ul>';
                echo '</nav>';
            }
            ?>
        </div>
        <?php
    }
    
    /**
     * Get status color
     *
     * @param string $status
     * @return string
     */
    private function get_status_color($status) {
        $colors = array(
            'pending' => 'warning',
            'processing' => 'info',
            'on-hold' => 'secondary',
            'completed' => 'success',
            'cancelled' => 'danger',
            'refunded' => 'dark',
            'failed' => 'danger',
        );
        
        return isset($colors[$status]) ? $colors[$status] : 'secondary';
    }
}
