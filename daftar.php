<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

$error = '';
$suksesDaftar = isset($_GET['sukses']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama_lengkap'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $noHp    = trim($_POST['no_hp'] ?? '');
    $alamat  = trim($_POST['alamat'] ?? '');
    $tglLahir = $_POST['tanggal_lahir'] ?? null;
    $gender  = $_POST['jenis_kelamin'] ?? null;

    if ($nama === '' || $email === '' || $noHp === '') {
        $error = 'Nama, email, dan no. HP wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (empty($_FILES['bukti_bayar']['name'])) {
        $error = 'Bukti pembayaran wajib diupload.';
    } else {
        $cek = $pdo->prepare("SELECT id FROM members WHERE email = ?");
        $cek->execute([$email]);
        if ($cek->fetch()) {
            $error = 'Email ini sudah terdaftar sebagai member.';
        } else {
            // Upload foto profil
            $namaFoto = null;
            if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $namaFoto = 'foto_' . uniqid() . '.' . $ext;
                    move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/uploads/foto/' . $namaFoto);
                }
            }

            // Upload Bukti Bayar (WAJIB)
            $buktiBayar = null;
            $extB = strtolower(pathinfo($_FILES['bukti_bayar']['name'], PATHINFO_EXTENSION));
            if (in_array($extB, ['jpg', 'jpeg', 'png', 'pdf'])) {
                $buktiBayar = 'bukti_' . uniqid() . '.' . $extB;
                move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], __DIR__ . '/uploads/bukti/' . $buktiBayar);
            }

            $kodeMember = buatKodeMember($pdo);
            $tglDaftar  = date('Y-m-d');
            $berlakuSampai = date('Y-m-d', strtotime('+1 year'));

            // INSERT DENGAN STATUS 'pending'
            $stmt = $pdo->prepare(
                "INSERT INTO members
                 (member_code, nama_lengkap, email, no_hp, alamat, tanggal_lahir, jenis_kelamin, foto, bukti_bayar, tier_id, total_poin, status, tanggal_daftar, berlaku_sampai)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0, 'pending', ?, ?)"
            );
            $stmt->execute([
                $kodeMember, $nama, $email, $noHp, $alamat,
                $tglLahir ?: null, $gender ?: null, $namaFoto, $buktiBayar,
                $tglDaftar, $berlakuSampai,
            ]);

            $qrPath = buatQRCodeMember($kodeMember, $nama, 'Silver');
            $pdo->prepare("UPDATE members SET qr_code = ? WHERE member_code = ?")->execute([$qrPath, $kodeMember]);

            // Redirect ke halaman sukses (bukan ke kartu.php karena status masih pending)
            header('Location: daftar.php?sukses=1&kode=' . urlencode($kodeMember));
            exit;
        }
    }
}

$judulHalaman = 'Daftar Member VIP';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card-box" style="max-width: 650px; margin: 20px auto; padding: 40px; border-top: 6px solid var(--primary-light); box-shadow: 0 15px 30px rgba(198, 40, 40, 0.2);">
    
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="font-size: 26px; margin-bottom: 5px; color: var(--primary);">
            <i class="fa-solid fa-fire icon-api" style="color: #ff9800; font-size: 24px;"></i> 
            PENDAFTARAN VIP SEBLAK NENI 
        </h2>
    </div>

    <?php if ($suksesDaftar): ?>
        <div style="text-align: center; padding: 30px 10px;">
            <i class="fa-solid fa-circle-check" style="font-size: 60px; color: #2e7d32; margin-bottom: 15px;"></i>
            <h3 style="color: #2e7d32; margin-top: 0;">Pendaftaran Berhasil Dikirim!</h3>
            <p style="font-size: 15px; color: #555; line-height: 1.6;">
                Terima kasih telah mendaftar. Data dan Bukti Pembayaran Anda sedang <strong>menunggu verifikasi Admin</strong> (Maks 1x24 Jam).<br><br>
                Harap catat Kode Member Anda:<br>
                <strong style="font-size: 24px; color: var(--primary); font-family: monospace; background: #ffebee; padding: 5px 15px; border-radius: 8px; display: inline-block; margin-top: 10px;"><?= esc($_GET['kode'] ?? '') ?></strong>
            </p>
            <p style="font-size: 13px; color: #777; margin-top: 20px;">
                Jika pendaftaran sudah di-ACC, Anda dapat mencetak Kartu Digital melalui menu <a href="portal.php" style="color: var(--primary); font-weight: bold;">Cek Poin & Status</a>.
            </p>
        </div>
    <?php else: ?>

        <?php if ($error): ?>
            <div class="alert alert-error" style="border-left: 5px solid #c62828;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= esc($error) ?>
            </div>
        <?php endif; ?>

        <!-- Info Rekening -->
        <div style="background: #e3f2fd; border: 1px dashed #64b5f6; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
            <h4 style="margin: 0 0 10px 0; color: #1565c0;"><i class="fa-solid fa-wallet"></i> Biaya Pendaftaran: Rp 20.000</h4>
            <p style="font-size: 13px; margin: 0; color: #333;">Silakan transfer ke rekening <strong>BCA 123456789 a.n Neni Andriana</strong>, atau Bayar langsung ke Kasir. Wajib lampirkan bukti bayar di bawah.</p>
        </div>

        <form method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
            
            <div style="margin-bottom: 15px;">
                <label><i class="fa-solid fa-user" style="color:#ff5252;"></i> Nama Lengkap <span style="color:red;">*</span></label>
                <input type="text" name="nama_lengkap" required value="<?= esc($_POST['nama_lengkap'] ?? '') ?>" placeholder="Sesuai KTP/Identitas Anda" style="padding-left: 15px;">
            </div>

            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px; margin-bottom: 15px;">
                    <label><i class="fa-solid fa-envelope" style="color:#ff5252;"></i> Email Aktif <span style="color:red;">*</span></label>
                    <input type="email" name="email" required value="<?= esc($_POST['email'] ?? '') ?>" placeholder="contoh@gmail.com">
                </div>

                <div style="flex: 1; min-width: 250px; margin-bottom: 15px;">
                    <label><i class="fa-brands fa-whatsapp" style="color:#2e7d32;"></i> No. HP / WhatsApp <span style="color:red;">*</span></label>
                    <input type="text" name="no_hp" required value="<?= esc($_POST['no_hp'] ?? '') ?>" placeholder="081234567890">
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label><i class="fa-solid fa-map-location-dot" style="color:#ff5252;"></i> Alamat Lengkap</label>
                <textarea name="alamat" rows="3" placeholder="Tuliskan alamat domisili Anda..."><?= esc($_POST['alamat'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px; margin-bottom: 15px;">
                    <label><i class="fa-solid fa-calendar-days" style="color:#ff5252;"></i> Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="<?= esc($_POST['tanggal_lahir'] ?? '') ?>">
                </div>

                <div style="flex: 1; min-width: 250px; margin-bottom: 15px;">
                    <label><i class="fa-solid fa-venus-mars" style="color:#ff5252;"></i> Jenis Kelamin</label>
                    <select name="jenis_kelamin">
                        <option value="">-- Pilih --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px; margin-bottom: 20px; background: #ffebee; padding: 15px; border-radius: 8px; border: 1px solid #ffcdd2;">
                    <label style="margin-top: 0;"><i class="fa-solid fa-camera-retro" style="color:#ff5252;"></i> Pas Foto (Opsional)</label>
                    <input type="file" name="foto" accept=".jpg,.jpeg,.png" style="background: white; padding: 8px;">
                </div>

                <div style="flex: 1; min-width: 250px; margin-bottom: 20px; background: #e3f2fd; padding: 15px; border-radius: 8px; border: 1px solid #90caf9;">
                    <label style="margin-top: 0; color: #1565c0;"><i class="fa-solid fa-receipt"></i> Bukti Bayar <span style="color:red;">*</span></label>
                    <input type="file" name="bukti_bayar" accept=".jpg,.jpeg,.png,.pdf" required style="background: white; padding: 8px; border-color: #90caf9;">
                </div>
            </div>

            <div style="text-align: center;">
                <button type="submit" class="btn" style="width: 100%; padding: 15px; font-size: 18px; border-radius: 50px;">
                    <i class="fa-solid fa-paper-plane icon-api"></i> KIRIM DATA & BUKTI BAYAR
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>