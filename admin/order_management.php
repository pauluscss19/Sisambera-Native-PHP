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
        $query = "UPDATE pesanan SET status = 'dikonfirmasi' WHERE pesanan_id = $pesanan_id";
        $conn->query($query);
        $query = "UPDATE pembayaran SET status = 'berhasil', tanggal_bayar = NOW() WHERE pesanan_id = $pesanan_id";
        $conn->query($query);
        $_SESSION['message'] = 'Pesanan berhasil dikonfirmasi!';
    } elseif ($action == 'process') {
        $query = "UPDATE pesanan SET status = 'diproses' WHERE pesanan_id = $pesanan_id";
        $conn->query($query);
        $_SESSION['message'] = 'Pesanan sedang diproses!';
    } elseif ($action == 'complete') {
        $query = "UPDATE pesanan SET status = 'selesai' WHERE pesanan_id = $pesanan_id";
        $conn->query($query);
        $_SESSION['message'] = 'Pesanan selesai!';
    } elseif ($action == 'cancel') {
        $query = "UPDATE pesanan SET status = 'dibatalkan' WHERE pesanan_id = $pesanan_id";
        $conn->query($query);
        $_SESSION['message'] = 'Pesanan dibatalkan!';
    }

    header('Location: order_management.php');
    exit();
}

// Get filter (default: active)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'active';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query berdasarkan filter
$query = "SELECT p.*, u.nama as customer_name, u.no_hp, l.jenis_layanan,
          pm.metode, pm.bank, pm.status as payment_status, pm.bukti_bayar
          FROM pesanan p
          LEFT JOIN user u ON p.user_id = u.user_id
          LEFT JOIN layanan l ON p.layanan_id = l.layanan_id
          LEFT JOIN pembayaran pm ON p.pesanan_id = pm.pesanan_id";

if ($filter == 'active') {
    $query .= " WHERE p.status IN ('pending', 'dikonfirmasi', 'diproses')";
} else {
    // history: tampilkan semua ATAU hanya selesai + dibatalkan
    $query .= " WHERE p.status IN ('selesai', 'dibatalkan')";
}

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

<?php if ($message): ?>
    <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<!-- Tab Filter -->
<div style="margin-bottom: 25px; display: flex; gap: 12px;">
    <a href="?filter=active"
        class="filter-tab <?php echo $filter == 'active' ? 'active' : ''; ?>">
        📋 Active Orders
    </a>
    <a href="?filter=history"
        class="filter-tab <?php echo $filter == 'history' ? 'active' : ''; ?>">
        📜 History
    </a>
</div>

<!-- Search Bar -->
<div class="search-wrapper">
    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
    </svg>
    <form method="GET">
        <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
        <input type="text" name="search" class="search-bar"
            placeholder="Cari nomor antrian atau nama customer..."
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
            <?php if ($result->num_rows > 0): ?>
                <?php while ($order = $result->fetch_assoc()): ?>
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
                            if ($metode == 'transfer') {
                                echo '<span class="badge bank">🏦 BANK</span>';
                                if ($order['bank']) {
                                    echo '<br><small style="color: #666;">' . htmlspecialchars($order['bank']) . '</small>';
                                }
                            } elseif ($metode == 'qris') {
                                echo '<span class="badge qris">📱 QRIS</span>';
                            } else {
                                echo '<span class="badge cash">💵 CASH</span>';
                            }
                            if ($order['bukti_bayar']) {
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
                            $orderstatus = ucfirst($order['status']);
                            $orderclass = '';
                            switch ($order['status']) {
                                case 'pending':
                                    $orderclass = 'pending';
                                    break;
                                case 'dikonfirmasi':
                                    $orderclass = 'confirmed';
                                    break;
                                case 'diproses':
                                    $orderclass = 'processing';
                                    break;
                                case 'selesai':
                                    $orderclass = 'completed';
                                    break;
                                case 'dibatalkan':
                                    $orderclass = 'cancelled';
                                    break;
                            }
                            ?>
                            <span class="badge <?php echo $orderclass; ?>"><?php echo $orderstatus; ?></span>
                        </td>
                        <td>
                            <a href="order_detail.php?id=<?php echo $order['pesanan_id']; ?>"
                                class="action-btn" title="Lihat Detail">👁️</a>

                            <?php if ($order['status'] == 'pending'): ?>
                                <a href="?action=confirm&id=<?php echo $order['pesanan_id']; ?>"
                                    onclick="return confirm('Konfirmasi pesanan ini?')"
                                    class="action-btn btn-confirm">Confirm</a>
                                <a href="?action=cancel&id=<?php echo $order['pesanan_id']; ?>"
                                    onclick="return confirm('Batalkan pesanan ini?')"
                                    class="action-btn btn-cancel">Cancel</a>
                            <?php endif; ?>

                            <?php if ($order['status'] == 'dikonfirmasi'): ?>
                                <a href="?action=process&id=<?php echo $order['pesanan_id']; ?>"
                                    class="action-btn btn-process">Process</a>
                            <?php endif; ?>

                            <?php if ($order['status'] == 'diproses'): ?>
                                <a href="?action=complete&id=<?php echo $order['pesanan_id']; ?>"
                                    class="action-btn btn-complete">Complete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                        <?php echo $filter == 'active' ? 'Tidak ada pesanan aktif' : 'Tidak ada riwayat pesanan'; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
    /* Badge for order status */
    .badge.cancelled {
        background: #ffcdd2;
        color: #c62828;
    }

    /* Filter tabs */
    .filter-tab {
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 15px;
        color: #666;
        background: #f5f5f5;
        transition: all 0.3s;
        display: inline-block;
    }

    .filter-tab:hover {
        background: #e0e0e0;
    }

    .filter-tab.active {
        background: #5d3a3a;
        color: white;
    }
</style>

<?php
$conn->close();
include 'includes/footer.php';
?>