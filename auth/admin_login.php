<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (isset($_SESSION['admin_id'])) { header('Location: ../admin/dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) { $error = 'Please enter both fields.'; }
    else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['dept_id'] = $admin['dept_id'];
            header('Location: ../admin/dashboard.php'); exit;
        } else { $error = 'Invalid email or password.'; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – SLIATE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-container">
    <div class="login-card">
        <div class="login-logo"><i class="fas fa-shield-alt" style="color:var(--accent); font-size:3rem;"></i></div>
        <h3>Administrator Login</h3>
        <p class="login-subtitle">Access the administration panel</p>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?><button class="alert-close" onclick="this.parentElement.remove()">×</button></div><?php endif; ?>
        <form method="POST">
            <div class="input-group"><span class="input-icon"><i class="fas fa-envelope"></i></span><input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($email ?? '') ?>" required></div>
            <div class="input-group"><span class="input-icon"><i class="fas fa-lock"></i></span><input type="password" name="password" id="password" placeholder="Password" required><button type="button" class="toggle-password" data-target="#password"><i class="fas fa-eye"></i></button></div>
            <button type="submit" class="btn btn-accent w-100" style="margin-top:8px;"><i class="fas fa-sign-in-alt"></i> Log In</button>
            <p style="margin-top:16px;font-size:0.9rem;"><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
        </form>
    </div>
    <script src="../assets/js/script.js"></script>
</body>
</html>