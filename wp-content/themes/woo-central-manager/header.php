<?php
/**
 * The header for our theme
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="wcm-wrapper d-flex">
    <!-- Sidebar -->
    <div class="wcm-sidebar bg-dark text-white">
        <div class="sidebar-header p-3">
            <?php 
            if (has_custom_logo()) {
                the_custom_logo();
            } else {
                echo '<h1 class="site-title"><a href="' . esc_url(home_url('/')) . '">' . get_bloginfo('name') . '</a></h1>';
            }
            ?>
        </div>
        
        <div class="sidebar-menu">
            <nav class="nav flex-column">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'sidebar-menu',
                    'container' => false,
                    'menu_class' => 'list-unstyled',
                    'fallback_cb' => '__return_false',
                    'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'depth' => 2,
                    'walker' => new WCM_Nav_Walker()
                ));
                ?>
            </nav>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="wcm-main-content flex-grow-1">
        <!-- Top Bar -->
        <header class="wcm-topbar bg-white shadow-sm">
            <div class="container-fluid d-flex justify-content-between align-items-center py-2">
                <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="d-flex align-items-center">
                    <div class="dropdown me-3">
                        <button class="btn btn-sm btn-outline-secondary position-relative" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo wcm_get_notification_count(); ?>
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                            <?php wcm_display_notifications(); ?>
                        </ul>
                    </div>
                    
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo get_avatar(get_current_user_id(), 24, '', '', array('class' => 'rounded-circle me-1')); ?>
                            <span class="d-none d-md-inline-block ms-1"><?php echo wp_get_current_user()->display_name; ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo esc_url(home_url('/profile/')); ?>"><i class="fas fa-user me-2"></i> Hồ sơ</a></li>
                            <li><a class="dropdown-item" href="<?php echo esc_url(home_url('/settings/')); ?>"><i class="fas fa-cog me-2"></i> Cài đặt</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo wp_logout_url(home_url()); ?>"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <div class="wcm-content p-3 p-md-4">
