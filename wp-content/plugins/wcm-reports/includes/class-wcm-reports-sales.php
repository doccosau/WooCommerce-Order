<?php
/**
 * WCM Reports Sales
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * WCM_Reports_Sales Class
 */
class WCM_Reports_Sales {
    /**
     * Get sales data
     *
     * @param string $period
     * @param int $site_id
     * @return array
     */
    public function get_data($period = '30days', $site_id = 0) {
        global $wpdb;
        
        // Set date range based on period
        $date_to = current_time('mysql');
        $date_from = '';
        
        switch ($period) {
            case '7days':
                $date_from = date('Y-m-d H:i:s', strtotime('-7 days', strtotime($date_to)));
                break;
                
            case '30days':
                $date_from = date('Y-m-d H:i:s', strtotime('-30 days', strtotime($date_to)));
                break;
                
            case '90days':
                $date_from = date('Y-m-d H:i:s', strtotime('-90 days', strtotime($date_to)));
                break;
                
            case 'year':
                $date_from = date('Y-01-01 00:00:00');
                break;
                
            case 'custom':
                $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : date('Y-m-d H:i:s', strtotime('-30 days', strtotime($date_to)));
                $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : $date_to;
                break;
        }
        
        // Build query
        $query = "SELECT 
                    DATE(date_created) as date,
                    COUNT(*) as orders_count,
                    SUM(total) as total_sales
                 FROM {$wpdb->prefix}wcm_orders
                 WHERE date_created BETWEEN %s AND %s";
        
        $query_args = array($date_from, $date_to);
        
        // Filter by site
        if (!empty($site_id)) {
            $query .= " AND site_id = %d";
            $query_args[] = $site_id;
        }
        
        // Filter by status (only completed and processing)
        $query .= " AND status IN ('completed', 'processing')";
        
        // Group by date
        $query .= " GROUP BY DATE(date_created)";
        
        // Order by date
        $query .= " ORDER BY date ASC";
        
        // Prepare the query
        $prepared_query = $wpdb->prepare($query, $query_args);
        
        // Get results
        $results = $wpdb->get_results($prepared_query);
        
        // Process results
        $dates = array();
        $sales = array();
        $orders = array();
        
        // Fill in missing dates
        $start = new DateTime($date_from);
        $end = new DateTime($date_to);
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);
        
        foreach ($period as $date) {
            $date_str = $date->format('Y-m-d');
            $dates[] = $date->format('d/m/Y');
            $sales[$date_str] = 0;
            $orders[$date_str] = 0;
        }
        
        // Fill in actual data
        foreach ($results as $result) {
            $sales[$result->date] = (float) $result->total_sales;
            $orders[$result->date] = (int) $result->orders_count;
        }
        
        // Get summary data
        $total_sales = array_sum($sales);
        $total_orders = array_sum($orders);
        $avg_order_value = $total_orders > 0 ? $total_sales / $total_orders : 0;
        
        // Get comparison data (previous period)
        $prev_date_to = $date_from;
        $prev_date_from = '';
        
        switch ($period) {
            case '7days':
                $prev_date_from = date('Y-m-d H:i:s', strtotime('-14 days', strtotime($date_to)));
                break;
                
            case '30days':
                $prev_date_from = date('Y-m-d H:i:s', strtotime('-60 days', strtotime($date_to)));
                break;
                
            case '90days':
                $prev_date_from = date('Y-m-d H:i:s', strtotime('-180 days', strtotime($date_to)));
                break;
                
            case 'year':
                $prev_date_from = date('Y-01-01 00:00:00', strtotime('-1 year'));
                $prev_date_to = date('Y-01-01 00:00:00');
                break;
                
            case 'custom':
                $days_diff = (strtotime($date_to) - strtotime($date_from)) / (60 * 60 * 24);
                $prev_date_from = date('Y-m-d H:i:s', strtotime("-{$days_diff} days", strtotime($date_from)));
                break;
        }
        
        // Build comparison query
        $comp_query = "SELECT 
                        SUM(total) as total_sales,
                        COUNT(*) as orders_count
                     FROM {$wpdb->prefix}wcm_orders
                     WHERE date_created BETWEEN %s AND %s";
        
        $comp_query_args = array($prev_date_from, $prev_date_to);
        
        // Filter by site
        if (!empty($site_id)) {
            $comp_query .= " AND site_id = %d";
            $comp_query_args[] = $site_id;
        }
        
        // Filter by status (only completed and processing)
        $comp_query .= " AND status IN ('completed', 'processing')";
        
        // Prepare the query
        $comp_prepared_query = $wpdb->prepare($comp_query, $comp_query_args);
        
        // Get results
        $comp_result = $wpdb->get_row($comp_prepared_query);
        
        $prev_total_sales = (float) $comp_result->total_sales;
        $prev_total_orders = (int) $comp_result->orders_count;
        $prev_avg_order_value = $prev_total_orders > 0 ? $prev_total_sales / $prev_total_orders : 0;
        
        // Calculate growth
        $sales_growth = $prev_total_sales > 0 ? (($total_sales - $prev_total_sales) / $prev_total_sales) * 100 : 0;
        $orders_growth = $prev_total_orders > 0 ? (($total_orders - $prev_total_orders) / $prev_total_orders) * 100 : 0;
        $aov_growth = $prev_avg_order_value > 0 ? (($avg_order_value - $prev_avg_order_value) / $prev_avg_order_value) * 100 : 0;
        
        // Get top products
        $top_products_query = "SELECT 
                                p.name,
                                COUNT(*) as orders_count,
                                SUM(o.total) as total_sales
                             FROM {$wpdb->prefix}wcm_orders o
                             JOIN {$wpdb->prefix}wcm_products p ON JSON_CONTAINS(o.order_data, CONCAT('\"', p.product_id, '\"'), '$.line_items[*].product_id')
                             WHERE o.date_created BETWEEN %s AND %s";
        
        $top_products_args = array($date_from, $date_to);
        
        // Filter by site
        if (!empty($site_id)) {
            $top_products_query .= " AND o.site_id = %d";
            $top_products_args[] = $site_id;
        }
        
        // Filter by status (only completed and processing)
        $top_products_query .= " AND o.status IN ('completed', 'processing')";
        
        // Group by product
        $top_products_query .= " GROUP BY p.product_id";
        
        // Order by sales
        $top_products_query .= " ORDER BY total_sales DESC";
        
        // Limit to top 5
        $top_products_query .= " LIMIT 5";
        
        // Prepare the query
        $top_products_prepared_query = $wpdb->prepare($top_products_query, $top_products_args);
        
        // Get results
        $top_products = $wpdb->get_results($top_products_prepared_query);
        
        // Return data
        return array(
            'dates' => $dates,
            'sales' => array_values($sales),
            'orders' => array_values($orders),
            'summary' => array(
                'total_sales' => $total_sales,
                'total_orders' => $total_orders,
                'avg_order_value' => $avg_order_value,
                'sales_growth' => $sales_growth,
                'orders_growth' => $orders_growth,
                'aov_growth' => $aov_growth,
            ),
            'top_products' => $top_products,
        );
    }
    
    /**
     * Display sales report
     *
     * @param array $args
     */
    public function display($args = array()) {
        $defaults = array(
            'period' => '30days',
            'site_id' => 0,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Get initial data
        $data = $this->get_data($args['period'], $args['site_id']);
        
        // Get sites for filter
        $sites = get_posts(array(
            'post_type' => 'wcm_site',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));
        
        // Display report
        ?>
        <div class="wcm-reports-container">
            <div class="wcm-reports-filters mb-4">
                <form id="wcm-reports-filter-form" class="row g-3">
                    <div class="col-md-4">
                        <label for="filter-period" class="form-label"><?php _e('Thời gian', 'wcm-reports'); ?></label>
                        <select id="filter-period" name="period" class="form-select">
                            <option value="7days" <?php selected($args['period'], '7days'); ?>><?php _e('7 ngày qua', 'wcm-reports'); ?></option>
                            <option value="30days" <?php selected($args['period'], '30days'); ?>><?php _e('30 ngày qua', 'wcm-reports'); ?></option>
                            <option value="90days" <?php selected($args['period'], '90days'); ?>><?php _e('90 ngày qua', 'wcm-reports'); ?></option>
                            <option value="year" <?php selected($args['period'], 'year'); ?>><?php _e('Năm nay', 'wcm-reports'); ?></option>
                            <option value="custom" <?php selected($args['period'], 'custom'); ?>><?php _e('Tùy chỉnh', 'wcm-reports'); ?></option>
                        </select>
                    </div>
                    
                    <div class="col-md-4 custom-date-range" style="display: <?php echo $args['period'] === 'custom' ? 'block' : 'none'; ?>;">
                        <label for="filter-date-range" class="form-label"><?php _e('Khoảng thời gian', 'wcm-reports'); ?></label>
                        <input type="text" id="filter-date-range" name="date_range" class="form-control wcm-daterange" placeholder="<?php _e('Chọn khoảng thời gian', 'wcm-reports'); ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="filter-site" class="form-label"><?php _e('Website', 'wcm-reports'); ?></label>
                        <select id="filter-site" name="site_id" class="form-select">
                            <option value=""><?php _e('Tất cả website', 'wcm-reports'); ?></option>
                            <?php foreach ($sites as $site) : ?>
                                <option value="<?php echo esc_attr($site->ID); ?>" <?php selected($args['site_id'], $site->ID); ?>>
                                    <?php echo esc_html($site->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary"><?php _e('Áp dụng', 'wcm-reports'); ?></button>
                        <a href="#" class="btn btn-outline-secondary ms-2 wcm-export-report" data-type="sales">
                            <i class="fas fa-download"></i> <?php _e('Xuất báo cáo', 'wcm-reports'); ?>
                        </a>
                    </div>
                </form>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php _e('Tổng doanh thu', 'wcm-reports'); ?></h5>
                            <h2 class="card-text"><?php echo wc_price($data['summary']['total_sales']); ?></h2>
                            <p class="card-text text-<?php echo $data['summary']['sales_growth'] >= 0 ? 'success' : 'danger'; ?>">
                                <i class="fas fa-<?php echo $data['summary']['sales_growth'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                                <?php echo number_format(abs($data['summary']['sales_growth']), 2); ?>%
                                <span class="text-muted"><?php _e('so với kỳ trước', 'wcm-reports'); ?></span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php _e('Tổng đơn hàng', 'wcm-reports'); ?></h5>
                            <h2 class="card-text"><?php echo number_format($data['summary']['total_orders']); ?></h2>
                            <p class="card-text text-<?php echo $data['summary']['orders_growth'] >= 0 ? 'success' : 'danger'; ?>">
                                <i class="fas fa-<?php echo $data['summary']['orders_growth'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                                <?php echo number_format(abs($data['summary']['orders_growth']), 2); ?>%
                                <span class="text-muted"><?php _e('so với kỳ trước', 'wcm-reports'); ?></span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php _e('Giá trị đơn hàng trung bình', 'wcm-reports'); ?></h5>
                            <h2 class="card-text"><?php echo wc_price($data['summary']['avg_order_value']); ?></h2>
                            <p class="card-text text-<?php echo $data['summary']['aov_growth'] >= 0 ? 'success' : 'danger'; ?>">
                                <i class="fas fa-<?php echo $data['summary']['aov_growth'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                                <?php echo number_format(abs($data['summary']['aov_growth']), 2); ?>%
                                <span class="text-muted"><?php _e('so với kỳ trước', 'wcm-reports'); ?></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><?php _e('Doanh thu theo thời gian', 'wcm-reports'); ?></h5>
                        </div>
                        <div class="card-body">
                            <canvas id="sales-chart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><?php _e('Top 5 sản phẩm bán chạy', 'wcm-reports'); ?></h5>
                        </div>
                        <div class="card-body">
                            <canvas id="top-products-chart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Initialize charts
            const salesCtx = document.getElementById('sales-chart').getContext('2d');
            const salesChart = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($data['dates']); ?>,
                    datasets: [{
                        label: '<?php _e('Doanh thu', 'wcm-reports'); ?>',
                        data: <?php echo json_encode($data['sales']); ?>,
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        pointRadius: 3,
                        pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointBorderColor: 'rgba(78, 115, 223, 1)',
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'y'
                    }, {
                        label: '<?php _e('Đơn hàng', 'wcm-reports'); ?>',
                        data: <?php echo json_encode($data['orders']); ?>,
                        backgroundColor: 'rgba(28, 200, 138, 0.05)',
                        borderColor: 'rgba(28, 200, 138, 1)',
                        pointRadius: 3,
                        pointBackgroundColor: 'rgba(28, 200, 138, 1)',
                        pointBorderColor: 'rgba(28, 200, 138, 1)',
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: 'rgba(28, 200, 138, 1)',
                        pointHoverBorderColor: 'rgba(28, 200, 138, 1)',
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: '<?php _e('Doanh thu', 'wcm-reports'); ?>'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '₫' + value.toLocaleString();
                                }
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: '<?php _e('Đơn hàng', 'wcm-reports'); ?>'
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.datasetIndex === 0) {
                                        label += '₫' + context.parsed.y.toLocaleString();
                                    } else {
                                        label += context.parsed.y;
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
            
            // Top products chart
            const topProductsCtx = document.getElementById('top-products-chart').getContext('2d');
            const topProductsChart = new Chart(topProductsCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_map(function($product) { return $product->name; }, $data['top_products'])); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_map(function($product) { return $product->total_sales; }, $data['top_products'])); ?>,
                        backgroundColor: [
                            '#4e73df',
                            '#1cc88a',
                            '#36b9cc',
                            '#f6c23e',
                            '#e74a3b'
                        ],
                        hoverBackgroundColor: [
                            '#2e59d9',
                            '#17a673',
                            '#2c9faf',
                            '#dda20a',
                            '#be2617'
                        ],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += '₫' + context.parsed.toLocaleString();
                                    return label;
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
            
            // Handle period change
            $('#filter-period').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('.custom-date-range').show();
                } else {
                    $('.custom-date-range').hide();
                }
            });
            
            // Handle form submission
            $('#wcm-reports-filter-form').on('submit', function(e) {
                e.preventDefault();
                
                const period = $('#filter-period').val();
                const site_id = $('#filter-site').val();
                let date_from = '';
                let date_to = '';
                
                if (period === 'custom') {
                    const date_range = $('#filter-date-range').val().split(' - ');
                    if (date_range.length === 2) {
                        date_from = date_range[0];
                        date_to = date_range[1];
                    }
                }
                
                // Show loading
                $('.wcm-reports-container').addClass('loading');
                
                // Get report data
                $.ajax({
                    url: wcm_reports.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wcm_get_report_data',
                        nonce: wcm_reports.nonce,
                        type: 'sales',
                        period: period,
                        site_id: site_id,
                        date_from: date_from,
                        date_to: date_to
                    },
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            
                            // Update summary cards
                            $('.card-text:contains("Tổng doanh thu")').next().html('₫' + data.summary.total_sales.toLocaleString());
                            $('.card-text:contains("Tổng đơn hàng")').next().html(data.summary.total_orders.toLocaleString());
                            $('.card-text:contains("Giá trị đơn hàng trung bình")').next().html('₫' + data.summary.avg_order_value.toLocaleString());
                            
                            // Update growth indicators
                            const salesGrowthEl = $('.card-text:contains("Tổng doanh thu")').next().next();
                            salesGrowthEl.removeClass('text-success text-danger').addClass(data.summary.sales_growth >= 0 ? 'text-success' : 'text-danger');
                            salesGrowthEl.find('i').removeClass('fa-arrow-up fa-arrow-down').addClass(data.summary.sales_growth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down');
                            salesGrowthEl.html(salesGrowthEl.html().replace(/[\d.]+%/, Math.abs(data.summary.sales_growth).toFixed(2) + '%'));
                            
                            const ordersGrowthEl = $('.card-text:contains("Tổng đơn hàng")').next().next();
                            ordersGrowthEl.removeClass('text-success text-danger').addClass(data.summary.orders_growth >= 0 ? 'text-success' : 'text-danger');
                            ordersGrowthEl.find('i').removeClass('fa-arrow-up fa-arrow-down').addClass(data.summary.orders_growth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down');
                            ordersGrowthEl.html(ordersGrowthEl.html().replace(/[\d.]+%/, Math.abs(data.summary.orders_growth).toFixed(2) + '%'));
                            
                            const aovGrowthEl = $('.card-text:contains("Giá trị đơn hàng trung bình")').next().next();
                            aovGrowthEl.removeClass('text-success text-danger').addClass(data.summary.aov_growth >= 0 ? 'text-success' : 'text-danger');
                            aovGrowthEl.find('i').removeClass('fa-arrow-up fa-arrow-down').addClass(data.summary.aov_growth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down');
                            aovGrowthEl.html(aovGrowthEl.html().replace(/[\d.]+%/, Math.abs(data.summary.aov_growth).toFixed(2) + '%'));
                            
                            // Update charts
                            salesChart.data.labels = data.dates;
                            salesChart.data.datasets[0].data = data.sales;
                            salesChart.data.datasets[1].data = data.orders;
                            salesChart.update();
                            
                            // Update top products chart
                            const productLabels = data.top_products.map(product => product.name);
                            const productData = data.top_products.map(product => product.total_sales);
                            
                            topProductsChart.data.labels = productLabels;
                            topProductsChart.data.datasets[0].data = productData;
                            topProductsChart.update();
                        } else {
                            alert(wcm_reports.i18n.error);
                        }
                        
                        // Hide loading
                        $('.wcm-reports-container').removeClass('loading');
                    },
                    error: function() {
                        alert(wcm_reports.i18n.error);
                        $('.wcm-reports-container').removeClass('loading');
                    }
                });
            });
            
            // Handle export
            $('.wcm-export-report').on('click', function(e) {
                e.preventDefault();
                
                const type = $(this).data('type');
                const period = $('#filter-period').val();
                const site_id = $('#filter-site').val();
                let date_from = '';
                let date_to = '';
                
                if (period === 'custom') {
                    const date_range = $('#filter-date-range').val().split(' - ');
                    if (date_range.length === 2) {
                        date_from = date_range[0];
                        date_to = date_range[1];
                    }
                }
                
                // Redirect to export URL
                window.location.href = wcm_reports.ajax_url + '?action=wcm_export_report&nonce=' + wcm_reports.nonce + '&type=' + type + '&period=' + period + '&site_id=' + site_id + '&date_from=' + date_from + '&date_to=' + date_to;
            });
        });
        </script>
        <?php
    }
}
