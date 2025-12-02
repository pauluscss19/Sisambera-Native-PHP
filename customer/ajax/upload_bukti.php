<?php
session_start();
require_once '../../config/config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['bukti_bayar'])) {
    $pesanan_id = intval($_POST['pesanan_id']);
    $file = $_FILES['bukti_bayar'];
    
    // Validasi file
    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        $response['message'] = 'Format file tidak didukung';
        echo json_encode($response);
        exit;
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB
        $response['message'] = 'Ukuran file terlalu besar (max 5MB)';
        echo json_encode($response);
        exit;
    }
    
    // Upload file
    $upload_dir = '../../uploads/bukti_bayar/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $filename = 'bukti_' . $pesanan_id . '_' . time() . '.' . $ext;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Update database
        $conn = getConnection();
        $update = "UPDATE pembayaran SET bukti_bayar = '$filename' WHERE pesanan_id = $pesanan_id";
        
        if ($conn->query($update)) {
            $response['success'] = true;
            $response['message'] = 'Bukti pembayaran berhasil diupload';
        } else {
            $response['message'] = 'Gagal menyimpan ke database';
        }
        $conn->close();
    } else {
        $response['message'] = 'Gagal upload file';
    }
} else {
    $response['message'] = 'No file uploaded';
}

echo json_encode($response);
?>
