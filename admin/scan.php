<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
require_once __DIR__ . '/../includes/auth.php';
wajibLoginAdmin();

$member = null;
$errorPesan = '';$pesanSukses = '';

// Konfigurasi Poin Seblak Neni
$KELIPATAN_POIN = 10000; 

// --- LOGIKA HITUNG POIN (DENGAN PENGAMAN STATUS) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_poin_cepat'])) {
    $memberId = (int)$_POST['member_id'];
    
    // Cek Status Validasi 
    $cekStatus =$pdo->prepare("SELECT status FROM members WHERE id = ?");
    $cekStatus->execute([$memberId]);
    $statusSekarang =$cekStatus->fetchColumn();

    if ($statusSekarang !== 'aktif') {$errorPesan = "Gagal: Transaksi DITOLAK! Status kartu member ini " . strtoupper($statusSekarang) . ".";
        $kodeUntukTampil =$_POST['member_code'] ?? '';
    } else {
        $diskonPersen = (float)$_POST['diskon_tier'];
        $totalBelanja = max(0, (int)$_POST['total_belanja']);
        
        $potongan =$totalBelanja * ($diskonPersen / 100);$totalBayar = $totalBelanja -$potongan;
        $jumlahPoin = floor($totalBayar / $KELIPATAN_POIN);
        
        if ($jumlahPoin > 0) {$ket = trim($_POST['keterangan'] ?? '') ?: 'Belanja Rp'.number_format($totalBelanja,0,',','.').' (Diskon '.$diskonPersen.'\% = Bayar Rp'.number_format($totalBayar,0,',','.').')';
            catatTransaksiPoin($pdo,$memberId, 'earn', $jumlahPoin,$ket, $_SESSION['admin_id']);$pesanSukses = "Berhasil menambahkan $jumlahPoin poin (Total Bayar: Rp " . number_format($totalBayar, 0, ',', '.') . ").";
        } else {
            $errorPesan = "Total Bayar Rp " . number_format($totalBayar, 0, ',', '.') . " belum mencapai minimal (Rp 10.000) untuk poin.";
        }
        $kodeUntukTampil =$_POST['member_code'] ?? '';
    }
} else {
    $kodeUntukTampil = trim($_POST['hasil_scan'] ?? $_GET['hasil_scan'] ?? '');
}

$kodeMember = '';
if ($kodeUntukTampil !== '') {
    $kodeMember =$kodeUntukTampil;

    $stmt =$pdo->prepare("SELECT m.*, t.nama_tier, t.diskon_persen FROM members m JOIN membership_tiers t ON m.tier_id = t.id WHERE m.member_code = ?");
    $stmt->execute([trim($kodeMember)]);
    $member =$stmt->fetch();
    
    if (!$member) {$errorPesan = 'Kartu tidak dikenali / Member tidak ditemukan: ' . esc($kodeMember); 
    }
}

$judulHalaman = 'Kasir & Scan Member';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- BANNER HALAMAN KASIR -->
<div style="background: linear-gradient(135deg, var(--primary) 0%, #b71c1c 100%); border-radius: 12px; padding: 25px 30px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 20px rgba(198, 40, 40, 0.3); margin-bottom: 25px; position: relative; overflow: hidden;">
    <div style="position: relative; z-index: 2;">
        <h1 style="margin: 0; color: white; font-size: 26px; letter-spacing: 1px;">
            <i class="fa-solid fa-cash-register" style="color: #ffeb3b;"></i> Kasir & Scan Member VIP
        </h1>
        <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Arahkan kartu ke kamera, scan barcode, atau ketik ID Member untuk proses diskon & poin otomatis.</p>
    </div>
    <div style="position: absolute; right: -15px; top: -30px; opacity: 0.1; transform: rotate(15deg); z-index: 1;">
        <i class="fa-solid fa-pepper-hot" style="font-size: 130px;"></i>
    </div>
</div>

<?php if ($errorPesan): ?><div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= $errorPesan ?></div><?php endif; ?>
<?php if ($pesanSukses): ?><div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $pesanSukses ?></div><?php endif; ?>

<div style="display:flex; gap:20px; flex-wrap:wrap;">
    
    <!-- Area Kamera Scanner -->
    <div style="flex:1; min-width:300px;">
        <div class="card-box" style="text-align:center; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.08);">
            <h3 style="margin-top:0; border-bottom: 2px solid #ffcdd2; padding-bottom: 10px;">
                <i class="fa-solid fa-camera" style="color: var(--primary-light);"></i> Kamera Scanner QR
            </h3>
            <p style="font-size: 12px; color: #666; margin-bottom: 15px;">Posisikan QR Code kartu member di depan kamera laptop/webcam Anda.</p>
            <div id="qr-reader"></div>
        </div>
    </div>

    <!-- Area Hasil & Hitung Poin -->
    <div style="flex:1; min-width:300px;">
        
        <!-- Input Manual -->
        <div class="card-box" style="border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.08); margin-bottom: 20px;">
            <form id="form-scan" method="GET">
                <label style="margin-top:0;"><i class="fa-solid fa-keyboard" style="color: var(--primary-light);"></i> Input Manual Kode Member (MBR-...)</label>
                <div style="display:flex; gap:10px; margin-top: 8px;">
                    <input type="text" id="hasil_scan" name="hasil_scan" autofocus autocomplete="off" placeholder="Ketik MBR-..." value="" style="border-radius: 8px;">
                    <button type="submit" class="btn" style="border-radius: 8px;"><i class="fa-solid fa-search"></i> Cari</button>
                </div>
            </form>
        </div>

        <?php if ($member): ?>
        <div class="card-box" style="border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.08);">
            
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ffcdd2; padding-bottom: 12px; margin-bottom: 15px;">
                <h2 style="margin:0; font-size: 20px; color: var(--primary);">
                    <i class="fa-solid fa-user-check"></i> <?= esc($member['nama_lengkap']) ?>
                </h2>
                <span class="badge badge-<?= esc($member['nama_tier']) ?>"><?= esc($member['nama_tier']) ?></span>
            </div>

            <div style="display: flex; gap: 10px; margin-bottom: 15px; text-align: center;">
                <div style="flex: 1; background: #fafafa; padding: 10px; border-radius: 8px; border: 1px solid #eee;">
                    <div style="font-size: 11px; color: #777; font-weight: bold;">TOTAL POIN</div>
                    <div style="font-size: 18px; font-weight: 800; color: var(--primary); margin-top: 2px;"><?= (int)$member['total_poin'] ?></div>
                </div>
                <div style="flex: 1; background: #fafafa; padding: 10px; border-radius: 8px; border: 1px solid #eee;">
                    <div style="font-size: 11px; color: #777; font-weight: bold;">DISKON TIER</div>
                    <div style="font-size: 18px; font-weight: 800; color: #2e7d32; margin-top: 2px;"><?= $member['diskon_persen'] ?>%</div>
                </div>
            </div>

            <!-- HANYA MUNCULKAN KALKULATOR JIKA AKTIF -->
            <?php if ($member['status'] === 'aktif'): ?>
                <div style="background:#ffebee; padding:18px; border-radius:10px; border:1px dashed var(--primary-light);">
                    <h3 style="margin-top:0; color:var(--primary); font-size: 15px; margin-bottom: 12px;">
                        <i class="fa-solid fa-pepper-hot icon-cabai"></i> Kalkulator Diskon & Poin
                    </h3>
                    
                    <form method="POST">
                        <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                        <input type="hidden" name="member_code" value="<?= esc($member['member_code']) ?>">
                        <input type="hidden" id="diskon_tier" name="diskon_tier" value="<?= $member['diskon_persen'] ?>">
                        
                        <label style="margin-top:0;">Total Belanja Awal (Rp)</label>
                        <input type="number" name="total_belanja" id="input_belanja" min="1" required placeholder="Contoh: 35000" onkeyup="hitungDiskon()" style="font-size:18px; font-weight:bold; color:var(--primary); border-radius: 8px;">
                        
                        <div id="info_diskon" style="color:var(--primary); font-size:13px; font-weight:bold; margin-top:10px; margin-bottom:12px; background: white; padding: 10px; border-radius: 6px; border: 1px solid #ffcdd2; line-height: 1.5;"></div>
                        
                        <label>Keterangan Tambahan (Opsional)</label>
                        <input type="text" name="keterangan" placeholder="Contoh: Seblak Extra Tulang Rangu" style="border-radius: 8px;">
                        
                        <button type="submit" name="tambah_poin_cepat" value="1" class="btn btn-gold" style="width:100%; margin-top:15px; font-size:16px; border-radius: 30px; padding: 12px;">
                            <i class="fa-solid fa-fire icon-api"></i> Konfirmasi Pembayaran & Poin
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div style="background: #ffebee; border: 1px solid #ef9a9a; border-radius: 8px; padding: 25px; text-align: center;">
                    <i class="fa-solid fa-ban" style="font-size: 40px; color: #c62828; margin-bottom: 15px;"></i>
                    <h3 style="color: #c62828; margin-top: 0;">TRANSAKSI DIBLOKIR</h3>
                    <p style="color: #555; margin-bottom: 0;">Member ini tidak dapat melakukan transaksi belanja maupun tukar poin karena status keanggotaannya saat ini adalah <strong><?= strtoupper($member['status']) ?></strong>.</p>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 15px; text-align: center;">
                <a class="btn btn-outline btn-sm" style="border-radius: 20px; padding: 6px 20px;" href="detail.php?id=<?= $member['id'] ?>">
                    <i class="fa-solid fa-address-card"></i> Lihat Riwayat Lengkap Member
                </a>
            </div>
        </div>
        
        <script>
        function hitungDiskon() {
            let fieldBelanja = document.getElementById('input_belanja');
            if(!fieldBelanja) return; // Mencegah error jika form tidak ada
            
            let belanja = parseFloat(fieldBelanja.value) || 0;
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
        </script>
        <?php endif; ?>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    function onScanSuccess(decodedText, decodedResult) {
        let extractedCode = decodedText;
        
        try {
            let parsed = JSON.parse(decodedText);
            if(parsed.code) {
                extractedCode = parsed.code; 
            }
        } catch (e) {}

        document.getElementById('hasil_scan').value = extractedCode;
        
        html5QrcodeScanner.clear().then(() => {
            document.getElementById('form-scan').submit();
        }).catch(error => { document.getElementById('form-scan').submit(); });
    }
    
    let config = { fps: 15, rememberLastUsedCamera: true, disableFlip: false };
    let html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", config, false);
    html5QrcodeScanner.render(onScanSuccess);
});
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>