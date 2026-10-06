<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

$kode = $_GET['kode'] ?? '';
$baru = isset($_GET['baru']);

$stmt = $pdo->prepare(
    "SELECT m.*, t.nama_tier, t.diskon_persen
     FROM members m JOIN membership_tiers t ON m.tier_id = t.id
     WHERE m.member_code = ?"
);
$stmt->execute([$kode]);
$member = $stmt->fetch();

$judulHalaman = 'Kartu Member VIP';
require_once __DIR__ . '/includes/header.php';

?>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/kartu-vip.css">
<?php

if (!$member) {
    echo '<div class="alert alert-error no-print"><i class="fa-solid fa-circle-exclamation"></i> Kode member tidak ditemukan.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// 1. BLOKIR JIKA STATUS MASIH PENDING
if ($member['status'] === 'pending') {
    echo '<div class="card-box no-print" style="max-width: 500px; margin: 50px auto; text-align: center;">';
    echo '<i class="fa-solid fa-clock-rotate-left" style="font-size: 60px; color: #f57f17; margin-bottom: 20px;"></i>';
    echo '<h3 style="color: #f57f17; margin-top: 0;">MENUNGGU KONFIRMASI ADMIN</h3>';
    echo '<p style="color: #555; line-height: 1.6;">Mohon maaf, Kartu Digital VIP Anda belum bisa dicetak/digunakan karena <strong>pembayaran Anda sedang dalam proses verifikasi</strong> oleh Admin.</p>';
    echo '<p style="font-size: 13px; color: #888;">Silakan cek kembali halaman ini nanti secara berkala. (Maksimal proses verifikasi 1x24 Jam).</p>';
    echo '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// 2. BLOKIR JIKA STATUS DITOLAK / NONAKTIF (DENGAN KONTAK ADMIN)
if ($member['status'] === 'nonaktif') {
    echo '<div class="card-box no-print" style="max-width: 500px; margin: 50px auto; text-align: center; border-top: 5px solid #c62828; box-shadow: 0 10px 25px rgba(198,40,40,0.15);">';
    echo '<i class="fa-solid fa-circle-xmark" style="font-size: 60px; color: #c62828; margin-bottom: 15px;"></i>';
    echo '<h3 style="color: #c62828; margin-top: 0;">PENDAFTARAN DITOLAK</h3>';
    echo '<p style="color: #555; line-height: 1.6; margin-bottom: 20px;">Mohon maaf, Kartu Digital VIP Anda tidak dapat dicetak/digunakan karena pendaftaran Anda <strong>telah ditolak atau dinonaktifkan</strong> oleh Admin. Hal ini biasanya terjadi jika bukti transfer tidak valid atau tidak terbaca.</p>';
    
    echo '<div style="background: #fafafa; padding: 15px; border-radius: 8px; border: 1px solid #eee; margin-bottom: 20px;">';
    echo '<p style="font-size: 13px; color: #444; margin-top: 0; margin-bottom: 15px;">Jika Anda merasa ini adalah kesalahan, silakan hubungi Admin Seblak Neni:</p>';
    // Ganti nomor 6281234567890 dengan nomor WA Admin yang asli
    echo '<a href="https://wa.me/6281234567890" target="_blank" class="btn" style="background: #25D366; color: white; padding: 10px 20px; border-radius: 30px; text-decoration: none; font-size: 14px; box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3); display: inline-block;">';
    echo '<i class="fa-brands fa-whatsapp" style="font-size: 16px;"></i> Hubungi Admin (0812-3456-7890)</a>';
    echo '</div>';
    
    echo '<a href="daftar.php" class="btn btn-outline" style="border-radius: 30px; font-size: 14px; padding: 8px 20px;"><i class="fa-solid fa-rotate-right"></i> Daftar Ulang</a>';
    echo '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$inisial = strtoupper(substr($member['nama_lengkap'], 0, 1));
?>

<?php if ($baru): ?>
    <div class="alert alert-success no-print" style="max-width: 600px; margin: 0 auto 25px; border-left: 5px solid #2e7d32; box-shadow: 0 4px 15px rgba(46, 125, 50, 0.15);">
        <i class="fa-solid fa-circle-check" style="font-size: 18px; vertical-align: middle;"></i> 
        <strong>Pendaftaran Berhasil!</strong> Kartu VIP Eksklusif Anda sudah aktif. Simpan dan cetak kartu ini, serta catat kode member Anda: 
        <span style="font-family: monospace; background: #c8e6c9; padding: 2px 8px; border-radius: 4px; font-weight: bold;"><?= esc($member['member_code']) ?></span>
    </div>
<?php endif; ?>

<!-- CONTAINER KARTU BOLAK BALIK -->
<div class="kartu-container">
    
    <!-- KARTU DIGITAL VIP (BAGIAN DEPAN) -->
    <div class="kartu-member tier-<?= esc($member['nama_tier']) ?>">
        <div class="kartu-header">
            <div class="kartu-nama-sistem">
                <i class="fa-solid fa-pepper-hot icon-cabai" style="color: #ffeb3b;"></i> SEBLAK NENI VIP
            </div>
            <div class="kartu-tier">
                <?php 
                    if($member['nama_tier'] == 'Gold') echo '<i class="fa-solid fa-crown" style="color:#000;"></i> ';
                    if($member['nama_tier'] == 'Platinum') echo '<i class="fa-solid fa-gem" style="color:#000;"></i> ';
                ?>
                <?= esc($member['nama_tier']) ?>
            </div>
        </div>
        
        <div class="kartu-body">
            <?php if ($member['foto']): ?>
                <img class="kartu-foto" src="<?= BASE_URL ?>uploads/foto/<?= esc($member['foto']) ?>" alt="Foto">
            <?php else: ?>
                <div class="kartu-foto" style="display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:700; color:white;">
                    <?= esc($inisial) ?>
                </div>
            <?php endif; ?>
            
            <div class="kartu-info">
                <p class="nama"><?= esc($member['nama_lengkap']) ?></p>
                <p class="kode"><?= esc($member['member_code']) ?></p>
            </div>
        </div>
        
        <div class="kartu-footer">
            <div class="berlaku">
                Valid Thru
                <strong><?= formatTanggal($member['berlaku_sampai']) ?></strong>
            </div>
            <div class="kartu-qr">
                <img src="<?= BASE_URL . esc($member['qr_code']) ?>" alt="QR Code">
            </div>
        </div>
    </div>

    <!-- KARTU DIGITAL VIP (BAGIAN BELAKANG - SYARAT KETENTUAN) -->
    <div class="kartu-belakang tier-<?= esc($member['nama_tier']) ?>">
        <div class="kartu-belakang-konten">
            <div class="kartu-belakang-title"><i class="fa-solid fa-scroll"></i> Syarat & Ketentuan VIP</div>
            <div class="kartu-belakang-text">
                <ul>
                    <li>Kartu ini adalah milik eksklusif <strong>Seblak Prasmanan Neni</strong> dan tidak dipindahtangankan.</li>
                    <li>Tunjukkan QR Code ini kepada kasir saat transaksi untuk mendapatkan <strong>Poin & Diskon Tier</strong>.</li>
                    <li>Sistem Poin: Setiap pembelanjaan <strong>Rp 10.000</strong> mendapat <strong>1 Poin</strong> (Berlaku kelipatan).</li>
                    <li>Keuntungan Tier: Silver (0%), Gold (Diskon 5%), Platinum (Diskon 10%). Tier naik otomatis jika poin cukup.</li>
                    <li>Poin yang terkumpul dapat ditukarkan dengan berbagai promo menarik di kasir.</li>
                </ul>
                <div style="text-align: center; margin-top: 8px; font-weight: bold; font-size: 10px; opacity: 0.8;">
                    Terima kasih telah menjadi pelanggan setia kami! <i class="fa-solid fa-pepper-hot"></i>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- TOMBOL AKSI CETAK & CEK POIN -->
<div class="aksi-cetak no-print" style="display: flex; justify-content: center; gap: 15px; margin-top: 20px; width: 100%;">
    <button class="btn" onclick="window.print()" style="padding: 12px 25px; border-radius: 30px; font-size: 15px;">
        <i class="fa-solid fa-print"></i> Cetak Kartu (Depan Belakang)
    </button>
    <a class="btn btn-outline" href="portal.php?kode=<?= urlencode($member['member_code']) ?>" style="padding: 12px 25px; border-radius: 30px; font-size: 15px; display: inline-flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-coins"></i> Cek Poin & Diskon
    </a>
</div>

<!-- KOTAK DETAIL KEANGGOTAAN -->
<div class="card-box no-print" style="max-width: 550px; margin: 35px auto 20px; padding: 0; overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.08);">
    
    <div style="background: #fafafa; padding: 18px 25px; border-bottom: 1px solid #eee;">
        <h3 style="margin: 0; font-size: 16px; color: #333;">
            <i class="fa-solid fa-id-card-clip" style="color: var(--primary-light);"></i> Detail Informasi Keanggotaan
        </h3>
    </div>
    
    <div style="padding: 10px 20px;">
        <table style="margin-top: 0; width: 100%;">
            <tr>
                <th style="background: transparent; padding: 12px 0; color: #777; width: 35%; border-bottom: 1px solid #f5f5f5;"><i class="fa-solid fa-envelope"></i> Email</th>
                <td style="padding: 12px 0; border-bottom: 1px solid #f5f5f5; font-weight: 600; color: #444;"><?= esc($member['email']) ?></td>
            </tr>
            <tr>
                <th style="background: transparent; padding: 12px 0; color: #777; border-bottom: 1px solid #f5f5f5;"><i class="fa-brands fa-whatsapp"></i> No. HP</th>
                <td style="padding: 12px 0; border-bottom: 1px solid #f5f5f5; font-weight: 600; color: #444;"><?= esc($member['no_hp']) ?></td>
            </tr>
            <tr>
                <th style="background: transparent; padding: 12px 0; color: #777; border-bottom: 1px solid #f5f5f5;"><i class="fa-solid fa-location-dot"></i> Alamat</th>
                <td style="padding: 12px 0; border-bottom: 1px solid #f5f5f5; color: #555;"><?= esc($member['alamat'] ?: '-') ?></td>
            </tr>
            <tr>
                <th style="background: transparent; padding: 12px 0; color: #777; border-bottom: 1px solid #f5f5f5;"><i class="fa-solid fa-cake-candles"></i> Tgl Lahir</th>
                <td style="padding: 12px 0; border-bottom: 1px solid #f5f5f5; color: #555;"><?= formatTanggal($member['tanggal_lahir']) ?></td>
            </tr>
            <tr>
                <th style="background: transparent; padding: 12px 0; color: #777; border-bottom: 1px solid #f5f5f5;"><i class="fa-solid fa-venus-mars"></i> Kelamin</th>
                <td style="padding: 12px 0; border-bottom: 1px solid #f5f5f5; color: #555;"><?= $member['jenis_kelamin'] === 'L' ? 'Laki-laki' : ($member['jenis_kelamin'] === 'P' ? 'Perempuan' : '-') ?></td>
            </tr>
            <tr>
                <th style="background: transparent; padding: 12px 0; color: #777; border-bottom: 1px solid #f5f5f5;"><i class="fa-solid fa-calendar-days"></i> Tgl Daftar</th>
                <td style="padding: 12px 0; border-bottom: 1px solid #f5f5f5; color: #555;"><?= formatTanggal($member['tanggal_daftar']) ?></td>
            </tr>
            <tr>
                <th style="background: transparent; padding: 12px 0; color: #777; border-bottom: 1px solid #f5f5f5;"><i class="fa-solid fa-medal"></i> Status Tier</th>
                <td style="padding: 12px 0; border-bottom: 1px solid #f5f5f5;"><span class="badge badge-<?= esc($member['nama_tier']) ?>"><?= esc($member['nama_tier']) ?></span></td>
            </tr>
            <tr>
                <th style="background: transparent; padding: 12px 0; color: #777; border-bottom: 1px solid #f5f5f5;"><i class="fa-solid fa-percent"></i> Diskon Tier</th>
                <td style="padding: 12px 0; border-bottom: 1px solid #f5f5f5; font-weight: bold; color: var(--primary);"><?= $member['diskon_persen'] ?>%</td>
            </tr>
            <tr>
                <th style="background: transparent; padding: 12px 0; color: #777; border: none;"><i class="fa-solid fa-coins"></i> Total Poin</th>
                <td style="padding: 12px 0; border: none; font-weight: 800; font-size: 16px; color: #2e7d32;"><?= (int)$member['total_poin'] ?> Poin</td>
            </tr>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>