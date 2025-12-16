<?php
session_start();
header('Content-Type: application/json');

$response = ['success' => false];

if (isset($_POST['index'])) {
    $index = intval($_POST['index']);
    
    if (isset($_SESSION['cart'][$index])) {
        array_splice($_SESSION['cart'], $index, 1);
        $response['success'] = true;
    }
}

echo json_encode($response);
?>
