<?php
// admin/config/config.php - Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sambal_belut_buraden');

// Create connection
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Redirect to login if not logged in
function requireLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Format currency
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Generate nomor antrian
function generateNomorAntrian($conn) {
    $today = date('Y-m-d');
    $query = "SELECT COUNT(*) as total FROM pesanan WHERE DATE(tanggal) = '$today'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $number = $row['total'] + 1;
    
    $prefix = 'A';
    return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
}

// Update status bahan berdasarkan jumlah
function updateStatusBahan($conn, $bahan_id) {
    $query = "SELECT jumlah, minimum_stok FROM bahan WHERE bahan_id = $bahan_id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    $jumlah = $row['jumlah'];
    $minimum = $row['minimum_stok'];
    
    if ($jumlah <= ($minimum * 0.5)) {
        $status = 'very-low';
    } elseif ($jumlah <= $minimum) {
        $status = 'low-stock';
    } else {
        $status = 'safe';
    }
    
    $update = "UPDATE bahan SET status = '$status' WHERE bahan_id = $bahan_id";
    $conn->query($update);
}

// Get admin name
function getAdminName() {
    if (isset($_SESSION['admin_nama'])) {
        return $_SESSION['admin_nama'];
    }
    return 'Admin';
}

// Get admin email (username@domain)
function getAdminEmail() {
    if (isset($_SESSION['admin_username'])) {
        return $_SESSION['admin_username'] . '@sambalbelut.com';
    }
    return 'admin@sambalbelut.com';
}

// Get layanan name by ID
function getLayananName($conn, $layanan_id) {
    $query = "SELECT jenis_layanan FROM layanan WHERE layanan_id = $layanan_id";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['jenis_layanan'];
    }
    return '-';
}

// Get status badge color HTML
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span style="background: #ffc107; color: #000; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">Pending</span>',
        'dikonfirmasi' => '<span style="background: #2196F3; color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">Dikonfirmasi</span>',
        'diproses' => '<span style="background: #ff9800; color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">Diproses</span>',
        'selesai' => '<span style="background: #4caf50; color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">Selesai</span>',
        'dibatalkan' => '<span style="background: #f44336; color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">Dibatalkan</span>'
    ];
    return $badges[$status] ?? ucfirst($status);
}

// Get payment status badge
function getPaymentBadge($status) {
    $badges = [
        'pending' => '<span style="color: #ff9800; font-weight: 600;">⏳ Pending</span>',
        'berhasil' => '<span style="color: #4caf50; font-weight: 600;">✅ Berhasil</span>',
        'gagal' => '<span style="color: #f44336; font-weight: 600;">❌ Gagal</span>'
    ];
    return $badges[$status] ?? ucfirst($status);
}
?>