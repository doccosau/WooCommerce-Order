<?php
/**
 * Trang đồng bộ dữ liệu trong admin
 */

// Đảm bảo không truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

// Khởi tạo API Manager và Data Sync
$api_manager = new Woo_API_Manager();
$data_sync = new Woo_Data_Sync();

// Xử lý đồng bộ dữ liệu
$sync_results = array();

if (isset($_POST['woocenter_sync_action'], $_POST['woocenter_sync_nonce']) && wp_verify_nonce($_POST['woocenter_sync_nonce'], 'woocenter_sync_action')) {
    if ($_POST['woocenter_sync_action'] === 'sync_site' && isset($_POST['woocenter_site_id'])) {
        $site_id = sanitize_text_field($_POST['woocenter_site_id']);
        $site = $api_manager->get_site($site_id);
        
        if ($site) {
            $sync_type = isset($_POST['woocenter_sync_type']) ? sanitize_text_field($_POST['woocenter_sync_type']) : 'all';
            
            switch ($sync_type) {
                case 'products':
                    $sync_results[$site_id] = array(
                        'products' => $data_sync->sync_products($site_id)
                    );
                    break;
                    
                case 'orders':
                    $sync_results[$site_id] = array(
                        'orders' => $data_sync->sync_orders($site_id)
                    );
                    break;
                    
                case 'customers':
                    $sync_results[$site_id] = array(
                        'customers' => $data_sync->sync_customers($site_id)
                    );
                    break;
                    
                case 'all':
                default:
                    $sync_results[$site_id] = $data_sync->sync_all($site_id);
                    break;
            }
        }
    } elseif ($_POST['woocenter_sync_action'] === 'sync_all_sites') {
        $sync_results = $data_sync->sync_all_sites();
    }
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Đồng bộ dữ liệu</h1>
    <hr class="wp-header-end">

    <?php if (!empty($sync_results)): ?>
        <div class="notice notice-success is-dismissible">
            <p>Đồng bộ dữ liệu hoàn tất!</p>
        </div>
        
        <div class="card">
            <h2>Kết quả đồng bộ</h2>
            
            <?php foreach ($sync_results as $site_id => $result): ?>
                <?php $site = $api_manager->get_site($site_id); ?>
                
                <h3><?php echo esc_html($site['name']); ?></h3>
                
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Loại dữ liệu</th>
                            <th>Trạng thái</th>
                            <th>Kết quả</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($result['products'])): ?>
                            <tr>
                                <td>Sản phẩm</td>
                                <td>
                                    <?php if ($result['products']['success']): ?>
                                        <span style="color: green;">Thành công</span>
                                    <?php else: ?>
                                        <span style="color: red;">Thất bại</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($result['products']['success']): ?>
                                        Đã đồng bộ <?php echo esc_html($result['products']['count']); ?> sản phẩm
                                    <?php else: ?>
                                        <?php echo esc_html($result['products']['message']); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($result['orders'])): ?>
                            <tr>
                                <td>Đơn hàng</td>
                                <td>
                                    <?php if ($result['orders']['success']): ?>
                                        <span style="color: green;">Thành công</span>
                                    <?php else: ?>
                                        <span style="color: red;">Thất bại</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($result['orders']['success']): ?>
                                        Đã đồng bộ <?php echo esc_html($result['orders']['count']); ?> đơn hàng
                                    <?php else: ?>
                                        <?php echo esc_html($result['orders']['message']); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($result['customers'])): ?>
                            <tr>
                                <td>Khách hàng</td>
                                <td>
                                    <?php if ($result['customers']['success']): ?>
                                        <span style="color: green;">Thành công</span>
                                    <?php else: ?>
                                        <span style="color: red;">Thất bại</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($result['customers']['success']): ?>
                                        Đã đồng bộ <?php echo esc_html($result['customers']['count']); ?> khách hàng
                                    <?php else: ?>
                                        <?php echo esc_html($result['customers']['message']); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Đồng bộ tất cả các website</h2>
        <p>Đồng bộ dữ liệu từ tất cả các website đã kết nối.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('woocenter_sync_action', 'woocenter_sync_nonce'); ?>
            <input type="hidden" name="woocenter_sync_action" value="sync_all_sites">
            
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Đồng bộ tất cả">
            </p>
        </form>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h2>Đồng bộ theo website</h2>
        <p>Chọn website và loại dữ liệu cần đồng bộ.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('woocenter_sync_action', 'woocenter_sync_nonce'); ?>
            <input type="hidden" name="woocenter_sync_action" value="sync_site">
            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="woocenter_site_id">Website</label></th>
                        <td>
                            <select name="woocenter_site_id" id="woocenter_site_id" required>
                                <option value="">-- Chọn website --</option>
                                <?php
                                $sites = $api_manager->get_all_sites();
                                
                                foreach ($sites as $site_id => $site) {
                                    if ($site['status'] === 'active') {
                                        echo '<option value="' . esc_attr($site_id) . '">' . esc_html($site['name']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="woocenter_sync_type">Loại dữ liệu</label></th>
                        <td>
                            <select name="woocenter_sync_type" id="woocenter_sync_type">
                                <option value="all">Tất cả dữ liệu</option>
                                <option value="products">Sản phẩm</option>
                                <option value="orders">Đơn hàng</option>
                                <option value="customers">Khách hàng</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Đồng bộ">
            </p>
        </form>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h2>Lịch trình đồng bộ tự động</h2>
        <p>Cấu hình thời gian đồng bộ dữ liệu tự động.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('woocenter_sync_schedule', 'woocenter_sync_schedule_nonce'); ?>
            <input type="hidden" name="woocenter_sync_action" value="save_schedule">
            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="woocenter_sync_interval">Tần suất đồng bộ</label></th>
                        <td>
                            <select name="woocenter_sync_interval" id="woocenter_sync_interval">
                                <option value="hourly" <?php selected(get_option('woocenter_sync_interval', 'daily'), 'hourly'); ?>>Mỗi giờ</option>
                                <option value="twicedaily" <?php selected(get_option('woocenter_sync_interval', 'daily'), 'twicedaily'); ?>>Hai lần mỗi ngày</option>
                                <option value="daily" <?php selected(get_option('woocenter_sync_interval', 'daily'), 'daily'); ?>>Mỗi ngày</option>
                                <option value="weekly" <?php selected(get_option('woocenter_sync_interval', 'daily'), 'weekly'); ?>>Mỗi tuần</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Dữ liệu đồng bộ</th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text">Dữ liệu đồng bộ</legend>
                                <label for="woocenter_sync_products">
                                    <input name="woocenter_sync_products" type="checkbox" id="woocenter_sync_products" value="1" <?php checked(get_option('woocenter_sync_products', '1'), '1'); ?>>
                                    Sản phẩm
                                </label>
                                <br>
                                <label for="woocenter_sync_orders">
                                    <input name="woocenter_sync_orders" type="checkbox" id="woocenter_sync_orders" value="1" <?php checked(get_option('woocenter_sync_orders', '1'), '1'); ?>>
                                    Đơn hàng
                                </label>
                                <br>
                                <label for="woocenter_sync_customers">
                                    <input name="woocenter_sync_customers" type="checkbox" id="woocenter_sync_customers" value="1" <?php checked(get_option('woocenter_sync_customers', '1'), '1'); ?>>
                                    Khách hàng
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Lưu cài đặt">
            </p>
        </form>
    </div>
</div>
