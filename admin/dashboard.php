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
    <title>Admin Dashboard – Institute of Higher Technology</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Icon sizes for stat cards */
        .card-icon svg {
            width: 48px;
            height: 48px;
            fill: currentColor;
        }
        .btn svg {
            width: 18px;
            height: 18px;
            vertical-align: middle;
            margin-right: 5px;
            fill: currentColor;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Admin Sidebar -->
    <?php include '../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content w-100">
        <h2 class="mb-2">Dashboard</h2>
        <p class="text-muted"><?= htmlspecialchars($dept['dept_name']) ?> Department</p>

        <!-- Statistics Cards -->
        <div class="row mt-4">
            <div class="col-4 col-sm-12 mb-3">
                <div class="dashboard-card text-center">
                    <div class="card-icon">
                        <!-- People icon -->
                        <svg viewBox="0 0 24 24">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                        </svg>
                    </div>
                    <h3><?= $totalStudents ?></h3>
                    <p class="text-muted">Total Students</p>
                </div>
            </div>

            <div class="col-4 col-sm-12 mb-3">
                <div class="dashboard-card text-center">
                    <div class="card-icon">
                        <!-- Book icon -->
                        <svg viewBox="0 0 24 24">
                            <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/>
                        </svg>
                    </div>
                    <h3><?= $totalSubjects ?></h3>
                    <p class="text-muted">Total Subjects</p>
                </div>
            </div>

            <div class="col-4 col-sm-12 mb-3">
                <div class="dashboard-card text-center">
                    <div class="card-icon">
                        <!-- Results icon (checklist) -->
                        <svg viewBox="0 0 24 24">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                        </svg>
                    </div>
                    <h3><?= $totalResults ?></h3>
                    <p class="text-muted">Total Results Entered</p>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="dashboard-card">
                    <h5 class="mb-3">Quick Actions</h5>
                    <div class="d-flex gap-2" style="flex-wrap: wrap;">
                        <a href="students.php?action=add" class="btn btn-primary">
                            <svg viewBox="0 0 24 24"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            Add Student
                        </a>
                        <a href="results.php?action=add" class="btn btn-success">
                            <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                            Enter Results
                        </a>
                        <a href="subjects.php?action=add" class="btn btn-info">
                            <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                            Add Subject
                        </a>
                        <a href="reports.php" class="btn btn-secondary">
                            <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm-2 16H8v-2h4v2zm3-4H8v-2h7v2zm0-4H8V8h7v2z"/></svg>
                            Generate Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>