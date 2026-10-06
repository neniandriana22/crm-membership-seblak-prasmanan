<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

$kode = trim($_GET['kode'] ?? '');
$member = null;
$errorPesan = '';

if ($kode !== '') {
    $stmt = $pdo->prepare(
        "SELECT m.*, t.nama_tier, t.diskon_persen
         FROM members m JOIN membership_tiers t ON m.tier_id = t.id
         WHERE m.member_code = ?"
    );
    $stmt->execute([$kode]);
    $member = $stmt->fetch();
    if (!$member) {
        $errorPesan = 'Kode member tidak ditemukan. Periksa kembali kode pada kartu Anda.';
    }
}

$judulHalaman = 'Cek Poin VIP Member';
require_once __DIR__ . '/includes/header.php';
?>

<!-- KOTAK PENCARIAN -->
<div class="card-box" style="max-width: 550px; margin: 40px auto 20px; padding: 35px; text-align: center; border-top: 5px solid var(--primary-light); box-shadow: 0 10px 25px rgba(198, 40, 40, 0.15);">
    <h2 style="margin-top: 0; font-size: 24px; color: var(--primary);">
        <i class="fa-solid fa-magnifying-glass" style="color: #ff9800;"></i> 
        CEK STATUS VIP NENI <span style="display:inline-block; animation: shake-chili 1.5s infinite;">🌶️</span>
    </h2>
    <p style="color: #666; font-size: 14px; margin-bottom: 25px;">Masukkan Kode Member Anda (MBR-...) untuk melihat total poin, level tier, dan diskon yang sedang aktif.</p>

    <form method="GET">
        <div style="text-align: left; margin-bottom: 15px;">
            <label style="font-size: 13px;"><i class="fa-solid fa-id-card" style="color:#ff5252;"></i> Kode Member</label>
            <input type="text" name="kode" required placeholder="Contoh: MBR-20260909-AB12" value="<?= esc($kode) ?>" style="font-size: 16px; text-align: center; letter-spacing: 1px; text-transform: uppercase;">
        </div>
        <button type="submit" class="btn" style="width: 100%; padding: 14px; font-size: 16px; border-radius: 50px;">
            <i class="fa-solid fa-fire icon-api"></i> CEK SEKARANG
        </button>
    </form>
</div>

<!-- PESAN ERROR -->
<?php if ($errorPesan): ?>
    <div class="alert alert-error" style="max-width: 550px; margin: 0 auto; border-left: 5px solid #c62828;">
        <i class="fa-solid fa-circle-xmark"></i> <?= esc($errorPesan) ?>
    </div>
<?php endif; ?>

<!-- HASIL PENCARIAN (MINI DASHBOARD VIP) -->
<?php if ($member): ?>
<div class="card-box" style="max-width: 550px; margin: 20px auto; padding: 0; overflow: hidden; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.15); border: 1px solid rgba(244, 67, 54, 0.2);">
    
    <!-- Header Banner Hasil -->
    <div style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: white; padding: 25px 20px; text-align: center; position: relative;">
        <h3 style="margin: 0; color: white; font-size: 22px; text-transform: uppercase; letter-spacing: 1px;">
            <?= esc($member['nama_lengkap']) ?>
        </h3>
        <div style="margin-top: 10px;">
            <span class="badge badge-<?= esc($member['nama_tier']) ?>" style="font-size: 14px; padding: 6px 16px; box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
                <?php 
                    if($member['nama_tier'] == 'Gold') echo '<i class="fa-solid fa-crown" style="color:#fff;"></i> ';
                    if($member['nama_tier'] == 'Platinum') echo '<i class="fa-solid fa-gem" style="color:#fff;"></i> ';
                ?>
                TIER <?= esc($member['nama_tier']) ?>
            </span>
        </div>
    </div>

    <!-- Konten Detail -->
    <div style="padding: 25px;">
        
        <!-- Grid Poin & Diskon -->
        <div style="display: flex; gap: 15px; margin-bottom: 25px; text-align: center;">
            <div style="flex: 1; background: #ffebee; border: 1px dashed #ef9a9a; border-radius: 12px; padding: 15px;">
                <div style="font-size: 12px; color: #c62828; font-weight: bold; text-transform: uppercase;">Total Poin</div>
                <div style="font-size: 32px; font-weight: 900; color: var(--primary); margin: 5px 0;">
                    <i class="fa-solid fa-coins" style="color:#ffb300; font-size:24px;"></i> <?= (int)$member['total_poin'] ?>
                </div>
            </div>
            
            <div style="flex: 1; background: #e8f5e9; border: 1px dashed #a5d6a7; border-radius: 12px; padding: 15px;">
                <div style="font-size: 12px; color: #2e7d32; font-weight: bold; text-transform: uppercase;">Diskon Aktif</div>
                <div style="font-size: 32px; font-weight: 900; color: #2e7d32; margin: 5px 0;">
                    <i class="fa-solid fa-tags" style="font-size:24px;"></i> <?= $member['diskon_persen'] ?>%
                </div>
            </div>
        </div>

        <!-- Tabel Info Tambahan -->
        <table style="width: 100%; border-collapse: collapse; font-size: 14px; color: #444;">
            <tr>
                <th style="padding: 12px 0; border-bottom: 1px solid #eee; width: 40%; background: transparent; color: #777;"><i class="fa-solid fa-barcode"></i> Kode Member</th>
                <td style="padding: 12px 0; border-bottom: 1px solid #eee; text-align: right; font-weight: 700; font-family: monospace; font-size: 15px;"><?= esc($member['member_code']) ?></td>
            </tr>
            <tr>
                <th style="padding: 12px 0; border-bottom: 1px solid #eee; background: transparent; color: #777;"><i class="fa-solid fa-calendar-check"></i> Berlaku s/d</th>
                <td style="padding: 12px 0; border-bottom: 1px solid #eee; text-align: right; font-weight: 700;"><?= formatTanggal($member['berlaku_sampai']) ?></td>
            </tr>
        </table>

        <!-- Tombol Tampilkan Kartu -->
        <div style="margin-top: 25px; text-align: center;">
            <a href="kartu.php?kode=<?= urlencode($member['member_code']) ?>" class="btn btn-outline" style="width: 100%; padding: 12px; border-radius: 50px;">
                <i class="fa-solid fa-id-badge"></i> BUKA KARTU DIGITAL SAYA
            </a>
            <p style="font-size:12px; color:#888; margin-top:15px; margin-bottom:0;">
                <i class="fa-solid fa-circle-info"></i> Tunjukkan kartu digital Anda ke Kasir untuk menukar Poin atau memakai Diskon.
            </p>
        </div>

    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>