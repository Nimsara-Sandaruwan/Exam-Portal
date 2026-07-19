<?php
session_start();
if (isset($_SESSION['student_id'])) { header('Location: student/dashboard.php'); exit; }
if (isset($_SESSION['admin_id'])) { header('Location: admin/dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLIATE – Official Examination Results Portal</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%231E3A8A'/><text x='50' y='65' text-anchor='middle' fill='%23D4AF37' font-size='50' font-weight='bold'>S</text></svg>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ==================== NAVBAR ==================== -->
<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="index.php" class="nav-brand">
            <i class="fas fa-graduation-cap" style="font-size:2.2rem;color:var(--primary);"></i>
            <span class="brand-text">SLIATE<span style="color:var(--accent);">.edu</span><small>Examination Portal</small></span>
        </a>
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="#departments">Departments</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="auth/student_login.php">Student Login</a></li>
            <li><a href="auth/admin_login.php">Admin Login</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
        <button class="hamburger" id="hamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<!-- ==================== HERO SECTION ==================== -->
<section class="hero" id="home">
    <div class="container">
        <div class="hero-content">
            <span style="display:inline-block;background:rgba(212,175,55,0.2);color:var(--accent);padding:6px 16px;border-radius:20px;font-size:0.85rem;font-weight:600;margin-bottom:16px;">
                <i class="fas fa-shield-alt"></i> Official Government Portal
            </span>
            <h1>Sri Lanka Institute of<br><span class="accent">Advanced Technological Education</span></h1>
            <p class="hero-subtitle">Official Examination Results Management Portal</p>
            <p class="hero-desc">
                Access examination results securely, calculate semester GPA, download official result sheets,
                and manage academic records through the official SLIATE Examination Portal.
            </p>
            <div class="hero-buttons">
                <a href="auth/student_login.php" class="btn btn-accent btn-lg"><i class="fas fa-user-graduate"></i> Student Login</a>
                <a href="auth/admin_login.php" class="btn btn-outline-white btn-lg"><i class="fas fa-lock"></i> Administrator Login</a>
            </div>
        </div>
        <div class="hero-illustration">
            <svg width="320" height="280" viewBox="0 0 320 280" fill="none">
                <rect x="30" y="40" width="260" height="200" rx="16" fill="rgba(255,255,255,0.06)" stroke="rgba(255,255,255,0.15)" stroke-width="2"/>
                <rect x="50" y="70" width="140" height="14" rx="4" fill="rgba(255,255,255,0.2)"/>
                <rect x="50" y="98" width="100" height="10" rx="3" fill="rgba(255,255,255,0.12)"/>
                <circle cx="230" cy="100" r="35" fill="rgba(212,175,55,0.15)" stroke="rgba(212,175,55,0.3)" stroke-width="2"/>
                <text x="230" y="108" text-anchor="middle" fill="rgba(212,175,55,0.7)" font-size="28" font-weight="700">A+</text>
                <rect x="50" y="140" width="200" height="8" rx="3" fill="rgba(255,255,255,0.1)"/>
                <rect x="50" y="158" width="180" height="8" rx="3" fill="rgba(255,255,255,0.08)"/>
                <rect x="50" y="176" width="160" height="8" rx="3" fill="rgba(255,255,255,0.06)"/>
                <rect x="50" y="200" width="80" height="28" rx="6" fill="rgba(212,175,55,0.2)"/>
                <text x="90" y="220" text-anchor="middle" fill="rgba(212,175,55,0.8)" font-size="12" font-weight="600">GPA 3.85</text>
            </svg>
        </div>
    </div>
</section>

<!-- ==================== STATISTICS SECTION ==================== -->
<section class="section section-light">
    <div class="container">
        <div class="row">
            <div class="col-3 col-md-6 col-sm-12"><div class="stat-card"><div class="stat-icon"><i class="fas fa-building-columns"></i></div><div class="stat-number counter" data-target="3">0</div><div class="stat-label">Academic Departments</div></div></div>
            <div class="col-3 col-md-6 col-sm-12"><div class="stat-card accent"><div class="stat-icon"><i class="fas fa-book"></i></div><div class="stat-number counter" data-target="292">0</div><div class="stat-label">Total Credits Offered</div></div></div>
            <div class="col-3 col-md-6 col-sm-12"><div class="stat-card"><div class="stat-icon"><i class="fas fa-user-graduate"></i></div><div class="stat-number counter" data-target="1000">0</div><div class="stat-label">Students Enrolled</div></div></div>
            <div class="col-3 col-md-6 col-sm-12"><div class="stat-card accent"><div class="stat-icon"><i class="fas fa-shield-haltered"></i></div><div class="stat-number counter" data-target="101">0</div><div class="stat-label">Active Subjects</div></div></div>
        </div>
    </div>
</section>

<!-- ==================== FEATURES SECTION ==================== -->
<section class="section section-gray" id="features">
    <div class="container">
        <div class="section-header"><h2>Key Features</h2><p>Comprehensive tools for students and administrators</p></div>
        <div class="row">
            <div class="col-3 col-md-6 col-sm-12"><div class="feature-card"><div class="feature-icon"><i class="fas fa-user-graduate"></i></div><h4>Student Portal</h4><p>Access results, track GPA, download documents</p></div></div>
            <div class="col-3 col-md-6 col-sm-12"><div class="feature-card"><div class="feature-icon"><i class="fas fa-lock"></i></div><h4>Secure Login</h4><p>Department-based authentication</p></div></div>
            <div class="col-3 col-md-6 col-sm-12"><div class="feature-card"><div class="feature-icon"><i class="fas fa-file-pdf"></i></div><h4>PDF Reports</h4><p>Download official result documents</p></div></div>
            <div class="col-3 col-md-6 col-sm-12"><div class="feature-card"><div class="feature-icon"><i class="fas fa-calculator"></i></div><h4>GPA Calculator</h4><p>Automatic credit-weighted SGPA</p></div></div>
        </div>
    </div>
</section>

<!-- ==================== DEPARTMENTS SECTION ==================== -->
<section class="section section-light" id="departments">
    <div class="container">
        <div class="section-header"><h2>Our Departments</h2><p>Three specialized departments offering HND programs</p></div>
        <div class="row">
            <div class="col-4 col-md-6 col-sm-12">
                <div class="dept-card">
                    <div class="dept-header"><div class="dept-icon"><i class="fas fa-laptop-code"></i></div><h4>Information Technology</h4><p style="font-size:0.85rem;">HNDIT</p></div>
                    <div class="dept-body"><div class="dept-stats"><div class="dept-stat"><strong>4</strong><span>Semesters</span></div><div class="dept-stat"><strong>87</strong><span>Credits</span></div><div class="dept-stat"><strong>27</strong><span>Subjects</span></div></div><p style="font-size:0.85rem;text-align:center;">Software Engineering, Web Development, Database Systems, Networking</p></div>
                </div>
            </div>
            <div class="col-4 col-md-6 col-sm-12">
                <div class="dept-card">
                    <div class="dept-header"><div class="dept-icon"><i class="fas fa-calculator"></i></div><h4>Accountancy</h4><p style="font-size:0.85rem;">HNDA</p></div>
                    <div class="dept-body"><div class="dept-stats"><div class="dept-stat"><strong>8</strong><span>Semesters</span></div><div class="dept-stat"><strong>124</strong><span>Credits</span></div><div class="dept-stat"><strong>41</strong><span>Subjects</span></div></div><p style="font-size:0.85rem;text-align:center;">Financial Accounting, Auditing, Taxation, Business Law</p></div>
                </div>
            </div>
            <div class="col-4 col-md-6 col-sm-12">
                <div class="dept-card">
                    <div class="dept-header"><div class="dept-icon"><i class="fas fa-language"></i></div><h4>English</h4><p style="font-size:0.85rem;">HNDE</p></div>
                    <div class="dept-body"><div class="dept-stats"><div class="dept-stat"><strong>4</strong><span>Semesters</span></div><div class="dept-stat"><strong>81</strong><span>Credits</span></div><div class="dept-stat"><strong>32</strong><span>Subjects</span></div></div><p style="font-size:0.85rem;text-align:center;">Linguistics, Literature, Teaching Methodology, Communication</p></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== PROCESS SECTION ==================== -->
<section class="section section-gray">
    <div class="container">
        <div class="section-header"><h2>How It Works</h2><p>Simple steps for students to access their results</p></div>
        <div class="timeline">
            <div class="timeline-step"><div class="step-circle">1</div><div class="step-title">Student Login</div><div class="step-desc">Use registration number</div></div>
            <div class="timeline-step"><div class="step-circle">2</div><div class="step-title">View Results</div><div class="step-desc">Semester by semester</div></div>
            <div class="timeline-step"><div class="step-circle">3</div><div class="step-title">Calculate GPA</div><div class="step-desc">Automatic SGPA</div></div>
            <div class="timeline-step"><div class="step-circle">4</div><div class="step-title">Download PDF</div><div class="step-desc">Official result sheet</div></div>
            <div class="timeline-step"><div class="step-circle">5</div><div class="step-title">Track Progress</div><div class="step-desc">Academic journey</div></div>
        </div>
    </div>
</section>

<!-- ==================== ABOUT SECTION ==================== -->
<section class="section section-light" id="about">
    <div class="container">
        <div class="section-header"><h2>About SLIATE</h2><p>Sri Lanka Institute of Advanced Technological Education</p></div>
        <div class="row">
            <div class="col-6 col-sm-12"><div class="card"><div class="card-body"><h4><i class="fas fa-bullseye" style="color:var(--accent);"></i> Our Mission</h4><p>To provide high-quality advanced technological education and training that meets national and international standards, producing competent professionals for the global workforce.</p></div></div></div>
            <div class="col-6 col-sm-12"><div class="card"><div class="card-body"><h4><i class="fas fa-eye" style="color:var(--accent);"></i> Our Vision</h4><p>To be the premier institute of advanced technological education in Sri Lanka, recognized for academic excellence, innovation, and industry-relevant programs.</p></div></div></div>
        </div>
    </div>
</section>

<!-- ==================== WHY CHOOSE SECTION ==================== -->
<section class="section section-primary">
    <div class="container">
        <div class="section-header"><h2>Why Choose SLIATE</h2><p>Reasons to trust our institution</p></div>
        <div class="row">
            <div class="col-3 col-md-6 col-sm-12"><div class="stat-card" style="background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.1);"><div class="stat-icon" style="color:var(--accent);"><i class="fas fa-certificate"></i></div><h4 style="color:white;">Government Recognized</h4><p style="color:rgba(255,255,255,0.7);">Ministry of Education approved</p></div></div>
            <div class="col-3 col-md-6 col-sm-12"><div class="stat-card" style="background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.1);"><div class="stat-icon" style="color:var(--accent);"><i class="fas fa-industry"></i></div><h4 style="color:white;">Industry Relevant</h4><p style="color:rgba(255,255,255,0.7);">Modern curriculum aligned with industry</p></div></div>
            <div class="col-3 col-md-6 col-sm-12"><div class="stat-card" style="background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.1);"><div class="stat-icon" style="color:var(--accent);"><i class="fas fa-chalkboard-teacher"></i></div><h4 style="color:white;">Qualified Staff</h4><p style="color:rgba(255,255,255,0.7);">Experienced academic professionals</p></div></div>
            <div class="col-3 col-md-6 col-sm-12"><div class="stat-card" style="background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.1);"><div class="stat-icon" style="color:var(--accent);"><i class="fas fa-microchip"></i></div><h4 style="color:white;">Advanced Technology</h4><p style="color:rgba(255,255,255,0.7);">State-of-the-art learning facilities</p></div></div>
        </div>
    </div>
</section>

<!-- ==================== FOOTER ==================== -->
<footer class="footer" id="contact">
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-logo"><i class="fas fa-graduation-cap" style="font-size:2rem;color:var(--accent);"></i><span style="color:white;font-weight:700;">SLIATE</span></div>
                <p style="font-size:0.9rem;">Official Examination Results Management Portal for Sri Lanka Institute of Advanced Technological Education.</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div><h5>Quick Links</h5><a href="auth/student_login.php">Student Login</a><br><a href="auth/admin_login.php">Admin Login</a><br><a href="#departments">Departments</a><br><a href="#about">About</a></div>
            <div><h5>Departments</h5>Information Technology<br>Accountancy<br>English<br></div>
            <div><h5>Contact</h5><i class="fas fa-map-marker-alt"></i> Colombo, Sri Lanka<br><i class="fas fa-phone"></i> +94 11 2 123 456<br><i class="fas fa-envelope"></i> info@sliate.edu.lk<br><i class="fas fa-clock"></i> Mon-Fri 8:30AM-4:00PM</div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> SLIATE. All rights reserved. | Privacy Policy | Terms & Conditions | Developed by SLIATE IT Division</p>
        </div>
    </div>
</footer>

<button class="back-to-top" id="backToTop" aria-label="Back to top"><i class="fas fa-chevron-up"></i></button>

<script src="assets/js/script.js"></script>
</body>
</html>