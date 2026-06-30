<?php
// admin/download_report_pdf.php
session_start();
require_once '../config/db.php';

// --- Authentication ---
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$dept_id = $_SESSION['dept_id'];

// --- Read filter parameters from GET ---
$filter_year     = $_GET['filter_year'] ?? '';
$filter_semester = $_GET['filter_semester'] ?? '';
$filter_subject  = $_GET['filter_subject'] ?? '';

// --- Build dynamic WHERE clause (department‑safe) ---
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

// --- Fetch department name for the report header ---
$deptStmt = $pdo->prepare("SELECT dept_name FROM departments WHERE dept_id = ?");
$deptStmt->execute([$dept_id]);
$deptName = $deptStmt->fetchColumn();

// --- Fetch filtered results ---
$sql = "
    SELECT r.academic_year, r.semester, r.marks, r.grade, r.pass_fail,
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

// --- Build filter description for the PDF ---
$filterParts = [];
if (!empty($filter_year))     $filterParts[] = "Academic Year: $filter_year";
if (!empty($filter_semester)) $filterParts[] = "Semester: $filter_semester";
if (!empty($filter_subject)) {
    // Fetch the subject name if a subject filter is applied
    $subStmt = $pdo->prepare("SELECT CONCAT(subject_code, ' - ', subject_name) FROM subjects WHERE subject_id = ?");
    $subStmt->execute([$filter_subject]);
    $subjName = $subStmt->fetchColumn();
    $filterParts[] = "Subject: $subjName";
}
$filterText = !empty($filterParts) ? 'Filters: ' . implode(', ', $filterParts) : 'All Results';

// --- Build HTML for the PDF ---
$html = '
<style>
    body { font-family: "Segoe UI", sans-serif; font-size: 12px; }
    .header { text-align: center; margin-bottom: 20px; }
    .header h2 { margin: 0; color: #1e3c72; }
    .header p { margin: 2px 0; }
    .info { text-align: center; margin-bottom: 15px; font-size: 13px; }
    .result-table { width: 100%; border-collapse: collapse; }
    .result-table th { background-color: #1e3c72; color: white; padding: 8px; border: 1px solid #ccc; text-align: left; }
    .result-table td { padding: 6px; border: 1px solid #ccc; }
    .pass { color: green; font-weight: bold; }
    .fail { color: red; font-weight: bold; }
    .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #666; }
</style>

<div class="header">
    <h2>Institute of Higher Technology</h2>
    <h3>Exam Results Report</h3>
    <p><strong>Department:</strong> ' . htmlspecialchars($deptName) . '</p>
</div>

<div class="info">' . htmlspecialchars($filterText) . ' | Total Records: ' . count($results) . '</div>';

if (empty($results)) {
    $html .= '<p style="text-align:center;">No results found.</p>';
} else {
    $html .= '<table class="result-table">
        <tr>
            <th>Reg No</th>
            <th>Student Name</th>
            <th>Subject Code</th>
            <th>Subject Name</th>
            <th>Year</th>
            <th>Sem</th>
            <th>Marks</th>
            <th>Grade</th>
            <th>Status</th>
        </tr>';

    foreach ($results as $r) {
        $pfClass = ($r['pass_fail'] == 'Pass') ? 'pass' : 'fail';
        $html .= '<tr>
            <td>' . htmlspecialchars($r['reg_no']) . '</td>
            <td>' . htmlspecialchars($r['full_name']) . '</td>
            <td>' . htmlspecialchars($r['subject_code']) . '</td>
            <td>' . htmlspecialchars($r['subject_name']) . '</td>
            <td>' . $r['academic_year'] . '</td>
            <td>' . $r['semester'] . '</td>
            <td>' . $r['marks'] . '</td>
            <td>' . $r['grade'] . '</td>
            <td class="' . $pfClass . '">' . $r['pass_fail'] . '</td>
        </tr>';
    }
    $html .= '</table>';
}

$html .= '<div class="footer">Generated on ' . date('Y-m-d H:i:s') . ' – This is a computer‑generated document.</div>';

// --- Generate PDF using Dompdf ---
require_once '../vendor/autoload.php';  // adjust path if needed

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');    // landscape for wider tables
$dompdf->render();

// Force download
$dompdf->stream('Exam_Results_Report_' . date('Y-m-d') . '.pdf', ["Attachment" => true]);