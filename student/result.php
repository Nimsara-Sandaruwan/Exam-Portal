<?php
// student/result.php
session_start();
require_once '../config/db.php';
require_once '../functions.php';

// Authentication check
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch all results for this student, ordered by year, semester, subject code
$stmt = $pdo->prepare("
    SELECT r.academic_year,
           r.semester,
           r.marks,
           r.grade,
           r.grade_point,
           r.pass_fail,
           s.subject_code,
           s.subject_name
    FROM results r
    JOIN subjects s ON r.subject_id = s.subject_id
    WHERE r.student_id = ?
    ORDER BY r.academic_year, r.semester, s.subject_code
");
$stmt->execute([$student_id]);
$allResults = $stmt->fetchAll();

// Group results by academic_year and semester
$grouped = [];
foreach ($allResults as $row) {
    $key = $row['academic_year'] . '_' . $row['semester'];
    $grouped[$key]['academic_year'] = $row['academic_year'];
    $grouped[$key]['semester'] = $row['semester'];
    $grouped[$key]['subjects'][] = $row;
}

// Sort groups by year descending, semester descending (latest first)
uksort($grouped, function ($a, $b) {
    list($yearA, $semA) = explode('_', $a);
    list($yearB, $semB) = explode('_', $b);
    if ($yearA == $yearB) {
        return $semB - $semA;
    }
    return $yearB - $yearA;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Results – SLIATE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <?php include '../includes/student_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content w-100">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-alt"></i> My Results</h2>
            <?php if (count($allResults) > 0): ?>
                <a href="download_result_pdf.php" class="btn btn-accent btn-lg" target="_blank">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
            <?php endif; ?>
        </div>

        <?php if (empty($grouped)): ?>
            <div class="alert alert-info alert-dismissible">
                <i class="fas fa-info-circle"></i> No results have been published yet.
                <button class="alert-close" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php else: ?>
            <?php foreach ($grouped as $group): ?>
                <?php
                $year = $group['academic_year'];
                $sem  = $group['semester'];
                $gpa  = calculateSemesterGPA($pdo, $student_id, $year, $sem);
                ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 style="margin:0;">
                            Year <?= htmlspecialchars($year) ?> – Semester <?= htmlspecialchars($sem) ?>
                            <span class="badge" style="background:white;color:var(--primary);margin-left:12px;">GPA: <?= $gpa ?></span>
                        </h5>
                    </div>
                    <div class="table-container">
                        <table class="mb-0" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>Subject Code</th>
                                    <th>Subject Name</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($group['subjects'] as $subj): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($subj['subject_code']) ?></td>
                                        <td><?= htmlspecialchars($subj['subject_name']) ?></td>
                                        <td><?= htmlspecialchars($subj['marks']) ?></td>
                                        <td><span class="badge badge-primary"><?= htmlspecialchars($subj['grade']) ?></span></td>
                                        <td>
                                            <?php if ($subj['pass_fail'] == 'Pass'): ?>
                                                <span class="badge pass-badge">Pass</span>
                                            <?php else: ?>
                                                <span class="badge fail-badge">Fail</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>