<?php
/**
 * WCM Roles Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * WCM_Roles_Manager Class
 */
class WCM_Roles_Manager {
    /**
     * Get all roles
     *
     * @return array
     */
    public function get_roles() {
        global $wp_roles;
        
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        
        return $wp_roles->roles;
    }
    
    /**
     * Get role
     *
     * @param string $role
     * @return array|null
     */
    public function get_role($role) {
        $roles = $this->get_roles();
        
        return isset($roles[$role]) ? $roles[$role] : null;
    }
    
    /**
     * Add role
     *
     * @param string $role
     * @param string $display_name
     * @param array $capabilities
     * @return WP_Role|null
     */
    public function add_role($role, $display_name, $capabilities = array()) {
        return add_role($role, $display_name, $capabilities);
    }
    
    /**
     * Remove role
     *
     * @param string $role
     */
    public function remove_role($role) {
        remove_role($role);
    }
    
    /**
     * Update role
     *
     * @param string $role
     * @param array $capabilities
     * @return bool
     */
    public function update_role($role, $capabilities) {
        global $wp_roles;
        
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        
        $role_obj = $wp_roles->get_role($role);
        
        if (!$role_obj) {
            return false;
        }
        
        // Get all capabilities
        $all_caps = $this->get_all_capabilities();
        
        // Reset capabilities
        foreach ($all_caps as $cap) {
            $role_obj->remove_cap($cap);
        }
        
        // Add new capabilities
        foreach ($capabilities as $cap => $grant) {
            if ($grant) {
                $role_obj->add_cap($cap);
            }
        }
        
        return true;
    }
    
    /**
     * Get all capabilities
     *
     * @return array
     */
    public function get_all_capabilities() {
        $capabilities = array(
            // WordPress core capabilities
            'read',
            'edit_posts',
            'delete_posts',
            'publish_posts',
            'edit_published_posts',
            'delete_published_posts',
            'edit_pages',
            'delete_pages',
            'publish_pages',
            'edit_published_pages',
            'delete_published_pages',
            'edit_private_pages',
            'delete_private_pages',
            'read_private_pages',
            'edit_private_posts',
            'delete_private_posts',
            'read_private_posts',
            'edit_others_posts',
            'delete_others_posts',
            'edit_others_pages',
            'delete_others_pages',
            'manage_categories',
            'manage_links',
            'moderate_comments',
            'upload_files',
            'import',
            'export',
            'unfiltered_html',
            'edit_dashboard',
            'update_plugins',
            'delete_plugins',
            'install_plugins',
            'update_themes',
            'install_themes',
            'update_core',
            'list_users',
            'edit_users',
            'create_users',
            'delete_users',
            'promote_users',
            'edit_theme_options',
            'delete_themes',
            'edit_themes',
            'activate_plugins',
            'delete_plugins',
            'edit_plugins',
            'manage_options',
            'manage_links',
            'customize',
            
            // WCM capabilities
            'wcm_view_dashboard',
            'wcm_view_orders',
            'wcm_manage_orders',
            'wcm_view_products',
            'wcm_manage_products',
            'wcm_view_customers',
            'wcm_manage_customers',
            'wcm_view_reports',
            'wcm_manage_coupons',
            'wcm_manage_settings',
            'wcm_manage_sites',
            'wcm_manage_users',
        );
        
        return $capabilities;
    }
    
    /**
     * Get WCM capabilities
     *
     * @return array
     */
    public function get_wcm_capabilities() {
        $capabilities = array(
            'wcm_view_dashboard' => __('Xem bảng điều khiển', 'wcm-roles'),
            'wcm_view_orders' => __('Xem đơn hàng', 'wcm-roles'),
            'wcm_manage_orders' => __('Quản lý đơn hàng', 'wcm-roles'),
            'wcm_view_products' => __('Xem sản phẩm', 'wcm-roles'),
            'wcm_manage_products' => __('Quản lý sản phẩm', 'wcm-roles'),
            'wcm_view_customers' => __('Xem khách hàng', 'wcm-roles'),
            'wcm_manage_customers' => __('Quản lý khách hàng', 'wcm-roles'),
            'wcm_view_reports' => __('Xem báo cáo', 'wcm-roles'),
            'wcm_manage_coupons' => __('Quản lý khuyến mãi', 'wcm-roles'),
            'wcm_manage_settings' => __('Quản lý cài đặt', 'wcm-roles'),
            'wcm_manage_sites' => __('Quản lý sites', 'wcm-roles'),
            'wcm_manage_users' => __('Quản lý người dùng', 'wcm-roles'),
        );
        
        return $capabilities;
    }
    
    /**
     * Get users by role
     *
     * @param string $role
     * @return array
     */
    public function get_users_by_role($role) {
        $users = get_users(array(
            'role' => $role,
        ));
        
        return $users;
    }
    
    /**
     * Get role display name
     *
     * @param string $role
     * @return string
     */
    public function get_role_display_name($role) {
        $roles = $this->get_roles();
        
        return isset($roles[$role]['name']) ? $roles[$role]['name'] : $role;
    }
}
