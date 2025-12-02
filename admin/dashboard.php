<?php
require_once '../config/config.php';
requireLogin();

$page_title = 'Dashboard';
$page_subtitle = 'Welcome back, ' . getAdminName();

$conn = getConnection();

// Get statistics
$today = date('Y-m-d');

// Total orders today
$query = "SELECT COUNT(*) as total FROM pesanan WHERE DATE(tanggal) = '$today'";
$result = $conn->query($query);
$orderToday = $result->fetch_assoc()['total'];

// Total revenue today
$query = "SELECT SUM(total_harga) as total FROM pesanan WHERE DATE(tanggal) = '$today' AND status != 'dibatalkan'";
$result = $conn->query($query);
$revenueToday = $result->fetch_assoc()['total'] ?? 0;

// Yesterday's revenue for comparison
$yesterday = date('Y-m-d', strtotime('-1 day'));
$query = "SELECT SUM(total_harga) as total FROM pesanan WHERE DATE(tanggal) = '$yesterday' AND status != 'dibatalkan'";
$result = $conn->query($query);
$revenueYesterday = $result->fetch_assoc()['total'] ?? 1;

// Calculate percentage change
$revenueChange = (($revenueToday - $revenueYesterday) / $revenueYesterday) * 100;

// Low stock count
$query = "SELECT COUNT(*) as total FROM bahan WHERE status IN ('low-stock', 'very-low')";
$result = $conn->query($query);
$lowStock = $result->fetch_assoc()['total'];

// Weekly revenue
$weekAgo = date('Y-m-d', strtotime('-7 days'));
$query = "SELECT SUM(total_harga) as total FROM pesanan WHERE DATE(tanggal) >= '$weekAgo' AND status != 'dibatalkan'";
$result = $conn->query($query);
$weeklyRevenue = $result->fetch_assoc()['total'] ?? 0;

$conn->close();

include 'includes/header.php';
?>

<!-- Dashboard Cards -->
<div class="dashboard-cards">
    <div class="card">
        <div class="card-label">Orders Today</div>
        <div class="card-value"><?php echo $orderToday; ?></div>
        <div class="card-change">pesanan hari ini</div>
    </div>
    
    <div class="card">
        <div class="card-label">Revenue Today</div>
        <div class="card-value"><?php echo formatRupiah($revenueToday); ?></div>
        <div class="card-change <?php echo $revenueChange >= 0 ? 'positive' : 'negative'; ?>">
            <?php echo $revenueChange >= 0 ? '↑' : '↓'; ?> 
            <?php echo number_format(abs($revenueChange), 1); ?>% dari kemarin
        </div>
    </div>
    
    <div class="card">
        <div class="card-label">Low Stock Items</div>
        <div class="card-value"><?php echo $lowStock; ?></div>
        <div class="card-change">item perlu restok</div>
    </div>
</div>

<!-- Chart Section -->
<div class="chart-section">
    <div class="chart-header">
        <div>
            <div class="chart-title">Weekly Revenue</div>
            <div class="chart-value">
                <?php echo formatRupiah($weeklyRevenue); ?>
                <span class="chart-change">↑ 12.5%</span>
            </div>
            <div class="chart-subtitle">7 hari terakhir</div>
        </div>
    </div>
    <div class="chart-placeholder">
        <p style="text-align: center; padding-top: 130px; color: #999; font-size: 16px;">
            Chart visualization akan ditampilkan di sini<br>
            <small>Dapat menggunakan Chart.js atau library lainnya untuk visualisasi data</small>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
