<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'functions/laporan_functions.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fix: Pass database connection to constructor
$laporan = new LaporanFunctions($conn);

try {
    $obatKritis = $laporan->getObatKritis();
    $obatKadaluarsa = $laporan->getObatKadaluarsa();
    
    if (empty($obatKritis)) {
        echo "<div class='alert alert-info'>Tidak ada obat dengan stok kritis</div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    error_log("Laporan error: " . $e->getMessage());
    $obatKritis = [];
    $obatKadaluarsa = [];
}

// Get report data from session if exists
$reportData = $_SESSION['report_data'] ?? [];
$reportTitle = $_SESSION['report_title'] ?? 'Laporan';
unset($_SESSION['report_data'], $_SESSION['report_title']);
?>
<?php include 'includes/header.php'; ?>
<div class="row mb-4">
    <link rel="stylesheet" href="css/style.css">
    <div class="col-md-6">
        <h2>Laporan Apotek</h2>
    </div>
    <div class="col-md-6 text-end">
        <?php if (!empty($reportData)): ?>
            <a href="#" class="btn btn-success" onclick="window.print()">
                <i class="bi bi-printer"></i> Cetak Laporan
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Generate Laporan</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="processes/report_process.php?generate_stock_report" 
                       class="list-group-item list-group-item-action">
                        Laporan Stok Obat
                    </a>
                    
                    <form action="processes/report_process.php" method="GET" class="list-group-item">
                        <input type="hidden" name="generate_sales_report" value="1">
                        <label class="form-label">Laporan Penjualan</label>
                        <div class="row g-2 mb-2">
                            <div class="col">
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col">
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Generate</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Obat Kritis Section -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5>Obat Stok Kritis (Stok < 10)</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Obat</th>
                            <th>Stok</th>
                            <th>Satuan</th>
                            <th>Kadaluarsa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($obatKritis as $obat): ?>
                        <tr>
                            <td><?= htmlspecialchars($obat['nama_obat']) ?></td>
                            <td class="text-danger fw-bold"><?= $obat['stok'] ?></td>
                            <td><?= htmlspecialchars($obat['satuan']) ?></td>
                            <td><?= date('d/m/Y', strtotime($obat['tanggal_kadaluarsa'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Main Report Section -->
        <div class="card">
            <div class="card-header">
                <h5><?= htmlspecialchars($reportTitle) ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($reportData)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <?php foreach (array_keys($reportData[0]) as $header): ?>
                                        <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $header))) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportData as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $value): ?>
                                            <td>
                                                <?= is_numeric($value) ? number_format($value, 0, ',', '.') : htmlspecialchars($value) ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Silakan pilih jenis laporan untuk menampilkan data.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>