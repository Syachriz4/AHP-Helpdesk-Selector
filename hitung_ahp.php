<?php
session_start();
require_once 'config.php';

// pastikan user login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

/* ---------------------------
   Utilities
   --------------------------- */
function floatval_safe($v) {
    if ($v === null || $v === '') return 0.0;
    // replace comma with dot (just in case)
    $v = str_replace(',', '.', $v);
    return (float)$v;
}

function eigenvectorPriority($matrix, $maxIter = 200, $tol = 1e-12) {
    $n = count($matrix);
    if ($n === 0) return [];

    // init vector (positive)
    $v = array_fill(0, $n, 1.0 / $n);

    for ($iter = 0; $iter < $maxIter; $iter++) {
        $w = array_fill(0, $n, 0.0);
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $w[$i] += $matrix[$i][$j] * $v[$j];
            }
        }
        // normalize (sum = 1)
        $s = array_sum($w);
        if ($s == 0) break;
        for ($i = 0; $i < $n; $i++) $w[$i] /= $s;

        // check convergence
        $diff = 0.0;
        for ($i = 0; $i < $n; $i++) $diff += abs($w[$i] - $v[$i]);
        if ($diff < $tol) return $w;
        $v = $w;
    }
    return $v;
}

// Hitung prioritas dengan metode "normalisasi kolom -> rata-rata baris" (Excel style)
function columnNormalizationPriority($matrix) {
    $n = count($matrix);
    if ($n == 0) return [];

    // hitung jumlah tiap kolom
    $colSum = array_fill(0, $n, 0.0);
    for ($j = 0; $j < $n; $j++) {
        for ($i = 0; $i < $n; $i++) {
            $colSum[$j] += floatval($matrix[$i][$j]);
        }
        if ($colSum[$j] == 0) $colSum[$j] = 1.0;
    }

    // normalize per kolom
    $norm = array_fill(0, $n, array_fill(0, $n, 0.0));
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $norm[$i][$j] = floatval($matrix[$i][$j]) / $colSum[$j];
        }
    }

    // rata-rata baris -> prioritas
    $prior = array_fill(0, $n, 0.0);
    for ($i = 0; $i < $n; $i++) {
        $sum = 0.0;
        for ($j = 0; $j < $n; $j++) $sum += $norm[$i][$j];
        $prior[$i] = $sum / $n;
    }

    // normalisasi final supaya jumlah = 1
    $s = array_sum($prior);
    if ($s == 0) return $prior;
    for ($i = 0; $i < $n; $i++) $prior[$i] /= $s;

    return $prior;
}

// compute lambda_max and CI, CR
function consistencyMetrics($matrix, $priorityVector) {
    $n = count($matrix);
    if ($n <= 1) return ['lambda_max' => 1, 'CI' => 0, 'CR' => 0];

    // compute A * w
    $Aw = array_fill(0, $n, 0.0);
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $Aw[$i] += $matrix[$i][$j] * $priorityVector[$j];
        }
    }

    // lambda_i = (Aw_i / w_i)
    $lambda_sum = 0.0;
    $count = 0;
    for ($i = 0; $i < $n; $i++) {
        if ($priorityVector[$i] != 0) {
            $lambda_sum += $Aw[$i] / $priorityVector[$i];
            $count++;
        }
    }
    $lambda_max = ($count > 0) ? ($lambda_sum / $count) : $n;

    $CI = ($n == 1) ? 0 : ($lambda_max - $n) / ($n - 1);

    // Random Index (RI) table (Saaty) for n = 1..15
    $RI_table = [
        1 => 0.00, 2 => 0.00, 3 => 0.58, 4 => 0.90, 5 => 1.12,
        6 => 1.24, 7 => 1.32, 8 => 1.41, 9 => 1.45, 10 => 1.49,
        11 => 1.51, 12 => 1.48, 13 => 1.56, 14 => 1.57, 15 => 1.59
    ];
    $RI = isset($RI_table[$n]) ? $RI_table[$n] : $RI_table[15]; // fallback

    $CR = ($RI == 0) ? 0 : $CI / $RI;

    return ['lambda_max' => $lambda_max, 'CI' => $CI, 'CR' => $CR];
}

/* ---------------------------
   Helpers: build matrices from DB
   --------------------------- */

// Validate completeness for pairwise count: expected = n*(n-1)/2
function expectedPairs($n) {
    return intval($n * ($n - 1) / 2);
}

// build kriteria matrix (nxn) from ahp_penilaian_kriteria
function buildKriteriaMatrix($user_id) {
    $kriteria_rows = query("SELECT kriteria_id FROM kriteria ORDER BY kriteria_id");
    $ids = array_column($kriteria_rows, 'kriteria_id');
    $n = count($ids);

    $matrix = array_fill(0, $n, array_fill(0, $n, 1.0));

    // fetch comparisons
    $rows = query("SELECT kriteria1_id, kriteria2_id, nilai FROM ahp_penilaian_kriteria WHERE user_id = $user_id");
    foreach ($rows as $r) {
        $a = (int)$r['kriteria1_id'];
        $b = (int)$r['kriteria2_id'];
        $val = floatval_safe($r['nilai']);

        $ia = array_search($a, $ids);
        $ib = array_search($b, $ids);
        if ($ia === false || $ib === false) continue;

        if ($val <= 0) $val = 1.0;
        $matrix[$ia][$ib] = $val;
        $matrix[$ib][$ia] = 1.0 / $val;
    }

    return ['ids' => $ids, 'matrix' => $matrix, 'count_rows' => count($rows)];
}

// build alternatif matrix for a given kriteria_id
function buildAlternatifMatrix($user_id, $kriteria_id) {
    $alternatif_rows = query("SELECT alternatif_id FROM alternatif ORDER BY alternatif_id");
    $ids = array_column($alternatif_rows, 'alternatif_id');
    $m = count($ids);

    $matrix = array_fill(0, $m, array_fill(0, $m, 1.0));

    $rows = query("
        SELECT alternatif1_id, alternatif2_id, nilai
        FROM ahp_penilaian_alternatif
        WHERE user_id = $user_id AND kriteria_id = $kriteria_id
    ");

    foreach ($rows as $r) {
        $a = (int)$r['alternatif1_id'];
        $b = (int)$r['alternatif2_id'];
        $val = floatval_safe($r['nilai']);

        $ia = array_search($a, $ids);
        $ib = array_search($b, $ids);
        if ($ia === false || $ib === false) continue;

        if ($val <= 0) $val = 1.0;
        $matrix[$ia][$ib] = $val;
        $matrix[$ib][$ia] = 1.0 / $val;
    }

    return ['ids' => $ids, 'matrix' => $matrix, 'count_rows' => count($rows)];
}

/* ---------------------------
   Main AHP flow (dynamic)
   --------------------------- */

// Build kriteria matrix
$kdata = buildKriteriaMatrix($user_id);
$kriteria_ids = $kdata['ids'];
$n_k = count($kriteria_ids);

// DEBUG: simpan matriks kriteria
file_put_contents('debug_matrix_kriteria.txt', print_r($kdata['matrix'], true));

// check completeness
$expected_k_pairs = expectedPairs($n_k);
if ($kdata['count_rows'] < $expected_k_pairs) {
    $_SESSION['error'] = "Perbandingan kriteria belum lengkap. Diperlukan $expected_k_pairs pasangan, ditemukan {$kdata['count_rows']}. Silakan lengkapi penilaian.";
    header("Location: penilaian.php");
    exit;
}

// compute kriteria priority (eigenvector)
$k_prior = columnNormalizationPriority($kdata['matrix']);
$k_metrics = consistencyMetrics($kdata['matrix'], $k_prior);

// save prioritas kriteria (optional table)
execute("DELETE FROM ahp_prioritas_kriteria WHERE user_id = $user_id");
foreach ($kriteria_ids as $i => $kid) {
    $p = isset($k_prior[$i]) ? $k_prior[$i] : 0;
    execute("INSERT INTO ahp_prioritas_kriteria (user_id, kriteria_id, prioritas) VALUES ($user_id, $kid, $p)");
}

/* Per-kriteria: build alternatif matrix, check completeness, compute priority */
$alternatif_rows = query("SELECT alternatif_id FROM alternatif ORDER BY alternatif_id");
$alternatif_ids = array_column($alternatif_rows, 'alternatif_id');
$m = count($alternatif_ids);
$expected_alt_pairs = expectedPairs($m);

$alt_prior_per_k = []; // [kriteria_id => [alt_id => prior]]
$cr_warnings = []; // collect CR > 0.1 warnings

for ($ki = 0; $ki < $n_k; $ki++) {
    $kid = $kriteria_ids[$ki];

    $adata = buildAlternatifMatrix($user_id, $kid);

    // DEBUG: simpan matriks alternatif per kriteria
    file_put_contents("debug_matrix_alt_k{$kid}.txt", print_r($adata['matrix'], true));

    if ($adata['count_rows'] < $expected_alt_pairs) {
        $_SESSION['error'] = "Perbandingan alternatif untuk kriteria ID $kid belum lengkap. Diperlukan $expected_alt_pairs pasangan, ditemukan {$adata['count_rows']}. Silakan lengkapi penilaian.";
        header("Location: penilaian.php");
        exit;
    }

    $a_prior = columnNormalizationPriority($adata['matrix']);
    $a_metrics = consistencyMetrics($adata['matrix'], $a_prior);

    // save per-criteria alternative priorities (optional)
    execute("DELETE FROM ahp_prioritas_alternatif WHERE user_id = $user_id AND kriteria_id = $kid");
    foreach ($alternatif_ids as $ai => $altid) {
        $val = isset($a_prior[$ai]) ? $a_prior[$ai] : 0;
        execute("INSERT INTO ahp_prioritas_alternatif (user_id, kriteria_id, alternatif_id, prioritas) VALUES ($user_id, $kid, $altid, $val)");
    }

    // collect for final aggregation
    $map = [];
    foreach ($alternatif_ids as $ai => $altid) $map[$altid] = $a_prior[$ai];
    $alt_prior_per_k[$kid] = $map;

    // CR warning
    if ($a_metrics['CR'] > 0.10) {
        $cr_warnings[] = "Kriteria ID $kid memiliki CR = " . round($a_metrics['CR'], 4) . " (> 0.1)";
    }
}

// final aggregation: weighted sum of alternative priorities
$final_scores = [];
foreach ($alternatif_ids as $altid) $final_scores[$altid] = 0.0;

foreach ($kriteria_ids as $ki => $kid) {
    $w_k = $k_prior[$ki];
    foreach ($alternatif_ids as $altid) {
        $p_a = isset($alt_prior_per_k[$kid][$altid]) ? $alt_prior_per_k[$kid][$altid] : 0;
        $final_scores[$altid] += $w_k * $p_a;
    }
}

// normalize final (sum to 1)
$sum_final = array_sum($final_scores);
if ($sum_final == 0) $sum_final = 1;
foreach ($final_scores as $aid => $val) $final_scores[$aid] = $val / $sum_final;

// store final results
execute("DELETE FROM ahp_prioritas_final WHERE user_id = $user_id");
$rank = 1;
// sort desc
arsort($final_scores);
foreach ($final_scores as $aid => $val) {
    execute("INSERT INTO ahp_prioritas_final (user_id, alternatif_id, nilai_final, ranking) VALUES ($user_id, $aid, $val, $rank)");
    $rank++;
}

// prepare user message
$msg = "Perhitungan AHP berhasil. Kriteria CR = " . round($k_metrics['CR'], 4) . ".";
if (!empty($cr_warnings)) {
    $msg .= " Peringatan CR untuk beberapa kriteria: " . implode(" ; ", $cr_warnings) . ".";
}
$_SESSION['success'] = $msg;

header("Location: hasil.php");
exit;
?>