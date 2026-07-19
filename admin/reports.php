<?php
// admin/reports.php
session_start();
require_once '../config/db.php';

// --- Authentication ---
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$dept_id = $_SESSION['dept_id'];

// --- Filter parameters ---
$filter_year     = $_GET['filter_year'] ?? '';
$filter_semester = $_GET['filter_semester'] ?? '';
$filter_subject  = $_GET['filter_subject'] ?? '';

// Build WHERE clause – all reports are department‑scoped
$where  = "s.dept_id = ?";
$params = [$dept_id];
if ($filter_year !== '') {
    $where .= " AND r.academic_year = ?";
    $params[] = $filter_year;
}
if ($filter_semester !== '') {
    $where .= " AND r.semester = ?";
    $params[] = $filter_semester;
}
if ($filter_subject !== '') {
    $where .= " AND r.subject_id = ?";
    $params[] = $filter_subject;
}

// --- Fetch filtered results ---
$sql = "
    SELECT r.result_id, r.academic_year, r.semester, r.marks, r.grade, r.pass_fail,
           s.full_name, s.reg_no,
           sub.subject_code, sub.subject_name
    FROM results r
    JOIN students s ON r.student_id = s.student_id
    JOIN subjects sub ON r.subject_id = sub.subject_id
    WHERE $where
    ORDER BY r.academic_year DESC, r.semester DESC, s.reg_no, sub.subject_code
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

// --- Dropdown data (subjects of this department) ---
$subjStmt = $pdo->prepare("SELECT subject_id, subject_code, subject_name FROM subjects WHERE dept_id = ? ORDER BY subject_code");
$subjStmt->execute([$dept_id]);
$allSubjects = $subjStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports – SLIATE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="d-flex">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content w-100">
        <h2><i class="fas fa-file-pdf"></i> Exam Reports</h2>

        <!-- Filter Card -->
        <div class="filter-box mb-3" style="background:white; border-radius:var(--radius-lg); padding:20px; box-shadow:var(--shadow);">
            <form method="get" action="reports.php" class="row g-2">
                <div class="col-3 col-sm-12 mb-2">
                    <select name="filter_year" class="form-select">
                        <option value="">All Years</option>
                        <?php for ($y = date('Y'); $y >= 2015; $y--): ?>
                            <option value="<?= $y ?>" <?= $filter_year == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-3 col-sm-12 mb-2">
                    <select name="filter_semester" class="form-select">
                        <option value="">All Semesters</option>
                        <option value="1" <?= $filter_semester == '1' ? 'selected' : '' ?>>Semester 1</option>
                        <option value="2" <?= $filter_semester == '2' ? 'selected' : '' ?>>Semester 2</option>
                    </select>
                </div>
                <div class="col-4 col-sm-12 mb-2">
                    <select name="filter_subject" class="form-select">
                        <option value="">All Subjects</option>
                        <?php foreach ($allSubjects as $subj): ?>
                            <option value="<?= $subj['subject_id'] ?>" <?= $filter_subject == $subj['subject_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subj['subject_code'] . ' - ' . $subj['subject_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-2 col-sm-12 mb-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline flex-fill"><i class="fas fa-filter"></i> Filter</button>
                    <a href="reports.php" class="btn btn-outline flex-fill">Clear</a>
                </div>
            </form>
        </div>

        <!-- Action bar -->
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <?php if (count($results) > 0): ?>
                    <a href="download_report_pdf.php?<?= http_build_query($_GET) ?>" class="btn btn-accent">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                <?php endif; ?>
            </div>
            <p class="text-muted mb-0"><?= count($results) ?> record(s) found</p>
        </div>

        <!-- Results Table -->
        <div class="table-container dashboard-card">
            <table class="mb-0" style="width:100%;">
                <thead>
                    <tr>
                        <th>Reg No</th>
                        <th>Student Name</th>
                        <th>Subject</th>
                        <th>Year</th>
                        <th>Sem</th>
                        <th>Marks</th>
                        <th>Grade</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($results) > 0): ?>
                        <?php foreach ($results as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['reg_no']) ?></td>
                                <td><?= htmlspecialchars($r['full_name']) ?></td>
                                <td><?= htmlspecialchars($r['subject_code']) ?></td>
                                <td><?= $r['academic_year'] ?></td>
                                <td><?= $r['semester'] ?></td>
                                <td><?= $r['marks'] ?></td>
                                <td><span class="badge badge-primary"><?= $r['grade'] ?></span></td>
                                <td>
                                    <?php if ($r['pass_fail'] == 'Pass'): ?>
                                        <span class="badge pass-badge">Pass</span>
                                    <?php else: ?>
                                        <span class="badge fail-badge">Fail</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted">No records found for the selected filters.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>