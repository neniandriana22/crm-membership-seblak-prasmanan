<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($judulHalaman) ? esc($judulHalaman) . ' - ' : '' ?>Admin SEBLAK NENI</title>
    <!-- Tambahkan FontAwesome untuk Ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<div class="navbar no-print">
    <div class="brand"><span style="display:inline-block; animation: shake-chili 1.5s infinite;">🌶️</span> SEBLAK PRASMANAN NENI</div>
    <div>
        <a href="<?= BASE_URL ?>admin/index.php">Dashboard</a>
        <a href="<?= BASE_URL ?>admin/anggota.php">Data Member</a>
        <a href="<?= BASE_URL ?>admin/scan.php">Scan Kartu</a>
        <!-- Link Feedback sudah dihapus dari sini -->
        <a href="<?= BASE_URL ?>admin/logout.php">Keluar (<?= esc($_SESSION['admin_nama'] ?? '') ?>)</a>
    </div>
</div>
<div class="container">