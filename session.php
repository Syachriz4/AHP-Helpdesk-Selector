<?php
// File ini tidak digunakan lagi
// Login dilakukan melalui proses_login.php
// Redirect ke login.php jika belum login

session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

header("Location: index.php");
?>
