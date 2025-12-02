<?php
session_start();
include "../config.php";

// Check admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// CREATE
if (isset($_POST['add'])) {
    $kode = htmlspecialchars($_POST['kode']);
    $nama = htmlspecialchars($_POST['nama']);
    $ket  = htmlspecialchars($_POST['keterangan']);

    $result = mysqli_query($conn, "INSERT INTO kriteria (kode, nama_kriteria, keterangan, created_at)
                         VALUES ('$kode', '$nama', '$ket', NOW())");

    if ($result) {
        $_SESSION['success'] = '✓ Kriteria berhasil ditambahkan!';
    } else {
        $_SESSION['error'] = '✗ Gagal menambahkan kriteria!';
    }
    header("Location: kriteria.php");
    exit();
}

// UPDATE
if (isset($_POST['edit'])) {
    $id   = (int)$_POST['id'];
    $kode = htmlspecialchars($_POST['kode']);
    $nama = htmlspecialchars($_POST['nama']);
    $ket  = htmlspecialchars($_POST['keterangan']);

    $result = mysqli_query($conn, "UPDATE kriteria 
                         SET kode='$kode', nama_kriteria='$nama', keterangan='$ket'
                         WHERE kriteria_id=$id");

    if ($result) {
        $_SESSION['success'] = '✓ Kriteria berhasil diperbarui!';
    } else {
        $_SESSION['error'] = '✗ Gagal memperbarui kriteria!';
    }
    header("Location: kriteria.php");
    exit();
}

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $result = mysqli_query($conn, "DELETE FROM kriteria WHERE kriteria_id=$id");
    
    if ($result) {
        $_SESSION['success'] = '✓ Kriteria berhasil dihapus!';
    } else {
        $_SESSION['error'] = '✗ Gagal menghapus kriteria!';
    }
    header("Location: kriteria.php");
    exit();
}

// FETCH DATA FROM DATABASE
$kriteria_data = query("SELECT * FROM kriteria ORDER BY kriteria_id ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kelola Kriteria</title>

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

                <div class="d-flex justify-content-between mb-3">
                    <h3 class="text-gray-800">Kelola Kriteria</h3>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalAdd">
                        <i class="fas fa-plus"></i> Tambah Kriteria
                    </button>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-body">

                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Kode</th>
                                    <th>Nama Kriteria</th>
                                    <th>Keterangan</th>
                                    <th style="width: 15%">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($kriteria_data as $row): ?>
                                    <tr>
                                        <td><?= $row['kriteria_id'] ?></td>
                                        <td><?= $row['kode'] ?></td>
                                        <td><?= $row['nama_kriteria'] ?></td>
                                        <td><?= $row['keterangan'] ?></td>

                                        <td>
                                            <button class="btn btn-warning btn-sm"
                                                    data-toggle="modal"
                                                    data-target="#modalEdit<?= $row['kriteria_id'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <a href="kriteria.php?delete=<?= $row['kriteria_id'] ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Hapus kriteria ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- ================= MODAL EDIT ================= -->
                                    <div class="modal fade" id="modalEdit<?= $row['kriteria_id'] ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">

                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Kriteria</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>

                                                <form method="POST">

                                                    <div class="modal-body">

                                                        <input type="hidden" name="edit" value="1">
                                                        <input type="hidden" name="id" value="<?= $row['kriteria_id'] ?>">

                                                        <label>Kode</label>
                                                        <input type="text" class="form-control" name="kode"
                                                               value="<?= $row['kode'] ?>" required>

                                                        <label class="mt-2">Nama Kriteria</label>
                                                        <input type="text" class="form-control" name="nama"
                                                               value="<?= $row['nama_kriteria'] ?>" required>

                                                        <label class="mt-2">Keterangan</label>
                                                        <textarea class="form-control" name="keterangan" required><?= $row['keterangan'] ?></textarea>

                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                            Tutup
                                                        </button>
                                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                    </div>

                                                </form>

                                            </div>
                                        </div>
                                    </div>

                                <?php endforeach; ?>
                            </tbody>

                        </table>

                    </div>
                </div>

            </div>

        </div>
    </div>

</div>

<!-- ================= MODAL ADD ================= -->
<div class="modal fade" id="modalAdd">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Tambah Kriteria</h5>
                <button class="close" type="button" data-dismiss="modal">×</button>
            </div>

            <form method="POST">

                <div class="modal-body">

                    <input type="hidden" name="add" value="1">

                    <label>Kode</label>
                    <input type="text" class="form-control" name="kode" placeholder="C1 / C2 / ..." required>

                    <label class="mt-2">Nama Kriteria</label>
                    <input type="text" class="form-control" name="nama" required>

                    <label class="mt-2">Keterangan</label>
                    <textarea class="form-control" name="keterangan" required></textarea>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit">Tambah</button>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>
