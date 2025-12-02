<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

// Cek apakah user adalah manager
if (!isset($_SESSION['jabatan']) || $_SESSION['jabatan'] !== 'manager') {
    $_SESSION['error'] = 'Hanya Manager yang dapat menghitung Borda!';
    header("Location: hasil.php");
    exit;
}

// ============================== HITUNG BORDA COUNT ============================== //

// Ambil semua alternatif
$alternatif = query("SELECT * FROM alternatif ORDER BY alternatif_id ASC");
$jumlahAlternatif = count($alternatif);

// Ambil semua user DM yang sudah voting
$dmUsers = query("SELECT DISTINCT user_id FROM borda_input");

if (empty($dmUsers)) {
    $_SESSION['error'] = 'Belum ada Decision Maker yang melakukan voting!';
    header("Location: hasil.php");
    exit;
}

// Inisialisasi array skor Borda
$skor_borda = [];
foreach ($alternatif as $alt) {
    $skor_borda[$alt['alternatif_id']] = 0;
}

// Hitung skor Borda
// Ranking 1 = jumlahAlternatif poin
// Ranking 2 = jumlahAlternatif - 1 poin
// dst...

foreach ($dmUsers as $dm) {
    $userid = $dm['user_id'];
    
    // Ambil ranking dari user ini
    $userRankings = query("SELECT * FROM borda_input WHERE user_id = $userid ORDER BY ranking ASC");
    
    foreach ($userRankings as $rank) {
        $alt_id = $rank['alternatif_id'];
        $ranking = $rank['ranking'];
        
        // Skor Borda = (jumlahAlternatif - ranking + 1)
        $poin = $jumlahAlternatif - $ranking + 1;
        $skor_borda[$alt_id] += $poin;
    }
}

// Sort by score (descending)
arsort($skor_borda);

// Hapus data lama di tabel borda_hasil
execute("DELETE FROM borda_hasil");

// Simpan hasil baru ke database
$peringkat = 1;
foreach ($skor_borda as $alt_id => $skor) {
    execute("INSERT INTO borda_hasil (alternatif_id, skor_borda, peringkat) 
             VALUES ($alt_id, $skor, $peringkat)");
    $peringkat++;
}

$_SESSION['success'] = 'Borda Count berhasil dihitung! Lihat hasil di bawah.';
header("Location: hasil.php#borda-section");
exit;
?>
