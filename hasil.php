<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil hasil ranking dari database
$results = query("
    SELECT apf.id, apf.alternatif_id, a.nama_alternatif, apf.nilai_final, apf.ranking
    FROM ahp_prioritas_final apf
    JOIN alternatif a ON apf.alternatif_id = a.alternatif_id
    WHERE apf.user_id = $user_id
    ORDER BY apf.ranking ASC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Hasil Akhir - HelpDesk Selector</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <script src="vendor/chart.js/Chart.min.js"></script>
</head>

<body id="page-top">

<div id="wrapper">

    <?php include "sidebar.php"; ?>

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">

            <?php include "topbar.php"; ?>

            <div class="container-fluid">

                <h1 class="h3 mb-4 text-gray-800">Hasil Analisis GDSS</h1>

                <!-- RANKING -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Ranking Alternatif (Personal)</h6>
                    </div>
                    <div class="card-body">

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Peringkat</th>
                                    <th>Alternatif</th>
                                    <th>Nilai Akhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($results)) : ?>
                                    <?php foreach ($results as $index => $row) : ?>
                                        <tr>
                                            <td><?= $row['ranking'] ?></td>
                                            <td><?= $row['nama_alternatif'] ?></td>
                                            <td><?= number_format($row['nilai_final'], 4) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Belum ada hasil analisis</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                    </div>
                </div>

                <!-- CHART -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Grafik Hasil Personal</h6>
                    </div>

                    <div class="card-body">
                        <canvas id="resultChart"></canvas>
                    </div>
                </div>

                <!-- ============================== BORDA SECTION (MANAGER ONLY) ============================== -->
                <?php if (isset($_SESSION['jabatan']) && $_SESSION['jabatan'] === 'manager') : ?>
                <div id="borda-section">
                    <hr class="my-4">
                    
                    <h2 class="h4 mb-4 text-gray-800">Konsensus & Hasil Borda Count</h2>
                    <p class="text-muted mb-4">
                        Sebagai Manager, Anda memiliki akses khusus untuk menghitung hasil Borda Count 
                        dan melihat rekomendasi akhir sistem berdasarkan voting dari semua Decision Maker.
                    </p>

                    <!-- Status Voting -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Status Voting Decision Maker</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama Decision Maker</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $statusQuery = query("
                                        SELECT u.user_id, u.nama, u.jabatan,
                                               CASE WHEN COUNT(bi.id) > 0 THEN 'Sudah Voting' ELSE 'Belum Voting' END as status
                                        FROM users u
                                        LEFT JOIN borda_input bi ON u.user_id = bi.user_id
                                        WHERE u.role = 'dm'
                                        GROUP BY u.user_id
                                    ");
                                    
                                    if (!empty($statusQuery)) {
                                        foreach ($statusQuery as $status) {
                                            $badgeClass = ($status['status'] === 'Sudah Voting') ? 'badge-success' : 'badge-warning';
                                            echo "<tr>";
                                            echo "<td>{$status['nama']} ({$status['jabatan']})</td>";
                                            echo "<td><span class='badge {$badgeClass}'>{$status['status']}</span></td>";
                                            echo "</tr>";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tombol Hitung Borda -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-info">
                            <h6 class="m-0 font-weight-bold text-white">Proses Hitung Borda</h6>
                        </div>
                        <div class="card-body">
                            <p>
                                Jika semua Decision Maker sudah menyelesaikan voting, 
                                Anda dapat menghitung hasil akhir menggunakan metode <strong>Borda Count</strong>.
                            </p>
                            <a href="proses_borda.php" class="btn btn-lg btn-success">
                                <i class="fas fa-calculator"></i> Hitung Borda Count
                            </a>
                        </div>
                    </div>

                    <!-- Hasil Borda (Jika Sudah Dihitung) -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Hasil Akhir Borda (Private - Manager Only)</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                <strong>⚠️ PERHATIAN:</strong> Hasil ini hanya dapat dilihat oleh Manager IT. 
                                Hasil ini adalah rekomendasi akhir berdasarkan konsensus voting dari semua Decision Maker.
                            </p>
                            <table class="table table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Peringkat</th>
                                        <th>Alternatif</th>
                                        <th>Skor Borda</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $bordaResults = query("
                                        SELECT bh.peringkat, a.nama_alternatif, bh.skor_borda
                                        FROM borda_hasil bh
                                        JOIN alternatif a ON bh.alternatif_id = a.alternatif_id
                                        ORDER BY bh.peringkat ASC
                                    ");
                                    
                                    if (!empty($bordaResults)) {
                                        foreach ($bordaResults as $borda) {
                                            $medalIcon = '';
                                            if ($borda['peringkat'] == 1) $medalIcon = '🥇';
                                            elseif ($borda['peringkat'] == 2) $medalIcon = '🥈';
                                            elseif ($borda['peringkat'] == 3) $medalIcon = '🥉';
                                            
                                            echo "<tr>";
                                            echo "<td><strong>{$borda['peringkat']} {$medalIcon}</strong></td>";
                                            echo "<td>{$borda['nama_alternatif']}</td>";
                                            echo "<td><span class='badge badge-primary p-2'>{$borda['skor_borda']}</span></td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='3' class='text-center text-muted'>Belum ada hasil Borda. Silakan hitung terlebih dahulu.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>

        </div>

        <?php include "footer.php"; ?>

    </div>

</div>

<script>
<?php
// Siapkan data untuk chart
$labels = [];
$data = [];
if (!empty($results)) {
    foreach ($results as $row) {
        $labels[] = $row['nama_alternatif'];
        $data[] = round($row['nilai_final'], 4);
    }
}
?>

var ctx = document.getElementById("resultChart");
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: "Nilai Akhir",
            data: <?= json_encode($data) ?>,
            backgroundColor: [
                'rgba(75, 192, 192, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(255, 99, 132, 0.7)',
                'rgba(153, 102, 255, 0.7)'
            ]
        }]
    },
    options: {
        responsive: true,
        scales: {
            yAxes: [{
                beginAtZero: true
            }]
        }
    }
});
</script>

</body>
</html>
