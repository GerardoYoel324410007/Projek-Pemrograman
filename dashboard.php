<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php'; // Pastikan ini ada

if (!isLoggedIn()) {
    redirect('index.php', 'error', 'Silakan login terlebih dahulu!');
}

$pageTitle = "Dashboard";
include 'includes/header.php';

// Dapatkan data untuk dashboard
$lowStockCount = getLowStockCount();
$expiredDrugs = getExpiredDrugs(); // Sekarang fungsi ini tersedia
$todaySales = getTodaySales();     // Sekarang fungsi ini tersedia
?>

<!-- Konten dashboard Anda di sini -->

<div class="row">
    <!-- Card Stok Hampir Habis -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Stok Hampir Habis</h5>
                    <span class="badge bg-danger"><?= $lowStockCount ?></span>
                </div>
                <p class="card-text">Obat dengan stok kurang dari 10</p>
                <a href="reports.php" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
            </div>
        </div>
    </div>

    <!-- Card Obat Kadaluarsa -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Obat Kadaluarsa</h5>
                    <span class="badge bg-warning text-dark"><?= count($expiredDrugs) ?></span>
                </div>
                <?php if (!empty($expiredDrugs)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($expiredDrugs as $drug): ?>
                            <li class="list-group-item">
                                <?= $drug['nama_obat'] ?> - 
                                <?= indoDate($drug['tanggal_kadaluarsa']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="card-text">Tidak ada obat kadaluarsa</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Card Penjualan Hari Ini -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Penjualan Hari Ini</h5>
                <h2 class="card-text">Rp <?= number_format($todaySales, 0, ',', '.') ?></h2>
                <a href="reports.php" class="btn btn-sm btn-outline-success">Lihat Laporan</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>