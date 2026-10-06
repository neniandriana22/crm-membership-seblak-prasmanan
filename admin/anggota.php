<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
require_once __DIR__ . '/../includes/auth.php';
wajibLoginAdmin();

// --- LOGIKA KONFIRMASI STATUS MEMBER ---
if (isset($_GET['acc'])) {
    $idAcc = (int)$_GET['acc'];
    $pdo->prepare("UPDATE members SET status = 'aktif' WHERE id = ?")->execute([$idAcc]);
    header("Location: anggota.php?pesan=sukses_acc");
    exit;
}
if (isset($_GET['tolak'])) {
    $idTolak = (int)$_GET['tolak'];
    $pdo->prepare("UPDATE members SET status = 'nonaktif' WHERE id = ?")->execute([$idTolak]);
    header("Location: anggota.php?pesan=sukses_tolak");
    exit;
}

$cari = trim($_GET['cari'] ?? '');

$sql = "SELECT m.*, t.nama_tier FROM members m JOIN membership_tiers t ON m.tier_id = t.id";
$params = [];
if ($cari !== '') {
    $sql .= " WHERE m.nama_lengkap LIKE ? OR m.member_code LIKE ? OR m.email LIKE ?";
    $like = "%$cari%";
    $params = [$like, $like, $like];
}
$sql .= " ORDER BY m.created_at ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftarMember = $stmt->fetchAll();

$judulHalaman = 'Data Member VIP';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- BANNER HALAMAN DATA MEMBER -->
<div style="background: linear-gradient(135deg, var(--primary) 0%, #b71c1c 100%); border-radius: 12px; padding: 25px 30px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 20px rgba(198, 40, 40, 0.3); margin-bottom: 25px; position: relative; overflow: hidden;">
    <div style="position: relative; z-index: 2;">
        <h1 style="margin: 0; color: white; font-size: 26px; letter-spacing: 1px;">
            <i class="fa-solid fa-id-card-clip" style="color: #ffeb3b;"></i> Manajemen Data Member VIP
        </h1>
        <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Kelola seluruh data pelanggan dan verifikasi pembayaran pendaftaran.</p>
    </div>
    <div style="position: absolute; right: -15px; top: -30px; opacity: 0.1; transform: rotate(15deg); z-index: 1;">
        <i class="fa-solid fa-users" style="font-size: 130px;"></i>
    </div>
</div>

<?php if(isset($_GET['pesan']) && $_GET['pesan'] == 'sukses_acc'): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check"></i> Pendaftaran Member berhasil di-ACC dan diaktifkan!</div>
<?php endif; ?>
<?php if(isset($_GET['pesan']) && $_GET['pesan'] == 'sukses_tolak'): ?>
    <div class="alert alert-error" style="background:#ffebee; color:#c62828;"><i class="fa-solid fa-circle-xmark"></i> Pendaftaran Member telah DITOLAK.</div>
<?php endif; ?>

<!-- KOTAK PENCARIAN (SEARCH BOX) -->
<div class="card-box" style="padding: 20px 25px; margin-bottom: 20px;">
    <form method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px; position: relative;">
            <input type="text" name="cari" value="<?= esc($cari) ?>" placeholder="Cari berdasarkan Nama, Kode Member, atau Email..." style="padding-left: 38px; border-radius: 20px;">
            <i class="fa-solid fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #999;"></i>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn" style="border-radius: 20px; padding: 10px 20px;">
                <i class="fa-solid fa-magnifying-glass"></i> Cari
            </button>
            <?php if ($cari !== ''): ?>
                <a href="anggota.php" class="btn btn-outline" style="border-radius: 20px; padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-rotate-right"></i> Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card-box" style="padding: 0; overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.08);">
    <div style="background: #fafafa; padding: 18px 25px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 16px; color: #333;"><i class="fa-solid fa-list" style="color: var(--primary-light);"></i> Daftar Seluruh Anggota Terdaftar</h3>
        <span style="font-size: 13px; color: #666; font-weight: 600;">Total: <?= count($daftarMember) ?> Member</span>
    </div>
    
    <div style="padding: 0 10px 10px 10px; overflow-x: auto;">
        <table style="margin-top: 0; width: 100%;">
            <thead>
                <tr>
                    <th style="background: transparent; padding: 15px 10px; border-bottom: 2px solid #eee; color: #888; font-size: 13px; text-align: center; width: 60px;">NO.</th>
                    <th style="background: transparent; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 13px;">KODE / NAMA</th>
                    <th style="background: transparent; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 13px; text-align:center;">BUKTI BAYAR</th>
                    <th style="background: transparent; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 13px;">TIER & POIN</th>
                    <th style="background: transparent; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 13px;">STATUS</th>
                    <th style="background: transparent; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 13px; text-align: center;">AKSI ADMIN</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($daftarMember as $m): ?>
                <tr style="background: <?= $m['status'] == 'pending' ? '#fffde7' : 'transparent' ?>;">
                    <td style="padding: 15px 10px; border-bottom: 1px solid #f5f5f5; text-align: center; font-weight: bold; color: #666;">
                        <?= $no++ ?>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #f5f5f5;">
                        <div style="font-family: monospace; font-weight: bold; color: #555;"><?= esc($m['member_code']) ?></div>
                        <div style="font-weight: 700; color: var(--primary);"><?= esc($m['nama_lengkap']) ?></div>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #f5f5f5; text-align:center;">
                        <?php if ($m['bukti_bayar']): ?>
                            <a href="<?= BASE_URL ?>uploads/bukti/<?= esc($m['bukti_bayar']) ?>" target="_blank" class="btn btn-sm btn-outline" style="border-radius:20px; font-size:11px; padding: 4px 10px;">
                                <i class="fa-solid fa-file-invoice-dollar"></i> Cek Bukti
                            </a>
                        <?php else: ?>
                            <span style="color:#999; font-size:12px; font-style:italic;">Tanpa Bukti</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #f5f5f5;">
                        <span class="badge badge-<?= esc($m['nama_tier']) ?>"><?= esc($m['nama_tier']) ?></span><br>
                        <strong style="color: #d32f2f; font-size: 13px;"><?= (int)$m['total_poin'] ?> Poin</strong>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #f5f5f5;">
                        <?php if($m['status'] == 'pending'): ?>
                            <span class="badge" style="background:#f57f17;"><i class="fa-solid fa-clock"></i> PENDING</span>
                        <?php elseif($m['status'] == 'nonaktif'): ?>
                            <span class="badge" style="background:#c62828;"><i class="fa-solid fa-xmark"></i> DITOLAK</span>
                        <?php else: ?>
                            <span class="badge badge-<?= esc($m['status']) ?>"><?= esc($m['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #f5f5f5; text-align: center; white-space: nowrap;">
                        
                        <?php if($m['status'] == 'pending'): ?>
                            <a class="btn btn-sm" href="?acc=<?= $m['id'] ?>" style="background: #2e7d32; border-radius: 15px; padding: 5px 12px;" onclick="return confirm('ACC pembayaran ini dan aktifkan kartu?')">
                                <i class="fa-solid fa-check"></i> ACC
                            </a>
                            <a class="btn btn-sm btn-outline" href="?tolak=<?= $m['id'] ?>" style="border-radius: 15px; padding: 5px 12px; color: #c62828; border-color: #c62828;" onclick="return confirm('Tolak pendaftaran ini?')">
                                <i class="fa-solid fa-xmark"></i> Tolak
                            </a>
                        <?php else: ?>
                            <a class="btn btn-sm" href="detail.php?id=<?= $m['id'] ?>" style="border-radius: 15px; padding: 5px 12px;">
                                <i class="fa-solid fa-pen-to-square"></i> Detail
                            </a>
                            <?php if($m['status'] !== 'nonaktif'): ?>
                                <a class="btn btn-sm btn-outline" href="../kartu.php?kode=<?= urlencode($m['member_code']) ?>" target="_blank" style="border-radius: 15px; padding: 5px 12px;">
                                    <i class="fa-solid fa-print"></i> Cetak
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($daftarMember)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: #888; font-style: italic;">
                        Tidak ada data member yang ditemukan.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>