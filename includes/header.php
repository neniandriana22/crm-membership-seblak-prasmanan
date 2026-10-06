<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($judulHalaman) ? esc($judulHalaman) . ' - ' : '' ?>Sistem Membership CRM</title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<body>
<div class="navbar no-print">
    <div class="brand">💳 Membership Seblak Prasmanan Neni</div>
    <div>
        <a href="<?= BASE_URL ?>index.php">Beranda</a>
        <a href="<?= BASE_URL ?>daftar.php">Daftar Member</a>
        <a href="<?= BASE_URL ?>portal.php">Portal Member</a>
        <a href="<?= BASE_URL ?>admin/login.php">Login Admin</a>
    </div>
</div>
<div class="container">
