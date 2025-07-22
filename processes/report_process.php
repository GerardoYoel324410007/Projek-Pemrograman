<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('../index.php', 'error', 'Akses ditolak!');
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Generate laporan penjualan
    if (isset($_GET['generate_sales_report'])) {
        $start_date = sanitize($_GET['start_date']);
        $end_date = sanitize($_GET['end_date']);

        $sql = "SELECT t.transaksi_id, t.tanggal_transaksi, u.nama as kasir, t.total_harga
                FROM transaksi t
                JOIN users u ON t.user_id = u.user_id
                WHERE DATE(t.tanggal_transaksi) BETWEEN '$start_date' AND '$end_date'
                ORDER BY t.tanggal_transaksi DESC";

        $result = $conn->query($sql);
        $report_data = $result->fetch_all(MYSQLI_ASSOC);

        // Simpan data report di session untuk ditampilkan
        $_SESSION['report_data'] = $report_data;
        $_SESSION['report_title'] = "Laporan Penjualan ($start_date s/d $end_date)";
        
        redirect('../reports.php');
    }

    // Generate laporan stok
    if (isset($_GET['generate_stock_report'])) {
        $sql = "SELECT o.nama_obat, o.stok, o.satuan, s.nama_supplier, o.tanggal_kadaluarsa
                FROM obat o
                LEFT JOIN supplier s ON o.supplier_id = s.supplier_id
                ORDER BY o.stok ASC";

        $result = $conn->query($sql);
        $report_data = $result->fetch_all(MYSQLI_ASSOC);

        $_SESSION['report_data'] = $report_data;
        $_SESSION['report_title'] = "Laporan Stok Obat";
        
        redirect('../reports.php');
    }
}
?>