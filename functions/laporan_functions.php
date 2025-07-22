<?php
require_once __DIR__.'/../includes/config.php';

class LaporanFunctions {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Laporan obat dengan stok kritis (stok < 10)
     */
    public function getObatKritis() {
        $stmt = $this->conn->prepare("
            SELECT 
                o.*,
                s.nama_supplier
            FROM obat o
            LEFT JOIN supplier s ON o.supplier_id = s.supplier_id
            WHERE o.stok < 10
            ORDER BY o.stok ASC
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Laporan obat yang sudah/hampir kadaluarsa
     */
    public function getObatKadaluarsa($thresholdDays = 30) {
        $stmt = $this->conn->prepare("
            SELECT 
                o.*,
                s.nama_supplier,
                DATEDIFF(o.tanggal_kadaluarsa, CURDATE()) as hari_menuju_kadaluarsa
            FROM obat o
            LEFT JOIN supplier s ON o.supplier_id = s.supplier_id
            WHERE o.tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY o.tanggal_kadaluarsa ASC
        ");
        $stmt->bind_param("i", $thresholdDays);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Laporan penjualan harian
     */
    public function getLaporanPenjualan($start_date, $end_date) {
        $stmt = $this->conn->prepare("
            SELECT 
                DATE(t.tanggal_transaksi) as tanggal,
                COUNT(*) as jumlah_transaksi,
                SUM(t.total_harga) as total_penjualan,
                AVG(t.total_harga) as rata_rata
            FROM transaksi t
            WHERE DATE(t.tanggal_transaksi) BETWEEN ? AND ?
            GROUP BY DATE(t.tanggal_transaksi)
            ORDER BY tanggal ASC
        ");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Laporan obat terlaris
     */
    public function getObatTerlaris($start_date, $end_date, $limit = 5) {
        $stmt = $this->conn->prepare("
            SELECT 
                o.nama_obat,
                SUM(dt.jumlah) as total_terjual,
                SUM(dt.subtotal) as total_penjualan
            FROM detail_transaksi dt
            JOIN transaksi t ON dt.transaksi_id = t.transaksi_id
            JOIN obat o ON dt.obat_id = o.obat_id
            WHERE DATE(t.tanggal_transaksi) BETWEEN ? AND ?
            GROUP BY o.obat_id
            ORDER BY total_terjual DESC
            LIMIT ?
        ");
        $stmt->bind_param("ssi", $start_date, $end_date, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Laporan profit
     */
    public function getLaporanProfit($start_date, $end_date) {
        $stmt = $this->conn->prepare("
            SELECT
                o.nama_obat,
                SUM(dt.jumlah) as jumlah_terjual,
                SUM(dt.subtotal) as total_penjualan,
                SUM(dt.jumlah * o.harga_beli) as total_modal,
                SUM(dt.subtotal - (dt.jumlah * o.harga_beli)) as profit,
                ROUND((SUM(dt.subtotal - (dt.jumlah * o.harga_beli)) / SUM(dt.subtotal)) * 100, 2) as margin
            FROM detail_transaksi dt
            JOIN transaksi t ON dt.transaksi_id = t.transaksi_id
            JOIN obat o ON dt.obat_id = o.obat_id
            WHERE DATE(t.tanggal_transaksi) BETWEEN ? AND ?
            GROUP BY o.obat_id
            ORDER BY profit DESC
        ");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Laporan stok semua obat
     */
    public function getLaporanStok() {
        $stmt = $this->conn->prepare("
            SELECT 
                o.*,
                s.nama_supplier,
                DATEDIFF(o.tanggal_kadaluarsa, CURDATE()) as hari_menuju_kadaluarsa
            FROM obat o
            LEFT JOIN supplier s ON o.supplier_id = s.supplier_id
            ORDER BY o.stok ASC
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>