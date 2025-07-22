<?php
session_start();

// Koneksi ke Database
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'apotech_db';

$conn = new mysqli($host, $user, $password, $database);

// Test koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    error_log("Database connected successfully");
}

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Fungsi sanitasi input
function sanitize($data) {
    global $conn;
    // Tambahkan pengecekan null
    if ($data === null) {
        return null;
    }
    return $conn->real_escape_string(htmlspecialchars(trim($data)));
}

// Fungsi redirect dengan pesan
function redirect($location, $messageType = null, $message = null) {
    if ($messageType && $message) {
        $_SESSION[$messageType] = $message;
    }
    header("Location: $location");
    exit();
}
?>