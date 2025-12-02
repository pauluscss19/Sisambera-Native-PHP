<?php
require_once '../config/config.php';
requireLogin();

$page_title = 'Order Detail';
$page_subtitle = 'Detail informasi pesanan';

$conn = getConnection();

// Get pesanan_id from URL
$pesanan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($pesanan_id == 0) {
    header('Location: order_management.php');
    exit();
}

// Get order details
$query = "SELECT p.*, u.nama as customer_name, u.no_hp, l.jenis_layanan, 
          pm.metode, pm.bank, pm.status as payment_status, pm.bukti_bayar, pm.tanggal_bayar
          FROM pesanan p 
          LEFT JOIN user u ON p.user_id = u.user_id
          LEFT JOIN layanan l ON p.layanan_id = l.layanan_id
          LEFT JOIN pembayaran pm ON p.pesanan_id = pm.pesanan_id
          WHERE p.pesanan_id = $pesanan_id";

$result = $conn->query($query);

if ($result->num_rows == 0) {
    header('Location: order_management.php');
    exit();
}

$order = $result->fetch_assoc();

// Get order items
$query_items = "SELECT dp.*, m.nama_menu, m.foto 
                FROM detail_pesanan dp
                LEFT JOIN menu m ON dp.menu_id = m.menu_id
                WHERE dp.pesanan_id = $pesanan_id";
$items_result = $conn->query($query_items);

include 'includes/header.php';
?>

<style>
.detail-container {
    max-width: 1200px;
    margin: 0 auto;
}

.detail-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
    margin-bottom: 25px;
}

.detail-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.detail-card h3 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 10px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f5f5f5;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    color: #666;
    font-size: 13px;
    font-weight: 500;
}

.info-value {
    color: #333;
    font-size: 14px;
    font-weight: 600;
    text-align: right;
}

.order-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #f5f5f5;
}

.order-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
    margin-right: 15px;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.item-info {
    flex: 1;
}

.item-name {
    font-weight: 600;
    font-size: 14px;
    color: #333;
    margin-bottom: 4px;
}

.item-qty {
    font-size: 12px;
    color: #999;
}

.item-price {
    text-align: right;
    font-weight: 700;
    color: #333;
    font-size: 14px;
}

.payment-proof {
    text-align: center;
    margin-top: 15px;
}

.payment-proof img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    border: 2px solid #e0e0e0;
    cursor: pointer;
    transition: transform 0.3s;
}

.payment-proof img:hover {
    transform: scale(1.02);
}

.no-proof {
    padding: 40px 20px;
    text-align: center;
    color: #999;
    font-size: 13px;
    background: #f9f9f9;
    border-radius: 8px;
}

.status-badge {
    padding: 6px 14px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.status-badge.pending {
    background: #fff9c4;
    color: #f57f17;
}

.status-badge.dikonfirmasi {
    background: #b3e5fc;
    color: #01579b;
}

.status-badge.diproses {
    background: #ffe0b2;
    color: #e65100;
}

.status-badge.selesai {
    background: #c8e6c9;
    color: #1b5e20;
}

.status-badge.dibatalkan {
    background: #ffcdd2;
    color: #c62828;
}

.action-buttons {
    display: flex;
    gap: 10px;
    margin-top: 25px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.btn-back {
    background: #f5f5f5;
    color: #333;
}

.btn-back:hover {
    background: #e0e0e0;
}

.btn-confirm {
    background: #4caf50;
    color: white;
}

.btn-confirm:hover {
    background: #45a049;
}

.btn-process {
    background: #ff9800;
    color: white;
}

.btn-process:hover {
    background: #fb8c00;
}

.btn-complete {
    background: #2196f3;
    color: white;
}

.btn-complete:hover {
    background: #1976d2;
}

.btn-cancel {
    background: #f44336;
    color: white;
}

.btn-cancel:hover {
    background: #e53935;
}

.total-row {
    padding: 15px 0;
    border-top: 2px solid #333;
    margin-top: 10px;
    font-size: 16px;
    font-weight: 700;
}

/* Modal for image preview */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
}

.modal-content {
    margin: auto;
    display: block;
    max-width: 90%;
    max-height: 90%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.close-modal {
    position: absolute;
    top: 20px;
    right: 40px;
    color: #fff;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}

.close-modal:hover {
    color: #ccc;
}
</style>

<div class="detail-container">
    <!-- Header Info -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 12px; margin-bottom: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="font-size: 24px; margin-bottom: 8px;">Pesanan #<?php echo $order['nomor_antrian']; ?></h2>
                <p style="opacity: 0.9; font-size: 13px;"><?php echo date('d M Y, H:i', strtotime($order['tanggal'])); ?></p>
            </div>
            <div>
                <span class="status-badge <?php echo $order['status']; ?>" style="font-size: 14px; padding: 8px 16px;">
                    <?php echo strtoupper($order['status']); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="detail-grid">
        <!-- Left Column -->
        <div>
            <!-- Customer Info -->
            <div class="detail-card">
                <h3>👤 Informasi Customer</h3>
                <div class="info-row">
                    <span class="info-label">Nama</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">No. HP</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['no_hp']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Layanan</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['jenis_layanan']); ?></span>
                </div>
            </div>

            <!-- Order Items -->
            <div class="detail-card" style="margin-top: 25px;">
                <h3>🍽️ Detail Pesanan</h3>
                <?php while($item = $items_result->fetch_assoc()): ?>
                <div class="order-item">
                    <div class="item-image">
                        <?php if($item['foto'] && file_exists('uploads/' . $item['foto'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($item['foto']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['nama_menu']); ?>"
                                 style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                        <?php else: ?>
                            🍴
                        <?php endif; ?>
                    </div>
                    <div class="item-info">
                        <div class="item-name"><?php echo htmlspecialchars($item['nama_menu']); ?></div>
                        <div class="item-qty"><?php echo $item['jumlah']; ?>x @ <?php echo formatRupiah($item['harga_satuan']); ?></div>
                    </div>
                    <div class="item-price">
                        <?php echo formatRupiah($item['subtotal']); ?>
                    </div>
                </div>
                <?php endwhile; ?>

                <div class="total-row">
                    <div style="display: flex; justify-content: space-between;">
                        <span>TOTAL</span>
                        <span><?php echo formatRupiah($order['total_harga']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div>
            <!-- Payment Info -->
            <div class="detail-card">
                <h3>💳 Informasi Pembayaran</h3>
                <div class="info-row">
                    <span class="info-label">Metode</span>
                    <span class="info-value">
                        <?php 
                        $metode = strtoupper($order['metode']);
                        if ($metode == 'TRANSFER') {
                            echo '🏦 BANK';
                        } elseif ($metode == 'QRIS') {
                            echo '📱 QRIS';
                        } else {
                            echo '💵 CASH';
                        }
                        ?>
                    </span>
                </div>
                
                <?php if($order['bank']): ?>
                <div class="info-row">
                    <span class="info-label">Bank</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['bank']); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <?php 
                        if ($order['payment_status'] == 'berhasil') {
                            echo '<span style="color: #4caf50;">✓ Berhasil</span>';
                        } elseif ($order['payment_status'] == 'pending') {
                            echo '<span style="color: #ff9800;">⏳ Pending</span>';
                        } else {
                            echo '<span style="color: #f44336;">✗ Gagal</span>';
                        }
                        ?>
                    </span>
                </div>
                
                <?php if($order['tanggal_bayar']): ?>
                <div class="info-row">
                    <span class="info-label">Tanggal Bayar</span>
                    <span class="info-value"><?php echo date('d M Y, H:i', strtotime($order['tanggal_bayar'])); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Payment Proof -->
            <div class="detail-card" style="margin-top: 25px;">
                <h3>📎 Bukti Pembayaran</h3>
                
                <?php if($order['bukti_bayar'] && file_exists('../uploads/bukti_bayar/' . $order['bukti_bayar'])): ?>
                <div class="payment-proof">
                    <img src="../uploads/bukti_bayar/<?php echo htmlspecialchars($order['bukti_bayar']); ?>" 
                         alt="Bukti Pembayaran"
                         onclick="openModal(this.src)">
                    <p style="margin-top: 10px; font-size: 12px; color: #666;">
                        Klik gambar untuk memperbesar
                    </p>
                    <a href="../uploads/bukti_bayar/<?php echo htmlspecialchars($order['bukti_bayar']); ?>" 
                       download 
                       class="btn btn-back"
                       style="margin-top: 10px; display: inline-block;">
                        📥 Download Bukti
                    </a>
                </div>
                <?php else: ?>
                <div class="no-proof">
                    ❌ Belum ada bukti pembayaran
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="order_management.php" class="btn btn-back">← Kembali</a>
        
        <?php if($order['status'] == 'pending'): ?>
        <a href="order_management.php?action=confirm&id=<?php echo $pesanan_id; ?>" 
           onclick="return confirm('Konfirmasi pesanan ini?')" 
           class="btn btn-confirm">✓ Konfirmasi Pesanan</a>
        <a href="order_management.php?action=cancel&id=<?php echo $pesanan_id; ?>" 
           onclick="return confirm('Batalkan pesanan ini?')" 
           class="btn btn-cancel">✗ Batalkan Pesanan</a>
        <?php endif; ?>
        
        <?php if($order['status'] == 'dikonfirmasi'): ?>
        <a href="order_management.php?action=process&id=<?php echo $pesanan_id; ?>" 
           class="btn btn-process">🔄 Proses Pesanan</a>
        <?php endif; ?>
        
        <?php if($order['status'] == 'diproses'): ?>
        <a href="order_management.php?action=complete&id=<?php echo $pesanan_id; ?>" 
           class="btn btn-complete">✓ Selesaikan Pesanan</a>
        <?php endif; ?>
    </div>
</div>

<!-- Modal for Image Preview -->
<div id="imageModal" class="modal" onclick="closeModal()">
    <span class="close-modal" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImage">
</div>

<script>
function openModal(src) {
    document.getElementById('imageModal').style.display = 'block';
    document.getElementById('modalImage').src = src;
}

function closeModal() {
    document.getElementById('imageModal').style.display = 'none';
}

// Close modal with ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});
</script>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>
