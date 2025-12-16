<?php
require_once '../config/config.php';
$page_title = 'Konfirmasi Pembayaran - Sambal Belut Bu Raden';
include 'includes/header.php';

// Check if order exists
if (!isset($_SESSION['pesanan_id']) || !isset($_SESSION['metode_pembayaran'])) {
    header('Location: pilih-layanan.php');
    exit();
}

$pesanan_id = $_SESSION['pesanan_id'];
$nomor_antrian = $_SESSION['nomor_antrian'];
$metode = $_SESSION['metode_pembayaran'];
$bank = $_SESSION['bank'] ?? null;
$total = $_SESSION['total_pembayaran'];
?>

<div class="container">
    <div class="content-wrapper" style="grid-template-columns: 1fr;">
        <div class="confirmation-content">
            <h2>Konfirmasi Pembayaran</h2>
            <p class="confirmation-subtitle">
                Pesanan Anda telah dibuat dengan nomor antrian <strong><?php echo $nomor_antrian; ?></strong>
            </p>

            <!-- Payment Display -->
            <?php if ($metode == 'qris'): ?>
                <!-- QRIS Section -->
                <div class="payment-display">
                    <div class="payment-icon-large">📱</div>
                    <h3>Pembayaran QRIS</h3>
                    <p>Scan kode QR di bawah ini dengan aplikasi pembayaran digital Anda</p>
                </div>

                <div class="qr-section">
                    <div id="qrCode"></div>
                    <p class="qr-text">Total Pembayaran: <strong><?php echo formatRupiah($total); ?></strong></p>
                </div>

                <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
                <script>
                    // Generate QR Code
                    new QRCode(document.getElementById("qrCode"), {
                        text: "QRIS:<?php echo $pesanan_id; ?>:<?php echo $total; ?>",
                        width: 200,
                        height: 200
                    });
                </script>

            <?php elseif ($metode == 'transfer' && $bank): ?>
                <!-- Transfer Bank Section -->
                <div class="payment-display">
                    <div class="payment-icon-large">🏦</div>
                    <h3>Transfer Bank <?php echo htmlspecialchars($bank); ?></h3>
                    <p>Silakan transfer ke rekening di bawah ini</p>
                </div>

                <div class="bank-info">
                    <div class="bank-detail">
                        <span class="bank-label">Bank</span>
                        <span class="bank-value"><?php echo htmlspecialchars($bank); ?></span>
                    </div>
                    <div class="bank-detail">
                        <span class="bank-label">Nomor Rekening</span>
                        <span class="bank-value">
                            <?php
                            $rekening = [
                                'BCA' => '1234567890',
                                'BNI' => '0987654321',
                                'Mandiri' => '1357924680',
                                'BRI' => '2468013579'
                            ];
                            echo $rekening[$bank] ?? '0000000000';
                            ?>
                        </span>
                    </div>
                    <div class="bank-detail">
                        <span class="bank-label">Atas Nama</span>
                        <span class="bank-value">SAMBAL BELUT BU RADEN</span>
                    </div>
                    <div class="bank-detail" style="border-bottom: none;">
                        <span class="bank-label">Jumlah Transfer</span>
                        <span class="bank-value" style="color: #c1395d; font-size: 18px; font-weight: 700;">
                            <?php echo formatRupiah($total); ?>
                        </span>
                    </div>
                </div>

            <?php endif; ?>

            <!-- Buttons -->
            <div class="button-group">
                <button class="btn btn-back" onclick="window.location.href='index.php'">
                    Kembali ke Beranda
                </button>
                <button class="btn btn-done" onclick="window.location.href='status-pesanan.php'">
                    Lihat Status Pesanan
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>