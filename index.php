<?php
session_start();
if (!isset($_SESSION["user"])) {
    $_SESSION["user"] = "Alya";
}
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

                    <div class="row">

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Penilai</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">3 Orang</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Alternatif</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">5 Sistem</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Kriteria</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">5 Kriteria</div>
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
