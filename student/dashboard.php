<?php
// student/dashboard.php
session_start();
require_once '../config/db.php';
require_once '../functions.php';

// Redirect if not logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch student details along with department name
$stmt = $pdo->prepare("
    SELECT s.*, d.dept_name 
    FROM students s 
    JOIN departments d ON s.dept_id = d.dept_id 
    WHERE s.student_id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// Find the most recent semester that has results
$latestStmt = $pdo->prepare("
    SELECT academic_year, semester 
    FROM results 
    WHERE student_id = ? 
    ORDER BY academic_year DESC, semester DESC 
    LIMIT 1
");
$latestStmt->execute([$student_id]);
$latest = $latestStmt->fetch();

$current_gpa = '';
if ($latest) {
    $current_gpa = calculateSemesterGPA($pdo, $student_id, $latest['academic_year'], $latest['semester']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard – SLIATE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="d-flex">
    <?php include '../includes/student_sidebar.php'; ?>

    <div class="main-content">
        <h2><i class="fas fa-th-large"></i> Dashboard</h2>

        <div class="dashboard-card mt-3">
            <div class="row" style="align-items: center;">
                <div class="col-3 col-sm-12 text-center">
                    <img
                        src="../assets/uploads/<?= htmlspecialchars($student['profile_pic'] ?? 'default.png') ?>"
                        alt="Profile Picture"
                        class="profile-pic"
                    >
                </div>
                <div class="col-9 col-sm-12">
                    <h4><?= htmlspecialchars($student['full_name']) ?></h4>
                    <table class="mb-0" style="width:100%; margin-top:16px;">
                        <tr>
                            <td><strong>Registration No:</strong></td>
                            <td><?= htmlspecialchars($student['reg_no']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Department:</strong></td>
                            <td><?= htmlspecialchars($student['dept_name']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Academic Year:</strong></td>
                            <td><?= htmlspecialchars($student['academic_year']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Study Mode:</strong></td>
                            <td><?= $student['study_mode'] == 'F' ? 'Full-Time' : 'Part-Time' ?></td>
                        </tr>
                        <?php if ($latest): ?>
                        <tr>
                            <td><strong>Current Semester:</strong></td>
                            <td>Year <?= $latest['academic_year'] ?>, Semester <?= $latest['semester'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Semester GPA:</strong></td>
                            <td><span class="badge badge-primary" style="font-size:1rem;"><?= $current_gpa ?></span></td>
                        </tr>
                        <?php else: ?>
                        <tr>
                            <td colspan="2" class="text-muted">No results available yet.</td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>