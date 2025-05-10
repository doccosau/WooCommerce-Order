<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define theme constants
define('WCM_THEME_DIR', get_template_directory());
define('WCM_THEME_URI', get_template_directory_uri());
define('WCM_VERSION', '1.0.0');

/**
 * Theme setup
 */
function wcm_theme_setup() {
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    
    // Register navigation menus
    register_nav_menus(array(
        'sidebar-menu' => esc_html__('Sidebar Menu', 'woo-central-manager'),
        'user-menu' => esc_html__('User Menu', 'woo-central-manager'),
    ));
}
add_action('after_setup_theme', 'wcm_theme_setup');

/**
 * Enqueue scripts and styles
 */
function wcm_enqueue_scripts() {
    // Bootstrap 5
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', array(), '5.3.0');
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);
    
    // Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
    
    // Chart.js
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js', array(), '4.3.0', true);
    
    // Theme styles and scripts
    wp_enqueue_style('wcm-style', WCM_THEME_URI . '/assets/css/style.css', array(), WCM_VERSION);
    wp_enqueue_script('wcm-scripts', WCM_THEME_URI . '/assets/js/scripts.js', array('jquery'), WCM_VERSION, true);
    
    // Localize script for AJAX
    wp_localize_script('wcm-scripts', 'wcm_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wcm_ajax_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'wcm_enqueue_scripts');

/**
 * Include theme files
 */
require_once WCM_THEME_DIR . '/inc/template-functions.php';
require_once WCM_THEME_DIR . '/inc/dashboard-widgets.php';
require_once WCM_THEME_DIR . '/inc/custom-post-types.php';
require_once WCM_THEME_DIR . '/inc/api-helpers.php';

/**
 * Disable admin bar for non-administrators
 */
function wcm_disable_admin_bar() {
    if (!current_user_can('administrator')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'wcm_disable_admin_bar');

/**
 * Redirect dashboard access
 */
function wcm_redirect_dashboard() {
    if (!is_admin() && !wp_doing_ajax() && !current_user_can('administrator')) {
        wp_redirect(home_url('/dashboard/'));
        exit;
    }
}
add_action('template_redirect', 'wcm_redirect_dashboard');
