<?php
// Konfigurasi Database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'db_gdss_helpdesk';

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// Fungsi untuk query SELECT
function query($sql) {
    global $conn;
    $result = $conn->query($sql);
    
    if (!$result) {
        die("Query Error: " . $conn->error);
    }
    
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    return $rows;
}

// Fungsi untuk query INSERT, UPDATE, DELETE
function execute($sql) {
    global $conn;
    return $conn->query($sql);
}

// Fungsi untuk menghitung baris
function countRows($sql) {
    global $conn;
    $result = $conn->query($sql);
    
    if (!$result) {
        die("Query Error: " . $conn->error);
    }
    
    return $result->num_rows;
}
?>
