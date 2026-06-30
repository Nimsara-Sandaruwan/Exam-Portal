<?php
/**
 * seed_subjects.php
 * Run this file once (in your browser or CLI) to populate the subjects table
 * with all the official subjects for each department.
 * It will skip duplicates safely.
 */

require_once 'config/db.php';

// Helper function to get department ID by code
function getDeptId($pdo, $code) {
    $stmt = $pdo->prepare("SELECT dept_id FROM departments WHERE dept_code = ?");
    $stmt->execute([$code]);
    return $stmt->fetchColumn();
}

// Prepare insert statement (IGNORE duplicates)
$insert = $pdo->prepare("INSERT IGNORE INTO subjects (dept_id, subject_code, subject_name, year_of_study, semester) VALUES (?,?,?,?,?)");

// ==================== INFORMATION TECHNOLOGY ====================
$deptIT = getDeptId($pdo, 'IT');

$subjectsIT = [
    // First Year - First Semester
    ['HNDIT1012', 'Visual Application Programming', 1, 1],
    ['HNDIT1022', 'Web Design', 1, 1],
    ['HNDIT1032', 'Computer and Network Systems', 1, 1],
    ['HNDIT1042', 'Information Management and Information Systems', 1, 1],
    ['HNDIT1052', 'ICT Project', 1, 1],
    ['HNDIT1062', 'Communication Skills', 1, 1],

    // First Year - Second Semester
    ['HNDIT2012', 'Fundamentals of Programming', 1, 2],
    ['HNDIT2022', 'Software Development', 1, 2],
    ['HNDIT2032', 'System Analysis and Design', 1, 2],
    ['HNDIT2042', 'Data Communication and Computer Network', 1, 2],
    ['HNDIT2052', 'Principles of User Interface Design', 1, 2],
    ['HNDIT2062', 'ICT Project', 1, 2],
    ['HNDIT2072', 'Technical Writing', 1, 2],
    ['HNDIT2082', 'Human Value & Professional Ethics', 1, 2],

    // Second Year - First Semester
    ['HNDIT3012', 'Object Oriented Programming', 2, 1],
    ['HNDIT3022', 'Web Programming', 2, 1],
    ['HNDIT3032', 'Data Structures and Algorithms', 2, 1],
    ['HNDIT3042', 'Database Management Systems', 2, 1],
    ['HNDIT3052', 'Operating Systems', 2, 1],
    ['HNDIT3062', 'Information and Computer Security', 2, 1],
    ['HNDIT3072', 'Statistics for IT', 2, 1],

    // Second Year - Second Semester
    ['HNDIT4012', 'Software Engineering', 2, 2],
    ['HNDIT4022', 'Software Quality Assurance', 2, 2],
    ['HNDIT4032', 'IT Project Management', 2, 2],
    ['HNDIT4042', 'Professional World', 2, 2],
    ['HNDIT4052', 'Programming Individual Project', 2, 2],
    ['HNDIT4222', 'Business Analysis Practice', 2, 2],
    ['HNDIT4242', 'Computer Services Management', 2, 2],
];

foreach ($subjectsIT as $sub) {
    $insert->execute([$deptIT, $sub[0], $sub[1], $sub[2], $sub[3]]);
}

// ==================== ENGLISH ====================
$deptEN = getDeptId($pdo, 'EN');

$subjectsEN = [
    // First Year - First Semester
    ['ENGL1101', 'English Grammar & Vocabulary in Context Level 1', 1, 1],
    ['ENGL1102', 'Listening Skills in English Level 1', 1, 1],
    ['ENGL1103', 'Speaking Skills in English Level 1', 1, 1],
    ['ENGL1104', 'Reading Skills in English Level 1', 1, 1],
    ['ENGL1105', 'Writing Skills in English Level 1', 1, 1],
    ['ENGL1106', 'Introduction to Literature in English', 1, 1],
    ['ENGL1107', 'Technology Assisted Language Learning Level 1', 1, 1],
    ['ENGL1108', 'Language and Society', 1, 1],

    // First Year - Second Semester
    ['ENGL1201', 'English Grammar & Vocabulary in Context-Level 2', 1, 2],
    ['ENGL1202', 'Listening Skills in English-Level 2', 1, 2],
    ['ENGL1203', 'Speaking Skills in English-Level 2', 1, 2],
    ['ENGL1204', 'Reading Skills in English-Level 2', 1, 2],
    ['ENGL1205', 'Writing Skills in English-Level 2', 1, 2],
    ['ENGL1206', 'English Literature', 1, 2],
    ['ENGL1207', 'Technology Assisted Language Learning-Level 2', 1, 2],
    ['ENGL1208', 'Language and Mind', 1, 2],
    ['ENGL1209', 'Human Value and Professionalism', 1, 2],

    // Second Year - First Semester
    ['ENGL2101', 'Structure of English Language', 2, 1],
    ['ENGL2102', 'Listening Skills in English Level 3', 2, 1],
    ['ENGL2103', 'Speaking Skills in English Level 3', 2, 1],
    ['ENGL2104', 'Reading Skills in English-Level 3', 2, 1],
    ['ENGL2105', 'Writing Skills in English-Level 3', 2, 1],
    ['ENGL2106', 'Postcolonial Literature in English', 2, 1],

    // Second Year - Second Semester
    ['ENGL2201', 'Presentation Skills', 2, 2],
    ['ENGL2202', 'Reading Skills for Academic & Professional Contexts', 2, 2],
    ['ENGL2203', 'Writing Skills for Academic & Professional Contexts', 2, 2],
    ['ENGL2204', 'Sri Lankan Literature in English', 2, 2],
    ['ENGL2205', 'Key Concepts in Educational Philosophy', 2, 2],
    ['ENGL2206', 'Key Concepts in Educational Psychology', 2, 2],
    ['ENGL2207', 'Trends and Practices in Teaching English', 2, 2],
    ['ENGL2208', 'Testing and Assessment in Language Classroom', 2, 2],
    ['ENGL2209', 'Teaching Project', 2, 2],
];

foreach ($subjectsEN as $sub) {
    $insert->execute([$deptEN, $sub[0], $sub[1], $sub[2], $sub[3]]);
}

// ==================== ACCOUNTANCY ====================
$deptAC = getDeptId($pdo, 'AC');

$subjectsAC = [
    // First Year - First Semester
    ['DA1113', 'Financial Accounting 1', 1, 1],
    ['DA1123', 'Business Mathematics', 1, 1],
    ['DA1133', 'Business Economics', 1, 1],
    ['DA1142', 'Global Business Environment', 1, 1],
    ['DA1153', 'Business Communication 1', 1, 1],
    ['DA1161', 'Socialization Activities', 1, 1],

    // First Year - Second Semester
    ['DA1213', 'Financial Accounting 11', 1, 2],
    ['DA1223', 'Management Accounting', 1, 2],
    ['DA1232', 'Theory of Managing Organizations', 1, 2],
    ['DA1242', 'Business Law', 1, 2],
    ['DA1252', 'Introduction to ICT and Network', 1, 2],
    ['DA1263', 'Business Communication', 1, 2],
    ['DA1271', 'Community Work Project', 1, 2],

    // Second Year - First Semester
    ['DA2313', 'Advanced Financial Accounting', 2, 1],
    ['DA2323', 'Business Statistics', 2, 1],
    ['DA2332', 'Human Resources Management & OB', 2, 1],
    ['DA2343', 'Auditing and Assurance', 2, 1],
    ['DA2353', 'Financial Modelling', 2, 1],
    ['DA2362', 'Managing Information Systems', 2, 1],

    // Second Year - Second Semester
    ['DA2413', 'Advanced Management Accounting', 2, 2],
    ['DA2422', 'Marketing Management', 2, 2],
    ['DA2432', 'Operation Management', 2, 2],
    ['DA2443', 'Taxation', 2, 2],
    ['DA2452', 'Data Analytics', 2, 2],
    ['DA2463', 'Computer Based Accounting', 2, 2],

    // Third Year - First Semester
    ['DA3513', 'Financial Reporting Standards', 3, 1],
    ['DA3523', 'Financial Management', 3, 1],
    ['DA3533', 'Business Researching and Statistical Data Analysis', 3, 1],
    ['DA3543', 'Strategic Management Accounting', 3, 1],

    // Third Year - Second Semester
    ['HNDA3201', 'Advanced Financial Reporting', 3, 2],
    ['HNDA3202', 'Corporate Law', 3, 2],
    ['HNDA3203', 'Organizational Behavior & Human Resource Management', 3, 2],
    ['HNDA3204', 'Business System 1', 3, 2],

    // Fourth Year - First Semester
    ['HNDA4101', 'Financial Management', 4, 1],
    ['HNDA4102', 'Strategic Management', 4, 1],
    ['HNDA4103', 'Business System 11', 4, 1],
    ['HNDA4104', 'Computer Based Accounting', 4, 1],

    // Fourth Year - Second Semester
    ['HNDA4201', 'Strategic Management Accounting', 4, 2],
    ['HNDA4202', 'Financial Statement Analysis', 4, 2],
    ['HNDA4203', 'Strategic Financial Management', 4, 2],
    ['HNDA4204', 'Advanced Auditing & Assurance', 4, 2],
];

foreach ($subjectsAC as $sub) {
    $insert->execute([$deptAC, $sub[0], $sub[1], $sub[2], $sub[3]]);
}

echo "<h2>Subjects seeded successfully!</h2>";
echo "<p>All subjects for IT, English, and Accountancy departments have been inserted. Duplicates were ignored.</p>";