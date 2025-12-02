<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    
    // Gabung firstname + lastname jadi name
    $name = $firstname . ' ' . $lastname;
    
    // Validasi input
    if (empty($firstname) || empty($lastname) || empty($email) || empty($password) || empty($password2)) {
        $_SESSION['error'] = 'Semua field harus diisi!';
        header("Location: register.php");
        exit;
    }
    
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Email tidak valid!';
        header("Location: register.php");
        exit;
    }
    
    // Cek password sama
    if ($password !== $password2) {
        $_SESSION['error'] = 'Password tidak sama!';
        header("Location: register.php");
        exit;
    }
    
    // Cek email sudah ada
    $sql_check = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql_check);
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'Email sudah terdaftar!';
        header("Location: register.php");
        exit;
    }
    
    // Insert ke database (password tidak di-hash, sesuai struktur yang ada)
    // Default role adalah 'decision_maker'
    $sql = "INSERT INTO users (name, email, password, role) 
            VALUES ('$name', '$email', '$password', 'decision_maker')";
    
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = 'Register berhasil! Silahkan login.';
        header("Location: login.php");
        exit;
    } else {
        $_SESSION['error'] = 'Error: ' . $conn->error;
        header("Location: register.php");
        exit;
    }
} else {
    header("Location: register.php");
    exit;
}
?>
