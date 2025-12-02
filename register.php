<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Create Account</title>

    <!-- Custom fonts -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        /* Ganti gambar kiri sesuai kebutuhan */
        .bg-register-image {
            background: url('img/your-animation-register.gif');
            background-position: center;
            background-size: cover;
        }
    </style>

</head>

<body class="bg-gradient-primary">

    <?php
    session_start();
    ?>

    <div class="container">

        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">

                <!-- Row -->
                <div class="row">

                    <!-- Left Illustration -->
                    <div class="col-lg-5 d-none d-lg-block bg-register-image"></div>

                    <!-- Right Form -->
                    <div class="col-lg-7">
                        <div class="p-5">

                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Create an Account!</h1>
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

                            <form action="proses_register.php" method="POST" class="user">

                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <input type="text" 
                                               class="form-control form-control-user" 
                                               name="firstname"
                                               placeholder="First Name" required>
                                    </div>

                                    <div class="col-sm-6">
                                        <input type="text" 
                                               class="form-control form-control-user" 
                                               name="lastname"
                                               placeholder="Last Name" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <input type="email" 
                                           class="form-control form-control-user" 
                                           name="email"
                                           placeholder="Email Address" required>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <input type="password" 
                                               class="form-control form-control-user"
                                               name="password"
                                               placeholder="Password" required>
                                    </div>

                                    <div class="col-sm-6">
                                        <input type="password" 
                                               class="form-control form-control-user"
                                               name="password2"
                                               placeholder="Repeat Password" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-user btn-block">
                                    Register Account
                                </button>

                            </form>

                            <hr>

                            <div class="text-center">
                                <a class="small" href="login.php">Already have an account? Login!</a>
                            </div>

                        </div>
                    </div>
                    <!-- /Right Form -->

                </div>
                <!-- /Row -->

            </div>
        </div>

    </div>

    <!-- JS -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>

</body>

</html>
