<?php
// admin/subjects.php
session_start();
require_once '../config/db.php';

// --- Authentication ---
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$dept_id = $_SESSION['dept_id'];

$error   = '';
$success = '';

// --- Determine action ---
$action    = $_GET['action'] ?? 'list';
$subject_id = $_GET['id'] ?? null;

// ============================================================
//   DELETE SUBJECT
// ============================================================
if ($action === 'delete' && $subject_id) {
    // Check ownership
    $check = $pdo->prepare("SELECT subject_id FROM subjects WHERE subject_id = ? AND dept_id = ?");
    $check->execute([$subject_id, $dept_id]);
    if ($check->fetch()) {
        $del = $pdo->prepare("DELETE FROM subjects WHERE subject_id = ?");
        $del->execute([$subject_id]);
        $success = "Subject deleted successfully.";
    } else {
        $error = "Subject not found or access denied.";
    }
    header("Location: subjects.php?msg=" . urlencode($success ?: $error));
    exit;
}

// ============================================================
//   PROCESS ADD / EDIT FORM
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_subject'])) {
    $subject_id_post = $_POST['subject_id'] ?? '';
    $subject_code    = trim($_POST['subject_code'] ?? '');
    $subject_name    = trim($_POST['subject_name'] ?? '');
    $year_of_study   = intval($_POST['year_of_study'] ?? 0);
    $semester        = intval($_POST['semester'] ?? 0);

    if (empty($subject_code) || empty($subject_name) || $year_of_study < 1 || $semester < 1) {
        $error = "All fields are required.";
    } elseif (!in_array($year_of_study, [1,2,3,4])) {
        $error = "Invalid year of study.";
    } elseif (!in_array($semester, [1,2])) {
        $error = "Invalid semester.";
    } else {
        if ($subject_id_post) {
            // UPDATE
            $ownCheck = $pdo->prepare("SELECT subject_id FROM subjects WHERE subject_id = ? AND dept_id = ?");
            $ownCheck->execute([$subject_id_post, $dept_id]);
            if (!$ownCheck->fetch()) {
                $error = "Subject not found.";
            } else {
                try {
                    $upd = $pdo->prepare("UPDATE subjects SET subject_code = ?, subject_name = ?, year_of_study = ?, semester = ? WHERE subject_id = ?");
                    $upd->execute([$subject_code, $subject_name, $year_of_study, $semester, $subject_id_post]);
                    $success = "Subject updated.";
                    header("Location: subjects.php?msg=updated");
                    exit;
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) { // Duplicate entry
                        $error = "Subject code already exists in this department.";
                    } else {
                        $error = "Database error.";
                    }
                }
            }
        } else {
            // INSERT
            try {
                $ins = $pdo->prepare("INSERT INTO subjects (dept_id, subject_code, subject_name, year_of_study, semester) VALUES (?, ?, ?, ?, ?)");
                $ins->execute([$dept_id, $subject_code, $subject_name, $year_of_study, $semester]);
                $success = "Subject added.";
                header("Location: subjects.php?msg=added");
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "Subject code already exists for this department.";
                } else {
                    $error = "Database error.";
                }
            }
        }
    }
}

// ============================================================
//   FETCH SINGLE SUBJECT FOR EDIT
// ============================================================
$editSubject = null;
if ($action === 'edit' && $subject_id) {
    $editStmt = $pdo->prepare("SELECT * FROM subjects WHERE subject_id = ? AND dept_id = ?");
    $editStmt->execute([$subject_id, $dept_id]);
    $editSubject = $editStmt->fetch();
    if (!$editSubject) {
        $error = "Subject not found.";
        $action = 'list';
    }
}

// ============================================================
//   FILTERING
// ============================================================
$filter_year = $_GET['filter_year'] ?? '';
$filter_sem  = $_GET['filter_semester'] ?? '';
$search      = $_GET['search'] ?? '';

$where  = "dept_id = ?";
$params = [$dept_id];
if ($filter_year !== '') {
    $where .= " AND year_of_study = ?";
    $params[] = $filter_year;
}
if ($filter_sem !== '') {
    $where .= " AND semester = ?";
    $params[] = $filter_sem;
}
if (!empty($search)) {
    $where .= " AND (subject_code LIKE ? OR subject_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$listStmt = $pdo->prepare("SELECT * FROM subjects WHERE $where ORDER BY year_of_study, semester, subject_code");
$listStmt->execute($params);
$subjects = $listStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Subjects – SLIATE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="d-flex">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content w-100">
        <h2>
            <?= ($action === 'add' || $action === 'edit') ? ($action === 'add' ? '<i class="fas fa-plus-circle"></i> Add New Subject' : '<i class="fas fa-edit"></i> Edit Subject') : '<i class="fas fa-book"></i> Manage Subjects' ?>
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
            <!-- ==================== FORM ==================== -->
            <div class="dashboard-card">
                <form method="post" action="subjects.php">
                    <input type="hidden" name="subject_id" value="<?= $editSubject['subject_id'] ?? '' ?>">

                    <div class="row">
                        <div class="col-4 col-sm-12 mb-3">
                            <label class="form-label">Subject Code *</label>
                            <input type="text" name="subject_code" class="form-control" placeholder="e.g. HNDIT1012"
                                   value="<?= htmlspecialchars($editSubject['subject_code'] ?? '') ?>" required>
                        </div>
                        <div class="col-4 col-sm-12 mb-3">
                            <label class="form-label">Subject Name *</label>
                            <input type="text" name="subject_name" class="form-control" placeholder="e.g. Visual Application Programming"
                                   value="<?= htmlspecialchars($editSubject['subject_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-2 col-sm-12 mb-3">
                            <label class="form-label">Year of Study *</label>
                            <select name="year_of_study" class="form-select" required>
                                <option value="">--</option>
                                <?php for ($y = 1; $y <= 4; $y++): ?>
                                    <option value="<?= $y ?>" <?= (isset($editSubject) && $editSubject['year_of_study'] == $y) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-2 col-sm-12 mb-3">
                            <label class="form-label">Semester *</label>
                            <select name="semester" class="form-select" required>
                                <option value="">--</option>
                                <option value="1" <?= (isset($editSubject) && $editSubject['semester'] == 1) ? 'selected' : '' ?>>Semester 1</option>
                                <option value="2" <?= (isset($editSubject) && $editSubject['semester'] == 2) ? 'selected' : '' ?>>Semester 2</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="save_subject" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= $editSubject ? 'Update Subject' : 'Add Subject' ?>
                    </button>
                    <a href="subjects.php" class="btn btn-outline" style="margin-left:10px;">Cancel</a>
                </form>
            </div>
        <?php else: ?>
            <!-- ==================== FILTER & LIST ==================== -->
            <div class="filter-box mb-3" style="background:white; border-radius:var(--radius-lg); padding:20px; box-shadow:var(--shadow);">
                <form method="get" action="subjects.php" class="row g-2">
                    <div class="col-3 col-sm-12 mb-2">
                        <input type="text" name="search" class="form-control" placeholder="Search code or name" value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-2 col-sm-12 mb-2">
                        <select name="filter_year" class="form-select">
                            <option value="">All Years</option>
                            <?php for ($y = 1; $y <= 4; $y++): ?>
                                <option value="<?= $y ?>" <?= $filter_year == $y ? 'selected' : '' ?>>Year <?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-2 col-sm-12 mb-2">
                        <select name="filter_semester" class="form-select">
                            <option value="">All Semesters</option>
                            <option value="1" <?= $filter_sem == '1' ? 'selected' : '' ?>>Semester 1</option>
                            <option value="2" <?= $filter_sem == '2' ? 'selected' : '' ?>>Semester 2</option>
                        </select>
                    </div>
                    <div class="col-2 col-sm-12 mb-2">
                        <button type="submit" class="btn btn-outline w-100"><i class="fas fa-filter"></i> Filter</button>
                    </div>
                </form>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <a href="subjects.php?action=add" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add Subject</a>
                <a href="subjects.php" class="btn btn-outline">Clear Filters</a>
            </div>

            <div class="table-container dashboard-card">
                <table class="mb-0" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Year</th>
                            <th>Semester</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($subjects) > 0): ?>
                            <?php foreach ($subjects as $subj): ?>
                                <tr>
                                    <td><?= htmlspecialchars($subj['subject_code']) ?></td>
                                    <td><?= htmlspecialchars($subj['subject_name']) ?></td>
                                    <td><?= $subj['year_of_study'] ?></td>
                                    <td><?= $subj['semester'] ?></td>
                                    <td class="text-center">
                                        <a href="subjects.php?action=edit&id=<?= $subj['subject_id'] ?>" class="btn btn-sm btn-outline" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="subjects.php?action=delete&id=<?= $subj['subject_id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete" title="Delete"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted">No subjects found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>