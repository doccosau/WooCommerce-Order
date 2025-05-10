<?php
/**
 * Template Name: Dashboard
 */

get_header();

// Get connected sites
$connected_sites = wcm_get_connected_sites();
$total_orders = wcm_get_total_orders();
$total_revenue = wcm_get_total_revenue();
$total_customers = wcm_get_total_customers();
$recent_orders = wcm_get_recent_orders(5);
$low_stock_products = wcm_get_low_stock_products(5);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Bảng điều khiển</h1>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng đơn hàng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_orders); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Doanh thu</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo wc_price($total_revenue); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Khách hàng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_customers); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Sites kết nối</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($connected_sites); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-store fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Doanh thu theo thời gian</h6>
                    <div class="dropdown no-arrow">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="revenueDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="revenueDropdown">
                            <li><a class="dropdown-item" href="#" data-period="7">7 ngày</a></li>
                            <li><a class="dropdown-item" href="#" data-period="30">30 ngày</a></li>
                            <li><a class="dropdown-item" href="#" data-period="90">90 ngày</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Đơn hàng theo trạng thái</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Orders & Low Stock Products -->
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Đơn hàng gần đây</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Khách hàng</th>
                                    <th>Trạng thái</th>
                                    <th>Tổng</th>
                                    <th>Ngày</th>
                                    <th>Site</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order) : ?>
                                <tr>
                                    <td><a href="<?php echo esc_url(home_url('/order/' . $order->id)); ?>">#<?php echo $order->id; ?></a></td>
                                    <td><?php echo esc_html($order->customer_name); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo wcm_get_order_status_color($order->status); ?>">
                                            <?php echo esc_html(wcm_get_order_status_label($order->status)); ?>
                                        </span>
                                    </td>
                                    <td><?php echo wc_price($order->total); ?></td>
                                    <td><?php echo date_i18n(get_option('date_format'), strtotime($order->date_created)); ?></td>
                                    <td><?php echo esc_html($order->site_name); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?php echo esc_url(home_url('/orders/')); ?>" class="btn btn-primary btn-sm">Xem tất cả đơn hàng</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sản phẩm sắp hết hàng</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Tồn kho</th>
                                    <th>Site</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($low_stock_products as $product) : ?>
                                <tr>
                                    <td><a href="<?php echo esc_url(home_url('/product/' . $product->id)); ?>"><?php echo esc_html($product->name); ?></a></td>
                                    <td>
                                        <span class="badge bg-<?php echo wcm_get_stock_status_color($product->stock_quantity); ?>">
                                            <?php echo $product->stock_quantity; ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($product->site_name); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?php echo esc_url(home_url('/products/?filter=low_stock')); ?>" class="btn btn-primary btn-sm">Xem tất cả</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Revenue Chart
document.addEventListener('DOMContentLoaded', function() {
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(wcm_get_revenue_chart_labels(30)); ?>,
            datasets: [{
                label: 'Doanh thu',
                data: <?php echo json_encode(wcm_get_revenue_chart_data(30)); ?>,
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
                fill: true
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₫' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Order Status Chart
    const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(wcm_get_order_status_labels()); ?>,
            datasets: [{
                data: <?php echo json_encode(wcm_get_order_status_counts()); ?>,
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
                }
            },
            cutout: '70%'
        }
    });
    
    // Period selector for revenue chart
    document.querySelectorAll('[data-period]').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            const period = this.getAttribute('data-period');
            
            // AJAX call to get new data
            fetch(wcm_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'wcm_get_revenue_data',
                    nonce: wcm_ajax.nonce,
                    period: period
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update chart data
                    revenueChart.data.labels = data.labels;
                    revenueChart.data.datasets[0].data = data.values;
                    revenueChart.update();
                }
            });
        });
    });
});
</script>

<?php
get_footer();
