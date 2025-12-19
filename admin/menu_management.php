<?php
require_once '../config/config.php';
requireLogin();

$page_title = 'Menu Management';
$page_subtitle = 'Kelola menu makanan & minuman';

$conn = getConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $menu_id = intval($_GET['delete']);
    
    // Get foto untuk dihapus
    $query = "SELECT foto FROM menu WHERE menu_id = $menu_id";
    $result = $conn->query($query);
    if ($result && $menu = $result->fetch_assoc()) {
        if ($menu['foto'] && file_exists("uploads/" . $menu['foto'])) {
            unlink("uploads/" . $menu['foto']);
        }
    }
    
    $delete = "DELETE FROM menu WHERE menu_id = $menu_id";
    if ($conn->query($delete)) {
        $_SESSION['message'] = 'Menu berhasil dihapus!';
    }
    header('Location: menu_management.php');
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $menu_id = isset($_POST['menu_id']) ? intval($_POST['menu_id']) : 0;
    $nama_menu = $conn->real_escape_string($_POST['nama_menu']);
    $kategori = $conn->real_escape_string($_POST['kategori']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $harga = floatval($_POST['harga']);
    $stok = intval($_POST['stok']);
    $status = $_POST['status'];
    
    // Handle upload foto
    $foto_name = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $foto_name = uniqid() . '_' . time() . '.' . $ext;
            
            // Buat folder uploads jika belum ada
            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }
            
            move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $foto_name);
        }
    }
    
    if ($menu_id > 0) {
        // Update
        $query = "UPDATE menu SET 
                  nama_menu = '$nama_menu',
                  kategori = '$kategori',
                  deskripsi = '$deskripsi',
                  harga = $harga,
                  stok = $stok,
                  status = '$status'";
        
        if ($foto_name) {
            // Hapus foto lama
            $old = $conn->query("SELECT foto FROM menu WHERE menu_id = $menu_id")->fetch_assoc();
            if ($old['foto'] && file_exists("uploads/" . $old['foto'])) {
                unlink("uploads/" . $old['foto']);
            }
            $query .= ", foto = '$foto_name'";
        }
        
        $query .= " WHERE menu_id = $menu_id";
        $conn->query($query);
        $_SESSION['message'] = 'Menu berhasil diupdate!';
    } else {
        // Insert
        $query = "INSERT INTO menu (nama_menu, kategori, deskripsi, harga, stok, foto, status) 
                  VALUES ('$nama_menu', '$kategori', '$deskripsi', $harga, $stok, '$foto_name', '$status')";
        $conn->query($query);
        $_SESSION['message'] = 'Menu berhasil ditambahkan!';
    }
    
    header('Location: menu_management.php');
    exit();
}

// Get search parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get all menu
$query = "SELECT * FROM menu";
if ($search) {
    $search_esc = $conn->real_escape_string($search);
    $query .= " WHERE nama_menu LIKE '%$search_esc%' OR kategori LIKE '%$search_esc%'";
}
$query .= " ORDER BY kategori, nama_menu";
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
        <form method="GET">
            <input type="text" name="search" class="search-bar" placeholder="Cari menu..." 
                   value="<?php echo htmlspecialchars($search); ?>">
        </form>
    </div>
    <button class="add-btn" onclick="openAddModal()">
        <span style="font-size: 20px;">+</span> Add New Menu
    </button>
</div>

<!-- Table -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Foto</th>
                <th>Menu Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result->num_rows > 0): ?>
                <?php while($menu = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if($menu['foto'] && file_exists("uploads/" . $menu['foto'])): ?>
                            <img src="uploads/<?php echo $menu['foto']; ?>" alt="<?php echo htmlspecialchars($menu['nama_menu']); ?>" 
                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                        <?php else: ?>
                            <div style="width: 60px; height: 60px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999;">
                                No Image
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($menu['nama_menu']); ?></strong></td>
                    <td><?php echo htmlspecialchars($menu['kategori']); ?></td>
                    <td><?php echo formatRupiah($menu['harga']); ?></td>
                    <td><?php echo $menu['stok']; ?></td>
                    <td>
                        <span class="icon-btn" onclick='editMenu(<?php echo json_encode($menu); ?>)' title="Edit">✏️</span>
                        <a href="?delete=<?php echo $menu['menu_id']; ?>" 
                           onclick="return confirm('Hapus menu ini?')" 
                           class="icon-btn" title="Delete">🗑️</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                        Tidak ada menu ditemukan
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Add/Edit Menu -->
<div id="menuModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 40px; border-radius: 12px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2 id="modalTitle" style="margin: 0;">Add New Menu</h2>
            <span onclick="closeModal()" style="font-size: 28px; cursor: pointer; color: #999;">&times;</span>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="menu_id" id="menu_id">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Menu Name *</label>
                <input type="text" name="nama_menu" id="nama_menu" required 
                       style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Category *</label>
                    <select name="kategori" id="kategori" required 
                            style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                        <option value="">Select Category</option>
                        <option value="Makanan">Makanan</option>
                        <option value="Makanan Utama">Makanan Utama</option>
                        <option value="Minuman">Minuman</option>
                        <option value="Pesanan Lainnya">Pesanan Lainnya</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Status *</label>
                    <select name="status" id="status" required 
                            style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                        <option value="tersedia">Tersedia</option>
                        <option value="habis">Habis</option>
                    </select>
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Description</label>
                <textarea name="deskripsi" id="deskripsi" rows="3" 
                          style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;"></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Price *</label>
                    <input type="number" name="harga" id="harga" required min="0" step="0.01" 
                           style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Stock</label>
                    <input type="number" name="stok" id="stok" value="0" min="0" 
                           style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Photo</label>
                <input type="file" name="foto" id="foto" accept="image/*" 
                       style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                <small style="color: #666;">Format: JPG, PNG, GIF (Max 2MB)</small>
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
                <button type="button" onclick="closeModal()" 
                        style="padding: 12px 30px; background: #e0e0e0; border: none; border-radius: 8px; font-size: 16px; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" class="add-btn">
                    Save Menu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('menuModal').style.display = 'flex';
    document.getElementById('modalTitle').textContent = 'Add New Menu';
    document.getElementById('menu_id').value = '';
    document.querySelector('form').reset();
}

function editMenu(menu) {
    document.getElementById('menuModal').style.display = 'flex';
    document.getElementById('modalTitle').textContent = 'Edit Menu';
    document.getElementById('menu_id').value = menu.menu_id;
    document.getElementById('nama_menu').value = menu.nama_menu;
    document.getElementById('kategori').value = menu.kategori;
    document.getElementById('deskripsi').value = menu.deskripsi || '';
    document.getElementById('harga').value = menu.harga;
    document.getElementById('stok').value = menu.stok;
    document.getElementById('status').value = menu.status;
}

function closeModal() {
    document.getElementById('menuModal').style.display = 'none';
}
</script>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>
