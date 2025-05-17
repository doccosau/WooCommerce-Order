<?php
/**
 * Trang cài đặt API trong admin
 */

// Đảm bảo không truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

// Khởi tạo API Manager
$api_manager = new Woo_API_Manager();

// Xử lý form thêm/sửa website
if (isset($_POST['woocenter_api_action'])) {
    if ($_POST['woocenter_api_action'] === 'add_site' && isset($_POST['woocenter_site_name'], $_POST['woocenter_site_url'], $_POST['woocenter_consumer_key'], $_POST['woocenter_consumer_secret'])) {
        // Kiểm tra nonce
        if (!isset($_POST['woocenter_api_nonce']) || !wp_verify_nonce($_POST['woocenter_api_nonce'], 'woocenter_api_action')) {
            wp_die('Lỗi bảo mật');
        }

        $name = sanitize_text_field($_POST['woocenter_site_name']);
        $url = esc_url_raw($_POST['woocenter_site_url']);
        $consumer_key = sanitize_text_field($_POST['woocenter_consumer_key']);
        $consumer_secret = sanitize_text_field($_POST['woocenter_consumer_secret']);

        $result = $api_manager->add_site($name, $url, $consumer_key, $consumer_secret);

        if ($result) {
            echo '<div class="notice notice-success is-dismissible"><p>Thêm website thành công!</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Không thể kết nối đến website. Vui lòng kiểm tra lại thông tin.</p></div>';
        }
    } elseif ($_POST['woocenter_api_action'] === 'edit_site' && isset($_POST['woocenter_site_id'], $_POST['woocenter_site_name'], $_POST['woocenter_site_url'], $_POST['woocenter_consumer_key'], $_POST['woocenter_consumer_secret'])) {
        // Kiểm tra nonce
        if (!isset($_POST['woocenter_api_nonce']) || !wp_verify_nonce($_POST['woocenter_api_nonce'], 'woocenter_api_action')) {
            wp_die('Lỗi bảo mật');
        }

        $site_id = sanitize_text_field($_POST['woocenter_site_id']);
        $data = array(
            'name' => sanitize_text_field($_POST['woocenter_site_name']),
            'url' => esc_url_raw($_POST['woocenter_site_url']),
            'consumer_key' => sanitize_text_field($_POST['woocenter_consumer_key']),
            'consumer_secret' => sanitize_text_field($_POST['woocenter_consumer_secret']),
        );

        $result = $api_manager->update_site($site_id, $data);

        if ($result) {
            echo '<div class="notice notice-success is-dismissible"><p>Cập nhật website thành công!</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Không thể cập nhật website. Vui lòng kiểm tra lại thông tin.</p></div>';
        }
    }
}

// Xử lý xóa website
if (isset($_GET['action'], $_GET['site_id'], $_GET['_wpnonce']) && $_GET['action'] === 'delete') {
    if (wp_verify_nonce($_GET['_wpnonce'], 'delete_site_' . $_GET['site_id'])) {
        $site_id = sanitize_text_field($_GET['site_id']);
        $result = $api_manager->delete_site($site_id);

        if ($result) {
            echo '<div class="notice notice-success is-dismissible"><p>Xóa website thành công!</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Không thể xóa website.</p></div>';
        }
    } else {
        wp_die('Lỗi bảo mật');
    }
}

// Xử lý kiểm tra kết nối
if (isset($_GET['action'], $_GET['site_id'], $_GET['_wpnonce']) && $_GET['action'] === 'test') {
    if (wp_verify_nonce($_GET['_wpnonce'], 'test_site_' . $_GET['site_id'])) {
        $site_id = sanitize_text_field($_GET['site_id']);
        $site = $api_manager->get_site($site_id);

        if ($site) {
            $test_result = $api_manager->test_connection($site['url'], $site['consumer_key'], $site['consumer_secret']);

            if ($test_result['success']) {
                echo '<div class="notice notice-success is-dismissible"><p>Kết nối thành công đến ' . esc_html($site['name']) . '!</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Lỗi kết nối: ' . esc_html($test_result['message']) . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Website không tồn tại.</p></div>';
        }
    } else {
        wp_die('Lỗi bảo mật');
    }
}

// Hiển thị form thêm/sửa website
$edit_mode = false;
$edit_site = array();

if (isset($_GET['action'], $_GET['site_id']) && $_GET['action'] === 'edit') {
    $site_id = sanitize_text_field($_GET['site_id']);
    $edit_site = $api_manager->get_site($site_id);

    if ($edit_site) {
        $edit_mode = true;
    }
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo $edit_mode ? 'Chỉnh sửa Website' : 'Thêm Website Mới'; ?>
    </h1>
    
    <?php if (!$edit_mode): ?>
        <a href="?page=woocenter-api-settings" class="page-title-action">Thêm mới</a>
    <?php endif; ?>
    
    <hr class="wp-header-end">

    <form method="post" action="">
        <?php wp_nonce_field('woocenter_api_action', 'woocenter_api_nonce'); ?>
        <input type="hidden" name="woocenter_api_action" value="<?php echo $edit_mode ? 'edit_site' : 'add_site'; ?>">
        
        <?php if ($edit_mode): ?>
            <input type="hidden" name="woocenter_site_id" value="<?php echo esc_attr($edit_site['id']); ?>">
        <?php endif; ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="woocenter_site_name">Tên Website</label></th>
                    <td>
                        <input name="woocenter_site_name" type="text" id="woocenter_site_name" class="regular-text" value="<?php echo $edit_mode ? esc_attr($edit_site['name']) : ''; ?>" required>
                        <p class="description">Nhập tên để nhận diện website này (ví dụ: Shop Thời Trang)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="woocenter_site_url">URL Website</label></th>
                    <td>
                        <input name="woocenter_site_url" type="url" id="woocenter_site_url" class="regular-text" value="<?php echo $edit_mode ? esc_url($edit_site['url']) : ''; ?>" required>
                        <p class="description">Nhập URL đầy đủ của website (ví dụ: https://fashion.example.com)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="woocenter_consumer_key">Consumer Key</label></th>
                    <td>
                        <input name="woocenter_consumer_key" type="text" id="woocenter_consumer_key" class="regular-text" value="<?php echo $edit_mode ? esc_attr($edit_site['consumer_key']) : ''; ?>" required>
                        <p class="description">Nhập Consumer Key từ WooCommerce API</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="woocenter_consumer_secret">Consumer Secret</label></th>
                    <td>
                        <input name="woocenter_consumer_secret" type="text" id="woocenter_consumer_secret" class="regular-text" value="<?php echo $edit_mode ? esc_attr($edit_site['consumer_secret']) : ''; ?>" required>
                        <p class="description">Nhập Consumer Secret từ WooCommerce API</p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $edit_mode ? 'Cập nhật Website' : 'Thêm Website'; ?>">
            <?php if ($edit_mode): ?>
                <a href="?page=woocenter-api-settings" class="button">Hủy</a>
            <?php endif; ?>
        </p>
    </form>

    <h2 class="wp-heading-inline">Danh sách Website đã kết nối</h2>
    <hr class="wp-header-end">

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
            <?php
            $sites = $api_manager->get_all_sites();
            
            if (empty($sites)):
            ?>
                <tr>
                    <td colspan="5">Chưa có website nào được kết nối.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($sites as $site): ?>
                    <tr>
                        <td><?php echo esc_html($site['name']); ?></td>
                        <td><?php echo esc_url($site['url']); ?></td>
                        <td>
                            <?php if ($site['status'] === 'active'): ?>
                                <span class="dashicons dashicons-yes" style="color: green;"></span> Kết nối
                            <?php else: ?>
                                <span class="dashicons dashicons-no" style="color: red;"></span> Vô hiệu
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo !empty($site['last_sync']) ? esc_html($site['last_sync']) : 'Chưa đồng bộ'; ?>
                        </td>
                        <td>
                            <a href="?page=woocenter-api-settings&action=test&site_id=<?php echo esc_attr($site['id']); ?>&_wpnonce=<?php echo wp_create_nonce('test_site_' . $site['id']); ?>" class="button button-small">Kiểm tra kết nối</a>
                            <a href="?page=woocenter-api-settings&action=edit&site_id=<?php echo esc_attr($site['id']); ?>" class="button button-small">Chỉnh sửa</a>
                            <a href="?page=woocenter-api-settings&action=delete&site_id=<?php echo esc_attr($site['id']); ?>&_wpnonce=<?php echo wp_create_nonce('delete_site_' . $site['id']); ?>" class="button button-small" onclick="return confirm('Bạn có chắc chắn muốn xóa website này?');">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="wrap" style="margin-top: 20px;">
    <h2>Hướng dẫn tạo API Keys trên website WooCommerce</h2>
    <div class="card">
        <h3>Các bước tạo API Keys:</h3>
        <ol>
            <li>Đăng nhập vào trang quản trị WordPress của website con.</li>
            <li>Truy cập <strong>WooCommerce > Cài đặt > Nâng cao > REST API</strong>.</li>
            <li>Nhấn vào nút <strong>Thêm khóa</strong>.</li>
            <li>Nhập thông tin:
                <ul>
                    <li><strong>Mô tả:</strong> WooCenter Integration</li>
                    <li><strong>Quyền:</strong> Chọn "Đọc/Ghi" để có quyền truy cập đầy đủ</li>
                </ul>
            </li>
            <li>Nhấn <strong>Tạo API key</strong>.</li>
            <li>Sao chép <strong>Consumer Key</strong> và <strong>Consumer Secret</strong> và dán vào form bên trên.</li>
        </ol>
        <p><strong>Lưu ý:</strong> Consumer Secret chỉ hiển thị một lần. Hãy lưu lại ngay khi tạo key.</p>
    </div>
</div>
