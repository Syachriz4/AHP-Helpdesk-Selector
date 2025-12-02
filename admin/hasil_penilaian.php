<?php
session_start();
require_once '../config.php';

// Cek apakah user sudah login dan adalah admin
if (!isset($_SESSION["user"]) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ===== AMBIL DATA DARI DATABASE =====

// 1. Ambil semua alternatif dari database
$alternatif_data = query("SELECT alternatif_id, nama_alternatif FROM alternatif ORDER BY alternatif_id");

// 2. Hitung rata-rata AHP dari semua user (GDSS Consensus)
$gdss_data = query("
    SELECT 
        apf.alternatif_id,
        AVG(apf.nilai_final) as nilai_gdss
    FROM ahp_prioritas_final apf
    GROUP BY apf.alternatif_id
    ORDER BY apf.alternatif_id ASC
");

// 3. Ambil hasil Borda (jika sudah dihitung)
$borda_data = query("
    SELECT alternatif_id, skor_borda, peringkat FROM borda_hasil ORDER BY peringkat ASC
");

// Siapkan array final untuk ditampilkan
$hasil = [];
foreach ($alternatif_data as $alt) {
    $alt_id = $alt['alternatif_id'];
    
    // Cari nilai AHP dari salah satu user (atau rata-rata jika ingin GDSS)
    $ahp_value = 0;
    $gdss_value = 0;
    $borda_ranking = '-';
    
    // Cari di GDSS data
    foreach ($gdss_data as $gdss) {
        if ($gdss['alternatif_id'] == $alt_id) {
            $gdss_value = round($gdss['nilai_gdss'], 4);
            break;
        }
    }
    
    // Cari di Borda data
    foreach ($borda_data as $borda) {
        if ($borda['alternatif_id'] == $alt_id) {
            $borda_ranking = $borda['peringkat'];
            break;
        }
    }
    
    // Untuk AHP, ambil dari user manapun (bisa dipilih lebih spesifik jika perlu)
    $ahp_check = query("SELECT DISTINCT nilai_final FROM ahp_prioritas_final WHERE alternatif_id = $alt_id LIMIT 1");
    if (!empty($ahp_check)) {
        $ahp_value = round($ahp_check[0]['nilai_final'], 4);
    }
    
    $hasil[] = [
        'alternatif_id' => $alt_id,
        'nama' => $alt['nama_alternatif'],
        'ahp' => $ahp_value,
        'gdss' => $gdss_value,
        'borda' => $borda_ranking
    ];
}

// Siapkan data untuk chart
$altNames = array_map(function($h) { return $h['nama']; }, $hasil);
$nilaiAHP = array_map(function($h) { return $h['ahp']; }, $hasil);
$nilaiGDSS = array_map(function($h) { return $h['gdss']; }, $hasil);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Hasil Penilaian Alternatif</title>

    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body>

<div id="wrapper">

    <?php include "sidebar_admin.php"; ?>

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">

            <?php include "../topbar.php"; ?>

            <div class="container-fluid mt-4">

                <h3 class="text-gray-800 mb-4">Hasil Penilaian Alternatif</h3>

                <?php if (empty($hasil) || (isset($hasil[0]) && $hasil[0]['ahp'] == 0)) : ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <strong>ℹ️ Informasi:</strong> Belum ada data penilaian dari Decision Maker. 
                        Hasil akan muncul setelah DM melakukan voting dan Manager menghitung Borda.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- ================== TABEL PER ALTERNATIF ================== -->
                <div class="card shadow">
                    <div class="card-body">
                        
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Alternatif</th>
                                    <th>Bobot AHP</th>
                                    <th>GDSS (Rata-rata User)</th>
                                    <th>BORDA Ranking</th>
                                </tr>
                            </thead>
                            <tbody>

                            <?php if (!empty($hasil)) : ?>
                                <?php foreach ($hasil as $alt) : ?>
                                    <tr>
                                        <td><strong><?= $alt['nama'] ?></strong></td>
                                        <td>
                                            <?php if ($alt['ahp'] > 0) : ?>
                                                <span class="badge badge-primary p-2"><?= number_format($alt['ahp'], 4) ?></span>
                                            <?php else : ?>
                                                <span class="badge badge-secondary p-2">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($alt['gdss'] > 0) : ?>
                                                <span class="badge badge-info p-2"><?= number_format($alt['gdss'], 4) ?></span>
                                            <?php else : ?>
                                                <span class="badge badge-secondary p-2">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($alt['borda'] !== '-') : ?>
                                                <span class="badge badge-warning p-2"><?= $alt['borda'] ?></span>
                                            <?php else : ?>
                                                <span class="badge badge-secondary p-2">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada data penilaian</td>
                                </tr>
                            <?php endif; ?>

                            </tbody>
                        </table>

                    </div>
                </div>

                <!-- ================== GRAFIK AHP ================== -->
                <div class="card shadow mt-4">
                     <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Grafik AHP per Alternatif</h6>
                     </div>
                    <div class="card-body">
                        <canvas id="chartAHP"></canvas>
                    </div>
                </div>

                <!-- ================== GRAFIK GDSS ================== -->
                <div class="card shadow mt-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Grafik GDSS per Alternatif</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="chartGDSS"></canvas>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// ======== Data Grafik dari Database (via PHP) ========
const altNames = <?= json_encode($altNames) ?>;
const nilaiAHP = <?= json_encode($nilaiAHP) ?>;
const nilaiGDSS = <?= json_encode($nilaiGDSS) ?>;

// ======== Grafik AHP ========
new Chart(document.getElementById('chartAHP'), {
    type: 'bar',
    data: {
        labels: altNames,
        datasets: [{
            label: 'Bobot AHP',
            data: nilaiAHP,
            backgroundColor: '#4e73df'
        }]
    },
    options: {
        responsive: true,
        scales: {
            yAxes: [{
                beginAtZero: true,
                max: 1
            }]
        }
    }
});

// ======== Grafik GDSS ========
new Chart(document.getElementById('chartGDSS'), {
    type: 'bar',
    data: {
        labels: altNames,
        datasets: [{
            label: 'Nilai GDSS (Rata-rata)',
            data: nilaiGDSS,
            backgroundColor: '#1cc88a'
        }]
    },
    options: {
        responsive: true,
        scales: {
            yAxes: [{
                beginAtZero: true,
                max: 1
            }]
        }
    }
});

</script>

</body>
</html>
