<?php 
require_once '../config/config.php';
$page_title = 'Pilih Menu - Sambal Belut Bu Raden';

// Error message handling
$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Redirect jika belum pilih layanan
if (!isset($_SESSION['layanan_id']) || !isset($_SESSION['nama'])) {
    header('Location: pilih-layanan.php');
    exit();
}

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

include 'includes/header.php';

$conn = getConnection();

// Get categories
$kategori_list = [];
$query = "SELECT DISTINCT kategori FROM menu ORDER BY kategori";
$result = $conn->query($query);
while($row = $result->fetch_assoc()) {
    $kategori_list[] = $row['kategori'];
}

// Get all menu
$menu_query = "SELECT * FROM menu ORDER BY kategori, nama_menu";
$menu_result = $conn->query($menu_query);

$menu_by_kategori = [];
while($menu = $menu_result->fetch_assoc()) {
    $menu_by_kategori[$menu['kategori']][] = $menu;
}

// Calculate totals
$subtotal = 0;
foreach($_SESSION['cart'] as $item) {
    $subtotal += $item['harga'] * $item['qty'];
}
$pajak = $subtotal * 0.1;
$total = $subtotal + $pajak;
?>

<!-- Error Alert -->
<?php if($error_message): ?>
<div style="background: #ffebee; color: #c62828; padding: 15px 20px; border-radius: 8px; margin: 20px 30px; font-weight: 500; border-left: 4px solid #c62828;">
    ⚠️ <?php echo htmlspecialchars($error_message); ?>
</div>
<?php endif; ?>

<div class="container">
    <div class="content-wrapper">
        <!-- Main Content -->
        <div class="menu-content">
            <h2>Pilih Menu</h2>
            
            <!-- Category Tabs -->
            <div class="menu-tabs">
                <button class="menu-tab active" onclick="filterKategori('all', this)">Semua</button>
                <?php foreach($kategori_list as $kat): ?>
                <button class="menu-tab" onclick="filterKategori('<?php echo htmlspecialchars($kat); ?>', this)">
                    <?php echo htmlspecialchars($kat); ?>
                </button>
                <?php endforeach; ?>
            </div>
            
            <!-- Menu Grid by Category -->
            <?php 
            $kategori_icons = [
                'Makanan' => '🍛',
                'Makanan Utama' => '🍽️',
                'Minuman' => '🥤',
                'Pesanan Lainnya' => '🍰'
            ];
            
            foreach($menu_by_kategori as $kategori => $menus): 
            ?>
            <div class="kategori-section" data-kategori="<?php echo htmlspecialchars($kategori); ?>">
                <h3 style="font-size: 18px; margin: 25px 0 15px; color: #333; font-weight: 600;">
                    <?php echo $kategori_icons[$kategori] ?? '🍴'; ?> <?php echo htmlspecialchars($kategori); ?>
                </h3>
                
                <div class="menu-grid">
                    <?php foreach($menus as $menu): ?>
                        <?php 
                        $is_available = $menu['status'] == 'tersedia' && $menu['stok'] > 0;
                        $is_low_stock = $menu['stok'] > 0 && $menu['stok'] <= 5;
                        ?>
                        <div class="menu-card" style="<?php echo !$is_available ? 'opacity: 0.5; pointer-events: none;' : ''; ?>">
                            <div class="menu-image" style="position: relative;">
                                <?php if($menu['foto'] && file_exists('../admin/uploads/' . $menu['foto'])): ?>
                                    <img src="../admin/uploads/<?php echo htmlspecialchars($menu['foto']); ?>" 
                                         alt="<?php echo htmlspecialchars($menu['nama_menu']); ?>">
                                <?php else: ?>
                                    <?php echo $kategori_icons[$kategori] ?? '🍴'; ?>
                                <?php endif; ?>
                                
                                <!-- Stock Badge -->
                                <?php if(!$is_available): ?>
                                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 14px;">
                                        Habis
                                    </div>
                                <?php elseif($is_low_stock): ?>
                                    <div style="position: absolute; top: 8px; right: 8px; background: #ff9800; color: white; padding: 4px 10px; border-radius: 12px; font-size: 10px; font-weight: 700;">
                                        Stok <?php echo $menu['stok']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="menu-info">
                                <div class="menu-name"><?php echo htmlspecialchars($menu['nama_menu']); ?></div>
                                <div class="menu-desc"><?php echo htmlspecialchars($menu['deskripsi'] ?: 'Enak dan gurih'); ?></div>
                                
                                <div class="menu-footer">
                                    <div class="menu-price"><?php echo formatRupiah($menu['harga']); ?></div>
                                    
                                    <?php if($is_available): ?>
                                        <button class="menu-add" onclick="addToCart(<?php echo $menu['menu_id']; ?>, '<?php echo addslashes($menu['nama_menu']); ?>', <?php echo $menu['harga']; ?>, <?php echo $menu['stok']; ?>)">
                                            + Tambah
                                        </button>
                                    <?php else: ?>
                                        <button class="menu-add" disabled style="background: #ccc; cursor: not-allowed;">Habis</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Sidebar Summary -->
        <aside class="order-summary-side">
            <div class="summary-card">
                <h3>🛒 Keranjang Belanja</h3>
                
                <?php if(empty($_SESSION['cart'])): ?>
                    <p style="text-align: center; color: #999; padding: 30px 0; font-size: 13px;">
                        Belum ada item
                    </p>
                <?php else: ?>
                    <?php foreach($_SESSION['cart'] as $index => $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <span><?php echo htmlspecialchars($item['nama_menu']); ?></span>
                            <span class="cart-qty"><?php echo $item['qty']; ?>x</span>
                        </div>
                        <div class="cart-item-price"><?php echo formatRupiah($item['harga'] * $item['qty']); ?></div>
                        <div class="cart-item-actions">
                            <button onclick="updateQty(<?php echo $index; ?>, -1)" title="Kurangi">−</button>
                            <button onclick="updateQty(<?php echo $index; ?>, 1)" title="Tambah">+</button>
                            <button class="btn-delete" onclick="removeItem(<?php echo $index; ?>)" title="Hapus">🗑️</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="summary-subtotal">
                        <span>Subtotal</span>
                        <span><?php echo formatRupiah($subtotal); ?></span>
                    </div>
                    <div class="summary-fee">
                        <span>Pajak (10%)</span>
                        <span><?php echo formatRupiah($pajak); ?></span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span><?php echo formatRupiah($total); ?></span>
                    </div>
                    
                    <button class="btn-primary" onclick="window.location.href='metode-pembayaran.php'">
                        Lanjut Pembayaran
                    </button>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>

<script>
function filterKategori(kategori, btn) {
    // Update active tab
    const tabs = document.querySelectorAll('.menu-tab');
    tabs.forEach(tab => tab.classList.remove('active'));
    btn.classList.add('active');
    
    // Show/hide sections
    const sections = document.querySelectorAll('.kategori-section');
    sections.forEach(section => {
        if (kategori === 'all' || section.dataset.kategori === kategori) {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });
}

function addToCart(menuId, namaMenu, harga, maxStok) {
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `menu_id=${menuId}&nama_menu=${encodeURIComponent(namaMenu)}&harga=${harga}&qty=1&max_stok=${maxStok}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotif('✅ Ditambahkan ke keranjang');
            setTimeout(() => location.reload(), 500);
        } else {
            showNotif('❌ ' + (data.message || 'Gagal'), 'error');
        }
    })
    .catch(() => showNotif('❌ Terjadi kesalahan', 'error'));
}

function updateQty(index, change) {
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=update&index=${index}&change=${change}`
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); });
}

function removeItem(index) {
    if (confirm('Hapus item ini dari keranjang?')) {
        fetch('ajax/delete_cart.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `index=${index}`
        })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); });
    }
}

function showNotif(msg, type = 'success') {
    const n = document.createElement('div');
    n.textContent = msg;
    n.style.cssText = `position:fixed;top:20px;right:20px;z-index:9999;background:${type==='success'?'#4caf50':'#f44336'};color:white;padding:15px 25px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.2);animation:slideIn 0.3s ease`;
    document.body.appendChild(n);
    setTimeout(() => n.remove(), 3000);
}
</script>

<style>
@keyframes slideIn {
    from { transform: translateX(400px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.cart-item {
    border-bottom: 1px solid #f0f0f0 !important;
    padding: 10px 0 !important;
}

.cart-item-actions button {
    background: #f5f5f5;
    border: 1px solid #e0e0e0;
    padding: 4px 10px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s;
}

.cart-item-actions button:hover {
    background: #e8e8e8;
}
</style>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>
