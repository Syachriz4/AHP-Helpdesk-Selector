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
                                $kriteria = ["Kemudahan", "Harga", "Komunitas", "Konstruksi", "Omnichannel"];

                                for ($i=0; $i<count($kriteria); $i++) {
                                    for ($j=$i+1; $j<count($kriteria); $j++) {
                                        echo "
                                        <tr>
                                            <td>{$kriteria[$i]}</td>
                                            <td>
                                                <select class='form-control'>
                                                    <option value='1'>1 - Sama Penting</option>
                                                    <option value='3'>3 - Sedikit Lebih Penting</option>
                                                    <option value='5'>5 - Lebih Penting</option>
                                                    <option value='7'>7 - Sangat Penting</option>
                                                    <option value='9'>9 - Mutlak Lebih Penting</option>
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

                        <?php
                        $alternatif = ["osTicket", "Zendesk", "UVdesk", "Zammad", "Manual"];
                        ?>

                        <?php foreach ($kriteria as $k) : ?>
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
                                        echo "
                                        <tr>
                                            <td>{$alternatif[$i]}</td>
                                            <td>
                                                <select class='form-control'>
                                                    <option value='1'>1</option>
                                                    <option value='2'>2</option>
                                                    <option value='3'>3</option>
                                                    <option value='4'>4</option>
                                                    <option value='5'>5</option>
                                                    <option value='6'>6</option>
                                                    <option value='7'>7</option>
                                                    <option value='8'>8</option>
                                                    <option value='9'>9</option>
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

            </div>

        </div>

        <?php include "footer.php"; ?>

    </div>

</div>

</body>
</html>
