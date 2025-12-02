<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - HelpDesk Selector</title>

    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">

<div id="wrapper">

    <?php include "sidebar_admin.php"; ?>

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">

            <?php include "../topbar.php"; ?>

            <div class="container-fluid mt-4">

                <!-- ========================= SELAMAT DATANG ========================= -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-primary">
                        <h5 class="m-0 font-weight-bold text-white">Selamat Datang, Admin!</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-gray-800">
                            Selamat datang di <strong>HelpDesk Selector</strong>, sebuah sistem pendukung keputusan 
                            yang membantu menentukan <strong>aplikasi HelpDesk terbaik</strong> berdasarkan metode:
                        </p>

                        <ul class="text-gray-800">
                            <li><strong>AHP (Analytical Hierarchy Process)</strong> – menentukan bobot kriteria dan alternatif.</li>
                            <li><strong>GDSS (Group Decision Support System)</strong> – menggabungkan penilaian dari banyak user.</li>
                            <li><strong>BORDA</strong> – menghasilkan ranking dan rekomendasi akhir.</li>
                        </ul>

                        <p class="text-gray-800 mb-0">
                            Melalui dashboard ini, Anda dapat mengelola:
                        </p>

                        <ul class="text-gray-800 mb-3">
                            <li>Data Kriteria</li>
                            <li>Data Alternatif</li>
                            <li>Penilaian User</li>
                            <li>Hasil keputusan sistem</li>
                        </ul>

                        <p class="text-gray-800">
                            Gunakan menu di samping untuk mulai mengelola data dan melihat hasil perhitungan secara lengkap.
                        </p>
                    </div>
                </div>

                <!-- ========================= INFO STATISTIK ========================= -->
                <div class="row">

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total User Penilai</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">3 Orang</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Jumlah Alternatif</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">5 Sistem</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Jumlah Kriteria</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">5 Kriteria</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Rekomendasi Teratas</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Zendesk</div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>
