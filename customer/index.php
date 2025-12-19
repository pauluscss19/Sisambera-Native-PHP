<?php
require_once '../config/config.php';
$page_title = 'Beranda - Sambal Belut Bu Raden';
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <h1>Pesan makanan Anda sekarang dan rasakan kenikmatan Sambal Belut Bu Raden.</h1>
    <p>Nikmati Suasana Premium Kami</p>
    <div class="hero-buttons">
        <button class="btn-hero primary" onclick="window.location.href='pilih-layanan.php'">Pesan Sekarang</button>
        <button class="btn-hero secondary" onclick="window.location.href='daftar-menu.php'">Lihat Menu</button>
    </div>
</section>

<!-- Service Section -->
<section class="service-section">
    <div>
        <h2 class="section-title">Pilih Layanan Anda</h2>
        <div class="service-grid">
            <?php
            $conn = getConnection();
            $query = "SELECT * FROM layanan WHERE layanan_id IN (1, 2) ORDER BY layanan_id";
            $result = $conn->query($query);

            $icons = ['🍽️', '🥡', '🚗'];
            $i = 0;
            while ($layanan = $result->fetch_assoc()) {
                $type = strtolower(str_replace(' ', '-', $layanan['jenis_layanan']));
                echo '<div class="service-card" onclick="window.location.href=\'pilih-layanan.php?type=' . $type . '\'">';
                echo '<div class="service-icon">' . $icons[$i] . '</div>';
                echo '<h3>' . htmlspecialchars($layanan['jenis_layanan']) . '</h3>';
                echo '<p>' . htmlspecialchars($layanan['deskripsi']) . '</p>';
                echo '</div>';
                $i++;
            }
            ?>
        </div>
    </div>
</section>

<!-- Favorites Section -->
<section class="favorites-section">
    <div class="container">
        <h2 class="section-title" style="text-align: center; margin-bottom: 10px;">Cicipi Menu Favorit Kami</h2>
        <p style="text-align: center; color: #666; margin-bottom: 40px;">Menu paling banyak dipesan pelanggan kami</p>

        <div class="menu-favorite-grid">
            <?php
            // Query untuk mendapatkan menu yang paling banyak dipesan
            $query = "SELECT m.*, 
                      COALESCE(SUM(dp.jumlah), 0) as total_terjual,
                      COUNT(DISTINCT dp.pesanan_id) as jumlah_pesanan
                      FROM menu m
                      LEFT JOIN detail_pesanan dp ON m.menu_id = dp.menu_id
                      LEFT JOIN pesanan p ON dp.pesanan_id = p.pesanan_id
                      WHERE m.status = 'tersedia' 
                      AND (p.status IS NULL OR p.status != 'dibatalkan')
                      GROUP BY m.menu_id
                      ORDER BY total_terjual DESC, jumlah_pesanan DESC
                      LIMIT 3";
            $result = $conn->query($query);

            if ($result && $result->num_rows > 0) {
                while ($menu = $result->fetch_assoc()) {
            ?>
                    <div class="menu-favorite-card">
                        <div class="menu-favorite-image">
                            <?php if ($menu['foto'] && file_exists('../admin/uploads/' . $menu['foto'])): ?>
                                <img src="../admin/uploads/<?php echo htmlspecialchars($menu['foto']); ?>"
                                    alt="<?php echo htmlspecialchars($menu['nama_menu']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <span style="font-size: 60px;">🍽️</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="menu-favorite-content">
                            <h3 class="menu-favorite-title"><?php echo htmlspecialchars($menu['nama_menu']); ?></h3>
                            <p class="menu-favorite-desc">
                                <?php echo htmlspecialchars($menu['deskripsi'] ?: 'Enak dan gurih'); ?>
                            </p>

                            <div class="menu-favorite-footer">
                                <div class="menu-favorite-price"><?php echo formatRupiah($menu['harga']); ?></div>
                                <button class="btn-pesan" onclick="window.location.href='pilih-layanan.php'">
                                    Pesan
                                </button>
                            </div>
                        </div>
                    </div>
                <?php
                }
            } else {
                // Jika belum ada pesanan, tampilkan menu default
                $query_default = "SELECT * FROM menu WHERE status = 'tersedia' ORDER BY menu_id LIMIT 3";
                $result_default = $conn->query($query_default);

                while ($menu = $result_default->fetch_assoc()) {
                ?>
                    <div class="menu-favorite-card">
                        <div class="menu-favorite-image">
                            <?php if ($menu['foto'] && file_exists('../admin/uploads/' . $menu['foto'])): ?>
                                <img src="../admin/uploads/<?php echo htmlspecialchars($menu['foto']); ?>"
                                    alt="<?php echo htmlspecialchars($menu['nama_menu']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <span style="font-size: 60px;">🍽️</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="menu-favorite-content">
                            <h3 class="menu-favorite-title"><?php echo htmlspecialchars($menu['nama_menu']); ?></h3>
                            <p class="menu-favorite-desc">
                                <?php echo htmlspecialchars($menu['deskripsi'] ?: 'Enak dan gurih'); ?>
                            </p>

                            <div class="menu-favorite-footer">
                                <div class="menu-favorite-price"><?php echo formatRupiah($menu['harga']); ?></div>
                                <button class="btn-pesan" onclick="window.location.href='pilih-layanan.php'">
                                    Pesan
                                </button>
                            </div>
                        </div>
                    </div>
            <?php
                }
            }

            $conn->close();
            ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>