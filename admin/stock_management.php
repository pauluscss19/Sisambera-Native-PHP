<?php
require_once '../config/config.php';
requireLogin();

$page_title = 'Stock Management';
$page_subtitle = 'Kelola stok bahan baku';

$conn = getConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'update') {
        $bahan_id = intval($_POST['bahan_id']);
        $jumlah = floatval($_POST['jumlah']);
        
        $stmt = $conn->prepare("UPDATE bahan SET jumlah=? WHERE bahan_id=?");
        $stmt->bind_param("di", $jumlah, $bahan_id);
        $stmt->execute();
        $stmt->close();
        
        // Update status bahan
        updateStatusBahan($conn, $bahan_id);
        
        $_SESSION['message'] = 'Stock berhasil diupdate!';
        header('Location: stock_management.php');
        exit();
        
    } elseif ($action == 'add') {
        $nama_bahan = $conn->real_escape_string($_POST['nama_bahan']);
        $jumlah = floatval($_POST['jumlah']);
        $satuan = $conn->real_escape_string($_POST['satuan']);
        $minimum_stok = floatval($_POST['minimum_stok']);
        
        $stmt = $conn->prepare("INSERT INTO bahan (nama_bahan, jumlah, satuan, minimum_stok) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdsd", $nama_bahan, $jumlah, $satuan, $minimum_stok);
        $stmt->execute();
        $bahan_id = $conn->insert_id;
        updateStatusBahan($conn, $bahan_id);
        $stmt->close();
        
        $_SESSION['message'] = 'Bahan berhasil ditambahkan!';
        header('Location: stock_management.php');
        exit();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $bahan_id = intval($_GET['delete']);
    $delete = "DELETE FROM bahan WHERE bahan_id = $bahan_id";
    if ($conn->query($delete)) {
        $_SESSION['message'] = 'Bahan berhasil dihapus!';
    }
    header('Location: stock_management.php');
    exit();
}

// Get search parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get stock list
$query = "SELECT * FROM bahan";
if ($search) {
    $search_esc = $conn->real_escape_string($search);
    $query .= " WHERE nama_bahan LIKE '%$search_esc%'";
}
$query .= " ORDER BY status DESC, nama_bahan";
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

<!-- Header with Button -->
<div class="header-with-btn">
    <div class="search-wrapper" style="flex: 1; max-width: 500px;">
        <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
        </svg>
        <form method="GET">
            <input type="text" name="search" class="search-bar" placeholder="Cari bahan..." 
                   value="<?php echo htmlspecialchars($search); ?>">
        </form>
    </div>
    <button class="add-btn" onclick="openAddModal()">
        <span style="font-size: 20px;">+</span> Add New Stock
    </button>
</div>

<!-- Table -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Ingredient Name</th>
                <th>Available</th>
                <th>Unit</th>
                <th>Minimum Stock</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result->num_rows > 0): ?>
                <?php while($bahan = $result->fetch_assoc()): ?>
                <tr style="<?php echo $bahan['status'] == 'very-low' ? 'background: #ffebee;' : ($bahan['status'] == 'low-stock' ? 'background: #fff9e6;' : ''); ?>">
                    <td><strong><?php echo htmlspecialchars($bahan['nama_bahan']); ?></strong></td>
                    <td><?php echo number_format($bahan['jumlah'], 2); ?></td>
                    <td><?php echo htmlspecialchars($bahan['satuan']); ?></td>
                    <td><?php echo number_format($bahan['minimum_stok'], 2); ?></td>
                    <td>
                        <span class="badge <?php echo $bahan['status']; ?>">
                            <?php 
                            $status_text = ['safe' => 'Safe', 'low-stock' => 'Low Stock', 'very-low' => 'Very Low'];
                            echo $status_text[$bahan['status']]; 
                            ?>
                        </span>
                    </td>
                    <td>
                        <span class="icon-btn" onclick='updateStock(<?php echo json_encode($bahan); ?>)' title="Update">✏️</span>
                        <a href="?delete=<?php echo $bahan['bahan_id']; ?>" 
                           onclick="return confirm('Hapus bahan ini?')" 
                           class="icon-btn" title="Delete">🗑️</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                        Tidak ada data
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Update Stock -->
<div id="updateModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 40px; border-radius: 12px; width: 90%; max-width: 400px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2 style="margin: 0;">Update Stock</h2>
            <span onclick="closeUpdateModal()" style="font-size: 28px; cursor: pointer; color: #999;">&times;</span>
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="bahan_id" id="update_bahan_id">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Ingredient Name</label>
                <input type="text" id="update_nama_bahan" readonly 
                       style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; background: #f5f5f5;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">New Stock Amount *</label>
                <input type="number" name="jumlah" id="update_jumlah" required min="0" step="0.01" 
                       style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
                <button type="button" onclick="closeUpdateModal()" 
                        style="padding: 12px 30px; background: #e0e0e0; border: none; border-radius: 8px; font-size: 16px; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" class="add-btn">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Add Stock -->
<div id="addModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 40px; border-radius: 12px; width: 90%; max-width: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2 style="margin: 0;">Add New Ingredient</h2>
            <span onclick="closeAddModal()" style="font-size: 28px; cursor: pointer; color: #999;">&times;</span>
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Ingredient Name *</label>
                <input type="text" name="nama_bahan" required 
                       style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Initial Stock *</label>
                    <input type="number" name="jumlah" required min="0" step="0.01" 
                           style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Unit *</label>
                    <input type="text" name="satuan" required placeholder="Kg, liter, pcs" 
                           style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Minimum Stock *</label>
                <input type="number" name="minimum_stok" required min="0" step="0.01" 
                       style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                <small style="color: #666;">Alert akan muncul jika stok dibawah nilai ini</small>
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
                <button type="button" onclick="closeAddModal()" 
                        style="padding: 12px 30px; background: #e0e0e0; border: none; border-radius: 8px; font-size: 16px; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" class="add-btn">
                    Add Stock
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateStock(bahan) {
    document.getElementById('updateModal').style.display = 'flex';
    document.getElementById('update_bahan_id').value = bahan.bahan_id;
    document.getElementById('update_nama_bahan').value = bahan.nama_bahan;
    document.getElementById('update_jumlah').value = bahan.jumlah;
}

function closeUpdateModal() {
    document.getElementById('updateModal').style.display = 'none';
}

function openAddModal() {
    document.getElementById('addModal').style.display = 'flex';
}

function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
}
</script>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>
