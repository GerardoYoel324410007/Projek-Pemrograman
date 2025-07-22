<?php
require_once 'includes/config.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    $redirect = match($_SESSION['role']) {
        'admin' => 'dashboard.php',
        'apoteker' => 'restock.php',
        'kasir' => 'sales.php',
        default => 'index.php'
    };
    header("Location: $redirect");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Apotech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow" style="width: 400px;">
            <div class="text-center mb-4">
                <h3 class="mt-3">Login Apotech</h3>
            </div>
            <form action="processes/login_process.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
</body>
</html>