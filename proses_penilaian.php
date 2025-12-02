<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // ===== CEK APAKAH SUDAH PERNAH MENGISI PENILAIAN =====
    $checkData = query("SELECT COUNT(*) as total FROM ahp_penilaian_kriteria WHERE user_id = $user_id");
    if (!empty($checkData) && $checkData[0]['total'] > 0) {
        $_SESSION['error'] = '❌ Maaf, Anda sudah pernah mengisi penilaian sebelumnya. DM hanya bisa mengisi 1 kali saja!';
        header("Location: penilaian.php");
        exit;
    }
    
    // Ambil data kriteria dan alternatif
    $kriteria_data = query("SELECT kriteria_id, nama_kriteria FROM kriteria ORDER BY kriteria_id");
    $alternatif_data = query("SELECT alternatif_id, nama_alternatif FROM alternatif ORDER BY alternatif_id");
    
    $kriteria_ids = array_map(function($item) { return $item['kriteria_id']; }, $kriteria_data);
    $alternatif_ids = array_map(function($item) { return $item['alternatif_id']; }, $alternatif_data);
    
    try {
        // Simpan perbandingan kriteria ke ahp_penilaian_kriteria
        for ($i=0; $i<count($kriteria_ids); $i++) {
            for ($j=$i+1; $j<count($kriteria_ids); $j++) {
                $id_a = $kriteria_ids[$i];
                $id_b = $kriteria_ids[$j];
                $nilai = $_POST["kriteria_{$id_a}_{$id_b}"] ?? 1;
                
                // Insert
                $sql = "INSERT INTO ahp_penilaian_kriteria (user_id, kriteria1_id, kriteria2_id, nilai) 
                       VALUES ($user_id, $id_a, $id_b, $nilai)";
                execute($sql);
            }
        }
        
        // Simpan perbandingan alternatif ke ahp_penilaian_alternatif
        foreach ($kriteria_ids as $kriteria_id) {
            for ($i=0; $i<count($alternatif_ids); $i++) {
                for ($j=$i+1; $j<count($alternatif_ids); $j++) {
                    $alt_id_a = $alternatif_ids[$i];
                    $alt_id_b = $alternatif_ids[$j];
                    $nilai = $_POST["alt_{$kriteria_id}_{$alt_id_a}_{$alt_id_b}"] ?? 1;
                    
                    // Insert
                    $sql = "INSERT INTO ahp_penilaian_alternatif (user_id, kriteria_id, alternatif1_id, alternatif2_id, nilai) 
                           VALUES ($user_id, $kriteria_id, $alt_id_a, $alt_id_b, $nilai)";
                    execute($sql);
                }
            }
        }
        
        // Tambahkan record ke borda_input untuk menandakan sudah voting
        for ($i = 0; $i < count($alternatif_ids); $i++) {
            $sql = "INSERT INTO borda_input (user_id, alternatif_id, ranking) 
                   VALUES ($user_id, {$alternatif_ids[$i]}, 0)";
            execute($sql);
        }
        
        $_SESSION['success'] = '✅ Penilaian berhasil disimpan! Anda sudah menyelesaikan voting.';
        
        // Redirect ke hitung_ahp untuk hitung ranking
        header("Location: hitung_ahp.php");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
        header("Location: penilaian.php");
        exit;
    }
} else {
    header("Location: penilaian.php");
    exit;
}
?>
