<?php
// student/profile.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch current student data
$stmt = $pdo->prepare("
    SELECT s.*, d.dept_name
    FROM students s
    JOIN departments d ON s.dept_id = d.dept_id
    WHERE s.student_id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

$error = '';
$success = '';

// -------------------------------------------------------
// 1. HANDLE PASSWORD CHANGE
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_password     = $_POST['old_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill all password fields.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirmation do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } elseif (!password_verify($old_password, $student['password_hash'])) {
        $error = 'The old password is incorrect.';
    } else {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE students SET password_hash = ? WHERE student_id = ?");
        $updateStmt->execute([$new_hash, $student_id]);
        $success = 'Password changed successfully.';
    }
}

// -------------------------------------------------------
// 2. HANDLE PROFILE PICTURE UPLOAD
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_picture'])) {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath  = $_FILES['profile_pic']['tmp_name'];
        $fileName     = $_FILES['profile_pic']['name'];
        $fileSize     = $_FILES['profile_pic']['size'];
        $fileType     = $_FILES['profile_pic']['type'];

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            $error = 'Only JPG, PNG, and GIF files are allowed.';
        } elseif ($fileSize > 2 * 1024 * 1024) {
            $error = 'File size must be less than 2 MB.';
        } else {
            $newFileName = 'student_' . $student_id . '_' . time() . '.' . $fileExtension;
            $uploadDir   = __DIR__ . '/../assets/uploads/';
            $destPath    = $uploadDir . $newFileName;

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                if ($student['profile_pic'] !== 'default.png' && file_exists($uploadDir . $student['profile_pic'])) {
                    unlink($uploadDir . $student['profile_pic']);
                }
                $picStmt = $pdo->prepare("UPDATE students SET profile_pic = ? WHERE student_id = ?");
                $picStmt->execute([$newFileName, $student_id]);
                $student['profile_pic'] = $newFileName;
                $success = 'Profile picture updated successfully.';
            } else {
                $error = 'Failed to upload the picture. Please try again.';
            }
        }
    } else {
        $error = 'Please select a file to upload.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile – Institute of Higher Technology</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Small additional styles for icon alignment */
        .icon-left { width: 20px; height: 20px; vertical-align: middle; margin-right: 8px; fill: currentColor; }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../includes/student_sidebar.php'; ?>

    <div class="main-content w-100">
        <h2 class="mb-4">My Profile</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible">
                <?= htmlspecialchars($error) ?>
                <button class="alert-close" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible">
                <?= htmlspecialchars($success) ?>
                <button class="alert-close" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Left: Picture & basic info -->
            <div class="col-4 col-sm-12">
                <div class="dashboard-card text-center">
                    <img src="../assets/uploads/<?= htmlspecialchars($student['profile_pic'] ?? 'default.png') ?>"
                         alt="Profile Picture" class="profile-pic mb-3">
                    <h4><?= htmlspecialchars($student['full_name']) ?></h4>
                    <p class="text-muted"><?= htmlspecialchars($student['reg_no']) ?></p>
                    <p><strong>Department:</strong> <?= htmlspecialchars($student['dept_name']) ?></p>
                    <p><strong>Academic Year:</strong> <?= htmlspecialchars($student['academic_year']) ?> |
                       <strong>Mode:</strong> <?= $student['study_mode'] == 'F' ? 'Full-Time' : 'Part-Time' ?></p>
                </div>
            </div>

            <!-- Right: Forms -->
            <div class="col-8 col-sm-12">
                <!-- Change Password Card -->
                <div class="dashboard-card mb-4">
                    <h5>
                        <svg class="icon-left" viewBox="0 0 24 24"><path d="M12.65 10C11.83 7.67 9.61 6 7 6c-3.31 0-6 2.69-6 6s2.69 6 6 6c2.61 0 4.83-1.67 5.65-4H17v4h4v-4h2v-4H12.65zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                        Change Password
                    </h5>
                    <form method="POST" action="">
                        <input type="hidden" name="change_password" value="1">
                        <div class="form-group">
                            <label for="old_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="old_password" name="old_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </form>
                </div>

                <!-- Change Profile Picture Card -->
                <div class="dashboard-card">
                    <h5>
                        <svg class="icon-left" viewBox="0 0 24 24"><path d="M12 15.2c1.77 0 3.2-1.43 3.2-3.2s-1.43-3.2-3.2-3.2-3.2 1.43-3.2 3.2 1.43 3.2 3.2 3.2zM9 2L7.17 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2h-3.17L15 2H9zm3 15c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5z"/></svg>
                        Change Profile Picture
                    </h5>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="upload_picture" value="1">
                        <div class="form-group">
                            <label for="profile_pic" class="form-label">Choose a new photo (JPG, PNG, GIF – max 2 MB)</label>
                            <input type="file" class="form-control" id="profile_pic" name="profile_pic" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload Picture</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>