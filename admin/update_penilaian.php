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
    
    // ===== RECALCULATE AHP =====
    // Normalisasi array
    $normalize = function($values) {
        $sum = array_sum($values);
        if ($sum == 0) return $values;
        return array_map(function($v) use ($sum) { return $v / $sum; }, $values);
    };
    
    // Geometric mean
    $geometricMean = function($values) {
        if (empty($values) || array_product($values) <= 0) {
            return 0;
        }
        $n = count($values);
        $product = array_product($values);
        return pow($product, 1/$n);
    };
    
    // Hitung bobot kriteria
    $kriteria_data = query("SELECT kriteria_id FROM kriteria ORDER BY kriteria_id");
    $n = count($kriteria_data);
    $matrix = array_fill(0, $n, array_fill(0, $n, 1));
    
    $comparisons = query("SELECT kriteria1_id, kriteria2_id, nilai FROM ahp_penilaian_kriteria WHERE user_id = $user_id");
    foreach ($comparisons as $comp) {
        $id_a = $comp['kriteria1_id'];
        $id_b = $comp['kriteria2_id'];
        $value = $comp['nilai'];
        
        $idx_a = array_search($id_a, array_column($kriteria_data, 'kriteria_id'));
        $idx_b = array_search($id_b, array_column($kriteria_data, 'kriteria_id'));
        
        if ($idx_a !== false && $idx_b !== false) {
            $matrix[$idx_a][$idx_b] = $value;
            $matrix[$idx_b][$idx_a] = 1 / $value;
        }
    }
    
    $bobot_kriteria = [];
    for ($i = 0; $i < $n; $i++) {
        $bobot_kriteria[] = $geometricMean($matrix[$i]);
    }
    $bobot_kriteria = $normalize($bobot_kriteria);
    
    // Map bobot ke kriteria id
    $bobot_map = [];
    foreach ($kriteria_data as $idx => $krit) {
        $bobot_map[$krit['kriteria_id']] = $bobot_kriteria[$idx];
    }
    
    // Hitung skor akhir untuk setiap alternatif
    $alternatif_data = query("SELECT alternatif_id FROM alternatif ORDER BY alternatif_id");
    $hasil_akhir = [];
    
    foreach ($alternatif_data as $alt) {
        $alt_id = $alt['alternatif_id'];
        $skor_akhir = 0;
        
        foreach ($bobot_map as $krit_id => $bobot) {
            // Hitung skor alternatif untuk kriteria ini
            $alt_n = count($alternatif_data);
            $alt_matrix = array_fill(0, $alt_n, array_fill(0, $alt_n, 1));
            
            $alt_comparisons = query("SELECT alternatif1_id, alternatif2_id, nilai 
                                      FROM ahp_penilaian_alternatif 
                                      WHERE user_id = $user_id AND kriteria_id = $krit_id");
            foreach ($alt_comparisons as $alt_comp) {
                $alt_id_a = $alt_comp['alternatif1_id'];
                $alt_id_b = $alt_comp['alternatif2_id'];
                $alt_value = $alt_comp['nilai'];
                
                $alt_idx_a = array_search($alt_id_a, array_column($alternatif_data, 'alternatif_id'));
                $alt_idx_b = array_search($alt_id_b, array_column($alternatif_data, 'alternatif_id'));
                
                if ($alt_idx_a !== false && $alt_idx_b !== false) {
                    $alt_matrix[$alt_idx_a][$alt_idx_b] = $alt_value;
                    $alt_matrix[$alt_idx_b][$alt_idx_a] = 1 / $alt_value;
                }
            }
            
            $skor_alt = [];
            for ($i = 0; $i < $alt_n; $i++) {
                $skor_alt[] = $geometricMean($alt_matrix[$i]);
            }
            $skor_alt = $normalize($skor_alt);
            
            // Tambah ke skor akhir
            foreach ($alternatif_data as $idx => $alt_item) {
                if ($alt_item['alternatif_id'] == $alt_id) {
                    $skor_akhir += $bobot * $skor_alt[$idx];
                    break;
                }
            }
        }
        
        $hasil_akhir[$alt_id] = $skor_akhir;
    }
    
    // Sort hasil (descending)
    arsort($hasil_akhir);
    
    // Simpan hasil ke ahp_prioritas_final
    $rank = 1;
    foreach ($hasil_akhir as $alt_id => $nilai_final) {
        $sql = "INSERT INTO ahp_prioritas_final (user_id, alternatif_id, nilai_final, ranking) 
                VALUES ($user_id, $alt_id, $nilai_final, $rank)";
        execute($sql);
        
        // Insert ke borda_input juga (untuk tracking voting)
        $sql_borda = "INSERT INTO borda_input (user_id, alternatif_id, ranking) 
                      VALUES ($user_id, $alt_id, $rank)
                      ON DUPLICATE KEY UPDATE ranking = $rank";
        execute($sql_borda);
        
        $rank++;
    }
    
    $_SESSION['success'] = '✅ Penilaian user berhasil diperbarui!';
    
} catch (Exception $e) {
    $_SESSION['error'] = '❌ Error: ' . $e->getMessage();
}

header("Location: data_penilaian.php");
exit;
?>
