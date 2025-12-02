<?php
session_start();
require_once '../config.php';

// Cek apakah user sudah login dan adalah admin
if (!isset($_SESSION["user"]) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ===== AMBIL DATA PENILAIAN DARI DATABASE =====

// Query: Ambil semua DM dengan data penilaian mereka
$data = query("
    SELECT 
        u.user_id,
        u.nama,
        u.jabatan,
        COALESCE(apf.hasil_sistem, '-') as hasil_sistem,
        COALESCE(apf.nilai_final, 0) as nilai_final,
        COALESCE(apf.ranking, '-') as ranking,
        COALESCE(bi.sudah_voting, 0) as sudah_voting
    FROM users u
    LEFT JOIN (
        SELECT 
            apf.user_id,
            a.nama_alternatif as hasil_sistem,
            apf.nilai_final,
            apf.ranking
        FROM ahp_prioritas_final apf
        JOIN alternatif a ON apf.alternatif_id = a.alternatif_id
        WHERE apf.ranking = 1
    ) apf ON u.user_id = apf.user_id
    LEFT JOIN (
        SELECT user_id, COUNT(*) as sudah_voting
        FROM borda_input
        GROUP BY user_id
    ) bi ON u.user_id = bi.user_id
    WHERE u.role = 'dm'
    ORDER BY u.user_id ASC
");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Data Penilaian User</title>

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

                <div class="d-flex justify-content-between mb-3">
                    <h3 class="text-gray-800">Data Penilaian User</h3>
                    <?php if (count($data) == 0) : ?>
                        <span class="badge badge-danger badge-lg">Belum ada data penilaian</span>
                    <?php else : ?>
                        <span class="badge badge-success badge-lg"><?= count($data) ?> User telah dinilai</span>
                    <?php endif; ?>
                </div>

                <div class="card shadow">
                    <div class="card-body">

                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 5%">ID</th>
                                    <th style="width: 20%">Nama User</th>
                                    <th style="width: 20%">Jabatan</th>
                                    <th style="width: 25%">Hasil Akhir (Ranking 1)</th>
                                    <th style="width: 10%">Nilai AHP</th>
                                    <th style="width: 15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>

                            <?php if (!empty($data)) : ?>
                                <?php foreach ($data as $row) : ?>
                                    <tr>
                                        <td><?= $row['user_id'] ?></td>
                                        <td><?= $row['nama'] ?></td>
                                        <td>
                                            <?php if ($row['jabatan'] === 'manager') : ?>
                                                <span class="badge badge-info">Manager</span>
                                            <?php else : ?>
                                                <span class="badge badge-secondary">Staff</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['hasil_sistem'] !== '-' && $row['sudah_voting'] > 0) : ?>
                                                <strong><?= $row['hasil_sistem'] ?></strong> 
                                                <span class="text-muted">(Ranking <?= $row['ranking'] ?>)</span>
                                            <?php else : ?>
                                                <span class="badge badge-warning">Belum Voting</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['nilai_final'] > 0) : ?>
                                                <?= number_format($row['nilai_final'], 4) ?>
                                            <?php else : ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <!-- tombol edit -->
                                            <a href="edit_penilaian.php?id=<?= $row['user_id'] ?>"
                                               class="btn btn-warning btn-sm"
                                               title="Edit Penilaian">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>

                                            <!-- tombol delete (disabled jika belum ada data) -->
                                            <?php if ($row['sudah_voting'] > 0) : ?>
                                                <a href="javascript:void(0);"
                                                   class="btn btn-danger btn-sm"
                                                   title="Delete Penilaian"
                                                   onclick="if(confirm('Hapus data penilaian user ini? Data tidak bisa dikembalikan.')) { window.location.href='hapus_penilaian.php?id=<?= $row['user_id'] ?>'; }">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada data penilaian
                                    </td>
                                </tr>
                            <?php endif; ?>

                            </tbody>
                        </table>

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
