<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Pastikan hanya admin/apoteker yang bisa akses
if (!isLoggedIn() || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'apoteker')) {
    redirect('index.php', 'error', 'Akses ditolak!');
}

// Ambil data supplier untuk dropdown
$suppliers = getSuppliers();

$pageTitle = "Input Data Obat";
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">Form Input Obat Baru</h3>
    </div>
    <div class="card-body">
        <form action="processes/drug_process.php" method="POST" id="formObat">
            <div class="row">
                <!-- Kolom Kiri -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Obat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_obat" required 
                               placeholder="Contoh: Paracetamol 500mg">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" name="kategori" required>
                            <option value="">Pilih Kategori</option>
                            <option value="analgesik">Analgesik</option>
                            <option value="antibiotik">Antibiotik</option>
                            <option value="antivirus">Antivirus</option>
                            <option value="antihipertensi">Antihipertensi</option>
                            <option value="vitamin">Vitamin</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Harga Beli <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="harga_beli" 
                                           min="0" step="100" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="harga_jual" 
                                           min="0" step="100" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stok Awal <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="stok" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                <select class="form-select" name="satuan" required>
                                    <option value="">Pilih Satuan</option>
                                    <option value="tablet">Tablet</option>
                                    <option value="kapsul">Kapsul</option>
                                    <option value="botol">Botol</option>
                                    <option value="tube">Tube</option>
                                    <option value="sachet">Sachet</option>
                                    <option value="vial">Vial</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Kadaluarsa <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="tanggal_kadaluarsa" 
                               min="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select class="form-select" name="supplier_id">
                            <option value="">Pilih Supplier</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= $supplier['supplier_id'] ?>">
                                    <?= htmlspecialchars($supplier['nama_supplier']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" name="add_drug" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Data
                </button>
                <button type="reset" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Validasi client-side
document.getElementById('formObat').addEventListener('submit', function(e) {
    const hargaBeli = parseFloat(this.elements['harga_beli'].value);
    const hargaJual = parseFloat(this.elements['harga_jual'].value);
    
    if (hargaJual <= hargaBeli) {
        e.preventDefault();
        alert('Harga jual harus lebih besar dari harga beli!');
        this.elements['harga_jual'].focus();
    }
});
</script>

<?php include 'includes/footer.php'; ?>