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
    <title>Reports – Institute of Higher Technology</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
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
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content w-100">
        <h2 class="mb-3">Exam Reports</h2>

        <!-- Filter Card -->
        <div class="filter-box mb-3">
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
                <div class="col-2 col-sm-12 mb-2 d-flex" style="gap: 10px;">
                    <button type="submit" class="btn btn-outline-primary flex-fill">
                        <svg viewBox="0 0 24 24"><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></svg> Filter
                    </button>
                    <a href="reports.php" class="btn btn-outline-secondary flex-fill">Clear</a>
                </div>
            </form>
        </div>

        <!-- Action bar -->
        <div class="d-flex justify-content-between align-items-center mb-3" style="flex-wrap: wrap; gap: 10px;">
            <div>
                <?php if (count($results) > 0): ?>
                    <a href="download_report_pdf.php?<?= http_build_query($_GET) ?>" class="btn btn-success">
                        <svg viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg> Download PDF
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
                                <td><span class="badge badge-secondary"><?= $r['grade'] ?></span></td>
                                <td>
                                    <?php if ($r['pass_fail'] == 'Pass'): ?>
                                        <span class="badge badge-success">Pass</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Fail</span>
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