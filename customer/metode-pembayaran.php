<?php
require_once '../config/config.php';
$page_title = 'Metode Pembayaran - Sambal Belut Bu Raden';
include 'includes/header.php';

// Check steps
if (!isset($_SESSION['layanan_id']) || !isset($_SESSION['nama']) || empty($_SESSION['cart'])) {
    header('Location: pilih-layanan.php');
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['harga'] * $item['qty'];
}
$pajak = $subtotal * 0.1;
$total = $subtotal + $pajak;
?>

<div class="breadcrumb">
    <span style="background: #e8e8e8; padding: 3px 8px; border-radius: 15px; font-size: 10px; margin-right: 5px;">1. Pilih Layanan ✓</span>
    <span style="background: #e8e8e8; padding: 3px 8px; border-radius: 15px; font-size: 10px; margin-right: 5px;">2. Isi Data Diri ✓</span>
    <span style="background: #e8e8e8; padding: 3px 8px; border-radius: 15px; font-size: 10px; margin-right: 5px;">3. Pilih Menu ✓</span>
    <span style="background: #c1395d; color: white; padding: 3px 8px; border-radius: 15px; font-size: 10px;">4. Metode Pembayaran</span>
</div>

<div class="container">
    <div class="content-wrapper">
        <div class="payment-content">
            <h2>Metode Pembayaran</h2>

            <form action="ajax/process_order.php" method="POST" id="paymentForm">
                <div class="payment-methods">
                    <!-- CASH -->
                    <div class="payment-option" data-method="cash" onclick="selectPayment(this, 'cash')">
                        <div class="payment-icon">💵</div>
                        <div class="payment-info">
                            <h3>Cash</h3>
                            <p>Bayar langsung di tempat</p>
                        </div>
                    </div>

                    <!-- QRIS -->
                    <div class="payment-option" data-method="qris" onclick="selectPayment(this, 'qris')">
                        <div class="payment-icon">📱</div>
                        <div class="payment-info">
                            <h3>QRIS</h3>
                            <p>Scan kode QR dengan ponsel anda</p>
                        </div>
                    </div>

                    <!-- TRANSFER BANK -->
                    <div class="payment-option" data-method="transfer" onclick="selectPayment(this, 'transfer')">
                        <div class="payment-icon">🏦</div>
                        <div class="payment-info">
                            <h3>Transfer Bank</h3>
                            <p>Transfer via mobile banking</p>
                        </div>
                    </div>
                </div>

                <!-- Bank Selection (Hidden by default) -->
                <div id="bankSelection" style="display: none; margin-top: 25px;">
                    <h3 style="font-size: 16px; margin-bottom: 15px; color: #333;">Pilih Bank</h3>
                    <div class="bank-selection">
                        <div class="bank-option" data-bank="BCA" onclick="selectBank(this, 'BCA')">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Bank_Central_Asia.svg/320px-Bank_Central_Asia.svg.png" alt="BCA" class="bank-logo">
                            <div class="payment-info">
                                <h3>BCA</h3>
                                <p>Bank Central Asia</p>
                            </div>
                        </div>

                        <div class="bank-option" data-bank="BNI" onclick="selectBank(this, 'BNI')">
                            <img src="https://upload.wikimedia.org/wikipedia/id/thumb/5/55/BNI_logo.svg/320px-BNI_logo.svg.png" alt="BNI" class="bank-logo">
                            <div class="payment-info">
                                <h3>BNI</h3>
                                <p>Bank Negara Indonesia</p>
                            </div>
                        </div>

                        <div class="bank-option" data-bank="Mandiri" onclick="selectBank(this, 'Mandiri')">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/ad/Bank_Mandiri_logo_2016.svg/320px-Bank_Mandiri_logo_2016.svg.png" alt="Mandiri" class="bank-logo">
                            <div class="payment-info">
                                <h3>Mandiri</h3>
                                <p>Bank Mandiri</p>
                            </div>
                        </div>

                        <div class="bank-option" data-bank="BRI" onclick="selectBank(this, 'BRI')">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2e/BRI_2020.svg/320px-BRI_2020.svg.png" alt="BRI" class="bank-logo">
                            <div class="payment-info">
                                <h3>BRI</h3>
                                <p>Bank Rakyat Indonesia</p>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="metode_pembayaran" id="metodePembayaran" required>
                <input type="hidden" name="bank" id="selectedBank">

                <button type="submit" class="btn btn-primary" id="btnProses" disabled style="opacity: 0.5; margin-top: 30px;">
                    Proses Pesanan
                </button>
            </form>
        </div>

        <div class="order-summary-side">
            <div class="summary-card">
                <h3>Ringkasan Pesanan</h3>

                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <div><?php echo htmlspecialchars($item['nama_menu']); ?></div>
                            <div class="cart-qty"><?php echo $item['qty']; ?>x</div>
                        </div>
                        <div class="cart-item-price"><?php echo formatRupiah($item['harga'] * $item['qty']); ?></div>
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
            </div>
        </div>
    </div>
</div>

<script>
    let currentMethod = '';
    let currentBank = '';

    function selectPayment(element, method) {
        // Remove selected from all payment options
        document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
        element.classList.add('selected');

        currentMethod = method;
        document.getElementById('metodePembayaran').value = method;

        // Show/hide bank selection
        const bankSelection = document.getElementById('bankSelection');

        if (method === 'transfer') {
            bankSelection.style.display = 'block';
            // Disable button until bank selected
            document.getElementById('btnProses').disabled = true;
            document.getElementById('btnProses').style.opacity = '0.5';
            // Reset bank selection
            document.querySelectorAll('.bank-option').forEach(opt => opt.classList.remove('selected'));
            currentBank = '';
            document.getElementById('selectedBank').value = '';
        } else {
            bankSelection.style.display = 'none';
            currentBank = '';
            document.getElementById('selectedBank').value = '';
            // Enable button for cash and qris
            document.getElementById('btnProses').disabled = false;
            document.getElementById('btnProses').style.opacity = '1';
            document.getElementById('btnProses').style.cursor = 'pointer';
        }
    }

    function selectBank(element, bank) {
        // Remove selected from all banks
        document.querySelectorAll('.bank-option').forEach(opt => opt.classList.remove('selected'));
        element.classList.add('selected');

        currentBank = bank;
        document.getElementById('selectedBank').value = bank;

        // Enable submit button
        const btn = document.getElementById('btnProses');
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
    }
</script>

<?php include 'includes/footer.php'; ?>