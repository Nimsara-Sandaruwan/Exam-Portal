<?php
// functions.php
// Shared helper functions for the Institute Exam Results Portal

/**
 * Generate a unique registration number in the format:
 * TAN/DeptCode/AcademicYear/StudyMode/0001
 */
function generateRegNo($pdo, $dept_code, $academic_year, $study_mode) {
    $prefix = "TAN/$dept_code/$academic_year/$study_mode/";
    $stmt = $pdo->prepare("SELECT MAX(reg_no) AS max_reg FROM students WHERE reg_no LIKE ?");
    $stmt->execute(["$prefix%"]);
    $row = $stmt->fetch();
    if ($row && $row['max_reg']) {
        $lastNum = (int)substr($row['max_reg'], -4);
        $nextNum = $lastNum + 1;
    } else {
        $nextNum = 1;
    }
    return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
}

/**
 * Compute grade and grade point based on marks.
 * Official SLIATE grading – no D+, D, or F; fail = I(SE)
 */
function computeGrade($marks) {
    if ($marks >= 85) return ['grade' => 'A+',   'gp' => 4.0];
    if ($marks >= 75) return ['grade' => 'A',    'gp' => 4.0];
    if ($marks >= 70) return ['grade' => 'A-',   'gp' => 3.7];
    if ($marks >= 65) return ['grade' => 'B+',   'gp' => 3.3];
    if ($marks >= 60) return ['grade' => 'B',    'gp' => 3.0];
    if ($marks >= 55) return ['grade' => 'B-',   'gp' => 2.7];
    if ($marks >= 50) return ['grade' => 'C+',   'gp' => 2.3];
    if ($marks >= 45) return ['grade' => 'C',    'gp' => 2.0];
    if ($marks >= 40) return ['grade' => 'C-',   'gp' => 1.7];
    // Below 40 = fail (I(SE))
    return ['grade' => 'I(SE)', 'gp' => 0.0];
}

/**
 * Calculate semester GPA – credit‑weighted formula:
 * GPA = Σ (credits × grade_point) / Σ credits
 */
function calculateSemesterGPA($pdo, $student_id, $academic_year, $semester) {
    $stmt = $pdo->prepare("
        SELECT SUM(sub.credits * r.grade_point) AS weighted_sum,
               SUM(sub.credits) AS total_credits
        FROM results r
        JOIN subjects sub ON r.subject_id = sub.subject_id
        WHERE r.student_id = ?
          AND r.academic_year = ?
          AND r.semester = ?
    ");
    $stmt->execute([$student_id, $academic_year, $semester]);
    $row = $stmt->fetch();
    if ($row && $row['total_credits'] > 0) {
        return round($row['weighted_sum'] / $row['total_credits'], 2);
    }
    return 0.00;
}
?>