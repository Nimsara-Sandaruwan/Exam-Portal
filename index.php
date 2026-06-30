<?php
// index.php
session_start();
if (isset($_SESSION['student_id'])) {
    header('Location: student/dashboard.php');
    exit;
} elseif (isset($_SESSION['admin_id'])) {
    header('Location: admin/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institute of Higher Technology – Exam Results Portal</title>
    <!-- Custom CSS (no Bootstrap) -->
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        /* Hero section – uses existing gradient colours */
        .hero-section {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #fff;
            padding: 5rem 0;
            text-align: center;
        }
        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
        }
        .hero-section p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        .hero-section .btn {
            margin: 10px;
            padding: 14px 28px;
            font-size: 1.2rem;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .btn-light {
            background: #ffffff;
            color: #1e3c72;
            border: 2px solid #ffffff;
        }
        .btn-light:hover {
            background: #e6e6e6;
            border-color: #e6e6e6;
        }
        .btn-outline-light {
            background: transparent;
            color: #ffffff;
            border: 2px solid #ffffff;
        }
        .btn-outline-light:hover {
            background: rgba(255,255,255,0.1);
        }
        /* Info cards */
        .info-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            text-align: center;
            padding: 2rem;
            background: white;
            margin-bottom: 1.5rem;
        }
        .info-card:hover {
            transform: translateY(-5px);
        }
        .info-card .icon {
            font-size: 3rem;
            color: #1e3c72;
            margin-bottom: 1rem;
        }
        .footer {
            background: #1e272e;
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 3rem;
        }
    </style>
</head>
<body>
    <!-- Inline SVG icons used on this page -->
    <svg style="display:none;">
        <symbol id="icon-mortarboard" viewBox="0 0 24 24"><path d="M12 2L1 21h22L12 2zm0 3.99L19.53 19H4.47L12 5.99z" fill="currentColor"/><circle cx="12" cy="16" r="1" fill="currentColor"/><path d="M11 10h2v4h-2z" fill="currentColor"/></symbol>
        <symbol id="icon-person" viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v1.2h19.2v-1.2c0-3.2-6.4-4.8-9.6-4.8z" fill="currentColor"/></symbol>
        <symbol id="icon-shield" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z" fill="currentColor"/></symbol>
        <symbol id="icon-laptop" viewBox="0 0 24 24"><path d="M20 18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6z" fill="currentColor"/></symbol>
        <symbol id="icon-people" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" fill="currentColor"/></symbol>
        <symbol id="icon-building" viewBox="0 0 24 24"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z" fill="currentColor"/></symbol>
    </svg>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="white"><use href="#icon-mortarboard"/></svg>
            <h1>Institute of Higher Technology</h1>
            <p class="lead">Welcome to the official Exam Results Portal</p>
            <div style="display:flex; justify-content:center; gap:20px; flex-wrap:wrap;">
                <a href="auth/student_login.php" class="btn btn-light">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><use href="#icon-person"/></svg> Student Login
                </a>
                <a href="auth/admin_login.php" class="btn btn-outline-light">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><use href="#icon-shield"/></svg> Admin Login
                </a>
            </div>
        </div>
    </div>

    <!-- Information Cards Section -->
    <div class="container" style="margin-top:3rem; margin-bottom:3rem;">
        <div class="row">
            <div class="col-4 col-sm-12">
                <div class="card info-card">
                    <div class="icon"><svg width="48" height="48" viewBox="0 0 24 24"><use href="#icon-laptop"/></svg></div>
                    <h5>View Your Results</h5>
                    <p>Students can access all exam results, semester GPA, and download result documents.</p>
                </div>
            </div>
            <div class="col-4 col-sm-12">
                <div class="card info-card">
                    <div class="icon"><svg width="48" height="48" viewBox="0 0 24 24"><use href="#icon-people"/></svg></div>
                    <h5>Manage Students & Results</h5>
                    <p>Admins can add/edit students, enter results, manage subjects, and generate reports.</p>
                </div>
            </div>
            <div class="col-4 col-sm-12">
                <div class="card info-card">
                    <div class="icon"><svg width="48" height="48" viewBox="0 0 24 24"><use href="#icon-building"/></svg></div>
                    <h5>Three Departments</h5>
                    <p>Information Technology | Accountancy | English – each with dedicated management.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Institute of Higher Technology. All rights reserved.</p>
    </footer>

    <!-- Custom Scripts (only if needed) -->
    <script src="assets/js/script.js"></script>
</body>
</html>