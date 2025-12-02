<?php
session_start();
require_once '../config.php';

// Cek apakah user sudah login dan adalah admin
if (!isset($_SESSION["user"]) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil ID user yang akan di-edit
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'ID user tidak ditemukan';
    header("Location: data_penilaian.php");
    exit;
}

$user_id = $_GET['id'];

// Ambil data user
$user_data = query("SELECT user_id, nama FROM users WHERE user_id = $user_id AND role = 'dm'");
if (empty($user_data)) {
    $_SESSION['error'] = 'User DM tidak ditemukan';
    header("Location: data_penilaian.php");
    exit;
}
$user_name = $user_data[0]['nama'];

// Ambil data kriteria dari database
$kriteria_data = query("SELECT kriteria_id, nama_kriteria FROM kriteria ORDER BY kriteria_id");
$kriteria = array_map(function($item) { return $item['nama_kriteria']; }, $kriteria_data);
$kriteria_ids = array_map(function($item) { return $item['kriteria_id']; }, $kriteria_data);

// Ambil data alternatif dari database
$alternatif_data = query("SELECT alternatif_id, nama_alternatif FROM alternatif ORDER BY alternatif_id");
$alternatif = array_map(function($item) { return $item['nama_alternatif']; }, $alternatif_data);
$alternatif_ids = array_map(function($item) { return $item['alternatif_id']; }, $alternatif_data);

// Ambil perbandingan kriteria dari database
$perbandingan_kriteria = query("SELECT kriteria1_id, kriteria2_id, nilai FROM ahp_penilaian_kriteria WHERE user_id = $user_id");
$nilai_kriteria = [];
foreach ($perbandingan_kriteria as $row) {
    $key = $row['kriteria1_id'] . "|" . $row['kriteria2_id'];
    $nilai_kriteria[$key] = $row['nilai'];
}

// Ambil perbandingan alternatif dari database
$perbandingan_alternatif = query("SELECT kriteria_id, alternatif1_id, alternatif2_id, nilai FROM ahp_penilaian_alternatif WHERE user_id = $user_id");
$nilai_alternatif = [];
foreach ($perbandingan_alternatif as $row) {
    $krit_id = $row['kriteria_id'];
    $key = $row['alternatif1_id'] . "|" . $row['alternatif2_id'];
    if (!isset($nilai_alternatif[$krit_id])) {
        $nilai_alternatif[$krit_id] = [];
    }
    $nilai_alternatif[$krit_id][$key] = $row['nilai'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Edit Penilaian - HelpDesk Selector</title>

    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">

<div id="wrapper">

    <?php include "sidebar_admin.php"; ?>

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">

            <?php include "../topbar.php"; ?>

            <div class="container-fluid">

                <h1 class="h3 mb-4 text-gray-800">Edit Penilaian - <?= $user_name ?></h1>

                <form method="POST" action="update_penilaian.php">
                <input type="hidden" name="user_id" value="<?= $user_id ?>">

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
                                foreach ($kriteria_ids as $i => $id_a) {
                                    for ($j = $i + 1; $j < count($kriteria_ids); $j++) {
                                        $id_b = $kriteria_ids[$j];
                                        $key = $id_a . "|" . $id_b;
                                        $value = $nilai_kriteria[$key] ?? 1;
                                        $nama_a = $kriteria[$i];
                                        $nama_b = $kriteria[$j];
                                        ?>

                                        <tr>
                                            <td><?= $nama_a ?></td>

                                            <td>
                                                <select class="form-control" name="kriteria_<?= $id_a ?>_<?= $id_b ?>">
                                                    <?php
                                                    $options = [
                                                        1 => "1 - Sama Penting",
                                                        3 => "3 - Sedikit Lebih Penting",
                                                        5 => "5 - Lebih Penting",
                                                        7 => "7 - Sangat Penting",
                                                        9 => "9 - Mutlak Lebih Penting",
                                                        0.33 => "0.33 - Sedikit Tidak Penting",
                                                        0.20 => "0.20 - Tidak Penting",
                                                        0.14 => "0.14 - Sangat Tidak Penting",
                                                        0.11 => "0.11 - Mutlak Tidak Penting",
                                                    ];

                                                    foreach ($options as $num => $label) {
                                                        $selected = ((float)$value == (float)$num) ? "selected" : "";
                                                        echo "<option value='$num' $selected>$label</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </td>

                                            <td><?= $nama_b ?></td>
                                        </tr>

                                <?php
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

                        <?php foreach ($kriteria_ids as $idx => $kriteria_id) : 
                            $kriteria_name = $kriteria[$idx];
                        ?>
                            <h5 class="mb-3 mt-4 font-weight-bold"><?= $kriteria_name ?></h5>

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
                                foreach ($alternatif_ids as $i => $alt_id_a) {
                                    for ($j = $i + 1; $j < count($alternatif_ids); $j++) {
                                        $alt_id_b = $alternatif_ids[$j];
                                        $key = $alt_id_a . "|" . $alt_id_b;
                                        $value = $nilai_alternatif[$kriteria_id][$key] ?? 1;
                                        $nama_a = $alternatif[$i];
                                        $nama_b = $alternatif[$j];
                                        ?>

                                        <tr>
                                            <td><?= $nama_a ?></td>

                                            <td>
                                                <select class="form-control"
                                                    name="alt_<?= $kriteria_id ?>_<?= $alt_id_a ?>_<?= $alt_id_b ?>">

                                                    <?php
                                                    foreach ($options as $num => $label) {
                                                        $selected = ((float)$value == (float)$num) ? "selected" : "";
                                                        echo "<option value='$num' $selected>$label</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </td>

                                            <td><?= $nama_b ?></td>
                                        </tr>

                                <?php
                                    }
                                }
                                ?>

                                </tbody>
                            </table>

                        <?php endforeach; ?>

                    </div>
                </div>

                <button type="submit" class="btn btn-primary mb-4">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>

                </form>

            </div>

        </div>

        <?php include "../footer.php"; ?>

    </div>

</div>

</body>
</html>
