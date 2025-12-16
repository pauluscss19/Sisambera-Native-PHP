<?php
session_start();
require_once '../../config/config.php';

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['bukti_bayar'])) {
    $pesanan_id = intval($_POST['pesanan_id']);
    $file = $_FILES['bukti_bayar'];

    // Debug: Log received data
    error_log("Upload request for pesanan_id: $pesanan_id");
    error_log("File name: " . $file['name']);

    // Validasi file
    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        $response['message'] = 'Format file tidak didukung. Gunakan JPG, PNG, atau PDF';
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
        error_log("Created upload directory: $upload_dir");
    }

    // Generate unique filename
    $filename = 'bukti_' . $pesanan_id . '_' . time() . '.' . $ext;
    $filepath = $upload_dir . $filename;

    error_log("Target file path: $filepath");

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        error_log("File uploaded successfully: $filepath");

        // Update database - PENTING!
        $conn = getConnection();

        if (!$conn) {
            $response['message'] = 'Database connection failed';
            echo json_encode($response);
            exit;
        }

        // Escape filename
        $filename_esc = $conn->real_escape_string($filename);

        // Update pembayaran dengan bukti_bayar dan tanggal_bayar
        $update = "UPDATE pembayaran SET bukti_bayar = '$filename_esc', tanggal_bayar = NOW() WHERE pesanan_id = $pesanan_id";

        error_log("Executing query: $update");

        if ($conn->query($update)) {
            // Verify the update
            $verify = "SELECT bukti_bayar FROM pembayaran WHERE pesanan_id = $pesanan_id";
            $result = $conn->query($verify);

            if ($result && $row = $result->fetch_assoc()) {
                error_log("Database updated successfully. bukti_bayar = " . $row['bukti_bayar']);
            }

            $response['success'] = true;
            $response['message'] = 'Bukti pembayaran berhasil diupload';
            $response['filename'] = $filename;
            $response['path'] = '/uploads/bukti_bayar/' . $filename;
        } else {
            error_log("Database update failed: " . $conn->error);
            $response['message'] = 'Gagal menyimpan ke database: ' . $conn->error;
        }

        $conn->close();
    } else {
        error_log("File move failed. Error: " . error_get_last()['message']);
        $response['message'] = 'Gagal upload file: ' . error_get_last()['message'];
    }
} else {
    $response['message'] = 'No file uploaded or invalid request';
    error_log("Invalid request - POST and FILES not set properly");
}

echo json_encode($response);
