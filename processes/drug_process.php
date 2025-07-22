<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_drug'])) {
    // Validasi input wajib
    $required = [
        'nama_obat', 'kategori', 'harga_beli', 
        'harga_jual', 'stok', 'satuan', 'tanggal_kadaluarsa'
    ];
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "Kolom " . ucfirst(str_replace('_', ' ', $field)) . " wajib diisi!";
            header("Location: ../restock.php");
            exit();
        }
    }

    // Ambil dan sanitize data
    $data = [
        'nama_obat' => sanitize($_POST['nama_obat']),
        'kategori' => sanitize($_POST['kategori']),
        'harga_beli' => (float)$_POST['harga_beli'],
        'harga_jual' => (float)$_POST['harga_jual'],
        'stok' => (int)$_POST['stok'],
        'satuan' => sanitize($_POST['satuan']),
        'tanggal_kadaluarsa' => date('Y-m-d', strtotime($_POST['tanggal_kadaluarsa'])),
        'supplier_id' => !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null
    ];

    // Validasi bisnis
    if ($data['harga_jual'] <= $data['harga_beli']) {
        $_SESSION['error'] = "Harga jual harus lebih besar dari harga beli!";
        header("Location: ../restock.php");
        exit();
    }

    if ($data['stok'] < 0) {
        $_SESSION['error'] = "Stok tidak boleh negatif!";
        header("Location: ../restock.php");
        exit();
    }

    // Simpan ke database
    try {
        $sql = "INSERT INTO obat (
                    nama_obat, kategori, harga_beli, harga_jual, 
                    stok, satuan, tanggal_kadaluarsa, supplier_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssddissi",
            $data['nama_obat'],
            $data['kategori'],
            $data['harga_beli'],
            $data['harga_jual'],
            $data['stok'],
            $data['satuan'],
            $data['tanggal_kadaluarsa'],
            $data['supplier_id']
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Data obat berhasil disimpan!";
        } else {
            throw new Exception("Gagal menyimpan data: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    header("Location: ../restock.php");
    exit();
}