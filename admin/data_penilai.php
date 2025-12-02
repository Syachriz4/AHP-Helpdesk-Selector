<?php 
include "../config.php"; 
session_start();

// CREATE
if (isset($_POST['add'])) {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $jabatan = $_POST['jabatan'] ?? 'staff';

    mysqli_query($conn, "INSERT INTO users (nama, username, password, role, jabatan) 
                         VALUES ('$nama', '$username', '$password', 'dm', '$jabatan')");

    header("Location: data_penilai.php");
    exit();
}

// UPDATE
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $jabatan = $_POST['jabatan'] ?? 'staff';

    if ($_POST['password'] != "") {
        $password = $_POST['password'];
        mysqli_query($conn, "UPDATE users 
                             SET nama='$nama', username='$username', password='$password', jabatan='$jabatan' 
                             WHERE user_id=$id");
    } else {
        mysqli_query($conn, "UPDATE users 
                             SET nama='$nama', username='$username', jabatan='$jabatan' 
                             WHERE user_id=$id");
    }

    header("Location: data_penilai.php");
    exit();
}

// DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE user_id=$id AND role='dm'");
    header("Location: data_penilai.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Data Penilai</title>

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
                    <h3 class="text-gray-800">Data Penilai</h3>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalAdd">
                        <i class="fas fa-user-plus"></i> Tambah Penilai
                    </button>
                </div>

                <div class="card shadow">
                    <div class="card-body">

                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 8%">ID</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Status Pengisian</th>
                                    <th style="width: 20%">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php 
                                $data = mysqli_query($conn, "SELECT * FROM users WHERE role='dm' ORDER BY user_id");
                                while ($row = mysqli_fetch_assoc($data)) { ?>
                                    <tr>
                                        <td><?= $row['user_id'] ?></td>
                                        <td><?= $row['nama'] ?></td>
                                        <td><?= $row['username'] ?></td>

                                        <td>
                                            <?php 
                                            $statusCheck = mysqli_query($conn, "SELECT COUNT(*) as jumlah FROM borda_input WHERE user_id={$row['user_id']}");
                                            $statusResult = mysqli_fetch_assoc($statusCheck);
                                            if ($statusResult['jumlah'] > 0) { ?>
                                                <span class="badge badge-success">Sudah Menilai</span>
                                            <?php } else { ?>
                                                <span class="badge badge-warning">Belum Menilai</span>
                                            <?php } ?>
                                        </td>

                                        <td>
                                            <button class="btn btn-warning btn-sm"
                                                    data-toggle="modal"
                                                    data-target="#modalEdit<?= $row['user_id'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <a href="data_penilai.php?delete=<?= $row['user_id'] ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Hapus penilai ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Modal Edit -->
                                    <div class="modal fade" id="modalEdit<?= $row['user_id'] ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">

                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Penilai</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>

                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $row['user_id'] ?>">
                                                        
                                                        <label>Nama</label>
                                                        <input type="text" name="nama" class="form-control"
                                                               value="<?= $row['nama'] ?>" required>

                                                        <label class="mt-2">Username</label>
                                                        <input type="text" name="username" 
                                                               class="form-control"
                                                               value="<?= $row['username'] ?>" required>

                                                        <label class="mt-2">Password (isi jika ingin diganti)</label>
                                                        <input type="password" name="password" 
                                                               class="form-control" placeholder="kosongkan jika tidak ganti">

                                                        <label class="mt-2">Jabatan</label>
                                                        <select name="jabatan" class="form-control" required>
                                                            <option value="manager" <?= ($row['jabatan'] === 'manager') ? 'selected' : '' ?>>Manager</option>
                                                            <option value="staff" <?= ($row['jabatan'] === 'staff') ? 'selected' : '' ?>>Staff</option>
                                                        </select>

                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="submit" name="edit" class="btn btn-warning">Update</button>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>

                                <?php } ?>
                            </tbody>

                        </table>

                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalAdd">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Tambah Penilai</h5>
                <button class="close" type="button" data-dismiss="modal">×</button>
            </div>

            <form method="POST">
                <div class="modal-body">

                    <label>Nama</label>
                    <input type="text" name="nama" class="form-control" required>

                    <label class="mt-2">Username</label>
                    <input type="text" name="username" class="form-control" required>

                    <label class="mt-2">Password</label>
                    <input type="password" name="password" class="form-control" required>

                    <label class="mt-2">Jabatan</label>
                    <select name="jabatan" class="form-control" required>
                        <option value="staff">Staff</option>
                        <option value="manager">Manager</option>
                    </select>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" name="add">Tambah</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>
