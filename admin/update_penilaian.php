<?php
session_start();
require_once '../config.php';

// Cek apakah user sudah login dan adalah admin
if (!isset($_SESSION["user"]) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['user_id'])) {
    $_SESSION['error'] = 'Invalid request';
    header("Location: data_penilaian.php");
    exit;
}

$user_id = $_POST['user_id'];

try {
    // Ambil data kriteria dan alternatif
    $kriteria_data = query("SELECT kriteria_id FROM kriteria ORDER BY kriteria_id");
    $alternatif_data = query("SELECT alternatif_id FROM alternatif ORDER BY alternatif_id");
    
    $kriteria_ids = array_map(function($item) { return $item['kriteria_id']; }, $kriteria_data);
    $alternatif_ids = array_map(function($item) { return $item['alternatif_id']; }, $alternatif_data);
    
    // Hapus data perbandingan lama
    execute("DELETE FROM ahp_penilaian_kriteria WHERE user_id = $user_id");
    execute("DELETE FROM ahp_penilaian_alternatif WHERE user_id = $user_id");
    execute("DELETE FROM ahp_prioritas_final WHERE user_id = $user_id");
    execute("DELETE FROM borda_input WHERE user_id = $user_id");
    
    // Update perbandingan kriteria
    for ($i=0; $i<count($kriteria_ids); $i++) {
        for ($j=$i+1; $j<count($kriteria_ids); $j++) {
            $id_a = $kriteria_ids[$i];
            $id_b = $kriteria_ids[$j];
            $fieldname = "kriteria_{$id_a}_{$id_b}";
            $nilai = $_POST[$fieldname] ?? 1;
            
            $sql = "INSERT INTO ahp_penilaian_kriteria (user_id, kriteria1_id, kriteria2_id, nilai) 
                   VALUES ($user_id, $id_a, $id_b, $nilai)";
            execute($sql);
        }
    }
    
    // Update perbandingan alternatif
    foreach ($kriteria_ids as $kriteria_id) {
        for ($i=0; $i<count($alternatif_ids); $i++) {
            for ($j=$i+1; $j<count($alternatif_ids); $j++) {
                $alt_id_a = $alternatif_ids[$i];
                $alt_id_b = $alternatif_ids[$j];
                $fieldname = "alt_{$kriteria_id}_{$alt_id_a}_{$alt_id_b}";
                $nilai = $_POST[$fieldname] ?? 1;
                
                $sql = "INSERT INTO ahp_penilaian_alternatif (user_id, kriteria_id, alternatif1_id, alternatif2_id, nilai) 
                       VALUES ($user_id, $kriteria_id, $alt_id_a, $alt_id_b, $nilai)";
                execute($sql);
            }
        }
    }
    
    // Hapus hasil perhitungan lama
    execute("DELETE FROM ahp_prioritas_final WHERE user_id = $user_id");
    
    // Include hitung_ahp.php untuk recalculate nilai user
    require_once '../hitung_ahp.php';
    hitungBobotKriteria($user_id);
    
    $_SESSION['success'] = '✅ Penilaian user berhasil diperbarui!';
    
} catch (Exception $e) {
    $_SESSION['error'] = '❌ Error: ' . $e->getMessage();
}

header("Location: data_penilaian.php");
exit;
?>
