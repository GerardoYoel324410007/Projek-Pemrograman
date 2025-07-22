<?php
require_once __DIR__.'/../includes/config.php';

class ObatFunctions {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Ambil data obat berdasarkan ID
     */
    public function getObatById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM obat WHERE obat_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Ambil semua data obat dengan filter opsional
     */
    public function getAllObat($filter = '') {
        $sql = "SELECT o.*, s.nama_supplier 
                FROM obat o
                LEFT JOIN supplier s ON o.supplier_id = s.supplier_id";

        switch ($filter) {
            case 'kritis':
                $sql .= " WHERE o.stok < 10";
                break;
            case 'kadaluarsa':
                $sql .= " WHERE o.tanggal_kadaluarsa BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
                break;
            case 'habis':
                $sql .= " WHERE o.stok <= 0";
                break;
        }

        $sql .= " ORDER BY o.nama_obat ASC";
        return $this->conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Update stok obat (increase/decrease)
     */
    public function updateStok($obat_id, $jumlah, $operation = 'increase') {
        $operator = ($operation === 'increase') ? '+' : '-';
        $stmt = $this->conn->prepare("UPDATE obat SET stok = stok $operator ? WHERE obat_id = ?");
        $stmt->bind_param("ii", $jumlah, $obat_id);
        return $stmt->execute();
    }

    /**
     * Proses restock obat
     */
    public function processRestock($supplier_id, $items) {
        $this->conn->begin_transaction();
        try {
            // Insert header pembelian
            $stmt = $this->conn->prepare("INSERT INTO pembelian (supplier_id, tanggal_pembelian) VALUES (?, CURDATE())");
            $stmt->bind_param("i", $supplier_id);
            $stmt->execute();
            $pembelian_id = $this->conn->insert_id;

            $total_biaya = 0;

            // Insert detail pembelian
            foreach ($items as $item) {
                $subtotal = $item['jumlah'] * $item['harga'];
                $total_biaya += $subtotal;

                $stmt = $this->conn->prepare("INSERT INTO detail_pembelian (pembelian_id, obat_id, jumlah, harga_satuan) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $pembelian_id, $item['obat_id'], $item['jumlah'], $item['harga']);
                $stmt->execute();

                // Update stok
                $this->updateStok($item['obat_id'], $item['jumlah'], 'increase');
            }

            // Update total biaya
            $stmt = $this->conn->prepare("UPDATE pembelian SET total_biaya = ? WHERE pembelian_id = ?");
            $stmt->bind_param("di", $total_biaya, $pembelian_id);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Gagal memproses restock: " . $e->getMessage());
        }
    }

    /**
     * Cek obat yang akan kadaluarsa
     */
    public function getObatKadaluarsa($threshold_days = 30) {
        $stmt = $this->conn->prepare("
            SELECT *, DATEDIFF(tanggal_kadaluarsa, CURDATE()) AS hari_menuju_kadaluarsa
            FROM obat
            WHERE tanggal_kadaluarsa BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY tanggal_kadaluarsa ASC
        ");
        $stmt->bind_param("i", $threshold_days);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>