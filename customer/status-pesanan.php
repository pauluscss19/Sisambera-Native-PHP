<?php
require_once '../config/config.php';
$page_title = 'Status Pesanan - Sambal Belut Bu Raden';
include 'includes/header.php';

if (!isset($_SESSION['pesanan_id'])) {
    header('Location: index.php');
    exit();
}

$conn = getConnection();
$pesanan_id = $_SESSION['pesanan_id'];
$query = "SELECT p.*, l.jenis_layanan, u.nama, u.no_hp 
          FROM pesanan p 
          JOIN layanan l ON p.layanan_id = l.layanan_id
          LEFT JOIN user u ON p.user_id = u.user_id
          WHERE p.pesanan_id = $pesanan_id";
$result = $conn->query($query);
$pesanan = $result->fetch_assoc();

// Get detail pesanan
$detail_query = "SELECT dp.*, m.nama_menu FROM detail_pesanan dp
                 JOIN menu m ON dp.menu_id = m.menu_id
                 WHERE dp.pesanan_id = $pesanan_id";
$detail_result = $conn->query($detail_query);
$conn->close();
?>

<div class="status-container">
    <div class="status-header">
        <h1>Status Pesanan Dan Antrian Anda</h1>
        <a href="index.php" class="back-button" id="backToMenu">Back to Menu</a>

        <script>
            document.getElementById('backToMenu')?.addEventListener('click', function(e) {
                const ok = confirm('Yakin kembali ke halaman awal? Status pesanan masih bisa dipantau di halaman ini.');
                if (!ok) e.preventDefault();
            });
        </script>
    </div>

    <p class="status-subtitle">Tunjukkan bukti pembayaran ke kasir untuk memproses pesanan anda</p>

    <!-- Queue Number Card -->
    <div class="queue-info-card">
        <p class="queue-desc">Nomor Antrian Anda</p>
        <div class="queue-number"><?php echo $pesanan['nomor_antrian']; ?></div>
        <div class="queue-image">🍽️</div>
    </div>

    <!-- Status Message -->
    <?php if ($pesanan['status'] == 'pending'): ?>
        <div class="next-info">
            <p class="next-label">Pesanan Anda Sedang Menunggu</p>
            <p class="next-text">Silahkan menunggu konfirmasi dari kasir</p>
        </div>
    <?php elseif ($pesanan['status'] == 'dikonfirmasi'): ?>
        <div class="next-info">
            <p class="next-label">Pesanan Dikonfirmasi</p>
            <p class="next-text">Pesanan Anda sedang disiapkan</p>
        </div>
    <?php elseif ($pesanan['status'] == 'diproses'): ?>
        <div class="next-info">
            <p class="next-label">Pesanan Sedang Diproses</p>
            <p class="next-text">Chef sedang menyiapkan pesanan Anda</p>
        </div>
    <?php elseif ($pesanan['status'] == 'selesai'): ?>
        <div class="next-info" style="background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);">
            <p class="next-label">Pesanan Siap!</p>
            <p class="next-text">Silahkan ambil pesanan anda di kasir pengambilan</p>
        </div>
    <?php endif; ?>

    <!-- Status Progress -->
    <div class="status-progress">
        <p class="progress-label">Status Pesanan</p>
        <div class="progress-bar">
            <div class="progress-line <?php echo in_array($pesanan['status'], ['dikonfirmasi', 'diproses', 'selesai']) ? 'active' : ''; ?>"></div>
            <div class="progress-step <?php echo $pesanan['status'] == 'pending' ? 'active' : 'completed'; ?>">⏱️</div>
            <div class="progress-step <?php echo $pesanan['status'] == 'dikonfirmasi' ? 'active' : (in_array($pesanan['status'], ['diproses', 'selesai']) ? 'completed' : ''); ?>">👨‍🍳</div>
            <div class="progress-step <?php echo $pesanan['status'] == 'diproses' ? 'active' : ($pesanan['status'] == 'selesai' ? 'completed' : ''); ?>">🍳</div>
            <div class="progress-step <?php echo $pesanan['status'] == 'selesai' ? 'active' : ''; ?>">✅</div>
        </div>
    </div>

    <!-- Order Details -->
    <button class="detail-pesanan-btn" onclick="toggleDetail()">Detail Pesanan ▼</button>

    <div id="orderDetail" style="display: none; margin-top: 20px;">
        <div class="summary-card">
            <h3>Detail Pesanan</h3>
            <div class="order-details">
                <div class="detail-row">
                    <span>Nomor Antrian:</span>
                    <span class="order-id"><?php echo $pesanan['nomor_antrian']; ?></span>
                </div>
                <div class="detail-row">
                    <span>Nama:</span>
                    <span><?php echo htmlspecialchars($pesanan['nama']); ?></span>
                </div>
                <div class="detail-row">
                    <span>No. HP:</span>
                    <span><?php echo htmlspecialchars($pesanan['no_hp']); ?></span>
                </div>
                <div class="detail-row">
                    <span>Layanan:</span>
                    <span><?php echo htmlspecialchars($pesanan['jenis_layanan']); ?></span>
                </div>
                <div class="detail-row">
                    <span>Waktu Pesan:</span>
                    <span><?php echo date('d M Y H:i', strtotime($pesanan['tanggal'])); ?></span>
                </div>
            </div>

            <h4 style="margin-top: 20px; margin-bottom: 10px;">Item Pesanan:</h4>
            <?php while ($detail = $detail_result->fetch_assoc()): ?>
                <div class="cart-item">
                    <div class="cart-item-info">
                        <div><?php echo htmlspecialchars($detail['nama_menu']); ?></div>
                        <div class="cart-qty"><?php echo $detail['jumlah']; ?>x @ <?php echo formatRupiah($detail['harga_satuan']); ?></div>
                    </div>
                    <div class="cart-item-price"><?php echo formatRupiah($detail['subtotal']); ?></div>
                </div>
            <?php endwhile; ?>

            <div class="summary-total" style="margin-top: 15px;">
                <span>Total</span>
                <span><?php echo formatRupiah($pesanan['total_harga']); ?></span>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleDetail() {
        const detail = document.getElementById('orderDetail');
        const btn = event.target;
        if (detail.style.display === 'none') {
            detail.style.display = 'block';
            btn.innerHTML = 'Detail Pesanan ▲';
        } else {
            detail.style.display = 'none';
            btn.innerHTML = 'Detail Pesanan ▼';
        }
    }

    // Auto refresh status every 10 seconds
    setInterval(function() {
        location.reload();
    }, 10000);
</script>

<?php include 'includes/footer.php'; ?>