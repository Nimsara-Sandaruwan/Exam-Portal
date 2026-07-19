<?php
// admin/dashboard.php
session_start();
require_once '../config/db.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$dept_id = $_SESSION['dept_id'];

// --- Fetch department name ---
$deptStmt = $pdo->prepare("SELECT dept_name FROM departments WHERE dept_id = ?");
$deptStmt->execute([$dept_id]);
$dept = $deptStmt->fetch();

// --- Count students in department ---
$studStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM students WHERE dept_id = ?");
$studStmt->execute([$dept_id]);
$totalStudents = $studStmt->fetch()['total'];

// --- Count subjects in department ---
$subjStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM subjects WHERE dept_id = ?");
$subjStmt->execute([$dept_id]);
$totalSubjects = $subjStmt->fetch()['total'];

// --- Count results for this department (via student_id) ---
$resStmt = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM results r
    JOIN students s ON r.student_id = s.student_id
    WHERE s.dept_id = ?
");
$resStmt->execute([$dept_id]);
$totalResults = $resStmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – SLIATE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="d-flex">
    <!-- Admin Sidebar -->
    <?php include '../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content w-100">
        <h2><i class="fas fa-th-large"></i> Dashboard</h2>
        <p class="text-muted"><?= htmlspecialchars($dept['dept_name']) ?> Department</p>

        <!-- Statistics Cards -->
        <div class="row mt-3">
            <div class="col-4 col-sm-12">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-number"><?= $totalStudents ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>
            <div class="col-4 col-sm-12">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-book"></i></div>
                    <div class="stat-number"><?= $totalSubjects ?></div>
                    <div class="stat-label">Total Subjects</div>
                </div>
            </div>
            <div class="col-4 col-sm-12">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-clipboard-check"></i></div>
                    <div class="stat-number"><?= $totalResults ?></div>
                    <div class="stat-label">Total Results</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-card mt-3">
            <h4>Quick Actions</h4>
            <div class="d-flex gap-2 flex-wrap mt-2">
                <a href="students.php?action=add" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Student</a>
                <a href="results.php?action=add" class="btn btn-success"><i class="fas fa-plus-circle"></i> Enter Results</a>
                <a href="subjects.php?action=add" class="btn btn-accent"><i class="fas fa-plus-circle"></i> Add Subject</a>
                <a href="reports.php" class="btn btn-outline"><i class="fas fa-file-pdf"></i> Generate Report</a>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>