<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (isset($_SESSION['student_id'])) { header('Location: ../student/dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reg_no = trim($_POST['reg_no'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($reg_no) || empty($password)) { $error = 'Please enter both fields.'; }
    else {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE reg_no = ?");
        $stmt->execute([$reg_no]);
        $student = $stmt->fetch();
        if ($student && password_verify($password, $student['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['student_name'] = $student['full_name'];
            $_SESSION['reg_no'] = $student['reg_no'];
            $_SESSION['dept_id'] = $student['dept_id'];
            header('Location: ../student/dashboard.php'); exit;
        } else { $error = 'Invalid registration number or password.'; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login – SLIATE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-container">
    <div class="login-card">
        <div class="login-logo"><i class="fas fa-graduation-cap" style="font-size:3rem;color:var(--primary);"></i></div>
        <h3>Student Login</h3>
        <p class="login-subtitle">Enter your credentials to access results</p>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?><button class="alert-close" onclick="this.parentElement.remove()">×</button></div><?php endif; ?>
        <form method="POST">
            <div class="input-group"><span class="input-icon"><i class="fas fa-id-card"></i></span><input type="text" name="reg_no" placeholder="Registration Number" value="<?= htmlspecialchars($reg_no ?? '') ?>" required></div>
            <div class="input-group"><span class="input-icon"><i class="fas fa-lock"></i></span><input type="password" name="password" id="password" placeholder="Password" required><button type="button" class="toggle-password" data-target="#password"><i class="fas fa-eye"></i></button></div>
            <button type="submit" class="btn btn-primary w-100" style="margin-top:8px;"><i class="fas fa-sign-in-alt"></i> Log In</button>
            <p style="margin-top:16px;font-size:0.9rem;"><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
        </form>
    </div>
    <script src="../assets/js/script.js"></script>
</body>
</html>