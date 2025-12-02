<?php 
require_once '../config/config.php';
$page_title = 'Pilih Layanan - Sambal Belut Bu Raden';
include 'includes/header.php';

$conn = getConnection();
$query = "SELECT * FROM layanan WHERE layanan_id IN (1, 2) ORDER BY layanan_id";
$result = $conn->query($query);

// Get pre-selected type from URL
$selected_type = isset($_GET['type']) ? $_GET['type'] : '';
?>

<div class="breadcrumb">
    <a href="index.php" style="color: #666;">Beranda</a>
    <span> > </span>
    <span style="color: #333; font-weight: 600;">Pilih Layanan</span>
</div>

<div class="container">
    <div class="content-wrapper">
        <div class="pilih-layanan-content">
            <h2>Pilih Layanan Anda</h2>
            
            <form action="isi-data-diri.php" method="POST" id="layananForm">
                <div class="layanan-options">
                    <?php
                    $icons = ['🍽️', '🥡'];
                    $i = 0;
                    while($layanan = $result->fetch_assoc()) {
                        $type_slug = strtolower(str_replace(' ', '-', $layanan['jenis_layanan']));
                        $selected = ($selected_type == $type_slug) ? 'selected' : '';
                        
                        echo '<div class="layanan-card ' . $selected . '" data-value="' . $layanan['layanan_id'] . '" onclick="selectLayanan(this)">';
                        echo '<div class="layanan-icon">' . $icons[$i] . '</div>';
                        echo '<h3>' . htmlspecialchars($layanan['jenis_layanan']) . '</h3>';
                        echo '<p>' . htmlspecialchars($layanan['deskripsi']) . '</p>';
                        echo '</div>';
                        $i++;
                    }
                    $conn->close();
                    ?>
                </div>
                
                <input type="hidden" name="layanan_id" id="layananId" value="<?php echo $selected_type ? '1' : ''; ?>" required>
                
                <button type="submit" class="btn btn-primary">Lanjutkan</button>
            </form>
        </div>
        
        <div class="order-summary-side">
            <div class="summary-card">
                <h3>Informasi Pemesanan</h3>
                <p style="font-size: 12px; color: #999;">
                    Pilih jenis layanan untuk melanjutkan pemesanan Anda.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function selectLayanan(element) {
    document.querySelectorAll('.layanan-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    element.classList.add('selected');
    document.getElementById('layananId').value = element.getAttribute('data-value');
}

// Auto select if from URL
window.addEventListener('DOMContentLoaded', function() {
    const selectedCard = document.querySelector('.layanan-card.selected');
    if (selectedCard) {
        document.getElementById('layananId').value = selectedCard.getAttribute('data-value');
    }
});

document.getElementById('layananForm').addEventListener('submit', function(e) {
    const layananId = document.getElementById('layananId').value;
    if (!layananId) {
        e.preventDefault();
        alert('Silakan pilih jenis layanan terlebih dahulu');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
