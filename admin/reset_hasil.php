<?php
session_start();
require_once '../config.php';

// Cek apakah user sudah login dan adalah admin
if (!isset($_SESSION["user"]) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

try {
    // Hapus semua data hasil penilaian
    execute("DELETE FROM borda_hasil");
    execute("DELETE FROM ahp_prioritas_final");
    execute("DELETE FROM borda_input");
    
    $_SESSION['success'] = '✅ Data hasil penilaian alternatif berhasil direset. DM dapat melakukan voting kembali.';
    
} catch (Exception $e) {
    $_SESSION['error'] = '❌ Error: ' . $e->getMessage();
}

header("Location: hasil_penilaian.php");
exit;
?>
