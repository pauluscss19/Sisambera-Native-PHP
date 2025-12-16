<?php 
require_once '../config/config.php';
$page_title = 'Pembayaran - Sambal Belut Bu Raden';
include 'includes/header.php';

if (!isset($_SESSION['pesanan_id'])) {
    header('Location: index.php');
    exit();
}

$conn = getConnection();
$pesanan_id = $_SESSION['pesanan_id'];
$query = "SELECT p.*, pm.metode, pm.total FROM pesanan p 
          JOIN pembayaran pm ON p.pesanan_id = pm.pesanan_id 
          WHERE p.pesanan_id = $pesanan_id";
$result = $conn->query($query);
$pesanan = $result->fetch_assoc();
$conn->close();
?>

<div class="container">
    <div class="confirmation-content">
        <h2>Konfirmasi Pembayaran</h2>
        <p class="confirmation-subtitle">Tunjukkan bukti pembayaran ke kasir untuk memproses pesanan anda</p>
        
        <?php if($pesanan['metode'] == 'qris'): ?>
        <div class="qr-section">
            <div id="qrCode"></div>
            <p class="qr-text">Scan QR Code untuk melakukan pembayaran</p>
        </div>
        <?php endif; ?>
        
        <div class="payment-display">
            <div class="payment-icon-large">
                <?php 
                $icons = ['cash' => '💵', 'qris' => '📱', 'card' => '💳'];
                echo $icons[$pesanan['metode']] ?? '💰';
                ?>
            </div>
            <h3><?php echo strtoupper($pesanan['metode']); ?></h3>
            <p>Nomor Antrian: <strong><?php echo $pesanan['nomor_antrian']; ?></strong></p>
            <p>Total: <strong><?php echo formatRupiah($pesanan['total']); ?></strong></p>
        </div>
        
        <div class="button-group">
            <button class="btn btn-primary btn-done" onclick="window.location.href='status-pesanan.php'">
                Lihat Status Pesanan
            </button>
        </div>
    </div>
</div>

<?php if($pesanan['metode'] == 'qris'): ?>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
new QRCode(document.getElementById("qrCode"), {
    text: "QRIS_PAYMENT_<?php echo $pesanan['nomor_antrian']; ?>_<?php echo $pesanan['total']; ?>",
    width: 200,
    height: 200
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
