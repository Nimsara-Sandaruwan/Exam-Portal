<?php
// admin/results.php
session_start();
require_once '../config/db.php';
require_once '../functions.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$dept_id = $_SESSION['dept_id'];
$error   = '';
$success = '';

// ---------- Determine action ----------
$action    = $_GET['action'] ?? 'list';
$result_id = $_GET['id'] ?? null;

// ================== DELETE ==================
if ($action === 'delete' && $result_id) {
    $check = $pdo->prepare("
        SELECT r.result_id FROM results r
        JOIN students s ON r.student_id = s.student_id
        WHERE r.result_id = ? AND s.dept_id = ?
    ");
    $check->execute([$result_id, $dept_id]);
    if ($check->fetch()) {
        $del = $pdo->prepare("DELETE FROM results WHERE result_id = ?");
        $del->execute([$result_id]);
        $success = "Result deleted.";
    } else {
        $error = "Result not found or access denied.";
    }
    header("Location: results.php?msg=" . urlencode($success ?: $error));
    exit;
}

// ================== SAVE (ADD / UPDATE) ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_result'])) {
    $result_id_post = $_POST['result_id'] ?? '';
    $student_id     = $_POST['student_id'] ?? '';
    $subject_id     = $_POST['subject_id'] ?? '';
    $academic_year  = $_POST['academic_year'] ?? '';
    $semester       = $_POST['semester'] ?? '';
    $marks          = $_POST['marks'] ?? '';

    if (empty($student_id) || empty($subject_id) || empty($academic_year) || empty($semester) || $marks === '') {
        $error = "All fields are required.";
    } else {
        $studCheck = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ? AND dept_id = ?");
        $studCheck->execute([$student_id, $dept_id]);
        $subjCheck = $pdo->prepare("SELECT subject_id FROM subjects WHERE subject_id = ? AND dept_id = ?");
        $subjCheck->execute([$subject_id, $dept_id]);
        if (!$studCheck->fetch() || !$subjCheck->fetch()) {
            $error = "Invalid student or subject selection.";
        }
    }

    if (!$error) {
        $gradeInfo = computeGrade($marks);
        $grade     = $gradeInfo['grade'];
        $gp        = $gradeInfo['gp'];
        $passFail  = ($grade == 'I(SE)') ? 'Fail' : 'Pass';

        if ($result_id_post) {
            // UPDATE
            $ownCheck = $pdo->prepare("
                SELECT r.result_id FROM results r
                JOIN students s ON r.student_id = s.student_id
                WHERE r.result_id = ? AND s.dept_id = ?
            ");
            $ownCheck->execute([$result_id_post, $dept_id]);
            if (!$ownCheck->fetch()) {
                $error = "Result not found.";
            } else {
                $upd = $pdo->prepare("UPDATE results SET student_id=?, subject_id=?, academic_year=?, semester=?, marks=?, grade=?, grade_point=?, pass_fail=? WHERE result_id=?");
                $upd->execute([$student_id, $subject_id, $academic_year, $semester, $marks, $grade, $gp, $passFail, $result_id_post]);
                $success = "Result updated.";
                header("Location: results.php?msg=updated");
                exit;
            }
        } else {
            // INSERT
            $dup = $pdo->prepare("SELECT result_id FROM results WHERE student_id=? AND subject_id=? AND academic_year=? AND semester=?");
            $dup->execute([$student_id, $subject_id, $academic_year, $semester]);
            if ($dup->fetch()) {
                $error = "This result already exists.";
            } else {
                $ins = $pdo->prepare("INSERT INTO results (student_id, subject_id, academic_year, semester, marks, grade, grade_point, pass_fail) VALUES (?,?,?,?,?,?,?,?)");
                $ins->execute([$student_id, $subject_id, $academic_year, $semester, $marks, $grade, $gp, $passFail]);
                $success = "Result added.";
                header("Location: results.php?msg=added");
                exit;
            }
        }
    }
}

// ================== FETCH FOR EDIT ==================
$editResult = null;
if ($action === 'edit' && $result_id) {
    $editStmt = $pdo->prepare("
        SELECT r.*, sub.year_of_study AS sub_year
        FROM results r
        JOIN students s ON r.student_id = s.student_id
        JOIN subjects sub ON r.subject_id = sub.subject_id
        WHERE r.result_id = ? AND s.dept_id = ?
    ");
    $editStmt->execute([$result_id, $dept_id]);
    $editResult = $editStmt->fetch();
    if (!$editResult) {
        $error = "Result not found.";
        $action = 'list';
    }
}

// ================== FILTERING ==================
$filter_year     = $_GET['filter_year'] ?? '';
$filter_semester = $_GET['filter_semester'] ?? '';
$filter_subject  = $_GET['filter_subject'] ?? '';

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

// ================== FETCH ALL RESULTS ==================
$listStmt = $pdo->prepare("
    SELECT r.result_id, r.academic_year, r.semester, r.marks, r.grade, r.pass_fail,
           s.full_name, s.reg_no,
           sub.subject_code, sub.subject_name
    FROM results r
    JOIN students s ON r.student_id = s.student_id
    JOIN subjects sub ON r.subject_id = sub.subject_id
    WHERE $where
    ORDER BY r.academic_year DESC, r.semester DESC, s.reg_no, sub.subject_code
");
$listStmt->execute($params);
$resultsList = $listStmt->fetchAll();

// ================== DROPDOWN DATA ==================
$studentsDrop = $pdo->prepare("SELECT student_id, full_name, reg_no FROM students WHERE dept_id = ? ORDER BY reg_no");
$studentsDrop->execute([$dept_id]);
$students = $studentsDrop->fetchAll();

// All subjects for the department
$subjAll = $pdo->prepare("SELECT subject_id, subject_code, subject_name, year_of_study, semester FROM subjects WHERE dept_id = ? ORDER BY subject_code");
$subjAll->execute([$dept_id]);
$allSubjects = $subjAll->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Results – SLIATE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="d-flex">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content w-100">
        <h2>
            <?= ($action === 'add' || $action === 'edit') ? ($action === 'add' ? '<i class="fas fa-plus-circle"></i> Add Exam Result' : '<i class="fas fa-edit"></i> Edit Exam Result') : '<i class="fas fa-clipboard-check"></i> Exam Results' ?>
        </h2>

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

        <?php if ($action === 'add' || $action === 'edit'): ?>
            <!-- ==================== ADD / EDIT FORM ==================== -->
            <div class="dashboard-card">
                <form method="post" action="results.php">
                    <input type="hidden" name="result_id" value="<?= $editResult['result_id'] ?? '' ?>">

                    <div class="row">
                        <div class="col-6 col-sm-12 mb-3">
                            <label class="form-label">Student *</label>
                            <select name="student_id" class="form-select" required>
                                <option value="">-- Select Student --</option>
                                <?php foreach ($students as $st): ?>
                                    <option value="<?= $st['student_id'] ?>"
                                        <?= (isset($editResult) && $editResult['student_id'] == $st['student_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($st['reg_no'] . ' - ' . $st['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-3 col-sm-12 mb-3">
                            <label class="form-label">Academic Year *</label>
                            <select name="academic_year" class="form-select" required>
                                <option value="">-- Year --</option>
                                <?php for ($y = date('Y'); $y >= 2015; $y--): ?>
                                    <option value="<?= $y ?>" <?= (isset($editResult) && $editResult['academic_year'] == $y) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-3 col-sm-12 mb-3">
                            <label class="form-label">Semester *</label>
                            <select name="semester" id="semester" class="form-select" required>
                                <option value="">-- Sem --</option>
                                <option value="1" <?= (isset($editResult) && $editResult['semester'] == 1) ? 'selected' : '' ?>>Semester 1</option>
                                <option value="2" <?= (isset($editResult) && $editResult['semester'] == 2) ? 'selected' : '' ?>>Semester 2</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-3 col-sm-12 mb-3">
                            <label class="form-label">Year of Study *</label>
                            <select id="year_of_study" class="form-select" required>
                                <option value="">-- Select Year --</option>
                                <option value="1" <?= (isset($editResult) && $editResult['sub_year'] == 1) ? 'selected' : '' ?>>Year 1</option>
                                <option value="2" <?= (isset($editResult) && $editResult['sub_year'] == 2) ? 'selected' : '' ?>>Year 2</option>
                                <option value="3" <?= (isset($editResult) && $editResult['sub_year'] == 3) ? 'selected' : '' ?>>Year 3</option>
                                <option value="4" <?= (isset($editResult) && $editResult['sub_year'] == 4) ? 'selected' : '' ?>>Year 4</option>
                            </select>
                        </div>

                        <div class="col-5 col-sm-12 mb-3">
                            <label class="form-label">Subject *</label>
                            <select name="subject_id" id="subject_id" class="form-select" required>
                                <option value="">-- Select Year of Study & Semester first --</option>
                                <?php if (isset($editResult)): ?>
                                    <?php foreach ($allSubjects as $subj): ?>
                                        <?php if ($subj['subject_id'] == $editResult['subject_id']): ?>
                                            <option value="<?= $subj['subject_id'] ?>" selected>
                                                <?= htmlspecialchars($subj['subject_code'] . ' - ' . $subj['subject_name']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-4 col-sm-12 mb-3">
                            <label class="form-label">Marks *</label>
                            <input type="number" name="marks" class="form-control" min="0" max="100" step="0.01"
                                   value="<?= htmlspecialchars($editResult['marks'] ?? '') ?>" required>
                        </div>
                    </div>

                    <button type="submit" name="save_result" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= $editResult ? 'Update Result' : 'Add Result' ?>
                    </button>
                    <a href="results.php" class="btn btn-outline" style="margin-left:10px;">Cancel</a>
                </form>
            </div>
        <?php else: ?>
            <!-- ==================== FILTERS & LIST ==================== -->
            <div class="filter-box mb-3" style="background:white; border-radius:var(--radius-lg); padding:20px; box-shadow:var(--shadow);">
                <form method="get" action="results.php" class="row g-2">
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
                    <div class="col-2 col-sm-12 mb-2">
                        <button type="submit" class="btn btn-outline w-100"><i class="fas fa-filter"></i> Filter</button>
                    </div>
                </form>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <a href="results.php?action=add" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add Result</a>
                <a href="results.php" class="btn btn-outline">Clear Filters</a>
            </div>

            <div class="table-container dashboard-card">
                <table class="mb-0" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Reg No</th>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>Year</th>
                            <th>Sem</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($resultsList) > 0): ?>
                            <?php foreach ($resultsList as $r): ?>
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
                                    <td class="text-center">
                                        <a href="results.php?action=edit&id=<?= $r['result_id'] ?>" class="btn btn-sm btn-outline" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="results.php?action=delete&id=<?= $r['result_id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete" title="Delete"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="text-center text-muted">No results found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Dynamic subject loading (CLIENT SIDE) -->
<script>
// Store subjects data for filtering
var allSubjectsData = <?= json_encode($allSubjects) ?>;

(function() {
    const yearOfStudySelect = document.getElementById('year_of_study');
    const semSelect         = document.getElementById('semester');
    const subjectDrop       = document.getElementById('subject_id');

    if (!yearOfStudySelect || !semSelect || !subjectDrop) return;

    function filterSubjects() {
        const yearOfStudy = yearOfStudySelect.value;
        const sem = semSelect.value;
        subjectDrop.innerHTML = '<option value="">-- Select Subject --</option>';
        if (!yearOfStudy || !sem) return;

        const filtered = allSubjectsData.filter(function(subj) {
            return subj.year_of_study == yearOfStudy && subj.semester == sem;
        });

        filtered.forEach(function(subj) {
            const opt = document.createElement('option');
            opt.value = subj.subject_id;
            opt.textContent = subj.subject_code + ' - ' + subj.subject_name;
            subjectDrop.appendChild(opt);
        });

        if (filtered.length === 0) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'No subjects found for this selection';
            opt.disabled = true;
            subjectDrop.appendChild(opt);
        }
    }

    yearOfStudySelect.addEventListener('change', filterSubjects);
    semSelect.addEventListener('change', filterSubjects);

    <?php if (isset($editResult)): ?>
        filterSubjects();
    <?php endif; ?>
})();
</script>
<script src="../assets/js/script.js"></script>
</body>
</html>