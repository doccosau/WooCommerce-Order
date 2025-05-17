<?php
/**
 * Trang dashboard trong admin
 */

// Đảm bảo không truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

// Khởi tạo API Manager
$api_manager = new Woo_API_Manager();
$sites = $api_manager->get_all_sites();

// Lấy thống kê
global $wpdb;
$products_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}woocenter_products");
$orders_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}woocenter_orders");
$customers_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}woocenter_customers");

// Lấy đơn hàng gần đây
$recent_orders = $wpdb->get_results(
    "SELECT o.*, s.name as site_name 
    FROM {$wpdb->prefix}woocenter_orders o
    LEFT JOIN (
        SELECT id, name FROM (
            SELECT id, name FROM (
                SELECT site_id as id, name FROM " . $wpdb->prefix . "woocenter_connected_sites
            ) as sites
        ) as s
    ) as s ON o.site_id = s.id
    ORDER BY o.date_created DESC
    LIMIT 5"
);

// Lấy tổng doanh thu
$total_revenue = $wpdb->get_var("SELECT SUM(total) FROM {$wpdb->prefix}woocenter_orders");
?>

<div class="wrap">
    <h1 class="wp-heading-inline">WooCenter Dashboard</h1>
    <hr class="wp-header-end">

    <div class="woocenter-dashboard">
        <!-- Thống kê tổng quan -->
        <div class="woocenter-stats">
            <div class="woocenter-stat-card">
                <div class="woocenter-stat-icon">
                    <span class="dashicons dashicons-store"></span>
                </div>
                <div class="woocenter-stat-content">
                    <h3><?php echo count($sites); ?></h3>
                    <p>Websites</p>
                </div>
            </div>
            
            <div class="woocenter-stat-card">
                <div class="woocenter-stat-icon">
                    <span class="dashicons dashicons-products"></span>
                </div>
                <div class="woocenter-stat-content">
                    <h3><?php echo $products_count; ?></h3>
                    <p>Sản phẩm</p>
                </div>
            </div>
            
            <div class="woocenter-stat-card">
                <div class="woocenter-stat-icon">
                    <span class="dashicons dashicons-cart"></span>
                </div>
                <div class="woocenter-stat-content">
                    <h3><?php echo $orders_count; ?></h3>
                    <p>Đơn hàng</p>
                </div>
            </div>
            
            <div class="woocenter-stat-card">
                <div class="woocenter-stat-icon">
                    <span class="dashicons dashicons-groups"></span>
                </div>
                <div class="woocenter-stat-content">
                    <h3><?php echo $customers_count; ?></h3>
                    <p>Khách hàng</p>
                </div>
            </div>
            
            <div class="woocenter-stat-card">
                <div class="woocenter-stat-icon">
                    <span class="dashicons dashicons-money-alt"></span>
                </div>
                <div class="woocenter-stat-content">
                    <h3><?php echo wc_price($total_revenue); ?></h3>
                    <p>Tổng doanh thu</p>
                </div>
            </div>
        </div>

        <!-- Danh sách website -->
        <div class="woocenter-card">
            <h2>Websites đã kết nối</h2>
            
            <?php if (empty($sites)): ?>
                <p>Chưa có website nào được kết nối. <a href="?page=woocenter-api-settings">Thêm website mới</a></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Tên Website</th>
                            <th>URL</th>
                            <th>Trạng thái</th>
                            <th>Đồng bộ lần cuối</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sites as $site): ?>
                            <tr>
                                <td><?php echo esc_html($site['name']); ?></td>
                                <td><a href="<?php echo esc_url($site['url']); ?>" target="_blank"><?php echo esc_url($site['url']); ?></a></td>
                                <td>
                                    <?php if ($site['status'] === 'active'): ?>
                                        <span class="woocenter-status woocenter-status-active">Kết nối</span>
                                    <?php else: ?>
                                        <span class="woocenter-status woocenter-status-inactive">Vô hiệu</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo !empty($site['last_sync']) ? esc_html($site['last_sync']) : 'Chưa đồng bộ'; ?>
                                </td>
                                <td>
                                    <a href="?page=woocenter-sync-data&site_id=<?php echo esc_attr($site['id']); ?>" class="button button-small">Đồng bộ</a>
                                    <a href="?page=woocenter-api-settings&action=edit&site_id=<?php echo esc_attr($site['id']); ?>" class="button button-small">Chỉnh sửa</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Đơn hàng gần đây -->
        <div class="woocenter-card">
            <h2>Đơn hàng gần đây</h2>
            
            <?php if (empty($recent_orders)): ?>
                <p>Chưa có đơn hàng nào.</p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Website</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                            <?php
                            $billing = json_decode($order->billing, true);
                            $customer_name = isset($billing['first_name'], $billing['last_name']) ? $billing['first_name'] . ' ' . $billing['last_name'] : '';
                            ?>
                            <tr>
                                <td>#<?php echo esc_html($order->number); ?></td>
                                <td><?php echo esc_html($order->site_name); ?></td>
                                <td><?php echo esc_html($customer_name); ?></td>
                                <td><?php echo wc_price($order->total); ?></td>
                                <td>
                                    <span class="woocenter-order-status woocenter-order-status-<?php echo esc_attr($order->status); ?>">
                                        <?php echo esc_html(wc_get_order_status_name($order->status)); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order->date_created))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p class="woocenter-view-all">
                    <a href="admin.php?page=woocenter-orders" class="button">Xem tất cả đơn hàng</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.woocenter-dashboard {
    margin-top: 20px;
}

.woocenter-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 20px;
}

.woocenter-stat-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    display: flex;
    align-items: center;
    flex: 1;
    min-width: 200px;
}

.woocenter-stat-icon {
    margin-right: 15px;
}

.woocenter-stat-icon .dashicons {
    font-size: 30px;
    width: 30px;
    height: 30px;
    color: #0073aa;
}

.woocenter-stat-content h3 {
    margin: 0;
    font-size: 24px;
    line-height: 1.2;
}

.woocenter-stat-content p {
    margin: 5px 0 0;
    color: #666;
}

.woocenter-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.woocenter-card h2 {
    margin-top: 0;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.woocenter-status {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
}

.woocenter-status-active {
    background: #d4f9d4;
    color: #0a6b0a;
}

.woocenter-status-inactive {
    background: #f9d4d4;
    color: #6b0a0a;
}

.woocenter-order-status {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
}

.woocenter-order-status-completed {
    background: #d4f9d4;
    color: #0a6b0a;
}

.woocenter-order-status-processing {
    background: #d4e7f9;
    color: #0a4a6b;
}

.woocenter-order-status-pending {
    background: #f9f4d4;
    color: #6b5a0a;
}

.woocenter-order-status-cancelled {
    background: #f9d4d4;
    color: #6b0a0a;
}

.woocenter-view-all {
    text-align: right;
    margin-top: 10px;
}
</style>
