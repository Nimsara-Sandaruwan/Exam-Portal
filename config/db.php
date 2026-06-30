<?php
// config/db.php
// Database configuration and connection using PDO

$host    = 'localhost';        // your database host (usually localhost)
$db      = 'exam_results';     // the database name you created
$user    = 'root';             // default XAMPP username
$pass    = '';                 // default XAMPP password (empty)
$charset = 'utf8mb4';          // character set for proper encoding

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options – adjust as needed
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                   // use real prepared statements
];

try {
    // Create PDO instance
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // If connection fails, show error (in production log the error instead of displaying it)
    die("Database connection failed: " . $e->getMessage());
}
?>