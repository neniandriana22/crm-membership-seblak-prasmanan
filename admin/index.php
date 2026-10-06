<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
require_once __DIR__ . '/../includes/auth.php';
wajibLoginAdmin();

$totalMember = (int) $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$totalPoinBeredar = (int) $pdo->query("SELECT COALESCE(SUM(total_poin),0) FROM members")->fetchColumn();

$distribusiTier = $pdo->query(
    "SELECT t.nama_tier, COUNT(m.id) AS jumlah
     FROM membership_tiers t LEFT JOIN members m ON m.tier_id = t.id
     GROUP BY t.id ORDER BY t.urutan"
)->fetchAll();

$memberTerbaru = $pdo->query(
    "SELECT m.*, t.nama_tier FROM members m JOIN membership_tiers t ON m.tier_id = t.id ORDER BY m.created_at DESC LIMIT 5"
)->fetchAll();

$judulHalaman = 'Dashboard Executive';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- BANNER SELAMAT DATANG -->
<div style="background: linear-gradient(135deg, var(--primary) 0%, #b71c1c 100%); border-radius: 12px; padding: 25px 30px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 20px rgba(198, 40, 40, 0.3); margin-bottom: 25px; position: relative; overflow: hidden;">
    <div style="position: relative; z-index: 2;">
        <h1 style="margin: 0; color: white; font-size: 28px; letter-spacing: 1px;">
            <i class="fa-solid fa-fire icon-api" style="color: #ffeb3b;"></i> Executive Dashboard
        </h1>
        <p style="margin: 5px 0 0 0; font-size: 15px; opacity: 0.9;">Ringkasan Data VIP Member <strong>Seblak Prasmanan Neni</strong></p>
    </div>
    <div style="position: absolute; right: -20px; top: -40px; opacity: 0.1; transform: rotate(15deg); z-index: 1;">
        <i class="fa-solid fa-pepper-hot" style="font-size: 150px;"></i>
    </div>
</div>

<!-- STATISTIK UTAMA (GRID 5 KOLOM) -->
<div class="stat-grid">
    
    <!-- Box Total Member -->
    <div class="stat-box" style="position: relative; overflow: hidden; background: linear-gradient(135deg, #ffffff 0%, #ffebee 100%); border-top: 4px solid #f44336;">
        <div style="position: relative; z-index: 2;">
            <div class="angka" style="font-size: 34px; color: #d32f2f;"><?= number_format($totalMember, 0, ',', '.') ?></div>
            <div class="label" style="text-transform: uppercase; font-size: 12px; letter-spacing: 1px; color: #7f0000; margin-top: 5px;">Total Member</div>
        </div>
        <i class="fa-solid fa-users" style="position: absolute; right: -10px; bottom: -10px; font-size: 70px; color: #ffcdd2; z-index: 1; opacity: 0.5;"></i>
    </div>
    
    <!-- Box Poin Beredar -->
    <div class="stat-box" style="position: relative; overflow: hidden; background: linear-gradient(135deg, #ffffff 0%, #fff8e1 100%); border-top: 4px solid #ffb300;">
        <div style="position: relative; z-index: 2;">
            <div class="angka" style="font-size: 34px; color: #f57f17;"><?= number_format($totalPoinBeredar, 0, ',', '.') ?></div>
            <div class="label" style="text-transform: uppercase; font-size: 12px; letter-spacing: 1px; color: #e65100; margin-top: 5px;">Poin Beredar</div>
        </div>
        <i class="fa-solid fa-coins" style="position: absolute; right: -10px; bottom: -10px; font-size: 70px; color: #ffecb3; z-index: 1; opacity: 0.7;"></i>
    </div>

    <!-- Box Loop Distribusi Tier -->
    <?php foreach ($distribusiTier as $d): 
        // Penyesuaian warna dan ikon berdasarkan Tier
        $tierColor = '#9e9e9e'; $bgColor = '#f5f5f5'; $icon = 'fa-medal'; $shadowColor = '#eeeeee';
        if ($d['nama_tier'] == 'Gold') { $tierColor = '#fbc02d'; $bgColor = '#fffde7'; $icon = 'fa-crown'; $shadowColor = '#fff9c4'; }
        if ($d['nama_tier'] == 'Platinum') { $tierColor = '#455a64'; $bgColor = '#eceff1'; $icon = 'fa-gem'; $shadowColor = '#cfd8dc'; }
    ?>
    <div class="stat-box" style="position: relative; overflow: hidden; background: linear-gradient(135deg, #ffffff 0%, <?= $bgColor ?> 100%); border-top: 4px solid <?= $tierColor ?>;">
        <div style="position: relative; z-index: 2;">
            <div class="angka" style="font-size: 34px; color: <?= $tierColor ?>;"><?= number_format((int)$d['jumlah'], 0, ',', '.') ?></div>
            <div class="label" style="text-transform: uppercase; font-size: 12px; letter-spacing: 1px; color: #555; margin-top: 5px;">
                Member <?= esc($d['nama_tier']) ?>
            </div>
        </div>
        <i class="fa-solid <?= $icon ?>" style="position: absolute; right: -10px; bottom: -15px; font-size: 70px; color: <?= $shadowColor ?>; z-index: 1; opacity: 0.8;"></i>
    </div>
    <?php endforeach; ?>

</div>

<!-- TABEL MEMBER TERBARU -->
<div class="card-box" style="margin-top: 30px; padding: 0; overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.08);">
    <div style="background: #fafafa; padding: 20px 25px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 18px; color: #333;"><i class="fa-solid fa-clock-rotate-left" style="color: var(--primary-light);"></i> Daftar VIP Terbaru</h3>
        <a href="anggota.php" style="font-size: 13px; text-decoration: none; font-weight: bold; color: var(--primary-light);">Lihat Semua &rarr;</a>
    </div>
    
    <div style="padding: 0 10px 10px 10px;">
        <table style="margin-top: 0; width: 100%;">
            <thead>
                <tr>
                    <th style="background: transparent; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 13px; text-transform: uppercase;">Kode Member</th>
                    <th style="background: transparent; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 13px; text-transform: uppercase;">Nama Pelanggan</th>
                    <th style="background: transparent; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 13px; text-transform: uppercase;">Tier</th>
                    <th style="background: transparent; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 13px; text-transform: uppercase;">Tgl Daftar</th>
                    <th style="background: transparent; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 13px; text-transform: uppercase; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($memberTerbaru as $m): ?>
                <tr style="transition: background 0.3s;" onmouseover="this.style.background='#fff9f9'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 15px; border-bottom: 1px solid #f5f5f5; font-family: monospace; font-weight: bold; color: #555;">
                        <?= esc($m['member_code']) ?>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #f5f5f5; font-weight: 600; color: var(--primary);">
                        <?= esc($m['nama_lengkap']) ?>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #f5f5f5;">
                        <span class="badge badge-<?= esc($m['nama_tier']) ?>"><?= esc($m['nama_tier']) ?></span>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #f5f5f5; color: #666; font-size: 13px;">
                        <?= formatTanggal($m['tanggal_daftar']) ?>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #f5f5f5; text-align: center;">
                        <a href="detail.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline" style="border-radius: 20px; padding: 5px 15px;">
                            <i class="fa-solid fa-eye"></i> Detail
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$memberTerbaru): ?>
                <tr><td colspan="5" style="text-align: center; padding: 30px; color: #999;">Belum ada member yang mendaftar.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>