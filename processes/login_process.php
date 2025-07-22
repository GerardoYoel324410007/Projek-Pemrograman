<?php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    // 1. Gunakan Prepared Statement
    $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // 2. Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
            
            // 3. Redirect dengan path yang benar
            $redirectMap = [
                'admin' => '../dashboard.php',
                'apoteker' => '../restock.php',
                'kasir' => '../sales.php'
            ];
            
            $redirect = $redirectMap[$user['role']] ?? '../index.php';
            header("Location: $redirect");
            exit();
        } else {
            $_SESSION['error'] = "Password salah!";
        }
    } else {
        $_SESSION['error'] = "Email tidak terdaftar!";
    }
    
    // 4. Jika gagal, kembali ke login
    header("Location: ../index.php");
    exit();
}

// 5. Blok akses langsung
header("Location: ../index.php");
exit();
?>