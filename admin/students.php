<?php
// admin/students.php
session_start();
require_once '../config/db.php';
require_once '../functions.php';

// --- Authentication ---
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$dept_id   = $_SESSION['dept_id'];
$admin_id  = $_SESSION['admin_id'];

// --- Get department code for reg-no generation ---
$deptCodeStmt = $pdo->prepare("SELECT dept_code FROM departments WHERE dept_id = ?");
$deptCodeStmt->execute([$dept_id]);
$dept_code = $deptCodeStmt->fetchColumn();

// --- Determine action ---
$action = $_GET['action'] ?? 'list';
$edit_id = $_GET['id'] ?? null;

// Messages
$error   = '';
$success = '';

// ============================================================
//   DELETE STUDENT
// ============================================================
if ($action === 'delete' && $edit_id) {
    $checkStmt = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ? AND dept_id = ?");
    $checkStmt->execute([$edit_id, $dept_id]);
    if ($checkStmt->fetch()) {
        $delStmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
        $delStmt->execute([$edit_id]);
        $success = "Student deleted successfully.";
    } else {
        $error = "Student not found or access denied.";
    }
    header("Location: students.php?msg=" . urlencode($success ?: $error));
    exit;
}

// ============================================================
//   PROCESS ADD / EDIT FORM
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_student'])) {
    $student_id   = $_POST['student_id'] ?? '';
    $full_name    = trim($_POST['full_name'] ?? '');
    $address      = trim($_POST['address'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $dob          = trim($_POST['dob'] ?? '');
    $academic_year = intval($_POST['academic_year'] ?? 0);
    $study_mode   = $_POST['study_mode'] ?? '';
    $password     = $_POST['password'] ?? '';

    if (empty($full_name) || empty($address) || empty($phone) || empty($dob) || empty($academic_year) || empty($study_mode)) {
        $error = "All fields are required.";
    } elseif (!in_array($study_mode, ['F', 'P'])) {
        $error = "Invalid study mode.";
    } else {
        if ($student_id) {  // EDIT EXISTING STUDENT
            $ownStmt = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ? AND dept_id = ?");
            $ownStmt->execute([$student_id, $dept_id]);
            if (!$ownStmt->fetch()) {
                $error = "Student not found.";
            } else {
                $updateData = [$full_name, $address, $phone, $dob, $academic_year, $study_mode, $student_id];
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $updStmt = $pdo->prepare("UPDATE students SET full_name=?, address=?, phone=?, dob=?, academic_year=?, study_mode=?, password_hash=? WHERE student_id=?");
                    array_splice($updateData, 6, 0, [$hashed]);
                } else {
                    $updStmt = $pdo->prepare("UPDATE students SET full_name=?, address=?, phone=?, dob=?, academic_year=?, study_mode=? WHERE student_id=?");
                }
                $updStmt->execute($updateData);
                $success = "Student updated successfully.";
                header("Location: students.php?msg=updated");
                exit;
            }
        } else { // ADD NEW STUDENT
            if (empty($password)) {
                $error = "Password is required for new students.";
            } else {
                $reg_no = generateRegNo($pdo, $dept_code, $academic_year, $study_mode);
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $insStmt = $pdo->prepare("INSERT INTO students (reg_no, password_hash, full_name, address, phone, dob, academic_year, study_mode, dept_id)
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insStmt->execute([$reg_no, $hashed, $full_name, $address, $phone, $dob, $academic_year, $study_mode, $dept_id]);
                $new_id = $pdo->lastInsertId();
                $success = "Student added successfully. Registration Number: <strong>$reg_no</strong>";
            }
        }
    }
}

// ============================================================
//   FILTER PARAMETERS (for list)
// ============================================================
$filter_name  = $_GET['search_name'] ?? '';
$filter_year  = $_GET['filter_year'] ?? '';
$filter_mode  = $_GET['filter_mode'] ?? '';

$where = "s.dept_id = ?";
$params = [$dept_id];

if (!empty($filter_name)) {
    $where .= " AND s.full_name LIKE ?";
    $params[] = "%$filter_name%";
}
if (!empty($filter_year)) {
    $where .= " AND s.academic_year = ?";
    $params[] = $filter_year;
}
if (!empty($filter_mode)) {
    $where .= " AND s.study_mode = ?";
    $params[] = $filter_mode;
}

// ============================================================
//   FETCH ALL STUDENTS FOR LISTING
// ============================================================
$listStmt = $pdo->prepare("SELECT s.*, d.dept_name FROM students s
                           JOIN departments d ON s.dept_id = d.dept_id
                           WHERE $where ORDER BY s.reg_no DESC");
$listStmt->execute($params);
$students = $listStmt->fetchAll();

// ============================================================
//   FETCH SINGLE STUDENT FOR EDIT
// ============================================================
$editStudent = null;
if ($action === 'edit' && $edit_id) {
    $editStmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ? AND dept_id = ?");
    $editStmt->execute([$edit_id, $dept_id]);
    $editStudent = $editStmt->fetch();
    if (!$editStudent) {
        $error = "Student not found.";
        $action = 'list';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students – Institute of Higher Technology</title>
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
            <?= ($action === 'add' || $action === 'edit') ? ($action === 'add' ? 'Add New Student' : 'Edit Student') : 'Manage Students' ?>
        </h2>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible">
                <?= $error ?>
                <button class="alert-close" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible">
                <?= $success ?>
                <button class="alert-close" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>

        <?php if ($action === 'add' || $action === 'edit'): ?>
            <!-- ============= ADD / EDIT FORM ============= -->
            <div class="dashboard-card">
                <form method="post" action="students.php">
                    <input type="hidden" name="student_id" value="<?= $editStudent['student_id'] ?? '' ?>">
                    <div class="row">
                        <div class="col-6 col-sm-12 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($editStudent['full_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-6 col-sm-12 mb-3">
                            <label class="form-label">Address *</label>
                            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($editStudent['address'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4 col-sm-12 mb-3">
                            <label class="form-label">Phone *</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($editStudent['phone'] ?? '') ?>" required>
                        </div>
                        <div class="col-4 col-sm-12 mb-3">
                            <label class="form-label">Date of Birth *</label>
                            <input type="date" name="dob" class="form-control" value="<?= $editStudent['dob'] ?? '' ?>" required>
                        </div>
                        <div class="col-4 col-sm-12 mb-3">
                            <label class="form-label">Academic Year (enrolment) *</label>
                            <select name="academic_year" class="form-select" required>
                                <?php for ($y = date('Y'); $y >= 2015; $y--): ?>
                                    <option value="<?= $y ?>" <?= ($editStudent['academic_year'] ?? '') == $y ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-sm-12 mb-3">
                            <label class="form-label">Study Mode *</label>
                            <select name="study_mode" class="form-select" required>
                                <option value="">-- Select Mode --</option>
                                <option value="F" <?= ($editStudent['study_mode'] ?? '') == 'F' ? 'selected' : '' ?>>Full-Time</option>
                                <option value="P" <?= ($editStudent['study_mode'] ?? '') == 'P' ? 'selected' : '' ?>>Part-Time</option>
                            </select>
                        </div>
                        <div class="col-6 col-sm-12 mb-3">
                            <label class="form-label">Password <?= $editStudent ? '(leave blank to keep unchanged)' : '*' ?></label>
                            <input type="password" name="password" class="form-control" <?= $editStudent ? '' : 'required' ?>>
                        </div>
                    </div>
                    <button type="submit" name="save_student" class="btn btn-primary">
                        <svg viewBox="0 0 24 24"><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg>
                        <?= $editStudent ? 'Update Student' : 'Add Student' ?>
                    </button>
                    <a href="students.php" class="btn btn-secondary" style="margin-left:10px;">Cancel</a>
                </form>
            </div>
        <?php else: ?>
            <!-- ============= FILTER & STUDENT LIST ============= -->
            <div class="filter-box mb-3">
                <form method="get" action="students.php" class="row g-2">
                    <div class="col-4 col-sm-12 mb-2">
                        <input type="text" name="search_name" class="form-control" placeholder="Search by name" value="<?= htmlspecialchars($filter_name) ?>">
                    </div>
                    <div class="col-3 col-sm-12 mb-2">
                        <select name="filter_year" class="form-select">
                            <option value="">All Years</option>
                            <?php for ($y = date('Y'); $y >= 2015; $y--): ?>
                                <option value="<?= $y ?>" <?= $filter_year == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-3 col-sm-12 mb-2">
                        <select name="filter_mode" class="form-select">
                            <option value="">All Modes</option>
                            <option value="F" <?= $filter_mode == 'F' ? 'selected' : '' ?>>Full-Time</option>
                            <option value="P" <?= $filter_mode == 'P' ? 'selected' : '' ?>>Part-Time</option>
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
                <a href="students.php?action=add" class="btn btn-primary">
                    <svg viewBox="0 0 24 24"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg> Add Student
                </a>
                <a href="students.php" class="btn btn-outline-secondary">Clear Filters</a>
            </div>

            <div class="table-container dashboard-card">
                <table class="mb-0" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Reg No</th>
                            <th>Full Name</th>
                            <th>Year</th>
                            <th>Mode</th>
                            <th>Phone</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($students) > 0): ?>
                            <?php foreach ($students as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['reg_no']) ?></td>
                                    <td><?= htmlspecialchars($s['full_name']) ?></td>
                                    <td><?= $s['academic_year'] ?></td>
                                    <td><?= $s['study_mode'] == 'F' ? 'Full-Time' : 'Part-Time' ?></td>
                                    <td><?= htmlspecialchars($s['phone']) ?></td>
                                    <td class="text-center">
                                        <a href="students.php?action=edit&id=<?= $s['student_id'] ?>" class="btn btn-sm btn-outline-info btn-action" title="Edit">
                                            <svg width="16" height="16" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                        </a>
                                        <a href="students.php?action=delete&id=<?= $s['student_id'] ?>" class="btn btn-sm btn-outline-danger btn-action confirm-delete" title="Delete">
                                            <svg width="16" height="16" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted">No students found.</td></tr>
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