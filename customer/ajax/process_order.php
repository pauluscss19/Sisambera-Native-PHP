<?php
session_start();
require_once '../../config/config.php';  // ← PATH YANG BENAR

// Debug mode
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validasi session
if (!isset($_SESSION['layanan_id']) || !isset($_SESSION['nama']) || !isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die("Error: Data tidak lengkap. <a href='../pilih-layanan.php'>Kembali</a>");
}

// Validasi POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['metode_pembayaran'])) {
    die("Error: Method tidak valid. <a href='../metode-pembayaran.php'>Kembali</a>");
}

$conn = getConnection();

// Get data from session and POST
$layanan_id = intval($_SESSION['layanan_id']);
$nama = $conn->real_escape_string($_SESSION['nama']);
$no_hp = $conn->real_escape_string($_SESSION['no_hp']);
$catatan = isset($_SESSION['catatan']) ? $conn->real_escape_string($_SESSION['catatan']) : '';
$metode = $conn->real_escape_string($_POST['metode_pembayaran']);
$bank = isset($_POST['bank']) && $_POST['bank'] ? $conn->real_escape_string($_POST['bank']) : null;
$cart = $_SESSION['cart'];

// ========== VALIDASI STOK ==========
$error_stok = [];
foreach($cart as $item) {
    $menu_id = intval($item['menu_id']);
    $qty_order = intval($item['qty']);
    
    $check_stok = "SELECT stok, nama_menu FROM menu WHERE menu_id = $menu_id";
    $result_stok = $conn->query($check_stok);
    
    if ($result_stok && $result_stok->num_rows > 0) {
        $menu_data = $result_stok->fetch_assoc();
        
        if ($menu_data['stok'] < $qty_order) {
            $error_stok[] = $menu_data['nama_menu'] . " (Stok: " . $menu_data['stok'] . ", Pesan: " . $qty_order . ")";
        }
    }
}

// Jika stok tidak cukup
if (count($error_stok) > 0) {
    $_SESSION['error_message'] = "❌ Stok tidak mencukupi: " . implode(", ", $error_stok);
    header('Location: ../pilih-menu.php');
    exit();
}

// Calculate totals
$subtotal = 0;
foreach($cart as $item) {
    $subtotal += floatval($item['harga']) * intval($item['qty']);
}
$pajak = $subtotal * 0.1;
$total = $subtotal + $pajak;

// Generate nomor antrian
$today = date('Y-m-d');
$query = "SELECT COUNT(*) as total FROM pesanan WHERE DATE(tanggal) = '$today'";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$number = $row['total'] + 1;
$nomor_antrian = 'A' . str_pad($number, 3, '0', STR_PAD_LEFT);

// Check/Create user
$check_user = "SELECT user_id FROM user WHERE no_hp = '$no_hp'";
$result = $conn->query($check_user);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_id = intval($user['user_id']);
    $conn->query("UPDATE user SET nama = '$nama' WHERE user_id = $user_id");
} else {
    $insert_user = "INSERT INTO user (nama, no_hp) VALUES ('$nama', '$no_hp')";
    if ($conn->query($insert_user)) {
        $user_id = $conn->insert_id;
    } else {
        die("Error creating user: " . $conn->error);
    }
}

// Start transaction
$conn->begin_transaction();

try {
    // Insert pesanan
    $tanggal = date('Y-m-d H:i:s');
    $insert_pesanan = "INSERT INTO pesanan (user_id, layanan_id, tanggal, status, total_harga, nomor_antrian) 
                       VALUES ($user_id, $layanan_id, '$tanggal', 'pending', $total, '$nomor_antrian')";
    
    if (!$conn->query($insert_pesanan)) {
        throw new Exception("Error insert pesanan: " . $conn->error);
    }
    
    $pesanan_id = $conn->insert_id;
    
    // Insert detail pesanan DAN kurangi stok
    foreach($cart as $item) {
        $menu_id = intval($item['menu_id']);
        $qty = intval($item['qty']);
        $harga = floatval($item['harga']);
        $item_subtotal = $harga * $qty;
        
        // Insert detail pesanan
        $insert_detail = "INSERT INTO detail_pesanan (pesanan_id, menu_id, jumlah, harga_satuan, subtotal) 
                          VALUES ($pesanan_id, $menu_id, $qty, $harga, $item_subtotal)";
        
        if (!$conn->query($insert_detail)) {
            throw new Exception("Error insert detail: " . $conn->error);
        }
        
        // ========== KURANGI STOK ==========
        $update_stok = "UPDATE menu SET stok = stok - $qty WHERE menu_id = $menu_id";
        if (!$conn->query($update_stok)) {
            throw new Exception("Error update stok menu_id $menu_id: " . $conn->error);
        }
        
        // Check if stok <= 0, update status to 'habis'
        $check_stok = "SELECT stok FROM menu WHERE menu_id = $menu_id";
        $result_check = $conn->query($check_stok);
        $stok_data = $result_check->fetch_assoc();
        
        if ($stok_data['stok'] <= 0) {
            $update_status = "UPDATE menu SET status = 'habis', stok = 0 WHERE menu_id = $menu_id";
            $conn->query($update_status);
        }
    }
    
    // Insert pembayaran (WITH BANK SUPPORT)
    $bank_value = $bank ? "'$bank'" : 'NULL';
    $insert_pembayaran = "INSERT INTO pembayaran (pesanan_id, metode, bank, total, status) 
                          VALUES ($pesanan_id, '$metode', $bank_value, $total, 'pending')";
    
    if (!$conn->query($insert_pembayaran)) {
        throw new Exception("Error insert pembayaran: " . $conn->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    // Save to session
    $_SESSION['pesanan_id'] = $pesanan_id;
    $_SESSION['nomor_antrian'] = $nomor_antrian;
    $_SESSION['metode_pembayaran'] = $metode;
    $_SESSION['bank'] = $bank;
    $_SESSION['total_pembayaran'] = $total;
    
    // Clear cart
    unset($_SESSION['cart']);
    
    // Redirect based on payment method
    if ($metode == 'cash') {
        header('Location: ../status-pesanan.php');
    } else {
        header('Location: ../konfirmasi.php');
    }
    exit();
    
} catch (Exception $e) {
    // Rollback
    $conn->rollback();
    $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
    header('Location: ../pilih-menu.php');
    exit();
}

$conn->close();
?>
