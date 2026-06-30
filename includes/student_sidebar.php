<?php
// includes/student_sidebar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}

// Retrieve student's name from session for display
$studentName = isset($_SESSION['student_name']) ? htmlspecialchars($_SESSION['student_name']) : 'Student';
?>
<!-- Inline SVG Icons -->
<svg style="display:none;">
    <symbol id="icon-dashboard" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></symbol>
    <symbol id="icon-result" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM6 20V4h7v5h5v11H6z"/></symbol>
    <symbol id="icon-profile" viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v1.2h19.2v-1.2c0-3.2-6.4-4.8-9.6-4.8z"/></symbol>
    <symbol id="icon-logout" viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></symbol>
</svg>

<!-- Sidebar -->
<div class="sidebar">
    <!-- Brand / Logo area -->
    <div class="brand">
        <h4>Institute Portal</h4>
        <small>Student Panel</small>
    </div>

    <!-- Profile summary -->
    <div class="user-info">
        <div>Welcome,</div>
        <div class="name"><?= $studentName ?></div>
    </div>

    <!-- Navigation links -->
    <ul class="nav">
        <li>
            <a href="dashboard.php">
                <svg width="20" height="20"><use href="#icon-dashboard"/></svg> Dashboard
            </a>
        </li>
        <li>
            <a href="result.php">
                <svg width="20" height="20"><use href="#icon-result"/></svg> Result
            </a>
        </li>
        <li>
            <a href="profile.php">
                <svg width="20" height="20"><use href="#icon-profile"/></svg> Profile
            </a>
        </li>
    </ul>

    <!-- Logout at the bottom -->
    <div class="logout">
        <a href="../auth/logout.php">
            <svg width="20" height="20"><use href="#icon-logout"/></svg> Logout
        </a>
    </div>
</div>