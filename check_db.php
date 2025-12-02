<?php
require_once 'config.php';

echo "<pre>\n";
echo "=== DATA IN TABLES ===\n\n";

echo "ahp_prioritas_final:\n";
$result = query('SELECT * FROM ahp_prioritas_final');
echo "Count: " . count($result) . "\n";
print_r($result);

echo "\n\nborda_hasil:\n";
$result = query('SELECT * FROM borda_hasil');
echo "Count: " . count($result) . "\n";
print_r($result);

echo "\n\nborda_input:\n";
$result = query('SELECT * FROM borda_input');
echo "Count: " . count($result) . "\n";
print_r($result);

echo "\n\nahp_penilaian_kriteria:\n";
$result = query('SELECT * FROM ahp_penilaian_kriteria');
echo "Count: " . count($result) . "\n";
print_r($result);

echo "</pre>";
?>
