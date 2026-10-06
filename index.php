<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

$judulHalaman = 'Beranda Seblak Neni';
require_once __DIR__ . '/includes/header.php';

// Statistik ringan untuk beranda (hanya menghitung member yang aktif)
$totalMember = (int) $pdo->query("SELECT COUNT(*) FROM members WHERE status='aktif'")->fetchColumn();
$totalTier   = $pdo->query("SELECT * FROM membership_tiers ORDER BY urutan")->fetchAll();
?>

<!-- KOTAK SELAMAT DATANG (HERO SECTION) -->
<div class="card-box" style="text-align: center; padding: 40px 20px; border-top: 5px solid var(--primary);">
    <h1 style="font-size: 28px; margin-bottom: 15px; color: var(--primary);">
        <i class="fa-solid fa-fire icon-api" style="color: #ff9800;"></i>
        VIP SEBLAK PRASMANAN NENI 
        <span style="display:inline-block; animation: shake-chili 1.5s infinite;">🌶️</span>
    </h1>
    <p style="font-size: 16px; color: #555; max-width: 800px; margin: 0 auto 25px; line-height: 1.6;">
        Bergabunglah menjadi Member VIP <strong>Seblak Prasmanan Neni</strong>! 
        Kumpulkan poin dari setiap porsi seblak pedas favoritmu, naikkan level tier-mu, dan nikmati <strong>diskon eksklusif</strong> yang otomatis terpotong saat kamu bayar di kasir.
    </p>
    <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
        <a class="btn" href="daftar.php" style="font-size:16px; padding:12px 25px;">
            <i class="fa-solid fa-id-card"></i> Daftar Member Baru
        </a>
        <a class="btn btn-outline" href="portal.php" style="font-size:16px; padding:12px 25px;">
            <i class="fa-solid fa-magnifying-glass"></i> Cek Poin & Status
        </a>
    </div>
</div>

<!-- KOTAK STATISTIK TIER (DIBUAT CENTER & SEJAJAR) -->
<div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 15px; margin-top: 20px;">
    
    <!-- Box Total Member -->
    <div class="stat-box" style="flex: 1; min-width: 200px; max-width: 250px; background: linear-gradient(135deg, #fff, #ffebee);">
        <div class="angka" style="font-size: 36px;"><i class="fa-solid fa-users" style="font-size:24px; color:#ff8a80;"></i> <?= $totalMember ?></div>
        <div class="label" style="font-size: 14px; text-transform:uppercase;">Total Member VIP</div>
    </div>

    <!-- Box Loop Tier -->
    <?php foreach ($totalTier as $t): ?>
    <div class="stat-box" style="flex: 1; min-width: 200px; max-width: 250px;">
        <div class="angka" style="font-size: 26px;">
            <?php 
                // Tambahkan icon mahkota/medali khusus untuk tier
                if($t['nama_tier'] == 'Gold') echo '<i class="fa-solid fa-crown" style="color:#fbc02d; font-size:20px;"></i> ';
                if($t['nama_tier'] == 'Platinum') echo '<i class="fa-solid fa-gem" style="color:#607d8b; font-size:20px;"></i> ';
                if($t['nama_tier'] == 'Silver') echo '<i class="fa-solid fa-medal" style="color:#9e9e9e; font-size:20px;"></i> ';
            ?>
            <?= esc($t['nama_tier']) ?>
        </div>
        <div class="label" style="font-size: 14px;">
            Min. <strong><?= (int)$t['min_poin'] ?> Poin</strong> <br>
            <span style="color:var(--primary); font-weight:800; font-size:16px;">Diskon <?= $t['diskon_persen'] ?>%</span>
        </div>
    </div>
    <?php endforeach; ?>

</div>

<!-- ================= PERBANDINGAN MEMBER VS NON-MEMBER ================= -->
<div style="margin-top: 35px; text-align: center;">
    <h2 style="color: var(--primary); margin-bottom: 5px;"><i class="fa-solid fa-scale-balanced"></i> Kenapa Harus Jadi Member VIP?</h2>
    <p style="color: #666; font-size: 14px; margin-bottom: 25px;">Lihat perbandingan keuntungan antara pelanggan biasa dengan pelanggan VIP kami.</p>
    
    <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; text-align: left;">
        
        <!-- Card Non-Member -->
        <div class="card-box" style="flex: 1; min-width: 280px; max-width: 400px; border-top: 5px solid #9e9e9e; background: #fafafa; margin-bottom: 0;">
            <h3 style="color: #555; text-align: center; margin-top:0;"><i class="fa-regular fa-user"></i> Pelanggan Biasa <br><small style="font-size:12px; font-weight:normal;">(Non-Member)</small></h3>
            <ul style="list-style: none; padding: 0; margin: 0; line-height: 2.2;">
                <li><i class="fa-solid fa-xmark" style="color:#c62828; width:20px;"></i> Membayar harga normal</li>
                <li><i class="fa-solid fa-xmark" style="color:#c62828; width:20px;"></i> Tidak mendapat Poin Belanja</li>
                <li><i class="fa-solid fa-xmark" style="color:#c62828; width:20px;"></i> Tidak ada Diskon Tier</li>
                <li><i class="fa-solid fa-xmark" style="color:#c62828; width:20px;"></i> Tidak berhak penukaran hadiah</li>
                <li><i class="fa-solid fa-check" style="color:#2e7d32; width:20px;"></i> Bebas tanpa biaya pendaftaran</li>
            </ul>
        </div>
        
        <!-- Card Member VIP -->
        <div class="card-box" style="flex: 1; min-width: 280px; max-width: 400px; border-top: 5px solid var(--gold); background: #fffde7; transform: scale(1.03); box-shadow: 0 15px 30px rgba(251, 192, 45, 0.3); z-index: 2; margin-bottom: 0;">
            <h3 style="color: #f57f17; text-align: center; margin-top:0;"><i class="fa-solid fa-crown"></i> Member VIP Neni <br><small style="font-size:12px; font-weight:normal; color:#555;">(Investasi awal terjangkau)</small></h3>
            <ul style="list-style: none; padding: 0; margin: 0; line-height: 2.2; font-weight: bold; color: #3e2723;">
                <li><i class="fa-solid fa-check" style="color:#2e7d32; width:20px;"></i> Mendapat Kartu Digital VIP Eksklusif</li>
                <li><i class="fa-solid fa-check" style="color:#2e7d32; width:20px;"></i> 1 Poin setiap kelipatan Rp 10.000</li>
                <li><i class="fa-solid fa-check" style="color:#2e7d32; width:20px;"></i> Otomatis naik Level (Silver, Gold, Platinum)</li>
                <li><i class="fa-solid fa-check" style="color:#2e7d32; width:20px;"></i> <strong style="color:var(--primary);">Diskon Langsung hingga 10%</strong> tiap beli</li>
                <li><i class="fa-solid fa-check" style="color:#2e7d32; width:20px;"></i> Poin bisa ditukar (Redeem) Seblak Gratis!</li>
            </ul>
        </div>

    </div>
</div>
<!-- ================= END PERBANDINGAN ================= -->

<!-- CARA KERJA SISTEM -->
<div class="card-box" style="margin-top:35px; padding: 30px;">
    <h3 style="border-bottom: 2px solid #ffcdd2; padding-bottom:10px; margin-top:0;">
        <i class="fa-solid fa-fire icon-api"></i> Bagaimana Cara Kerjanya?
    </h3>
    <ol style="line-height: 1.8; font-size: 15px; color: #444; margin-top: 15px; padding-left:20px;">
        <li>Pelanggan mengisi formulir <strong>pendaftaran member</strong> dan mengupload bukti transfer pembayaran.</li>
        <li>Sistem akan menahan status pendaftaran menjadi <strong>PENDING</strong> untuk diverifikasi admin.</li>
        <li>Admin/Kasir memverifikasi pembayaran. Jika sesuai, status di-<strong>ACC (Aktif)</strong> dan sistem mengaktifkan kartu.</li>
        <li>Tunjukkan kartu member (dari layar HP atau dicetak) saat akan membayar di kasir <strong>Seblak Neni</strong>.</li>
        <li>Kasir akan men-scan QR Code kartu untuk otomatis mencatat <strong>Poin</strong> belanja (Kelipatan Rp 10.000 = 1 Poin).</li>
        <li>Semakin banyak poin, <strong>Level Tier</strong> Anda akan naik (Silver &rarr; Gold &rarr; Platinum) dan nikmati diskon potong langsung di kasir!</li>
    </ol>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>