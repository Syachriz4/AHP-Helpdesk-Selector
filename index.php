<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

// Ambil data dari database
$totalPenilai = countRows("SELECT * FROM users");
$totalAlternatif = countRows("SELECT * FROM alternatif");
$totalKriteria = countRows("SELECT * FROM kriteria");

// Fallback jika table belum ada atau kosong
if ($totalPenilai == 0) $totalPenilai = 3;
if ($totalAlternatif == 0) $totalAlternatif = 5;
if ($totalKriteria == 0) $totalKriteria = 5;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>HelpDesk Selector</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">

        <?php include "sidebar.php"; ?>

        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                <?php include "topbar.php"; ?>

                <div class="container-fluid">

                    <h1 class="h3 mb-4 text-gray-800">Dashboard HelpDesk Selector</h1>

                    <!-- ============================ WELCOME CARD ============================ -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">Selamat Datang, <?= $_SESSION["user"] ?>!</h6>
                        </div>
                        <div class="card-body text-gray-800">
                            <p>
                                Anda berada di dalam sistem <strong>HelpDesk Selector</strong> — sebuah sistem pendukung keputusan
                                yang membantu menentukan <strong>aplikasi HelpDesk terbaik</strong> berdasarkan metode:
                            </p>
                            <ul>
                                <li><strong>AHP (Analytical Hierarchy Process)</strong> untuk menghitung bobot kriteria & alternatif.</li>
                                <li><strong>GDSS</strong> untuk menggabungkan hasil penilaian dari beberapa user.</li>
                                <li><strong>Borda</strong> untuk mendapatkan ranking akhir alternatif terbaik.</li>
                            </ul>
                            <p class="mb-0">
                                Silakan mulai melakukan penilaian melalui menu <strong>Form Penilaian</strong> di sidebar kiri.
                                <?php if (isset($_SESSION['jabatan']) && $_SESSION['jabatan'] === 'manager') : ?>
                                    Sebagai Manager, Anda juga memiliki akses ke menu <strong>Hitung Borda</strong> untuk menghitung hasil akhir.
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <!-- ============================ INFO STATISTIK ============================ -->
                    <div class="row">

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Penilai</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalPenilai ?> Orang</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Alternatif</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalAlternatif ?> Sistem</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Kriteria</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalKriteria ?> Kriteria</div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <?php include "footer.php"; ?>

        </div>

    </div>

</body>

</html>
