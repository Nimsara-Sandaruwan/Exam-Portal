<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../index.php'); exit; }
require_once __DIR__ . '/../config/db.php';
$admin_id = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT a.full_name, d.dept_name FROM admins a JOIN departments d ON a.dept_id = d.dept_id WHERE a.admin_id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
$adminName = $admin ? htmlspecialchars($admin['full_name']) : 'Admin';
$deptName = $admin ? htmlspecialchars($admin['dept_name']) : '';
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-shield-haltered" style="font-size:2rem;color:var(--accent);"></i>
        <h4>SLIATE</h4>
        <small>Admin Panel</small>
    </div>
    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($adminName, 0, 1)) ?></div>
        <div class="user-name"><?= $adminName ?></div>
        <div class="user-dept"><?= $deptName ?></div>
    </div>
    <ul class="sidebar-nav">
        <li><a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
        <li><a href="students.php"><i class="fas fa-users"></i> Students</a></li>
        <li><a href="results.php"><i class="fas fa-clipboard-check"></i> Results</a></li>
        <li><a href="subjects.php"><i class="fas fa-book"></i> Subjects</a></li>
        <li><a href="reports.php"><i class="fas fa-file-pdf"></i> Reports</a></li>
    </ul>
    <div class="sidebar-footer">
        <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>