<?php
require_once __DIR__.'/../includes/config.php';

class SupplierFunctions {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Ambil semua supplier
     */
    public function getAllSuppliers() {
        $result = $this->conn->query("
            SELECT * FROM supplier
            ORDER BY nama_supplier ASC
        ");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Tambah supplier baru
     */
    public function addSupplier($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO supplier (nama_supplier, alamat, telepon)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("sss", $data['nama'], $data['alamat'], $data['telepon']);
        return $stmt->execute();
    }

    /**
     * Ambil supplier by ID
     */
    public function getSupplierById($id) {
        $stmt = $this->conn->prepare("
            SELECT * FROM supplier
            WHERE supplier_id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Update data supplier
     */
    public function updateSupplier($id, $data) {
        $stmt = $this->conn->prepare("
            UPDATE supplier SET
                nama_supplier = ?,
                alamat = ?,
                telepon = ?
            WHERE supplier_id = ?
        ");
        $stmt->bind_param("sssi", $data['nama'], $data['alamat'], $data['telepon'], $id);
        return $stmt->execute();
    }
}
?>