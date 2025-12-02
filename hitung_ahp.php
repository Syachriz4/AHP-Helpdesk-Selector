<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ==================== FUNGSI AHP ====================

// Fungsi untuk normalisasi array
function normalize($values) {
    $sum = array_sum($values);
    if ($sum == 0) return $values;
    return array_map(function($v) use ($sum) { return $v / $sum; }, $values);
}

// Fungsi untuk hitung geometric mean
function geometricMean($values) {
    if (empty($values) || array_product($values) <= 0) {
        return 0;
    }
    $n = count($values);
    $product = array_product($values);
    return pow($product, 1/$n);
}

// Fungsi untuk hitung bobot kriteria
function hitungBobotKriteria($user_id) {
    global $conn;
    
    // Ambil semua kriteria
    $kriteria = query("SELECT kriteria_id FROM kriteria ORDER BY kriteria_id");
    $n = count($kriteria);
    
    if ($n == 0) return [];
    
    // Buat matrix perbandingan
    $matrix = array_fill(0, $n, array_fill(0, $n, 1));
    
    // Isi matrix dari database
    $comparisons = query("SELECT kriteria1_id, kriteria2_id, nilai FROM ahp_penilaian_kriteria WHERE user_id = $user_id");
    foreach ($comparisons as $comp) {
        $id_a = $comp['kriteria1_id'];
        $id_b = $comp['kriteria2_id'];
        $value = $comp['nilai'];
        
        // Cari index
        $idx_a = array_search($id_a, array_column($kriteria, 'kriteria_id'));
        $idx_b = array_search($id_b, array_column($kriteria, 'kriteria_id'));
        
        if ($idx_a !== false && $idx_b !== false) {
            $matrix[$idx_a][$idx_b] = $value;
            $matrix[$idx_b][$idx_a] = 1 / $value;
        }
    }
    
    // Hitung prioritas menggunakan geometric mean (Standar AHP)
    $bobot = [];
    for ($i = 0; $i < $n; $i++) {
        $bobot[] = geometricMean($matrix[$i]);
    }
    
    // Normalisasi bobot final
    $bobot = normalize($bobot);
    
    // Kembalikan dalam format array dengan id kriteria
    $result = [];
    foreach ($kriteria as $idx => $krit) {
        $result[$krit['kriteria_id']] = $bobot[$idx];
    }
    
    return $result;
}

// Fungsi untuk hitung skor alternatif per kriteria
function hitungSkorAlternatif($user_id, $kriteria_id) {
    global $conn;
    
    // Ambil semua alternatif
    $alternatif = query("SELECT alternatif_id FROM alternatif ORDER BY alternatif_id");
    $n = count($alternatif);
    
    if ($n == 0) return [];
    
    // Buat matrix perbandingan
    $matrix = array_fill(0, $n, array_fill(0, $n, 1));
    
    // Isi matrix dari database
    $comparisons = query("SELECT alternatif1_id, alternatif2_id, nilai 
                        FROM ahp_penilaian_alternatif 
                        WHERE user_id = $user_id AND kriteria_id = $kriteria_id");
    foreach ($comparisons as $comp) {
        $id_a = $comp['alternatif1_id'];
        $id_b = $comp['alternatif2_id'];
        $value = $comp['nilai'];
        
        // Cari index
        $idx_a = array_search($id_a, array_column($alternatif, 'alternatif_id'));
        $idx_b = array_search($id_b, array_column($alternatif, 'alternatif_id'));
        
        if ($idx_a !== false && $idx_b !== false) {
            $matrix[$idx_a][$idx_b] = $value;
            $matrix[$idx_b][$idx_a] = 1 / $value;
        }
    }
    
    // Hitung prioritas menggunakan geometric mean (Standar AHP)
    $skor = [];
    for ($i = 0; $i < $n; $i++) {
        $skor[] = geometricMean($matrix[$i]);
    }
    
    // Normalisasi skor final
    $skor = normalize($skor);
    
    // Kembalikan dalam format array dengan id alternatif
    $result = [];
    foreach ($alternatif as $idx => $alt) {
        $result[$alt['alternatif_id']] = $skor[$idx];
    }
    
    return $result;
}

// ==================== HITUNG AHP ====================

// Hitung bobot kriteria
$bobot_kriteria = hitungBobotKriteria($user_id);

if (empty($bobot_kriteria)) {
    $_SESSION['error'] = 'Belum ada data perbandingan kriteria!';
    header("Location: penilaian.php");
    exit;
}

// Ambil semua alternatif
$alternatif = query("SELECT alternatif_id, nama_alternatif FROM alternatif ORDER BY alternatif_id");
$kriteria_ids = array_keys($bobot_kriteria);

// Hitung skor akhir untuk setiap alternatif
$hasil_akhir = [];
foreach ($alternatif as $alt) {
    $alt_id = $alt['alternatif_id'];
    $skor_akhir = 0;
    
    // Untuk setiap kriteria
    foreach ($kriteria_ids as $krit_id) {
        // Hitung skor alternatif untuk kriteria ini
        $skor_alt = hitungSkorAlternatif($user_id, $krit_id);
        
        // Tambahkan ke skor akhir (bobot kriteria * skor alternatif)
        if (isset($skor_alt[$alt_id])) {
            $skor_akhir += $bobot_kriteria[$krit_id] * $skor_alt[$alt_id];
        }
    }
    
    $hasil_akhir[$alt_id] = $skor_akhir;
}

// Sort hasil (descending)
arsort($hasil_akhir);

// Hapus data lama di ahp_prioritas_final
execute("DELETE FROM ahp_prioritas_final WHERE user_id = $user_id");

// Simpan hasil ke ahp_prioritas_final
$rank = 1;
$normalized_sum = array_sum($hasil_akhir);

foreach ($hasil_akhir as $alt_id => $nilai_final) {
    $sql = "INSERT INTO ahp_prioritas_final (user_id, alternatif_id, nilai_final, ranking) 
            VALUES ($user_id, $alt_id, $nilai_final, $rank)";
    execute($sql);
    $rank++;
}

// Success message
$_SESSION['success'] = 'Perhitungan AHP berhasil! Silahkan lihat hasil di Hasil Analisis.';
header("Location: hasil.php");
exit;
?>
