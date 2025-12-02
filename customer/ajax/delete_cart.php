<?php
session_start();
header('Content-Type: application/json');

if (!isset($_POST['cart_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$cart_id = intval($_POST['cart_id']);

if (isset($_SESSION['cart'][$cart_id])) {
    unset($_SESSION['cart'][$cart_id]);
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex
}

echo json_encode(['success' => true]);
?>
