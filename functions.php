<?php
// functions.php
// Shared helper functions for the Institute Exam Results Portal

/**
 * Generate a unique registration number in the format:
 * TAN/DeptCode/AcademicYear/StudyMode/0001
 *
 * @param PDO    $pdo           Active database connection
 * @param string $dept_code     e.g. 'IT', 'AC', 'EN'
 * @param int    $academic_year e.g. 2023
 * @param string $study_mode    'F' or 'P'
 * @return string               Full registration number
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
 * Adjust the thresholds to match your institute's grading policy.
 *
 * @param float $marks
 * @return array  ['grade' => string, 'gp' => float]
 */
function computeGrade($marks) {
    if ($marks >= 85) return ['grade' => 'A+', 'gp' => 4.0];
    if ($marks >= 75) return ['grade' => 'A',  'gp' => 4.0];
    if ($marks >= 70) return ['grade' => 'A-', 'gp' => 3.7];
    if ($marks >= 65) return ['grade' => 'B+', 'gp' => 3.3];
    if ($marks >= 60) return ['grade' => 'B',  'gp' => 3.0];
    if ($marks >= 55) return ['grade' => 'B-', 'gp' => 2.7];
    if ($marks >= 50) return ['grade' => 'C+', 'gp' => 2.3];
    if ($marks >= 45) return ['grade' => 'C',  'gp' => 2.0];
    if ($marks >= 40) return ['grade' => 'C-', 'gp' => 1.7];
    if ($marks >= 35) return ['grade' => 'D+', 'gp' => 1.3];
    if ($marks >= 30) return ['grade' => 'D',  'gp' => 1.0];
    return ['grade' => 'F',  'gp' => 0.0];
}

/**
 * Calculate the GPA for a specific student in a given academic year and semester.
 * GPA = average of grade points for all subjects in that semester.
 *
 * @param PDO $pdo
 * @param int $student_id
 * @param int $academic_year
 * @param int $semester
 * @return float  GPA rounded to 2 decimal places
 */
function calculateSemesterGPA($pdo, $student_id, $academic_year, $semester) {
    $stmt = $pdo->prepare(
        "SELECT AVG(grade_point) AS gpa
         FROM results
         WHERE student_id = ? AND academic_year = ? AND semester = ?"
    );
    $stmt->execute([$student_id, $academic_year, $semester]);
    $gpa = $stmt->fetchColumn();
    return round($gpa, 2);
}
?>