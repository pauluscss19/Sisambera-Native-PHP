<?php 
require_once '../config/config.php';
$page_title = 'Isi Data Diri - Sambal Belut Bu Raden';
include 'includes/header.php';

// Check if layanan is set
if (!isset($_POST['layanan_id']) && !isset($_SESSION['layanan_id'])) {
    header('Location: pilih-layanan.php');
    exit();
}

// Save to session
if (isset($_POST['layanan_id'])) {
    $_SESSION['layanan_id'] = $_POST['layanan_id'];
}

$conn = getConnection();
$layanan_id = $_SESSION['layanan_id'];
$query = "SELECT * FROM layanan WHERE layanan_id = $layanan_id";
$result = $conn->query($query);
$layanan = $result->fetch_assoc();
$conn->close();
?>

<div class="breadcrumb">
    <a href="index.php" style="color: #666;">Beranda</a>
    <span> > </span>
    <a href="pilih-layanan.php" style="color: #666;">Pilih Layanan</a>
    <span> > </span>
    <span style="color: #333; font-weight: 600;">Isi Data Diri</span>
</div>

<div class="container">
    <div class="content-wrapper">
        <div class="data-diri-content">
            <h2>Isi Data Diri</h2>
            
            <form action="pilih-menu.php" method="POST" id="dataDiriForm">
                <div class="form-group">
                    <label for="nama">Nama Lengkap <span style="color: red;">*</span></label>
                    <input type="text" id="nama" name="nama" required 
                           placeholder="Masukkan nama lengkap Anda"
                           value="<?php echo isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="no_hp">Nomor Telepon <span style="color: red;">*</span></label>
                    <input type="tel" id="no_hp" name="no_hp" required 
                           placeholder="Contoh: 081234567890"
                           value="<?php echo isset($_SESSION['no_hp']) ? htmlspecialchars($_SESSION['no_hp']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="catatan">Catatan Khusus (Opsional)</label>
                    <textarea id="catatan" name="catatan" 
                              placeholder="Tambahkan catatan untuk pesanan Anda"><?php echo isset($_SESSION['catatan']) ? htmlspecialchars($_SESSION['catatan']) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Lanjutkan ke Menu</button>
            </form>
        </div>
        
        <div class="order-summary-side">
            <div class="summary-card">
                <h3>Informasi Pemesanan</h3>
                <div class="order-info-card">
                    <div class="detail-row">
                        <span>Jenis Layanan:</span>
                        <strong><?php echo htmlspecialchars($layanan['jenis_layanan']); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('dataDiriForm').addEventListener('submit', function(e) {
    const nama = document.getElementById('nama').value.trim();
    const no_hp = document.getElementById('no_hp').value.trim();
    
    if (!nama || !no_hp) {
        e.preventDefault();
        alert('Nama dan nomor telepon harus diisi');
        return;
    }
    
    const phoneRegex = /^[0-9]{10,13}$/;
    if (!phoneRegex.test(no_hp)) {
        e.preventDefault();
        alert('Format nomor telepon tidak valid. Gunakan 10-13 digit angka.');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
