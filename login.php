<?php
session_start();
require_once 'config.php';

// Kalau sudah login, langsung masuk ke dashboard
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Login</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        /* Kamu bisa ganti background kiri di sini */
        .bg-login-image {
            background: url('img/your-animation.gif');
            background-position: center;
            background-size: cover;
        }
    </style>

</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">

                        <!-- Row dalam card -->
                        <div class="row">

                            <!-- Bagian kiri -->
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>

                            <!-- Bagian kanan -->
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome!</h1>
                                    </div>

                                    <?php if (isset($_SESSION['error'])) : ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <?= $_SESSION['error'] ?>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <?php unset($_SESSION['error']); ?>
                                    <?php endif; ?>

                                    <?php if (isset($_SESSION['success'])) : ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <?= $_SESSION['success'] ?>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <?php unset($_SESSION['success']); ?>
                                    <?php endif; ?>

                                    <!-- Form login -->
                                    <form action="proses_login.php" method="POST" class="user">
                                        <div class="form-group">
                                            <input type="text" class="form-control form-control-user"
                                                name="username" placeholder="Username" required>
                                        </div>

                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"
                                                name="password" placeholder="Password" required>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                    </form>

                                    <hr>

                                    <div class="text-center">
                                        <a class="small" href="register.php">Create an Account!</a>
                                    </div>

                                </div>
                            </div>

                        </div>
                        <!-- /Row -->

                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Script SB Admin 2 -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>

</body>

</html>
