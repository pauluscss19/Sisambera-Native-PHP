<?php
require_once '../config/config.php';
requireLogin();

$page_title = 'Order Management';
$page_subtitle = 'Kelola pesanan pelanggan';

$conn = getConnection();

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $pesanan_id = intval($_GET['id']);
    
    if ($action == 'confirm') {
        // Konfirmasi pesanan
        $query = "UPDATE pesanan SET status = 'dikonfirmasi' WHERE pesanan_id = $pesanan_id";
        $conn->query($query);
        
        // Update pembayaran status
        $query = "UPDATE pembayaran SET status = 'berhasil', tanggal_bayar = NOW() WHERE pesanan_id = $pesanan_id";
        $conn->query($query);
        
        $_SESSION['message'] = 'Pesanan berhasil dikonfirmasi!';
        
    } elseif ($action == 'process') {
        // Proses pesanan
        $query = "UPDATE pesanan SET status = 'diproses' WHERE pesanan_id = $pesanan_id";
        $conn->query($query);
        $_SESSION['message'] = 'Pesanan sedang diproses!';
        
    } elseif ($action == 'complete') {
        // Selesaikan pesanan
        $query = "UPDATE pesanan SET status = 'selesai' WHERE pesanan_id = $pesanan_id";
        $conn->query($query);
        $_SESSION['message'] = 'Pesanan selesai!';
        
    } elseif ($action == 'cancel') {
        // Batalkan pesanan
        $query = "UPDATE pesanan SET status = 'dibatalkan' WHERE pesanan_id = $pesanan_id";
        $conn->query($query);
        $_SESSION['message'] = 'Pesanan dibatalkan!';
    }
    
    header('Location: order_management.php');
    exit();
}

// Get search parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get orders with details
$query = "SELECT p.*, u.nama as customer_name, u.no_hp, l.jenis_layanan, 
          pm.metode, pm.bank, pm.status as payment_status, pm.bukti_bayar
          FROM pesanan p 
          LEFT JOIN user u ON p.user_id = u.user_id
          LEFT JOIN layanan l ON p.layanan_id = l.layanan_id
          LEFT JOIN pembayaran pm ON p.pesanan_id = pm.pesanan_id
          WHERE p.status IN ('pending', 'dikonfirmasi', 'diproses')";

if ($search) {
    $search_esc = $conn->real_escape_string($search);
    $query .= " AND (p.nomor_antrian LIKE '%$search_esc%' OR u.nama LIKE '%$search_esc%')";
}

$query .= " ORDER BY p.tanggal DESC";
$result = $conn->query($query);

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);

include 'includes/header.php';
?>

<?php if($message): ?>
<div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
    <?php echo $message; ?>
</div>
<?php endif; ?>

<!-- Search Bar -->
<div class="search-wrapper">
    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
    </svg>
    <form method="GET">
        <input type="text" name="search" class="search-bar" placeholder="Cari nomor antrian atau nama customer..." 
               value="<?php echo htmlspecialchars($search); ?>">
    </form>
</div>

<!-- Table -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Nomor Antrian</th>
                <th>Customer</th>
                <th>Layanan</th>
                <th>Total</th>
                <th>Payment Method</th>
                <th>Payment Status</th>
                <th>Order Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result->num_rows > 0): ?>
                <?php while($order = $result->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo $order['nomor_antrian']; ?></strong></td>
                    <td>
                        <?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?><br>
                        <small style="color: #666;"><?php echo $order['no_hp'] ?? '-'; ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($order['jenis_layanan']); ?></td>
                    <td><?php echo formatRupiah($order['total_harga']); ?></td>
                    <td>
                        <?php
                        $metode = strtolower($order['metode']);
                        
                        // Tampilkan Payment Method
                        if ($metode == 'transfer') {
                            echo '<span class="badge bank">🏦 BANK</span>';
                            // Tampilkan detail bank di bawah jika ada
                            if ($order['bank']) {
                                echo '<br><small style="color: #666;">' . htmlspecialchars($order['bank']) . '</small>';
                            }
                        } elseif ($metode == 'qris') {
                            echo '<span class="badge qris">📱 QRIS</span>';
                        } else {
                            echo '<span class="badge cash">💵 CASH</span>';
                        }
                        
                        // Indikator bukti bayar
                        if($order['bukti_bayar']) {
                            echo '<br><small style="color: #4caf50; font-weight: 600;">✓ Bukti uploaded</small>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $pay_status = $order['payment_status'];
                        if ($pay_status == 'berhasil') {
                            echo '<span class="badge success">✓ Berhasil</span>';
                        } elseif ($pay_status == 'pending') {
                            echo '<span class="badge pending">⏳ Pending</span>';
                        } else {
                            echo '<span class="badge failed">✗ Gagal</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $order_status = ucfirst($order['status']);
                        $order_class = '';
                        switch($order['status']) {
                            case 'pending': $order_class = 'pending'; break;
                            case 'dikonfirmasi': $order_class = 'confirmed'; break;
                            case 'diproses': $order_class = 'processing'; break;
                            case 'selesai': $order_class = 'completed'; break;
                        }
                        ?>
                        <span class="badge <?php echo $order_class; ?>"><?php echo $order_status; ?></span>
                    </td>
                    <td>
                        <a href="order_detail.php?id=<?php echo $order['pesanan_id']; ?>" class="action-btn" title="Lihat Detail">👁️</a>
                        
                        <?php if($order['status'] == 'pending'): ?>
                        <a href="?action=confirm&id=<?php echo $order['pesanan_id']; ?>" 
                           onclick="return confirm('Konfirmasi pesanan ini?')" 
                           class="action-btn btn-confirm">Confirm</a>
                        <?php endif; ?>
                        
                        <?php if($order['status'] == 'dikonfirmasi'): ?>
                        <a href="?action=process&id=<?php echo $order['pesanan_id']; ?>" 
                           class="action-btn btn-process">Process</a>
                        <?php endif; ?>
                        
                        <?php if($order['status'] == 'diproses'): ?>
                        <a href="?action=complete&id=<?php echo $order['pesanan_id']; ?>" 
                           class="action-btn btn-complete">Complete</a>
                        <?php endif; ?>
                        
                        <?php if($order['status'] == 'pending'): ?>
                        <a href="?action=cancel&id=<?php echo $order['pesanan_id']; ?>" 
                           onclick="return confirm('Batalkan pesanan ini?')" 
                           class="action-btn btn-cancel">Cancel</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                        Tidak ada pesanan aktif
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.badge {
    padding: 5px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
}

/* Payment Method Badges */
.badge.cash {
    background: #e8f5e9;
    color: #2e7d32;
}

.badge.qris {
    background: #e3f2fd;
    color: #1565c0;
}

.badge.bank {
    background: #fff3e0;
    color: #e65100;
}

/* Payment Status Badges */
.badge.pending {
    background: #fff9c4;
    color: #f57f17;
}

.badge.success {
    background: #c8e6c9;
    color: #2e7d32;
}

.badge.failed {
    background: #ffcdd2;
    color: #c62828;
}

/* Order Status Badges */
.badge.confirmed {
    background: #b3e5fc;
    color: #01579b;
}

.badge.processing {
    background: #ffe0b2;
    color: #e65100;
}

.badge.completed {
    background: #c8e6c9;
    color: #1b5e20;
}

/* Action Buttons */
.action-btn {
    text-decoration: none;
    color: #333;
    margin-right: 8px;
    font-size: 14px;
    display: inline-block;
    transition: all 0.2s;
    padding: 5px 12px;
    border-radius: 6px;
}

.action-btn:hover {
    transform: translateY(-2px);
}

.btn-confirm {
    background: #4caf50;
    color: white !important;
}

.btn-confirm:hover {
    background: #45a049;
}

.btn-process {
    background: #ff9800;
    color: white !important;
}

.btn-process:hover {
    background: #fb8c00;
}

.btn-complete {
    background: #2196f3;
    color: white !important;
}

.btn-complete:hover {
    background: #1976d2;
}

.btn-cancel {
    background: #f44336;
    color: white !important;
}

.btn-cancel:hover {
    background: #e53935;
}
</style>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>
