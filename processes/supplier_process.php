<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin', 'apoteker'])) {
    redirect('../index.php', 'error', 'Akses ditolak!');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tambah supplier baru
    if (isset($_POST['add_supplier'])) {
        $nama = sanitize($_POST['nama_supplier']);
        $alamat = sanitize($_POST['alamat']);
        $telepon = sanitize($_POST['telepon']);

        $sql = "INSERT INTO supplier (nama_supplier, alamat, telepon)
                VALUES ('$nama', '$alamat', '$telepon')";

        if ($conn->query($sql)) {
            redirect('../restock.php', 'success', 'Supplier berhasil ditambahkan!');
        } else {
            redirect('../restock.php', 'error', 'Gagal menambah supplier: ' . $conn->error);
        }
    }

    // Update supplier
    if (isset($_POST['update_supplier'])) {
        $supplier_id = (int)$_POST['supplier_id'];
        $nama = sanitize($_POST['nama_supplier']);
        $alamat = sanitize($_POST['alamat']);
        $telepon = sanitize($_POST['telepon']);

        $sql = "UPDATE supplier SET 
                nama_supplier = '$nama',
                alamat = '$alamat',
                telepon = '$telepon'
                WHERE supplier_id = $supplier_id";

        if ($conn->query($sql)) {
            redirect('../restock.php', 'success', 'Data supplier berhasil diperbarui!');
        } else {
            redirect('../restock.php', 'error', 'Gagal update supplier: ' . $conn->error);
        }
    }
}
?>