<?php
require_once '../config/config.php';
requireLogin();

$page_title = 'Dashboard';
$page_subtitle = 'Welcome back, ' . getAdminName();

$conn = getConnection();

// =========
// TANGGAL
// =========
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

$start7 = date('Y-m-d', strtotime('-6 days'));      // 7 hari termasuk hari ini
$prevStart7 = date('Y-m-d', strtotime('-13 days')); // 7 hari sebelumnya
$prevEnd7   = date('Y-m-d', strtotime('-7 days'));

// ===================
// ORDERS HARI INI
// ===================
$query = "SELECT COUNT(*) AS total
          FROM pesanan
          WHERE DATE(tanggal) = '$today'";
$result = $conn->query($query);
$orderToday = (int)($result->fetch_assoc()['total'] ?? 0);

// ===================
// REVENUE HARI INI
// ===================
$query = "SELECT COALESCE(SUM(total_harga), 0) AS total
          FROM pesanan
          WHERE DATE(tanggal) = '$today'
            AND status != 'dibatalkan'";
$result = $conn->query($query);
$revenueToday = (float)($result->fetch_assoc()['total'] ?? 0);

// ===================
// REVENUE KEMARIN
// ===================
$query = "SELECT COALESCE(SUM(total_harga), 0) AS total
          FROM pesanan
          WHERE DATE(tanggal) = '$yesterday'
            AND status != 'dibatalkan'";
$result = $conn->query($query);
$revenueYesterday = (float)($result->fetch_assoc()['total'] ?? 0);

// ===================
// % PERUBAHAN HARIAN
// ===================
if ($revenueYesterday > 0) {
    $revenueChange = (($revenueToday - $revenueYesterday) / $revenueYesterday) * 100;
} else {
    // kalau kemarin 0, hari ini >0 dianggap naik 100% (atau bisa tampil "-" kalau mau)
    $revenueChange = ($revenueToday > 0) ? 100 : 0;
}
$revenueChangeClass = ($revenueChange >= 0) ? 'positive' : 'negative';
$revenueChangeArrow = ($revenueChange >= 0) ? '↑' : '↓';

// ===================
// LOW STOCK
// ===================
$query = "SELECT COUNT(*) AS total
          FROM bahan
          WHERE status IN ('low-stock', 'very-low')";
$result = $conn->query($query);
$lowStock = (int)($result->fetch_assoc()['total'] ?? 0);

// ===================
// WEEKLY REVENUE (7 HARI TERAKHIR)
// ===================
$query = "SELECT COALESCE(SUM(total_harga), 0) AS total
          FROM pesanan
          WHERE DATE(tanggal) BETWEEN '$start7' AND '$today'
            AND status != 'dibatalkan'";
$result = $conn->query($query);
$weeklyRevenue = (float)($result->fetch_assoc()['total'] ?? 0);

// 7 hari sebelumnya (untuk persen weekly)
$query = "SELECT COALESCE(SUM(total_harga), 0) AS total
          FROM pesanan
          WHERE DATE(tanggal) BETWEEN '$prevStart7' AND '$prevEnd7'
            AND status != 'dibatalkan'";
$result = $conn->query($query);
$weeklyRevenuePrev = (float)($result->fetch_assoc()['total'] ?? 0);

if ($weeklyRevenuePrev > 0) {
    $weeklyChange = (($weeklyRevenue - $weeklyRevenuePrev) / $weeklyRevenuePrev) * 100;
} else {
    $weeklyChange = ($weeklyRevenue > 0) ? 100 : 0;
}
$weeklyChangeClass = ($weeklyChange >= 0) ? 'positive' : 'negative';
$weeklyChangeArrow = ($weeklyChange >= 0) ? '↑' : '↓';

// ===================
// DATA CHART (TOTAL PER HARI, 7 HARI)
// ===================
$query = "SELECT DATE(tanggal) AS tgl, COALESCE(SUM(total_harga),0) AS total
          FROM pesanan
          WHERE DATE(tanggal) BETWEEN '$start7' AND '$today'
            AND status != 'dibatalkan'
          GROUP BY DATE(tanggal)
          ORDER BY DATE(tanggal)";
$res = $conn->query($query);

$map = [];
while ($row = $res->fetch_assoc()) {
    $map[$row['tgl']] = (float)$row['total'];
}

// Fill tanggal yang kosong jadi 0, supaya grafik rapi 7 titik
$labels = [];
$series = [];
for ($i = 0; $i < 7; $i++) {
    $d = date('Y-m-d', strtotime("$start7 +$i day"));
    $labels[] = date('d M', strtotime($d));
    $series[] = $map[$d] ?? 0;
}

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
        <div class="card-change <?php echo $revenueChangeClass; ?>">
            <?php echo $revenueChangeArrow; ?>
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
                <span class="chart-change <?php echo $weeklyChangeClass; ?>">
                    <?php echo $weeklyChangeArrow; ?> <?php echo number_format(abs($weeklyChange), 1); ?>%
                </span>
            </div>
            <div class="chart-subtitle">7 hari terakhir</div>
        </div>
    </div>

    <div class="chart-placeholder" style="padding: 12px;">
        <canvas id="weeklyRevenueChart" style="width: 100%; height: 280px;"></canvas>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = <?php echo json_encode($labels); ?>;
    const data = <?php echo json_encode($series); ?>;

    const ctx = document.getElementById('weeklyRevenueChart');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Revenue',
                data,
                tension: 0.35,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID')
                    }
                }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>