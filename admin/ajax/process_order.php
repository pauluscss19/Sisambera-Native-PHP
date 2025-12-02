<?php
session_start();
require_once '../../admin/config/config.php';

// Redirect jika tidak ada data yang diperlukan
if (!isset($_SESSION['layanan_id']) || !isset($_SESSION['nama']) || !isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: ../pilih-layanan.php');
    exit();
}

$conn = getConnection();

// Get data from session
$layanan_id = intval($_SESSION['layanan_id']);
$nama = $_SESSION['nama'];
$no_hp = $_SESSION['no_hp'];
$catatan = $_SESSION['catatan'] ?? '';
$metode = $_POST['metode_pembayaran'] ?? 'cash';
$cart = $_SESSION['cart'];

// ========== VALIDASI STOK SEBELUM PROSES ==========
$error_stok = [];
foreach($cart as $item) {
    $menu_id = intval($item['menu_id']);
    $qty_order = intval($item['qty']);
    
    // Cek stok tersedia
    $check_stok = "SELECT stok, nama_menu FROM menu WHERE menu_id = $menu_id";
    $result_stok = $conn->query($check_stok);
    
    if ($result_stok && $result_stok->num_rows > 0) {
        $menu_data = $result_stok->fetch_assoc();
        
        if ($menu_data['stok'] < $qty_order) {
            $error_stok[] = $menu_data['nama_menu'] . " (Stok tersisa: " . $menu_data['stok'] . ", Anda pesan: " . $qty_order . ")";
        }
    }
}

// Jika ada error stok, redirect kembali dengan pesan error
if (count($error_stok) > 0) {
    $_SESSION['error_message'] = "Stok tidak mencukupi untuk: " . implode(", ", $error_stok);
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
$nomor_antrian = generateNomorAntrian($conn);

// Escape strings
$nama_esc = $conn->real_escape_string($nama);
$no_hp_esc = $conn->real_escape_string($no_hp);
$catatan_esc = $conn->real_escape_string($catatan);
$metode_esc = $conn->real_escape_string($metode);

// Check if user exists or create new
$check_user = "SELECT user_id FROM user WHERE no_hp = '$no_hp_esc'";
$result = $conn->query($check_user);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_id = intval($user['user_id']);
    
    // Update nama jika berbeda
    $update_user = "UPDATE user SET nama = '$nama_esc' WHERE user_id = $user_id";
    $conn->query($update_user);
} else {
    $insert_user = "INSERT INTO user (nama, no_hp) VALUES ('$nama_esc', '$no_hp_esc')";
    if ($conn->query($insert_user)) {
        $user_id = $conn->insert_id;
    } else {
        die("Error creating user: " . $conn->error);
    }
}

// Start transaction untuk keamanan data
$conn->begin_transaction();

try {
    // Insert pesanan
    $tanggal = date('Y-m-d H:i:s');
    $insert_pesanan = "INSERT INTO pesanan (user_id, layanan_id, tanggal, status, total_harga, nomor_antrian) VALUES (
        $user_id,
        $layanan_id,
        '$tanggal',
        'pending',
        $total,
        '$nomor_antrian'
    )";
    
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
        $insert_detail = "INSERT INTO detail_pesanan (pesanan_id, menu_id, jumlah, harga_satuan, subtotal) VALUES (
            $pesanan_id,
            $menu_id,
            $qty,
            $harga,
            $item_subtotal
        )";
        
        if (!$conn->query($insert_detail)) {
            throw new Exception("Error insert detail: " . $conn->error);
        }
        
        // ========== KURANGI STOK MENU ==========
        $update_stok = "UPDATE menu SET stok = stok - $qty WHERE menu_id = $menu_id";
        if (!$conn->query($update_stok)) {
            throw new Exception("Error update stok: " . $conn->error);
        }
        
        // Cek jika stok <= 0, ubah status jadi 'habis'
        $check_stok_after = "SELECT stok FROM menu WHERE menu_id = $menu_id";
        $result_check = $conn->query($check_stok_after);
        $stok_data = $result_check->fetch_assoc();
        
        if ($stok_data['stok'] <= 0) {
            $update_status = "UPDATE menu SET status = 'habis', stok = 0 WHERE menu_id = $menu_id";
            $conn->query($update_status);
        }
    }
    
    // Insert pembayaran
    $insert_pembayaran = "INSERT INTO pembayaran (pesanan_id, metode, total, status) VALUES (
        $pesanan_id,
        '$metode_esc',
        $total,
        'pending'
    )";
    
    if (!$conn->query($insert_pembayaran)) {
        throw new Exception("Error insert pembayaran: " . $conn->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    // Save data to session
    $_SESSION['pesanan_id'] = $pesanan_id;
    $_SESSION['nomor_antrian'] = $nomor_antrian;
    $_SESSION['metode_pembayaran'] = $metode;
    
    // Clear cart
    unset($_SESSION['cart']);
    
    // Redirect based on payment method
    if ($metode == 'cash') {
        header('Location: ../status-pesanan.php');
    } else {
        header('Location: ../pembayaran.php');
    }
    exit();
    
} catch (Exception $e) {
    // Rollback jika ada error
    $conn->rollback();
    $_SESSION['error_message'] = "Terjadi kesalahan saat memproses pesanan: " . $e->getMessage();
    header('Location: ../pilih-menu.php');
    exit();
}

$conn->close();
?>
