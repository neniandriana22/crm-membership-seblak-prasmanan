<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
require_once __DIR__ . '/../includes/auth.php';
wajibLoginAdmin();

$id = (int) ($_GET['id'] ?? 0);
$pesan = '';$errorPesan = '';

$stmt =$pdo->prepare(
    "SELECT m.*, t.nama_tier, t.diskon_persen FROM members m
     JOIN membership_tiers t ON m.tier_id = t.id WHERE m.id = ?"
);
$stmt->execute([$id]);
$member =$stmt->fetch();

if (!$member) { die('Member tidak ditemukan.'); }

// --- LOGIKA HITUNG POIN & DISKON (DENGAN PENGAMAN STATUS) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_poin'])) {
    
    // BLOKIR JIKA STATUS BUKAN AKTIF
    if ($member['status'] !== 'aktif') {$errorPesan = 'Gagal: Transaksi DITOLAK karena status member ini ' . strtoupper($member['status']) . '.';
    } else {
        $jenis =$_POST['jenis'] === 'redeem' ? 'redeem' : 'earn';
        
        if ($jenis === 'earn') {
            $totalBelanja = max(0, (int) ($_POST['total_belanja'] ?? 0));
            $diskonPersen =$member['diskon_persen'];
            
            $potonganDiskon =$totalBelanja * ($diskonPersen / 100);$totalBayar = $totalBelanja -$potonganDiskon;
            
            $jumlah = floor($totalBayar / 10000);$ket = trim($_POST['keterangan'] ?? '') ?: 'Belanja Rp'.number_format($totalBelanja,0,',','.'). ' (Diskon '.$diskonPersen.'% = Bayar Rp'.number_format($totalBayar,0,',','.').')';
        } else {
            $jumlah = max(0, (int) ($_POST['jumlah_poin'] ?? 0));
            $ket = trim($_POST['keterangan'] ?? '') ?: 'Penukaran poin (Redeem)';
        }

        if ($jumlah > 0) {
            catatTransaksiPoin($pdo,$id, $jenis,$jumlah, $ket,$_SESSION['admin_id']);
            $pesan = 'Transaksi berhasil dicatat (' .$jumlah . ' poin).';
            // Refresh data member
            $stmt->execute([$id]);
            $member =$stmt->fetch();
        } else {
            $errorPesan = 'Gagal: Total bayar belum memenuhi minimal Rp 10.000 untuk dapat poin, atau poin tukar kosong.';
        }
    }
}

$riwayatPoin =$pdo->prepare("SELECT pt.*, a.nama_lengkap AS nama_admin FROM points_transactions pt LEFT JOIN admins a ON pt.admin_id = a.id WHERE pt.member_id = ? ORDER BY pt.created_at DESC");
$riwayatPoin->execute([$id]);
$riwayatPoin =$riwayatPoin->fetchAll();

$judulHalaman = 'Detail Member VIP';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div style="margin-bottom: 20px;">
    <a href="anggota.php" class="btn btn-outline" style="border-radius: 20px; padding: 8px 18px;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Data Member</a>
</div>

<!-- BANNER PROFIL MEMBER -->
<div style="background: linear-gradient(135deg, var(--primary) 0%, #b71c1c 100%); border-radius: 12px; padding: 25px 30px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 20px rgba(198, 40, 40, 0.3); margin-bottom: 25px; position: relative; overflow: hidden;">
    <div style="position: relative; z-index: 2; display: flex; align-items: center; gap: 20px;">
        <?php if ($member['foto']): ?>
            <img src="<?= BASE_URL ?>uploads/foto/<?= esc($member['foto']) ?>" alt="Foto" style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(255,255,255,0.7); box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
        <?php else: ?>
            <div style="width: 70px; height: 70px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; border: 3px solid rgba(255,255,255,0.7);">
                <?= strtoupper(substr($member['nama_lengkap'], 0, 1)) ?>
            </div>
        <?php endif; ?>
        <div>
            <h1 style="margin: 0; color: white; font-size: 26px; letter-spacing: 0.5px;">
                <?= esc($member['nama_lengkap']) ?>
            </h1>
            <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9; font-family: monospace;">
                <i class="fa-solid fa-barcode"></i> <?= esc($member['member_code']) ?> &bull; 
                <span class="badge badge-<?= esc($member['nama_tier']) ?>" style="font-size: 12px; vertical-align: middle;"><?= esc($member['nama_tier']) ?></span>
                &bull; 
                <?php if($member['status'] == 'pending'): ?>
                    <span class="badge" style="background:#f57f17; font-size: 12px; vertical-align: middle;">PENDING</span>
                <?php elseif($member['status'] == 'nonaktif'): ?>
                    <span class="badge" style="background:#c62828; font-size: 12px; vertical-align: middle;">DITOLAK</span>
                <?php else: ?>
                    <span class="badge badge-<?= esc($member['status']) ?>" style="font-size: 12px; vertical-align: middle;"><?= esc($member['status']) ?></span>
                <?php endif; ?>
            </p>
        </div>
    </div>
    <div style="position: absolute; right: -20px; top: -30px; opacity: 0.1; transform: rotate(15deg); z-index: 1;">
        <i class="fa-solid fa-pepper-hot" style="font-size: 130px;"></i>
    </div>
</div>

<?php if ($pesan): ?><div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= esc($pesan) ?></div><?php endif; ?>
<?php if ($errorPesan): ?><div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= esc($errorPesan) ?></div><?php endif; ?>

<div class="stat-grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 25px;">
    <div class="stat-box" style="background: linear-gradient(135deg, #fff 0%, #ffebee 100%); border-top: 4px solid var(--primary-light);">
        <div class="angka" style="color: var(--primary);"><i class="fa-solid fa-coins" style="color: #ffb300; font-size: 24px;"></i> <?= (int)$member['total_poin'] ?></div>
        <div class="label">Total Poin Saat Ini</div>
    </div>
    <div class="stat-box" style="background: linear-gradient(135deg, #fff 0%, #e8f5e9 100%); border-top: 4px solid #2e7d32;">
        <div class="angka" style="color: #2e7d32;"><i class="fa-solid fa-tags" style="font-size: 24px;"></i> <?= $member['diskon_persen'] ?>%</div>
        <div class="label">Diskon Spesial Tier</div>
    </div>
</div>

<div class="card-box" style="border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.08);">
    <h3 style="margin-top: 0; margin-bottom: 20px; border-bottom: 2px solid #ffcdd2; padding-bottom: 10px;">
        <i class="fa-solid fa-pepper-hot icon-cabai"></i> Catat Transaksi & Diskon Otomatis 
        <span style="display:inline-block; animation: shake-chili 1.5s infinite;">🌶️</span>
    </h3>
    
    <!-- HANYA TAMPILKAN FORM JIKA STATUS AKTIF -->
    <?php if ($member['status'] === 'aktif'): ?>
        <form method="POST" style="display:flex; gap:20px; align-items:flex-start; flex-wrap:wrap; background:#ffebee; padding:20px; border-radius:10px; border:1px dashed #ef9a9a;">
            
            <input type="hidden" id="diskon_tier" value="<?= $member['diskon_persen'] ?>">

            <div style="flex: 1; min-width: 200px;">
                <label><i class="fa-solid fa-exchange-alt"></i> Jenis Transaksi</label>
                <select name="jenis" id="jenis_transaksi" onchange="toggleInput()">
                    <option value="earn">➕ Tambah Poin (Belanja)</option>
                    <option value="redeem">➖ Tukar Poin (Redeem Hadiah)</option>
                </select>
            </div>
            
            <div id="div_belanja" style="flex: 1; min-width: 220px;">
                <label><i class="fa-solid fa-money-bill-wave"></i> Total Belanja Awal (Rp)</label>
                <input type="number" name="total_belanja" id="input_belanja" min="1" placeholder="Misal: 45000" onkeyup="hitungDiskon()" style="font-weight:bold; color:var(--primary); font-size: 16px;">
                <div id="info_diskon" style="color: #b71c1c; font-size: 13px; font-weight: bold; margin-top: 10px; background: white; padding: 10px; border-radius: 6px; border: 1px solid #ffcdd2; line-height: 1.5;"></div>
            </div>
            
            <div id="div_poin" style="flex: 1; min-width: 220px; display:none;">
                <label><i class="fa-solid fa-gift"></i> Jumlah Poin Ditukar</label>
                <input type="number" name="jumlah_poin" id="input_poin" min="1" placeholder="Misal: 50" style="font-size: 16px;">
            </div>

            <div style="flex: 2; min-width: 250px;">
                <label><i class="fa-solid fa-comment-dots"></i> Keterangan Tambahan</label>
                <input type="text" name="keterangan" placeholder="Contoh: Seblak Komplit + Extra Ceker...">
            </div>
            
            <div style="width: 100%; text-align: right; margin-top: 5px;">
                <button type="submit" name="tambah_poin" value="1" class="btn" style="padding: 12px 30px; font-size: 16px; border-radius: 30px;">
                    <i class="fa-solid fa-fire icon-api"></i> Simpan Transaksi VIP
                </button>
            </div>
        </form>
    <?php else: ?>
        <div style="background: #ffebee; border: 1px solid #ef9a9a; border-radius: 8px; padding: 25px; text-align: center;">
            <i class="fa-solid fa-ban" style="font-size: 40px; color: #c62828; margin-bottom: 15px;"></i>
            <h3 style="color: #c62828; margin-top: 0;">TRANSAKSI DIBLOKIR</h3>
            <p style="color: #555; margin-bottom: 0;">Member ini tidak dapat melakukan transaksi belanja maupun tukar poin karena status keanggotaannya saat ini adalah <strong><?= strtoupper($member['status']) ?></strong>.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function hitungDiskon() {
    let belanja = parseFloat(document.getElementById('input_belanja').value) || 0;
    let diskon = parseFloat(document.getElementById('diskon_tier').value) || 0;
    let potongan = belanja * (diskon / 100);
    let bayar = belanja - potongan;
    let poin = Math.floor(bayar / 10000);
    
    if(belanja > 0) {
        document.getElementById('info_diskon').innerHTML = 
            `<i class="fa-solid fa-tag"></i> Diskon Tier (${diskon}%): -Rp ${potongan.toLocaleString('id-ID')} <br>
             <i class="fa-solid fa-wallet"></i> Total Bayar: <strong>Rp ${bayar.toLocaleString('id-ID')}</strong> <br>
             <i class="fa-solid fa-coins"></i> Poin Didapat: <strong style="color: #2e7d32;">${poin} Poin</strong>`;
    } else {
        document.getElementById('info_diskon').innerHTML = '';
    }
}

function toggleInput() {
    let jenis = document.getElementById('jenis_transaksi');
    if(!jenis) return; // Mencegah error jika form tidak tampil (diblokir)
    
    if (jenis.value === 'earn') {
        document.getElementById('div_belanja').style.display = 'block';
        document.getElementById('input_belanja').required = true;
        document.getElementById('div_poin').style.display = 'none';
        document.getElementById('input_poin').required = false;
        hitungDiskon();
    } else {
        document.getElementById('div_belanja').style.display = 'none';
        document.getElementById('input_belanja').required = false;
        document.getElementById('div_poin').style.display = 'block';
        document.getElementById('input_poin').required = true;
    }
}
toggleInput();
</script>

<div class="card-box" style="margin-top: 30px; padding: 0; overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.08);">
    <div style="background: #fafafa; padding: 18px 25px; border-bottom: 1px solid #eee;">
        <h3 style="margin: 0; font-size: 16px; color: #333;"><i class="fa-solid fa-clock-rotate-left" style="color: var(--primary-light);"></i> Riwayat Transaksi & Penukaran Poin</h3>
    </div>
    
    <div style="padding: 0 10px 10px 10px;">
        <table style="margin-top: 0; width: 100%;">
            <thead>
                <tr>
                    <th style="background: transparent; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 13px; text-transform: uppercase;">Tanggal</th>
                    <th style="background: transparent; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 13px; text-transform: uppercase;">Jenis</th>
                    <th style="background: transparent; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 13px; text-transform: uppercase;">Poin</th>
                    <th style="background: transparent; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 13px; text-transform: uppercase;">Keterangan / Detail Belanja</th>
                    <th style="background: transparent; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 13px; text-transform: uppercase;">Kasir / Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($riwayatPoin as$r): ?>
                <tr style="transition: background 0.3s;" onmouseover="this.style.background='#fff9f9'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 15px; border-bottom: 1px solid #f5f5f5; color: #666; font-size: 13px;">
                        <?= formatTanggal($r['created_at']) ?>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #f5f5f5;">
                        <?php if($r['jenis'] === 'earn'): ?>
                            <span style="color: #2e7d32; font-weight: bold; background: #e8f5e9; padding: 4px 10px; border-radius: 12px; font-size: 12px;"><i class="fa-solid fa-plus"></i> Tambah</span>
                        <?php else: ?>
                            <span style="color: #c62828; font-weight: bold; background: #ffebee; padding: 4px 10px; border-radius: 12px; font-size: 12px;"><i class="fa-solid fa-minus"></i> Redeem</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #f5f5f5; font-weight: 800; font-size: 15px; color: <?= $r['jenis'] === 'earn' ? '#2e7d32' : '#c62828' ?>;">
                        <?= $r['jenis'] === 'earn' ? '+' : '-' ?><?= (int)$r['jumlah_poin'] ?>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #f5f5f5; color: #444;">
                        <?= esc($r['keterangan']) ?>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #f5f5f5; color: #666; font-size: 13px;">
                        <i class="fa-solid fa-user-shield"></i> <?= esc($r['nama_admin'] ?? '-') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$riwayatPoin): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #999;">
                        <i class="fa-regular fa-folder-open" style="font-size: 30px; margin-bottom: 10px; display: block;"></i>
                        Belum ada riwayat transaksi poin untuk member ini.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>