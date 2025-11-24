<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Hasil Akhir - HelpDesk Selector</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <script src="vendor/chart.js/Chart.min.js"></script>
</head>

<body id="page-top">

<div id="wrapper">

    <?php include "sidebar.php"; ?>

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">

            <?php include "topbar.php"; ?>

            <div class="container-fluid">

                <h1 class="h3 mb-4 text-gray-800">Hasil Analisis GDSS</h1>

                <!-- RANKING -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Ranking Alternatif</h6>
                    </div>
                    <div class="card-body">

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Peringkat</th>
                                    <th>Alternatif</th>
                                    <th>Nilai Akhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>1</td><td>osTicket</td><td>0.32</td></tr>
                                <tr><td>2</td><td>Zammad</td><td>0.27</td></tr>
                                <tr><td>3</td><td>Zendesk</td><td>0.20</td></tr>
                                <tr><td>4</td><td>UVdesk</td><td>0.15</td></tr>
                                <tr><td>5</td><td>Sistem Manual</td><td>0.06</td></tr>
                            </tbody>
                        </table>

                    </div>
                </div>

                <!-- CHART -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Grafik Hasil</h6>
                    </div>

                    <div class="card-body">
                        <canvas id="resultChart"></canvas>
                    </div>
                </div>

            </div>

        </div>

        <?php include "footer.php"; ?>

    </div>

</div>

<script>
var ctx = document.getElementById("resultChart");
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ["osTicket", "Zammad", "Zendesk", "UVdesk", "Manual"],
        datasets: [{
            label: "Skor",
            data: [32, 27, 20, 15, 6]
        }]
    }
});
</script>

</body>
</html>
