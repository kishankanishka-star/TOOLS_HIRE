<?php
session_start();
require_once '../config/database.php';
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash']) && $user['role'] === 'admin') {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials or access denied.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | Shelton Hire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #2c3e50; display: flex; align-items: center; min-height: 100vh; }
        .login-card { max-width: 400px; margin: auto; border: none; border-radius: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card login-card shadow-lg p-4">
            <div class="text-center mb-4">
                <h3 class="fw-bold text-primary">Shelton Admin</h3>
                <p class="text-muted small">Please sign in to continue</p>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-bold">Login to Dashboard</button>
            </form>
            <div class="text-center mt-4">
                <a href="../index.php" class="text-muted small text-decoration-none">← Back to Website</a>
            </div>
        </div>
    </div>
</body>
</html>
