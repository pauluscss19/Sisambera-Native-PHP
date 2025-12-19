<?php
session_start();
require_once '../config/config.php';

$page_title = 'Daftar Menu - Sambal Belut Bu Raden';
$conn = getConnection();

// Get all categories
$kategori_query = "SELECT DISTINCT kategori FROM menu ORDER BY kategori";
$kategori_result = $conn->query($kategori_query);
$kategori_list = [];
while ($row = $kategori_result->fetch_assoc()) {
    $kategori_list[] = $row['kategori'];
}

// Filter by category if set
$filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'all';

// Get menu based on filter
if ($filter_kategori == 'all') {
    $menu_query = "SELECT * FROM menu ORDER BY kategori, nama_menu";
} else {
    $menu_query = "SELECT * FROM menu WHERE kategori = '" . $conn->real_escape_string($filter_kategori) . "' ORDER BY nama_menu";
}
$menu_result = $conn->query($menu_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">SISAMBERA</a>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="daftar-menu.php" style="color: #c1395d;">Menu</a>
            <a href="tentang.php">Tentang</a>
        </nav>
        <button class="order-btn" onclick="window.location.href='pilih-layanan.php'">Pesan Sekarang</button>
    </header>

    <div class="breadcrumb">
        <a href="index.php" style="color: #666;">Beranda</a>
        <span> › </span>
        <span style="color: #333; font-weight: 600;">Daftar Menu</span>
    </div>

    <div class="container">
        <div style="max-width: 1200px; margin: 0 auto;">
            <h1 class="section-title" style="text-align: center; font-size: 28px; margin-bottom: 10px;">Daftar Menu Kami</h1>
            <p style="text-align: center; color: #999; margin-bottom: 40px; font-size: 14px;">
                Jelajahi berbagai pilihan menu lezat dengan cita rasa khas Sambal Belut Bu Raden
            </p>

            <!-- Filter Buttons -->
            <div class="menu-tabs" style="justify-content: center; margin-bottom: 40px;">
                <button class="menu-tab <?php echo $filter_kategori == 'all' ? 'active' : ''; ?>" 
                        onclick="window.location.href='daftar-menu.php?kategori=all'">
                    Semua Menu
                </button>
                <?php foreach ($kategori_list as $kategori): ?>
                    <button class="menu-tab <?php echo $filter_kategori == $kategori ? 'active' : ''; ?>" 
                            onclick="window.location.href='daftar-menu.php?kategori=<?php echo urlencode($kategori); ?>'">
                        <?php echo htmlspecialchars($kategori); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Menu Grid -->
            <div class="menu-favorite-grid">
                <?php if ($menu_result->num_rows > 0): ?>
                    <?php while ($menu = $menu_result->fetch_assoc()): ?>
                        <div class="menu-favorite-card" onclick="showMenuDetail(<?php echo htmlspecialchars(json_encode($menu)); ?>)" style="cursor: pointer;">
                            <div class="menu-favorite-image">
                                <?php if (!empty($menu['foto']) && file_exists('../admin/uploads/' . $menu['foto'])): ?>
                                    <img src="../admin/uploads/<?php echo htmlspecialchars($menu['foto']); ?>" 
                                         alt="<?php echo htmlspecialchars($menu['nama_menu']); ?>"
                                         onerror="this.parentElement.innerHTML='<div class=\'no-image\'><span style=\'font-size: 60px;\'>🍽️</span></div>'">
                                <?php else: ?>
                                    <div class="no-image">
                                        <span style="font-size: 60px;">🍽️</span>
                                    </div>
                                <?php endif; ?>

                                <!-- Badge Status -->
                                <div style="position: absolute; top: 15px; right: 15px; 
                                            background: <?php echo $menu['status'] == 'tersedia' ? 'linear-gradient(135deg, #4caf50, #45a049)' : 'linear-gradient(135deg, #f44336, #d32f2f)'; ?>; 
                                            color: white; padding: 6px 12px; border-radius: 15px; 
                                            font-size: 11px; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                                    <?php echo $menu['status'] == 'tersedia' ? '✓ Tersedia' : '✕ Habis'; ?>
                                </div>

                                <!-- Badge Kategori -->
                                <div style="position: absolute; top: 15px; left: 15px; 
                                            background: rgba(93, 64, 55, 0.9); color: white; 
                                            padding: 6px 12px; border-radius: 15px; 
                                            font-size: 11px; font-weight: 600;">
                                    <?php echo htmlspecialchars($menu['kategori']); ?>
                                </div>
                            </div>
                            <div class="menu-favorite-content">
                                <h3 class="menu-favorite-title"><?php echo htmlspecialchars($menu['nama_menu']); ?></h3>
                                <p class="menu-favorite-desc">
                                    <?php 
                                        $deskripsi = !empty($menu['deskripsi']) ? $menu['deskripsi'] : 'Menu lezat dengan cita rasa khas yang menggugah selera.';
                                        echo htmlspecialchars(strlen($deskripsi) > 80 ? substr($deskripsi, 0, 80) . '...' : $deskripsi); 
                                    ?>
                                </p>
                                <div class="menu-favorite-footer">
                                    <div>
                                        <div class="menu-favorite-price">
                                            Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?>
                                        </div>
                                        <?php if ($menu['status'] == 'tersedia'): ?>
                                            <div style="font-size: 11px; color: #999; margin-top: 5px;">
                                                📦 Stok: <?php echo $menu['stok']; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <button class="btn-pesan" onclick="event.stopPropagation(); window.location.href='pilih-layanan.php'"
                                            <?php echo $menu['status'] != 'tersedia' ? 'disabled style="background: #ccc; cursor: not-allowed;"' : ''; ?>>
                                        <?php echo $menu['status'] == 'tersedia' ? 'Pesan' : 'Habis'; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                        <div style="font-size: 80px; margin-bottom: 20px;">🍽️</div>
                        <h3 style="color: #999; font-size: 24px; margin-bottom: 10px;">Menu Tidak Ditemukan</h3>
                        <p style="color: #ccc; margin-bottom: 20px;">Tidak ada menu yang tersedia untuk kategori ini.</p>
                        <button class="btn-pesan" onclick="window.location.href='daftar-menu.php?kategori=all'">
                            ← Lihat Semua Menu
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Detail Menu -->
    <div id="menuDetailModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; 
                                      background: rgba(0, 0, 0, 0.7); z-index: 1000; padding: 20px; 
                                      overflow-y: auto; backdrop-filter: blur(5px);" onclick="closeModal(event)">
        <div id="modalContent" style="max-width: 900px; margin: 50px auto; background: white; 
                                      border-radius: 20px; overflow: hidden; 
                                      box-shadow: 0 20px 60px rgba(0,0,0,0.3);" onclick="event.stopPropagation()">
            <!-- Content will be inserted by JavaScript -->
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4>SISAMBERA</h4>
                <p>Sambal Belut Bu Raden</p>
                <p>Cita rasa khas yang menggugah selera</p>
            </div>
            <div class="footer-section">
                <h4>Navigasi</h4>
                <p><a href="index.php">Beranda</a></p>
                <p><a href="daftar-menu.php">Menu</a></p>
                <p><a href="tentang.php">Tentang</a></p>
            </div>
            <div class="footer-section">
                <h4>Kontak</h4>
                <p>Telp: (021) 12345678</p>
                <p>Email: info@sisambera.com</p>
            </div>
            <div class="footer-section">
                <h4>Ikuti Kami</h4>
                <div class="social-links">
                    <a href="#">📘</a>
                    <a href="#">📷</a>
                    <a href="#">🐦</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function showMenuDetail(menu) {
            const modal = document.getElementById('menuDetailModal');
            const modalContent = document.getElementById('modalContent');

            const foto = menu.foto ? `../admin/uploads/${menu.foto}` : '';
            const statusBadge = menu.status === 'tersedia' 
                ? '<span style="background: linear-gradient(135deg, #4caf50, #45a049); color: white; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;">✓ Tersedia</span>'
                : '<span style="background: linear-gradient(135deg, #f44336, #d32f2f); color: white; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;">✕ Habis</span>';

            const deskripsi = menu.deskripsi || 'Menu lezat dengan cita rasa khas yang menggugah selera. Dibuat dengan bahan berkualitas dan resep spesial.';

            modalContent.innerHTML = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0;">
                    <!-- Gambar -->
                    <div style="height: 500px; background: #f5f5f5; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        ${foto ? 
                            `<img src="${foto}" alt="${menu.nama_menu}" 
                                  style="width: 100%; height: 100%; object-fit: cover;"
                                  onerror="this.parentElement.innerHTML='<span style=\'font-size: 100px;\'>🍽️</span>'">` 
                            : '<span style="font-size: 100px;">🍽️</span>'
                        }
                    </div>

                    <!-- Info -->
                    <div style="padding: 40px;">
                        <!-- Close Button -->
                        <button onclick="closeModal()" 
                                style="position: absolute; right: 20px; top: 20px; 
                                       background: #f5f5f5; border: none; width: 40px; height: 40px; 
                                       border-radius: 50%; cursor: pointer; font-size: 20px; 
                                       transition: all 0.3s; display: flex; align-items: center; 
                                       justify-content: center; color: #666;"
                                onmouseover="this.style.background='#e0e0e0'; this.style.transform='rotate(90deg)'"
                                onmouseout="this.style.background='#f5f5f5'; this.style.transform='rotate(0)'">
                            ✕
                        </button>

                        <!-- Kategori Badge -->
                        <div style="display: inline-block; background: #5d4037; color: white; 
                                    padding: 6px 15px; border-radius: 15px; font-size: 12px; 
                                    font-weight: 600; margin-bottom: 15px;">
                            ${menu.kategori}
                        </div>

                        <!-- Nama Menu -->
                        <h2 style="font-size: 32px; font-weight: 700; color: #333; 
                                   margin-bottom: 15px; line-height: 1.2;">
                            ${menu.nama_menu}
                        </h2>

                        <!-- Status -->
                        <div style="margin-bottom: 20px;">
                            ${statusBadge}
                        </div>

                        <!-- Deskripsi -->
                        <div style="border-top: 2px solid #f0f0f0; padding-top: 20px; margin-bottom: 25px;">
                            <h4 style="font-size: 14px; color: #999; margin-bottom: 10px; 
                                       font-weight: 600; text-transform: uppercase; 
                                       letter-spacing: 1px;">Deskripsi</h4>
                            <p style="color: #666; line-height: 1.8; font-size: 14px;">
                                ${deskripsi}
                            </p>
                        </div>

                        <!-- Info Detail -->
                        <div style="background: #f8f8f5; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                                <span style="color: #999; font-size: 13px;">Harga</span>
                                <span style="font-size: 24px; font-weight: 700; color: #c93545;">
                                    Rp ${parseInt(menu.harga).toLocaleString('id-ID')}
                                </span>
                            </div>
                            ${menu.status === 'tersedia' ? `
                                <div style="display: flex; justify-content: space-between; 
                                           padding-top: 15px; border-top: 1px solid #e8e8e8;">
                                    <span style="color: #999; font-size: 13px;">Stok Tersedia</span>
                                    <span style="font-weight: 600; color: #333; font-size: 14px;">
                                        📦 ${menu.stok} porsi
                                    </span>
                                </div>
                            ` : ''}
                        </div>

                        <!-- Action Buttons -->
                        <div style="display: grid; grid-template-columns: 1fr; gap: 12px;">
                            ${menu.status === 'tersedia' ? `
                                <button onclick="window.location.href='pilih-layanan.php'" 
                                        class="btn-pesan" 
                                        style="width: 100%; padding: 15px; font-size: 15px; 
                                               border-radius: 12px;">
                                    🛒 Pesan Sekarang
                                </button>
                            ` : `
                                <button disabled 
                                        style="width: 100%; padding: 15px; font-size: 15px; 
                                               border-radius: 12px; background: #ccc; 
                                               color: white; border: none; cursor: not-allowed;">
                                    Menu Sedang Habis
                                </button>
                            `}
                            <button onclick="closeModal()" 
                                    style="width: 100%; padding: 15px; font-size: 14px; 
                                           background: transparent; color: #666; 
                                           border: 2px solid #e0e0e0; border-radius: 12px; 
                                           cursor: pointer; font-weight: 600; 
                                           transition: all 0.3s;"
                                    onmouseover="this.style.borderColor='#c1395d'; this.style.color='#c1395d'"
                                    onmouseout="this.style.borderColor='#e0e0e0'; this.style.color='#666'">
                                Kembali
                            </button>
                        </div>
                    </div>
                </div>
            `;

            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeModal(event) {
            if (!event || event.target.id === 'menuDetailModal') {
                document.getElementById('menuDetailModal').style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // Close modal with ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>