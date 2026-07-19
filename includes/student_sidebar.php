<?php
// includes/student_sidebar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}

// Retrieve student's name from session for display
$studentName = isset($_SESSION['student_name']) ? htmlspecialchars($_SESSION['student_name']) : 'Student';
?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <i class="fas fa-graduation-cap"></i>
        <h4>SLIATE</h4>
        <small>Student Portal</small>
    </div>

    <!-- User Info -->
    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($studentName, 0, 1)) ?></div>
        <div class="user-name"><?= $studentName ?></div>
    </div>

    <!-- Navigation Links -->
    <ul class="sidebar-nav">
        <li>
            <a href="dashboard.php">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="result.php">
                <i class="fas fa-file-alt"></i> Results
            </a>
        </li>
        <li>
            <a href="profile.php">
                <i class="fas fa-user-circle"></i> Profile
            </a>
        </li>
    </ul>

    <!-- Logout -->
    <div class="sidebar-footer">
        <a href="../auth/logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>