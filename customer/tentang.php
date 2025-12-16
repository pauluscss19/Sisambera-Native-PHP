<?php
require_once '../config/config.php';
$page_title = 'Tentang - Sambal Belut Bu Raden';
include 'includes/header.php';
?>

<div class="container about-page">
  <h2 class="about-title">Tentang SISAMBERA</h2>

  <div class="about-intro-card">
    <p>
      SISAMBERA adalah web pemesanan makanan untuk Rumah Makan Sambal Belut Bu Raden yang membantu
      pelanggan memesan lebih cepat, baik melalui kasir maupun pemesanan mandiri lewat QR.
    </p>
  </div>

  <div class="about-grid">
    <div class="about-card">
      <h3>Kenapa dibuat?</h3>
      <p>Mengurangi antrean, meminimalkan salah catat pesanan, dan membuat proses pelayanan lebih rapi.</p>
    </div>

    <div class="about-card">
      <h3>Cara kerja singkat</h3>
      <p>
        Pelanggan memilih menu, menentukan layanan (dine-in / takeaway), lalu memilih pembayaran.
        Pesanan masuk ke sistem admin untuk diproses hingga nomor antrian dipanggil.
      </p>
    </div>

    <div class="about-card">
      <h3>Fitur utama</h3>
      <ul>
        <li>Menu lengkap dengan foto dan harga.</li>
        <li>Pemesanan mandiri via QR (tanpa perlu login).</li>
        <li>Status pesanan & nomor antrian.</li>
      </ul>
    </div>
  </div>
</div>


<?php include 'includes/footer.php'; ?>