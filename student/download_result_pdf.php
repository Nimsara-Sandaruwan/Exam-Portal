<?php
// student/download_result_pdf.php
session_start();
require_once '../config/db.php';
require_once '../functions.php';

// --- Authentication ---
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}

$student_id = $_SESSION['student_id'];

// --- Fetch student details ---
$stmt = $pdo->prepare("
    SELECT s.*, d.dept_name
    FROM students s
    JOIN departments d ON s.dept_id = d.dept_id
    WHERE s.student_id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// --- Fetch all results grouped by year & semester ---
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

$grouped = [];
foreach ($allResults as $row) {
    $key = $row['academic_year'] . '_' . $row['semester'];
    $grouped[$key]['academic_year'] = $row['academic_year'];
    $grouped[$key]['semester'] = $row['semester'];
    $grouped[$key]['subjects'][] = $row;
}

// Sort groups chronologically (oldest first)
uksort($grouped, function ($a, $b) {
    list($y1, $s1) = explode('_', $a);
    list($y2, $s2) = explode('_', $b);
    if ($y1 == $y2) return $s1 - $s2;
    return $y1 - $y2;
});

// --- Build HTML for PDF ---
$html = '
<style>
    body { font-family: "Segoe UI", sans-serif; font-size: 12px; }
    .header { text-align: center; margin-bottom: 20px; }
    .header h2 { margin: 0; color: #1e3c72; }
    .header p { margin: 2px 0; }
    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .info-table td { padding: 4px 10px; border: 1px solid #ccc; }
    .semester-block { margin-bottom: 25px; }
    .semester-title { background: #1e3c72; color: white; padding: 8px; font-weight: bold; }
    .result-table { width: 100%; border-collapse: collapse; }
    .result-table th { background: #f2f2f2; border: 1px solid #ccc; padding: 6px; }
    .result-table td { border: 1px solid #ccc; padding: 5px; text-align: center; }
    .gpa-badge { font-weight: bold; color: #1e3c72; margin: 8px 0; }
    .pass { color: green; font-weight: bold; }
    .fail { color: red; font-weight: bold; }
    .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #666; }
</style>

<div class="header">
    <h2>Institute of Higher Technology</h2>
    <p>Official Exam Results</p>
</div>

<table class="info-table">
    <tr>
        <td><strong>Student Name:</strong> ' . htmlspecialchars($student['full_name']) . '</td>
        <td><strong>Registration No:</strong> ' . htmlspecialchars($student['reg_no']) . '</td>
    </tr>
    <tr>
        <td><strong>Department:</strong> ' . htmlspecialchars($student['dept_name']) . '</td>
        <td><strong>Academic Year:</strong> ' . htmlspecialchars($student['academic_year']) . '</td>
    </tr>
    <tr>
        <td><strong>Study Mode:</strong> ' . ($student['study_mode'] == 'F' ? 'Full-Time' : 'Part-Time') . '</td>
        <td></td>
    </tr>
</table>';

if (empty($grouped)) {
    $html .= '<p style="text-align:center;">No results available.</p>';
} else {
    foreach ($grouped as $group) {
        $year = $group['academic_year'];
        $sem  = $group['semester'];
        $gpa  = calculateSemesterGPA($pdo, $student_id, $year, $sem);

        $html .= '<div class="semester-block">';
        $html .= '<div class="semester-title">Year ' . $year . ' – Semester ' . $sem . '</div>';
        $html .= '<table class="result-table">
            <tr>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Marks</th>
                <th>Grade</th>
                <th>Pass/Fail</th>
            </tr>';

        foreach ($group['subjects'] as $subj) {
            $pfClass = ($subj['pass_fail'] == 'Pass') ? 'pass' : 'fail';
            $html .= '<tr>
                <td>' . htmlspecialchars($subj['subject_code']) . '</td>
                <td>' . htmlspecialchars($subj['subject_name']) . '</td>
                <td>' . htmlspecialchars($subj['marks']) . '</td>
                <td>' . htmlspecialchars($subj['grade']) . '</td>
                <td class="' . $pfClass . '">' . htmlspecialchars($subj['pass_fail']) . '</td>
            </tr>';
        }

        $html .= '</table>';
        $html .= '<div class="gpa-badge">Semester GPA: ' . $gpa . '</div>';
        $html .= '</div>';
    }
}

$html .= '<div class="footer">Generated on ' . date('Y-m-d H:i:s') . ' – This is a computer-generated document.</div>';

// --- Generate PDF with Dompdf ---
require_once '../vendor/autoload.php';   // path to Composer autoload

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);   // allow images if needed
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Force download
$dompdf->stream('Result_' . $student['reg_no'] . '.pdf', ["Attachment" => true]);