<?php
// student/change_password.php
session_start();
require_once '../config/db.php';

// --- Authentication ---
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch current password hash
$stmt = $pdo->prepare("SELECT password_hash FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

$error   = '';
$success = '';

// --- Process form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password     = $_POST['old_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirmation do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } elseif (!password_verify($old_password, $student['password_hash'])) {
        $error = 'Current password is incorrect.';
    } else {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE students SET password_hash = ? WHERE student_id = ?");
        $updateStmt->execute([$new_hash, $student_id]);
        $success = 'Password changed successfully.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password – Institute of Higher Technology</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Icon alignment */
        .icon-left { width: 20px; height: 20px; vertical-align: middle; margin-right: 8px; fill: currentColor; }
        .form-text { font-size: 0.85rem; color: var(--muted); margin-top: 0.25rem; }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../includes/student_sidebar.php'; ?>

    <div class="main-content w-100">
        <h2 class="mb-4">
            <!-- Shield lock icon -->
            <svg class="icon-left" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
            Change Password
        </h2>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible">
                <svg width="16" height="16" viewBox="0 0 16 16" style="vertical-align:middle; margin-right:5px;"><path d="M8 1a7 7 0 1 1 0 14A7 7 0 0 1 8 1zM7 4h2v5H7V4zm0 6h2v2H7v-2z" fill="currentColor"/></svg>
                <?= htmlspecialchars($error) ?>
                <button class="alert-close" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible">
                <svg width="16" height="16" viewBox="0 0 16 16" style="vertical-align:middle; margin-right:5px;"><path d="M8 1a7 7 0 1 1 0 14A7 7 0 0 1 8 1zM7.5 10.5L4.5 7.5 5.5 6.5l2 2 4-4L12.5 5l-5 5.5z" fill="currentColor"/></svg>
                <?= htmlspecialchars($success) ?>
                <button class="alert-close" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>

        <div class="dashboard-card" style="max-width: 500px;">
            <form method="POST" action="">
                <!-- Current Password -->
                <div class="form-group">
                    <label for="old_password" class="form-label">Current Password</label>
                    <div class="input-group">
                        <span>
                            <svg width="20" height="20" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM12 17c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2z"/></svg>
                        </span>
                        <input type="password" class="form-control" id="old_password" name="old_password" required>
                        <button class="toggle-password" type="button" data-target="#old_password">👁️</button>
                    </div>
                </div>

                <!-- New Password -->
                <div class="form-group">
                    <label for="new_password" class="form-label">New Password</label>
                    <div class="input-group">
                        <span>
                            <svg width="20" height="20" viewBox="0 0 24 24"><path d="M12.65 10C11.83 7.67 9.61 6 7 6c-3.31 0-6 2.69-6 6s2.69 6 6 6c2.61 0 4.83-1.67 5.65-4H17v4h4v-4h2v-4H12.65zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                        </span>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <button class="toggle-password" type="button" data-target="#new_password">👁️</button>
                    </div>
                    <div class="form-text">At least 6 characters</div>
                </div>

                <!-- Confirm New Password -->
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <div class="input-group">
                        <span>
                            <svg width="20" height="20" viewBox="0 0 24 24"><path d="M12.65 10C11.83 7.67 9.61 6 7 6c-3.31 0-6 2.69-6 6s2.69 6 6 6c2.61 0 4.83-1.67 5.65-4H17v4h4v-4h2v-4H12.65zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                        </span>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <button class="toggle-password" type="button" data-target="#confirm_password">👁️</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <svg class="icon-left" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    Update Password
                </button>
                <a href="profile.php" class="btn btn-outline-secondary" style="margin-left:10px;">
                    ← Back to Profile
                </a>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>