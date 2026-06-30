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
                    if ($e->getCode() == 23000) {
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
    <title>Manage Subjects – Institute of Higher Technology</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .btn svg {
            width: 18px;
            height: 18px;
            vertical-align: middle;
            margin-right: 5px;
            fill: currentColor;
        }
        .icon-btn svg {
            width: 16px;
            height: 16px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content w-100">
        <h2 class="mb-3">
            <?= ($action === 'add' || $action === 'edit') ? ($action === 'add' ? 'Add New Subject' : 'Edit Subject') : 'Manage Subjects' ?>
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
                        <svg viewBox="0 0 24 24"><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg>
                        <?= $editSubject ? 'Update Subject' : 'Add Subject' ?>
                    </button>
                    <a href="subjects.php" class="btn btn-secondary" style="margin-left:10px;">Cancel</a>
                </form>
            </div>
        <?php else: ?>
            <!-- ==================== FILTER & LIST ==================== -->
            <div class="filter-box mb-3">
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
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <svg width="16" height="16" viewBox="0 0 24 24"><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></svg> Filter
                        </button>
                    </div>
                </form>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3" style="flex-wrap: wrap; gap: 10px;">
                <a href="subjects.php?action=add" class="btn btn-primary">
                    <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg> Add Subject
                </a>
                <a href="subjects.php" class="btn btn-outline-secondary">Clear Filters</a>
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
                                        <a href="subjects.php?action=edit&id=<?= $subj['subject_id'] ?>" class="btn btn-sm btn-outline-info btn-action" title="Edit">
                                            <svg width="16" height="16" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                        </a>
                                        <a href="subjects.php?action=delete&id=<?= $subj['subject_id'] ?>" class="btn btn-sm btn-outline-danger btn-action confirm-delete" title="Delete">
                                            <svg width="16" height="16" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                        </a>
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