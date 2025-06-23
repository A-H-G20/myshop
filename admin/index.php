<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include 'config.php'; // Your DB connection

// Total users
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM users"))['count'];

// Total products
$totalProducts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM products"))['count'];

// Total orders
$totalOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM orders"))['count'];

// Total revenue
$totalRevenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) AS total FROM orders"))['total'] ?? 0;

// Admin users
$adminUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM users WHERE role = 'admin'"))['count'];

// Regular users
$regularUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM users WHERE role = 'customer'"))['count'];

// Pending orders
$pendingOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM orders WHERE status = 'processing'"))['count'];

// Completed orders
$completedOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM orders WHERE status = 'completed'"))['count'];

// Low stock products (threshold: 10)
$lowStockProducts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM products WHERE stock_quantity < 10"))['count'];

// Total categories
$totalCategories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM categories"))['count'];

// Function to get chart data for different time periods
function getChartData($conn, $period) {
    $chartData = [];
    
    switch($period) {
        case '7days':
            // Get data for last 7 days
            $query = "SELECT 
                        DATE(created_at) as date,
                        DAYNAME(created_at) as day_name,
                        COUNT(*) as order_count,
                        COALESCE(SUM(total_amount), 0) as revenue
                      FROM orders 
                      WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                      GROUP BY DATE(created_at), DAYNAME(created_at)
                      ORDER BY DATE(created_at) ASC";
            break;
            
        case '30days':
            // Get data for last 30 days (grouped by week)
            $query = "SELECT 
                        CONCAT('Week ', WEEK(created_at) - WEEK(CURDATE()) + 5) as date,
                        CONCAT('Week ', WEEK(created_at) - WEEK(CURDATE()) + 5) as day_name,
                        COUNT(*) as order_count,
                        COALESCE(SUM(total_amount), 0) as revenue
                      FROM orders 
                      WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                      GROUP BY WEEK(created_at)
                      ORDER BY WEEK(created_at) ASC";
            break;
            
        case '90days':
            // Get data for last 90 days (grouped by month)
            $query = "SELECT 
                        MONTHNAME(created_at) as date,
                        MONTHNAME(created_at) as day_name,
                        COUNT(*) as order_count,
                        COALESCE(SUM(total_amount), 0) as revenue
                      FROM orders 
                      WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                      GROUP BY MONTH(created_at), YEAR(created_at)
                      ORDER BY YEAR(created_at), MONTH(created_at) ASC";
            break;
            
        case '1year':
            // Get data for last 12 months
            $query = "SELECT 
                        CONCAT(MONTHNAME(created_at), ' ', YEAR(created_at)) as date,
                        CONCAT(MONTHNAME(created_at), ' ', YEAR(created_at)) as day_name,
                        COUNT(*) as order_count,
                        COALESCE(SUM(total_amount), 0) as revenue
                      FROM orders 
                      WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
                      GROUP BY MONTH(created_at), YEAR(created_at)
                      ORDER BY YEAR(created_at), MONTH(created_at) ASC";
            break;
    }
    
    $result = mysqli_query($conn, $query);
    $labels = [];
    $orderData = [];
    $revenueData = [];
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = $row['day_name'];
            $orderData[] = (int)$row['order_count'];
            $revenueData[] = (float)$row['revenue'];
        }
    } else {
        // If no data, provide empty arrays with default labels
        switch($period) {
            case '7days':
                $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                break;
            case '30days':
                $labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
                break;
            case '90days':
                $labels = ['Month 1', 'Month 2', 'Month 3'];
                break;
            case '1year':
                $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                break;
        }
        $orderData = array_fill(0, count($labels), 0);
        $revenueData = array_fill(0, count($labels), 0);
    }
    
    return [
        'labels' => $labels,
        'orderData' => $orderData,
        'revenueData' => $revenueData
    ];
}

// Get chart data for all periods
$chartData7days = getChartData($conn, '7days');
$chartData30days = getChartData($conn, '30days');
$chartData90days = getChartData($conn, '90days');
$chartData1year = getChartData($conn, '1year');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saadi Shop Admin Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <!-- Header -->
<?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="container">
        <div class="main-content">
            <div class="page-header fade-in">
                <h1 class="page-title">Dashboard Overview</h1>
                <p class="page-subtitle">Welcome back! Here's what's happening with your store today.</p>
            </div>

            <div class="stats-grid fade-in">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon users">👥</div>
                        
                    </div>
                    <div class="stat-number" id="totalUsers">0</div>
                    <div class="stat-label">Total Users</div>
                  
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon products">📦</div>
                       
                    </div>
                    <div class="stat-number" id="totalProducts">0</div>
                    <div class="stat-label">Total Products</div>
                 
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon orders">🛒</div>
                       
                    </div>
                    <div class="stat-number" id="totalOrders">0</div>
                    <div class="stat-label">Total Orders</div>
                    
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon revenue">💰</div>
                      
                    </div>
                    <div class="stat-number" id="totalRevenue">$0</div>
                    <div class="stat-label">Total Revenue</div>
                    
                </div>
            </div>

            <div class="chart-section fade-in">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3 class="chart-title">Sales Overview</h3>
                        <div class="chart-filters">
                            <button class="filter-btn active" onclick="changeTimeFilter('7days')">7 Days</button>
                            <button class="filter-btn" onclick="changeTimeFilter('30days')">30 Days</button>
                            <button class="filter-btn" onclick="changeTimeFilter('90days')">90 Days</button>
                            <button class="filter-btn" onclick="changeTimeFilter('1year')">1 Year</button>
                        </div>
                    </div>
                    <div class="chart-canvas">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <div class="quick-stats">
                    <h3>Quick Stats</h3>
                    <div class="quick-stat-item"><span class="quick-stat-label">Admin Users</span><span class="quick-stat-value" id="adminUsers">0</span></div>
                    <div class="quick-stat-item"><span class="quick-stat-label">Regular Users</span><span class="quick-stat-value" id="regularUsers">0</span></div>
                    <div class="quick-stat-item"><span class="quick-stat-label">Pending Orders</span><span class="quick-stat-value" id="pendingOrders">0</span></div>
                    <div class="quick-stat-item"><span class="quick-stat-label">Completed Orders</span><span class="quick-stat-value" id="completedOrders">0</span></div>
                    <div class="quick-stat-item"><span class="quick-stat-label">Low Stock Products</span><span class="quick-stat-value" id="lowStockProducts">0</span></div>
                    <div class="quick-stat-item"><span class="quick-stat-label">Categories</span><span class="quick-stat-value" id="totalCategories">0</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inject PHP data into JS -->
    <script>
        const dashboardData = {
            totalUsers: <?= $totalUsers ?>,
            totalProducts: <?= $totalProducts ?>,
            totalOrders: <?= $totalOrders ?>,
            totalRevenue: <?= $totalRevenue ?>,
            adminUsers: <?= $adminUsers ?>,
            regularUsers: <?= $regularUsers ?>,
            pendingOrders: <?= $pendingOrders ?>,
            completedOrders: <?= $completedOrders ?>,
            lowStockProducts: <?= $lowStockProducts ?>,
            totalCategories: <?= $totalCategories ?>
        };

        // Chart data from database
        const chartData = {
            '7days': {
                labels: <?= json_encode($chartData7days['labels']) ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?= json_encode($chartData7days['orderData']) ?>,
                    borderColor: '#8bc34a',
                    backgroundColor: 'rgba(139, 195, 74, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Revenue ($)',
                    data: <?= json_encode($chartData7days['revenueData']) ?>,
                    borderColor: '#2196f3',
                    backgroundColor: 'rgba(33, 150, 243, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            '30days': {
                labels: <?= json_encode($chartData30days['labels']) ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?= json_encode($chartData30days['orderData']) ?>,
                    borderColor: '#8bc34a',
                    backgroundColor: 'rgba(139, 195, 74, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Revenue ($)',
                    data: <?= json_encode($chartData30days['revenueData']) ?>,
                    borderColor: '#2196f3',
                    backgroundColor: 'rgba(33, 150, 243, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            '90days': {
                labels: <?= json_encode($chartData90days['labels']) ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?= json_encode($chartData90days['orderData']) ?>,
                    borderColor: '#8bc34a',
                    backgroundColor: 'rgba(139, 195, 74, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Revenue ($)',
                    data: <?= json_encode($chartData90days['revenueData']) ?>,
                    borderColor: '#2196f3',
                    backgroundColor: 'rgba(33, 150, 243, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            '1year': {
                labels: <?= json_encode($chartData1year['labels']) ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?= json_encode($chartData1year['orderData']) ?>,
                    borderColor: '#8bc34a',
                    backgroundColor: 'rgba(139, 195, 74, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Revenue ($)',
                    data: <?= json_encode($chartData1year['revenueData']) ?>,
                    borderColor: '#2196f3',
                    backgroundColor: 'rgba(33, 150, 243, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            }
        };
    </script>

    <!-- JS to Render Dashboard -->
    <script>
        let currentChart = null;
        let currentTimeFilter = '7days';

        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            initializeChart();
        });

        function loadDashboardData() {
            animateNumber('totalUsers', dashboardData.totalUsers);
            animateNumber('totalProducts', dashboardData.totalProducts);
            animateNumber('totalOrders', dashboardData.totalOrders);
            animateNumber('totalRevenue', dashboardData.totalRevenue, '$');
            document.getElementById('adminUsers').textContent = dashboardData.adminUsers;
            document.getElementById('regularUsers').textContent = dashboardData.regularUsers;
            document.getElementById('pendingOrders').textContent = dashboardData.pendingOrders;
            document.getElementById('completedOrders').textContent = dashboardData.completedOrders;
            document.getElementById('lowStockProducts').textContent = dashboardData.lowStockProducts;
            document.getElementById('totalCategories').textContent = dashboardData.totalCategories;
        }

        function animateNumber(id, target, prefix = '') {
            const el = document.getElementById(id);
            const increment = target / (2000 / 16);
            let current = 0;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                el.textContent = prefix + Math.floor(current).toLocaleString();
            }, 16);
        }

        function initializeChart() {
            const ctx = document.getElementById('salesChart').getContext('2d');
            currentChart = new Chart(ctx, {
                type: 'line',
                data: chartData[currentTimeFilter],
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        x: { grid: { display: false }},
                        y: { grid: { color: 'rgba(0,0,0,0.1)' }}
                    }
                }
            });
        }

        function changeTimeFilter(period) {
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            currentTimeFilter = period;
            if (currentChart) {
                currentChart.data = chartData[period];
                currentChart.update();
            }
        }
    </script>
</body>
</html>