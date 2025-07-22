<?php
// Enable strict error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base paths
define('BASE_PATH', __DIR__);
define('BASE_URL', 'http://localhost/apotek/'); // Adjust to your project URL
// Check if required constants are defined
if (!defined('BASE_PATH') || !defined('BASE_URL')) {
    die("<div class='alert alert-danger'>System Error: Required constants not defined</div>");
}

// Load dependencies with verification
$required_files = [
    'includes/config.php',
    'includes/auth.php',
    'includes/functions.php',
    'functions/transaksi_functions.php'
];

foreach ($required_files as $file) {
    $file_path = BASE_PATH . '/' . $file;
    if (!file_exists($file_path)) {
        die("<div class='alert alert-danger'>System Error: Missing required file - $file</div>");
    }
    require_once $file_path;
}

// Verify essential functions
$required_functions = [
    'isLoggedIn',
    'redirect',
    'sanitize',
    'getDrugsInStock'
];

foreach ($required_functions as $func) {
    if (!function_exists($func)) {
        die("<div class='alert alert-danger'>System Error: Function $func() not available</div>");
    }
}

// Authentication check
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('index.php', 'error', 'Akses ditolak!');
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    try {
        // Validate input
        if (empty($_POST['items']) || empty($_POST['total_harga'])) {
            throw new Exception("Data transaksi tidak valid!");
        }

        $items = json_decode($_POST['items'], true);
        $total = (float)$_POST['total_harga'];
        $metode = sanitize($_POST['metode_pembayaran']);

        if (!is_array($items) || json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Format data keranjang tidak valid!");
        }

        // Process transaction
        $transaksi = new TransaksiFunctions();
        $transaksi_id = $transaksi->createTransaction(
            $_SESSION['user_id'],
            $items,
            $total,
            $metode
        );

        // Clear cart and show success
        echo '<script>localStorage.removeItem("apotech_cart");</script>';
        $_SESSION['success'] = "Transaksi berhasil! No. Transaksi: $transaksi_id";
        header("Location: sales.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: sales.php");
        exit();
    }
}

// Get drugs data
$drugs = getDrugsInStock();
if (!is_array($drugs)) {
    $drugs = [];
    error_log("Failed to retrieve drug list");
}

$pageTitle = "Transaksi Penjualan";
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Transaksi Baru</h5>
            </div>
            <div class="card-body">
                <?php
                // Display session alerts
                if (isset($_SESSION['success'])) {
                    echo '<div class="alert alert-success alert-dismissible fade show">';
                    echo $_SESSION['success'];
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    echo '</div>';
                    unset($_SESSION['success']);
                }

                if (isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger alert-dismissible fade show">';
                    echo $_SESSION['error'];
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    echo '</div>';
                    unset($_SESSION['error']);
                }
                ?>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <select id="drugSelect" class="form-select">
                            <option value="">Pilih Obat...</option>
                            <?php foreach ($drugs as $drug): ?>
                                <?php if (!empty($drug['obat_id']) && !empty($drug['nama_obat'])): ?>
                                <option value="<?= $drug['obat_id'] ?>" 
                                        data-name="<?= htmlspecialchars($drug['nama_obat']) ?>"
                                        data-price="<?= $drug['harga_jual'] ?>"
                                        data-stock="<?= $drug['stok'] ?>"
                                        data-code="<?= htmlspecialchars($drug['kode_obat'] ?? '') ?>">
                                    <?= htmlspecialchars($drug['nama_obat']) ?> 
                                    (Rp <?= number_format($drug['harga_jual'], 0) ?>)
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" id="quantity" class="form-control" value="1" min="1" max="1000">
                    </div>
                    <div class="col-md-2">
                        <button id="addItem" class="btn btn-primary w-100">
                            <i class="bi bi-cart-plus"></i> Tambah
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button id="clearCart" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Kosongkan
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="cartTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="30%">Nama Obat</th>
                                <th width="15%">Kode</th>
                                <th width="15%">Harga</th>
                                <th width="15%">Jumlah</th>
                                <th width="15%">Subtotal</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Items will be populated by JavaScript -->
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <th colspan="4" class="text-end">Total</th>
                                <th id="totalAmount">Rp 0</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Pembayaran</h5>
            </div>
            <div class="card-body">
                <form id="checkoutForm" method="POST">
                    <input type="hidden" name="items" id="itemsInput">
                    <input type="hidden" name="total_harga" id="totalInput">
                    
                    <div class="mb-3">
                        <label class="form-label">Total Pembayaran</label>
                        <input type="text" class="form-control fw-bold fs-4" id="displayTotal" value="Rp 0" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Metode Pembayaran</label>
                        <select name="metode_pembayaran" class="form-select" required>
                            <option value="tunai">Tunai</option>
                            <option value="kartu">Kartu Debit/Kredit</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="checkout" class="btn btn-success w-100 py-3">
                        <i class="bi bi-credit-card"></i> PROSES PEMBAYARAN
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Load JavaScript with cache busting -->
<script src="<?= BASE_URL ?>assets/js/sales.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>assets/js/script.js?v=<?= time() ?>"></script>


<!-- Fallback system -->
<script>
window.addEventListener('DOMContentLoaded', function() {
    // Check if main system loaded
    setTimeout(function() {
        if (typeof SalesSystem === 'undefined') {
            // Create emergency notification
            const warningDiv = document.createElement('div');
            warningDiv.className = 'alert alert-warning fixed-bottom m-3';
            warningDiv.innerHTML = `
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Mode Darurat</strong> Sistem utama gagal dimuat.
                <a href="javascript:location.reload()" class="alert-link">Refresh</a> atau
                <button onclick="loadEmergencyCart()" class="btn btn-sm btn-light">Gunakan mode sederhana</button>
            `;
            document.body.appendChild(warningDiv);
            
            // Basic cart functionality
            window.loadEmergencyCart = function() {
                localStorage.setItem('emergency_cart', 'active');
                location.reload();
            };
        }
    }, 1000);
});
</script>