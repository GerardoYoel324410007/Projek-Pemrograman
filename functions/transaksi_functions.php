<?php
class TransaksiFunctions {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function createTransaction($user_id, $items, $total, $metode_pembayaran) {
        // Validasi input
        if (empty($items)) {
            throw new Exception("Keranjang belanja kosong!");
        }

        if ($total <= 0) {
            throw new Exception("Total pembayaran tidak valid!");
        }

        // Mulai transaksi database
        $this->conn->begin_transaction();

        try {
            // 1. Simpan transaksi utama
            $stmt = $this->conn->prepare("
                INSERT INTO transaksi 
                (user_id, total_harga, metode_pembayaran) 
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("ids", $user_id, $total, $metode_pembayaran);
            $stmt->execute();
            $transaksi_id = $this->conn->insert_id;

            // 2. Simpan detail transaksi dan update stok
            foreach ($items as $item) {
                // Validasi item
                if (empty($item['obat_id']) || empty($item['quantity']) || empty($item['price'])) {
                    throw new Exception("Data item tidak valid!");
                }

                // Simpan detail transaksi
                $stmt = $this->conn->prepare("
                    INSERT INTO detail_transaksi 
                    (transaksi_id, obat_id, jumlah, harga_satuan, subtotal) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $subtotal = $item['price'] * $item['quantity'];
                $stmt->bind_param(
                    "iiidd", 
                    $transaksi_id,
                    $item['obat_id'],
                    $item['quantity'],
                    $item['price'],
                    $subtotal
                );
                $stmt->execute();

                // Update stok obat
                $stmt = $this->conn->prepare("
                    UPDATE obat SET stok = stok - ? 
                    WHERE obat_id = ? AND stok >= ?
                ");
                $stmt->bind_param("iii", $item['quantity'], $item['obat_id'], $item['quantity']);
                $stmt->execute();

                if ($stmt->affected_rows === 0) {
                    throw new Exception("Stok obat tidak mencukupi atau tidak tersedia!");
                }
            }

            $this->conn->commit();
            return $transaksi_id;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Gagal memproses transaksi: " . $e->getMessage());
        }
    }

    // Fungsi tambahan untuk mendapatkan riwayat transaksi
    public function getTransactionHistory($limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT t.*, u.nama as kasir 
            FROM transaksi t
            JOIN users u ON t.user_id = u.user_id
            ORDER BY t.tanggal_transaksi DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}