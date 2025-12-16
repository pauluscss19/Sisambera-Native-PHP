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
$query = "SELECT p.*, 
                 u.nama as customer_name, 
                 u.no_hp, 
                 l.jenis_layanan, 
                 pm.metode, 
                 pm.bank, 
                 pm.status as payment_status, 
                 pm.bukti_bayar, 
                 pm.tanggal_bayar
          FROM pesanan p 
          LEFT JOIN user u ON p.user_id = u.user_id
          LEFT JOIN layanan l ON p.layanan_id = l.layanan_id
          LEFT JOIN pembayaran pm ON p.pesanan_id = pm.pesanan_id
          WHERE p.pesanan_id = $pesanan_id";

$result = $conn->query($query);

if (!$result) {
    die("Database Error: " . $conn->error);
}

if ($result->num_rows == 0) {
    echo "
    <div style='padding: 40px; text-align: center;'>
        <h2 style='color: #f44336;'>⚠️ Pesanan Tidak Ditemukan</h2>
        <p style='color: #999; margin: 20px 0;'>ID Pesanan: $pesanan_id</p>
        <a href='order_management.php' style='color: #5d3a3a; text-decoration: none; font-weight: 600;'>← Kembali ke Order Management</a>
    </div>
    ";
    exit();
}

$order = $result->fetch_assoc();

if (!$order) {
    header('Location: order_management.php');
    exit();
}

// Get order items
$query_items = "SELECT dp.*, m.nama_menu, m.foto 
                FROM detail_pesanan dp
                LEFT JOIN menu m ON dp.menu_id = m.menu_id
                WHERE dp.pesanan_id = $pesanan_id";

$items_result = $conn->query($query_items);

if (!$items_result) {
    die("Database Error: " . $conn->error);
}

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
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
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
        flex-wrap: wrap;
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

    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
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

    .upload-tab {
        padding: 12px 20px;
        border: none;
        background: transparent;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: #999;
        transition: all 0.3s;
        position: relative;
    }

    .upload-tab.active {
        color: #333;
    }

    .upload-tab.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: #5d3a3a;
    }

    .camera-btn {
        padding: 10px 16px;
        border: 1px solid #e0e0e0;
        background: #f5f5f5;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .camera-btn:hover {
        background: #eeeeee;
        border-color: #ccc;
    }

    .camera-btn.capture {
        background: #4caf50 !important;
        color: white !important;
        border-color: #4caf50 !important;
    }

    .camera-btn.capture:hover {
        background: #45a049 !important;
    }

    #dropZone {
        user-select: none;
    }

    #dropZone:hover {
        background: #f0f0f0;
        border-color: #5d3a3a;
    }

    #dropZone.dragover {
        background: #f5f5f5;
        border-color: #5d3a3a;
        box-shadow: 0 0 0 3px rgba(93, 58, 58, 0.1);
    }
</style>

<div class="detail-container">
    <!-- Header Info -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 12px; margin-bottom: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="font-size: 24px; margin-bottom: 8px;">Pesanan #<?php echo htmlspecialchars($order['nomor_antrian']); ?></h2>
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
                    <span class="info-value"><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">No. HP</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['no_hp'] ?? '-'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Layanan</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['jenis_layanan'] ?? '-'); ?></span>
                </div>
            </div>

            <!-- Order Items -->
            <div class="detail-card" style="margin-top: 25px;">
                <h3>🍽️ Detail Pesanan</h3>
                <?php
                if ($items_result && $items_result->num_rows > 0) {
                    while ($item = $items_result->fetch_assoc()):
                ?>
                        <div class="order-item">
                            <div class="item-image">
                                <?php if ($item['foto'] && file_exists('uploads/' . $item['foto'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($item['foto']); ?>"
                                        alt="<?php echo htmlspecialchars($item['nama_menu']); ?>"
                                        style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    🍴
                                <?php endif; ?>
                            </div>
                            <div class="item-info">
                                <div class="item-name"><?php echo htmlspecialchars($item['nama_menu'] ?? 'Menu'); ?></div>
                                <div class="item-qty"><?php echo $item['jumlah']; ?>x @ <?php echo formatRupiah($item['harga_satuan']); ?></div>
                            </div>
                            <div class="item-price">
                                <?php echo formatRupiah($item['subtotal']); ?>
                            </div>
                        </div>
                <?php
                    endwhile;
                } else {
                    echo '<p style="color: #999; text-align: center; padding: 20px;">Tidak ada item</p>';
                }
                ?>

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
                        $metode = strtoupper($order['metode'] ?? 'UNKNOWN');
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

                <?php if ($order['bank']): ?>
                    <div class="info-row">
                        <span class="info-label">Bank</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['bank']); ?></span>
                    </div>
                <?php endif; ?>

                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <?php
                        $payment_status = $order['payment_status'] ?? 'pending';
                        if ($payment_status == 'berhasil') {
                            echo '<span style="color: #4caf50;">✓ Berhasil</span>';
                        } elseif ($payment_status == 'pending') {
                            echo '<span style="color: #ff9800;">⏳ Pending</span>';
                        } else {
                            echo '<span style="color: #f44336;">✗ Gagal</span>';
                        }
                        ?>
                    </span>
                </div>

                <?php if ($order['tanggal_bayar']): ?>
                    <div class="info-row">
                        <span class="info-label">Tanggal Bayar</span>
                        <span class="info-value"><?php echo date('d M Y, H:i', strtotime($order['tanggal_bayar'])); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Payment Proof -->
            <div class="detail-card" style="margin-top: 25px;">
                <h3>📄 Bukti Pembayaran Terpilih</h3>

                <?php
                $bukti = $order['bukti_bayar'] ?? '';

                $BUKTI_DIR_FS = realpath(__DIR__ . '/../uploads/bukti_bayar'); // PATH fisik di server
                $BUKTI_URL    = '../uploads/bukti_bayar/';                    // URL relatif dari admin/

                $ext = strtolower(pathinfo($bukti, PATHINFO_EXTENSION));
                $filePath = ($BUKTI_DIR_FS && $bukti) ? ($BUKTI_DIR_FS . DIRECTORY_SEPARATOR . $bukti) : '';
                $fileUrl  = $bukti ? ($BUKTI_URL . rawurlencode($bukti)) : '';
                ?>

                <?php if ($bukti && $filePath && file_exists($filePath)) : ?>
                    <div class="payment-proof">
                        <?php if ($ext === 'pdf') : ?>
                            <div style="padding:16px;background:#f9f9f9;border-radius:8px;border:1px solid #eee;">
                                📄 Bukti pembayaran (PDF): <strong><?= htmlspecialchars($bukti) ?></strong>
                            </div>
                            <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-back"
                                style="margin-top:10px;display:inline-block;">👁️ Lihat PDF</a>
                            <a href="<?= $fileUrl ?>" download class="btn btn-back"
                                style="margin-top:10px;display:inline-block;">📥 Download Bukti</a>
                        <?php else: ?>
                            <img src="<?= $fileUrl ?>" alt="Bukti Pembayaran" onclick="openModal(this.src)">
                            <p style="margin-top:10px;font-size:12px;color:#666;">Klik gambar untuk memperbesar</p>
                            <a href="<?= $fileUrl ?>" download class="btn btn-back"
                                style="margin-top:10px;display:inline-block;">📥 Download Bukti</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="no-proof">❌ Belum ada bukti pembayaran</div>
                <?php endif; ?>

            </div>

            <!-- Upload Bukti Section -->
            <div class="detail-card" style="margin-top: 25px;">
                <h3>📎 Upload Bukti Pembayaran</h3>

                <?php
                $metode_pembayaran = isset($order['metode']) ? $order['metode'] : 'cash';
                if ($metode_pembayaran != 'cash'):
                ?>
                    <div style="margin-bottom: 20px;">
                        <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
                            Upload bukti pembayaran untuk verifikasi pesanan
                        </p>



                        <!-- CAMERA MODE -->
                        <div id="cameraMode" class="upload-mode">
                            <div style="border: 2px solid #e0e0e0; border-radius: 12px; overflow: hidden; margin-bottom: 15px;">
                                <video id="videoPreview"
                                    style="width: 100%; height: 400px; background: #000; display: none; object-fit: cover;"></video>

                                <canvas id="canvasPreview"
                                    style="width: 100%; height: 400px; display: none; border-radius: 8px;"></canvas>

                                <div id="noCameraMsg" style="width: 100%; height: 400px; background: #f5f5f5; display: flex; align-items: center; justify-content: center; flex-direction: column; color: #999;">
                                    <p style="font-size: 48px; margin-bottom: 15px;">📷</p>
                                    <p>Kamera tidak tersedia atau tidak diizinkan</p>
                                    <small style="margin-top: 10px; color: #bbb;">Gunakan mode "Upload File" sebagai alternatif</small>
                                </div>
                            </div>

                            <!-- Camera Controls -->
                            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
                                <button type="button" id="startCameraBtn" class="camera-btn" onclick="startCamera()">
                                    ▶️ Nyalakan Kamera
                                </button>
                                <button type="button" id="stopCameraBtn" class="camera-btn" onclick="stopCamera()" style="display: none;">
                                    ⏹️ Matikan Kamera
                                </button>
                                <button type="button" id="captureBtn" class="camera-btn capture" onclick="capturePhoto()" style="display: none; background: #4caf50; color: white;">
                                    📸 Ambil Foto
                                </button>
                                <button type="button" id="retakeCameraBtn" class="camera-btn" onclick="retakePhoto()" style="display: none; background: #ff9800; color: white;">
                                    🔄 Ambil Ulang
                                </button>
                            </div>
                        </div>



                        <!-- Upload Button -->
                        <div style="margin-top: 15px; display: flex; gap: 10px;">
                            <button type="button" id="uploadBtn" class="btn"
                                onclick="submitUpload()"
                                style="background: #5d3a3a; color: white; width: 100%; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                                ✓ Upload Bukti
                            </button>
                        </div>

                        <div id="uploadStatus" style="margin-top: 15px; font-size: 12px; text-align: center;"></div>
                    </div>
                <?php else: ?>
                    <p style="color: #999; font-size: 13px; text-align: center; padding: 20px;">
                        Metode pembayaran: CASH - Tidak perlu upload bukti
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="order_management.php" class="btn btn-back">← Kembali</a>

        <?php if ($order['status'] == 'pending'): ?>
            <a href="order_management.php?action=confirm&id=<?php echo $pesanan_id; ?>"
                onclick="return confirm('Konfirmasi pesanan ini?')"
                class="btn btn-confirm">✓ Konfirmasi Pesanan</a>
            <a href="order_management.php?action=cancel&id=<?php echo $pesanan_id; ?>"
                onclick="return confirm('Batalkan pesanan ini?')"
                class="btn btn-cancel">✗ Batalkan Pesanan</a>
        <?php endif; ?>

        <?php if ($order['status'] == 'dikonfirmasi'): ?>
            <a href="order_management.php?action=process&id=<?php echo $pesanan_id; ?>"
                class="btn btn-process">🔄 Proses Pesanan</a>
        <?php endif; ?>

        <?php if ($order['status'] == 'diproses'): ?>
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
    let currentUploadMode = 'camera';
    let stream = null;
    let capturedImage = null;

    // Switch between camera and file upload
    function switchUploadMode(mode, btn) {
        currentUploadMode = mode;

        // Update tab styling
        document.querySelectorAll('.upload-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');

        // Show/hide modes
        document.getElementById('cameraMode').style.display = mode === 'camera' ? 'block' : 'none';

        // Stop camera jika switch ke file
        if (mode === 'file') {
            stopCamera();
        }
    }

    // ===== CAMERA FUNCTIONS =====
    function startCamera() {
        const video = document.getElementById('videoPreview');
        const noCameraMsg = document.getElementById('noCameraMsg');

        console.log('Starting camera...');

        // Cek apakah browser support getUserMedia
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            console.error('getUserMedia not supported');
            noCameraMsg.style.display = 'flex';
            alert('Browser Anda tidak support akses kamera');
            return;
        }

        const constraints = {
            video: {
                width: {
                    ideal: 1280
                },
                height: {
                    ideal: 720
                }
            },
            audio: false
        };

        navigator.mediaDevices.getUserMedia(constraints)
            .then(function(s) {
                console.log('Camera stream obtained:', s);
                stream = s;
                video.srcObject = stream;

                // Tunggu sampai video ready
                video.onloadedmetadata = function() {
                    console.log('Video metadata loaded');
                    video.play().then(() => {
                        console.log('Video playing');
                        video.style.display = 'block';
                        noCameraMsg.style.display = 'none';

                        document.getElementById('startCameraBtn').style.display = 'none';
                        document.getElementById('stopCameraBtn').style.display = 'inline-block';
                        document.getElementById('captureBtn').style.display = 'inline-block';
                    }).catch(err => {
                        console.error('Play error:', err);
                        noCameraMsg.style.display = 'flex';
                        alert('Error: ' + err.message);
                    });
                };

                video.onerror = function(err) {
                    console.error('Video error:', err);
                    noCameraMsg.style.display = 'flex';
                };
            })
            .catch(function(err) {
                console.error('Camera error:', err);
                noCameraMsg.style.display = 'flex';
                alert('Tidak bisa mengakses kamera:\n' + err.name + ': ' + err.message);
            });
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }

        document.getElementById('videoPreview').style.display = 'none';
        document.getElementById('startCameraBtn').style.display = 'inline-block';
        document.getElementById('stopCameraBtn').style.display = 'none';
        document.getElementById('captureBtn').style.display = 'none';
        document.getElementById('retakeCameraBtn').style.display = 'none';
    }

    function capturePhoto() {
        const video = document.getElementById('videoPreview');
        const canvas = document.getElementById('canvasPreview');
        const ctx = canvas.getContext('2d');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        ctx.drawImage(video, 0, 0);

        canvas.toBlob(function(blob) {
            capturedImage = blob;

            video.style.display = 'none';
            canvas.style.display = 'block';

            document.getElementById('stopCameraBtn').style.display = 'none';
            document.getElementById('captureBtn').style.display = 'none';
            document.getElementById('retakeCameraBtn').style.display = 'inline-block';
        }, 'image/jpeg', 0.9);
    }

    function retakePhoto() {
        const video = document.getElementById('videoPreview');
        const canvas = document.getElementById('canvasPreview');

        capturedImage = null;
        video.style.display = 'block';
        canvas.style.display = 'none';

        document.getElementById('stopCameraBtn').style.display = 'inline-block';
        document.getElementById('captureBtn').style.display = 'inline-block';
        document.getElementById('retakeCameraBtn').style.display = 'none';
    }

    // ===== FILE UPLOAD FUNCTIONS =====
    function previewFile() {
        const file = document.getElementById('bukti_bayar').files[0];
        if (!file) return;

        const preview = document.getElementById('filePreview');
        const imgPreview = document.getElementById('imgPreview');
        const pdfPreview = document.getElementById('pdfPreview');
        const pdfName = document.getElementById('pdfName');

        preview.style.display = 'block';

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgPreview.src = e.target.result;
                imgPreview.style.display = 'block';
                pdfPreview.style.display = 'none';
            };
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            imgPreview.style.display = 'none';
            pdfPreview.style.display = 'block';
            pdfName.textContent = file.name;
        }
    }

    // Drag & Drop
    const dropZone = document.getElementById('dropZone');
    if (dropZone) {
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('bukti_bayar').files = files;
                previewFile();
            }
        });
    }

    // ===== SUBMIT UPLOAD =====
    function submitUpload() {
        const status = document.getElementById('uploadStatus');
        const pesananId = <?php echo $pesanan_id; ?>;

        let file = null;

        if (!capturedImage) {
            status.innerHTML = '❌ Silakan ambil foto terlebih dahulu';
            status.style.color = '#f44336';
            return;
        }
        file = capturedImage;

        if (file.size > 5 * 1024 * 1024) {
            status.innerHTML = '❌ Ukuran file terlalu besar (max 5MB)';
            status.style.color = '#f44336';
            return;
        }

        const formData = new FormData();
        formData.append('pesanan_id', pesananId);
        formData.append('bukti_bayar', file, 'bukti_' + pesananId + '.jpg');

        status.innerHTML = '⏳ Uploading...';
        status.style.color = '#ff9800';

        fetch('ajax/upload_bukti_admin.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                console.log('Upload response:', data);
                if (data.success) {
                    status.innerHTML = '✅ ' + data.message;
                    status.style.color = '#4caf50';

                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    status.innerHTML = '❌ ' + data.message;
                    status.style.color = '#f44336';
                }
            })
            .catch(err => {
                console.error('Upload error:', err);
                status.innerHTML = '❌ Terjadi kesalahan: ' + err.message;
                status.style.color = '#f44336';
            });
    }

    // Modal functions
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