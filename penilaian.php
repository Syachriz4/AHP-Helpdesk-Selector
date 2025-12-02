<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

// Ambil data kriteria dari database
$kriteria_data = query("SELECT kriteria_id, nama_kriteria FROM kriteria ORDER BY kriteria_id");
$kriteria = array_map(function($item) { return $item['nama_kriteria']; }, $kriteria_data);
$kriteria_ids = array_map(function($item) { return $item['kriteria_id']; }, $kriteria_data);

// Ambil data alternatif dari database
$alternatif_data = query("SELECT alternatif_id, nama_alternatif FROM alternatif ORDER BY alternatif_id");
$alternatif = array_map(function($item) { return $item['nama_alternatif']; }, $alternatif_data);
$alternatif_ids = array_map(function($item) { return $item['alternatif_id']; }, $alternatif_data);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Form Penilaian - HelpDesk Selector</title>

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

                <h1 class="h3 mb-4 text-gray-800">Form Penilaian</h1>

                <?php if (isset($_SESSION['success'])) : ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])) : ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php 
                // CEK APAKAH USER SUDAH PERNAH MENGISI PENILAIAN
                $user_id = $_SESSION['user_id'];
                $checkSubmitted = query("SELECT COUNT(*) as total FROM ahp_penilaian_kriteria WHERE user_id = $user_id");
                $alreadySubmitted = !empty($checkSubmitted) && $checkSubmitted[0]['total'] > 0;
                ?>

                <?php if ($alreadySubmitted) : ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>⚠️ Perhatian!</strong> Anda sudah pernah mengisi penilaian sebelumnya. 
                        Setiap DM hanya bisa mengisi 1 kali saja untuk mencegah penumpukan data di database.
                        <br><br>
                        <a href="hasil.php" class="btn btn-sm btn-primary">Lihat Hasil Penilaian</a>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <form action="proses_penilaian.php" method="POST" <?php echo $alreadySubmitted ? 'style="display:none;"' : ''; ?>>

                <!-- PERBANDINGAN KRITERIA -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Perbandingan Kriteria</h6>
                    </div>
                    <div class="card-body">

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Kriteria A</th>
                                    <th>Nilai</th>
                                    <th>Kriteria B</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                for ($i=0; $i<count($kriteria); $i++) {
                                    for ($j=$i+1; $j<count($kriteria); $j++) {
                                        $id_a = $kriteria_ids[$i];
                                        $id_b = $kriteria_ids[$j];
                                        echo "
                                        <tr>
                                            <td>{$kriteria[$i]}</td>
                                            <td>
                                                <select class='form-control' name='kriteria_{$id_a}_{$id_b}'>
                                                    <option value='1'>1 - Sama Penting</option>
                                                    <option value='3'>3 - Sedikit Lebih Penting</option>
                                                    <option value='5'>5 - Lebih Penting</option>
                                                    <option value='7'>7 - Sangat Penting</option>
                                                    <option value='9'>9 - Mutlak Lebih Penting</option>
                                                    <option value='0.33'>0.33 - Sedikit Tidak Lebih Penting</option>
                                                    <option value='0.20'>0.20 - Tidak Lebih Penting</option>
                                                    <option value='0.14'>0.14 - Sangat Tidak Lebih Penting</option>
                                                    <option value='0.11'>0.11 - Mutlak Tidak Lebih Penting</option>
                                                </select>
                                            </td>
                                            <td>{$kriteria[$j]}</td>
                                        </tr>
                                        ";
                                    }
                                }
                                ?>

                            </tbody>
                        </table>

                    </div>
                </div>

                <!-- PERBANDINGAN ALTERNATIF -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Perbandingan Alternatif</h6>
                    </div>
                    <div class="card-body">

                        <?php foreach ($kriteria as $idx => $k) : 
                            $kriteria_id = $kriteria_ids[$idx];
                        ?>
                            <h5 class="mb-3 mt-4 font-weight-bold"><?= $k ?></h5>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Alternatif A</th>
                                        <th>Nilai</th>
                                        <th>Alternatif B</th>
                                    </tr>
                                </thead>
                                <tbody>

                                <?php
                                for ($i=0; $i<count($alternatif); $i++) {
                                    for ($j=$i+1; $j<count($alternatif); $j++) {
                                        $alt_id_a = $alternatif_ids[$i];
                                        $alt_id_b = $alternatif_ids[$j];
                                        echo "
                                        <tr>
                                            <td>{$alternatif[$i]}</td>
                                            <td>
                                                <select class='form-control' name='alt_{$kriteria_id}_{$alt_id_a}_{$alt_id_b}'>
                                                    <option value='1'>1 - Sama Baik</option>
                                                    <option value='3'>3 - Sedikit Lebih Baik</option>
                                                    <option value='5'>5 - Lebih Baik</option>
                                                    <option value='7'>7 - Sangat Baik</option>
                                                    <option value='9'>9 - Mutlak Lebih Baik</option>
                                                    <option value='0.33'>0.33 - Sedikit Tidak Lebih Baik</option>
                                                    <option value='0.20'>0.20 - Tidak Lebih Baik</option>
                                                    <option value='0.14'>0.14 - Sangat Tidak Lebih Baik</option>
                                                    <option value='0.11'>0.11 - Mutlak Tidak Lebih Baik</option>
                                                </select>
                                            </td>
                                            <td>{$alternatif[$j]}</td>
                                        </tr>";
                                    }
                                }
                                ?>

                                </tbody>
                            </table>

                        <?php endforeach; ?>

                    </div>
                </div>

                <!-- TOMBOL SUBMIT -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-save"></i> Simpan Penilaian
                        </button>
                    </div>
                </div>

                </form>

            </div>

        </div>

        <?php include "footer.php"; ?>

    </div>

</div>

</body>
</html>
