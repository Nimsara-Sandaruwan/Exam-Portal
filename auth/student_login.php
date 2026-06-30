<?php
// auth/student_login.php
session_start();
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['student_id'])) {
    header('Location: ../student/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reg_no   = trim($_POST['reg_no'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($reg_no) || empty($password)) {
        $error = 'Please enter both registration number and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE reg_no = :reg_no");
        $stmt->execute([':reg_no' => $reg_no]);
        $student = $stmt->fetch();

        if ($student && password_verify($password, $student['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['student_id']   = $student['student_id'];
            $_SESSION['student_name'] = $student['full_name'];
            $_SESSION['reg_no']       = $student['reg_no'];
            $_SESSION['dept_id']      = $student['dept_id'];
            header('Location: ../student/dashboard.php');
            exit;
        } else {
            $error = 'Invalid registration number or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login – Institute of Higher Technology</title>
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
            <svg width="48" height="48" viewBox="0 0 24 24" fill="#1e3c72">
                <path d="M12 2L1 21h22L12 2zm0 3.99L19.53 19H4.47L12 5.99z"/>
                <circle cx="12" cy="16" r="1"/>
                <path d="M11 10h2v4h-2z"/>
            </svg>
            <h3>Student Login</h3>
            <p class="text-muted">Enter your credentials to access your results</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible">
                <?= htmlspecialchars($error) ?>
                <button class="alert-close" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>

        <form action="" method="POST" novalidate>
            <!-- Registration Number -->
            <div class="form-group">
                <label for="reg_no" class="form-label">Registration Number</label>
                <div class="input-group">
                    <span>
                        <svg width="20" height="20" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                        </svg>
                    </span>
                    <input type="text"
                           class="form-control"
                           id="reg_no"
                           name="reg_no"
                           placeholder="e.g. TAN/IT/2023/F/0001"
                           value="<?= htmlspecialchars($reg_no ?? '') ?>"
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

            <!-- Submit -->
            <button type="submit" class="btn btn-primary w-100">Log In</button>

            <!-- Back to Home -->
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