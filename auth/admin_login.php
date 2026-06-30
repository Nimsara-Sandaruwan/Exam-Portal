<?php
// auth/admin_login.php
session_start();
require_once __DIR__ . '/../config/db.php';

// If already logged in as admin, redirect to admin dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: ../admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);

            $_SESSION['admin_id']   = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['dept_id']    = $admin['dept_id'];

            header('Location: ../admin/dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – Institute of Higher Technology</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .input-group svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }
    </style>
</head>
<body class="login-container">
    <div class="login-card">
        <div class="text-center mb-4">
            <!-- Shield lock icon -->
            <svg width="48" height="48" viewBox="0 0 24 24" fill="#27ae60">
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
            </svg>
            <h3>Admin Login</h3>
            <p class="text-muted">Access the administration panel</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible">
                <?= htmlspecialchars($error) ?>
                <button class="alert-close" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>

        <form action="" method="POST" novalidate>
            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span>
                        <svg width="20" height="20" viewBox="0 0 24 24">
                            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                        </svg>
                    </span>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           placeholder="admin@example.com"
                           value="<?= htmlspecialchars($email ?? '') ?>"
                           required>
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span>
                        <svg width="20" height="20" viewBox="0 0 24 24">
                            <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM12 17c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2z"/>
                        </svg>
                    </span>
                    <input type="password"
                           class="form-control"
                           id="password"
                           name="password"
                           placeholder="Enter your password"
                           required>
                    <button class="toggle-password"
                            type="button"
                            data-target="#password">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100">Log In</button>

            <div style="margin-top:15px; text-align:center;">
                <a href="../index.php" style="color: #1e3c72; text-decoration: none;">
                    ← Back to Home
                </a>
            </div>
        </form>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>