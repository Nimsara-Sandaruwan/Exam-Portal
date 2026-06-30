<?php
// includes/admin_sidebar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Fetch admin details
$admin_id = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT a.full_name, d.dept_name 
                       FROM admins a 
                       JOIN departments d ON a.dept_id = d.dept_id 
                       WHERE a.admin_id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

$adminName = $admin ? htmlspecialchars($admin['full_name']) : 'Administrator';
$deptName  = $admin ? htmlspecialchars($admin['dept_name']) : 'Department';
?>
<!-- Inline SVG Icons -->
<svg style="display:none;">
    <symbol id="icon-dashboard" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></symbol>
    <symbol id="icon-people" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></symbol>
    <symbol id="icon-results" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></symbol>
    <symbol id="icon-book" viewBox="0 0 24 24"><path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/></symbol>
    <symbol id="icon-report" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm-2 16H8v-2h4v2zm3-4H8v-2h7v2zm0-4H8V8h7v2z"/></symbol>
    <symbol id="icon-logout" viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></symbol>
</svg>

<!-- Sidebar -->
<div class="sidebar">
    <!-- Brand -->
    <div class="brand">
        <h4>Institute Portal</h4>
        <small>Admin Panel</small>
    </div>

    <!-- Admin Info -->
    <div class="user-info">
        <div>Welcome,</div>
        <div class="name"><?= $adminName ?></div>
        <div class="dept"><?= $deptName ?></div>
    </div>

    <!-- Navigation Links -->
    <ul class="nav">
        <li>
            <a href="dashboard.php">
                <svg width="20" height="20"><use href="#icon-dashboard"/></svg> Dashboard
            </a>
        </li>
        <li>
            <a href="students.php">
                <svg width="20" height="20"><use href="#icon-people"/></svg> Students
            </a>
        </li>
        <li>
            <a href="results.php">
                <svg width="20" height="20"><use href="#icon-results"/></svg> Results
            </a>
        </li>
        <li>
            <a href="subjects.php">
                <svg width="20" height="20"><use href="#icon-book"/></svg> Subjects
            </a>
        </li>
        <li>
            <a href="reports.php">
                <svg width="20" height="20"><use href="#icon-report"/></svg> Reports
            </a>
        </li>
    </ul>

    <!-- Logout -->
    <div class="logout">
        <a href="../auth/logout.php">
            <svg width="20" height="20"><use href="#icon-logout"/></svg> Logout
        </a>
    </div>
</div>