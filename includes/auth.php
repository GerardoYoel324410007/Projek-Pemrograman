<?php
require_once 'config.php';

// Cek status login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Validasi role
function requireRole($allowedRoles) {
    if (!isLoggedIn() || !in_array($_SESSION['role'], $allowedRoles)) {
        redirect('index.php', 'error', 'Akses ditolak!');
    }
}

// Get user data
function getUserData() {
    global $conn;
    if (isLoggedIn()) {
        $userId = $_SESSION['user_id'];
        $sql = "SELECT * FROM users WHERE user_id = $userId";
        $result = $conn->query($sql);
        return $result->fetch_assoc();
    }
    return null;
}
?>