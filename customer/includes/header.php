<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Sambal Belut Bu Raden'; ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">SISAMBERA</a>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="pilih-layanan.php">Menu</a>
            <a href="tentang.php">Tentang</a>
        </nav>
        <button class="order-btn" onclick="window.location.href='pilih-layanan.php'">Pesan Sekarang</button>
    </header>
