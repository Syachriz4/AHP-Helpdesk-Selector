<?php
session_start();
require_once '../config.php';

// Cek apakah user sudah login dan adalah admin
if (!isset($_SESSION["user"]) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'ID user tidak ditemukan';
    header("Location: data_penilaian.php");
    exit;
}

$user_id = $_GET['id'];

try {
    // Hapus semua data penilaian user ini
    execute("DELETE FROM ahp_penilaian_kriteria WHERE user_id = $user_id");
    execute("DELETE FROM ahp_penilaian_alternatif WHERE user_id = $user_id");
    execute("DELETE FROM ahp_prioritas_final WHERE user_id = $user_id");
    execute("DELETE FROM borda_input WHERE user_id = $user_id");
    
    $_SESSION['success'] = '✅ Data penilaian user berhasil dihapus. User dapat mengisi penilaian kembali.';
    
} catch (Exception $e) {
    $_SESSION['error'] = '❌ Error: ' . $e->getMessage();
}

header("Location: data_penilaian.php");
exit;
?>
