<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'kasir') {
    redirect('../index.php', 'error', 'Akses ditolak!');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Proses transaksi baru
    if (isset($_POST['checkout'])) {
        $user_id = $_SESSION['user_id'];
        $total_harga = (float)$_POST['total_harga'];
        $metode_pembayaran = sanitize($_POST['metode_pembayaran']);
        $items = json_decode($_POST['items'], true);

        // Mulai transaksi database
        $conn->begin_transaction();

        try {
            // Simpan transaksi utama
            $sql = "INSERT INTO transaksi (user_id, total_harga, metode_pembayaran) 
                    VALUES ($user_id, $total_harga, '$metode_pembayaran')";
            
            if (!$conn->query($sql)) {
                throw new Exception("Gagal menyimpan transaksi: " . $conn->error);
            }

            $transaksi_id = $conn->insert_id;

            // Simpan detail transaksi
            foreach ($items as $item) {
                $obat_id = (int)$item['id'];
                $jumlah = (int)$item['quantity'];
                $subtotal = (float)$item['subtotal'];

                $sql = "INSERT INTO detail_transaksi (transaksi_id, obat_id, jumlah, subtotal)
                        VALUES ($transaksi_id, $obat_id, $jumlah, $subtotal)";
                
                if (!$conn->query($sql)) {
                    throw new Exception("Gagal menyimpan detail transaksi: " . $conn->error);
                }

                // Kurangi stok
                $sql = "UPDATE obat SET stok = stok - $jumlah WHERE obat_id = $obat_id";
                
                if (!$conn->query($sql)) {
                    throw new Exception("Gagal update stok obat: " . $conn->error);
                }
            }

            // Commit transaksi jika semua berhasil
            $conn->commit();
            redirect('../sales.php', 'success', 'Transaksi berhasil disimpan! No. Transaksi: ' . $transaksi_id);

        } catch (Exception $e) {
            // Rollback jika ada error
            $conn->rollback();
            redirect('../sales.php', 'error', $e->getMessage());
        }
    }
}
?>