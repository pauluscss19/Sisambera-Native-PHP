<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$response = ['success' => false, 'message' => ''];

$action = $_POST['action'] ?? 'add';

if ($action == 'add') {
    $menu_id = intval($_POST['menu_id']);
    $nama_menu = $_POST['nama_menu'];
    $harga = floatval($_POST['harga']);
    $qty = intval($_POST['qty'] ?? 1);
    $max_stok = intval($_POST['max_stok'] ?? 999);
    
    // Cek apakah sudah ada di cart
    $found = false;
    foreach ($_SESSION['cart'] as $key => &$item) {
        if ($item['menu_id'] == $menu_id) {
            // Validasi stok
            if ($item['qty'] + $qty > $max_stok) {
                $response['message'] = "Stok tidak mencukupi! Maksimal $max_stok item.";
                echo json_encode($response);
                exit;
            }
            $item['qty'] += $qty;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $_SESSION['cart'][] = [
            'menu_id' => $menu_id,
            'nama_menu' => $nama_menu,
            'harga' => $harga,
            'qty' => $qty
        ];
    }
    
    $response['success'] = true;
    $response['message'] = 'Item berhasil ditambahkan';
    
} elseif ($action == 'update') {
    $index = intval($_POST['index']);
    $change = intval($_POST['change']);
    
    if (isset($_SESSION['cart'][$index])) {
        $_SESSION['cart'][$index]['qty'] += $change;
        
        if ($_SESSION['cart'][$index]['qty'] <= 0) {
            array_splice($_SESSION['cart'], $index, 1);
        }
        
        $response['success'] = true;
    }
}

echo json_encode($response);
?>
