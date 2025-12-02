<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['username'] ?? '';  // Form kirim username, tapi kami gunakan sebagai email
    $password = $_POST['password'] ?? '';
    
    // Validasi input
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Email dan password harus diisi!';
        header("Location: login.php");
        exit;
    }
    
    // Cek user di database berdasarkan username
    $sql = "SELECT * FROM users WHERE username = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password (langsung cocokkan, tidak di-hash)
        if ($password === $user['password']) {
            // Login berhasil
            $_SESSION['user'] = $user['nama'];
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['jabatan'] = $user['jabatan'];
            $_SESSION['success'] = 'Login berhasil!';
            
            // Redirect berdasarkan role
            if ($user['role'] === 'admin') {
                header("Location: admin/admin.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $_SESSION['error'] = 'Password salah!';
            header("Location: login.php");
            exit;
        }
    } else {
        $_SESSION['error'] = 'Username tidak ditemukan!';
        header("Location: login.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
?>
