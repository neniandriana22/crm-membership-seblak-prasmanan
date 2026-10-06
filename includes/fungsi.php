<?php
require_once __DIR__ . '/../vendor/phpqrcode/phpqrcode.php';

/**
 * Membuat kode member unik, format: MBR-YYYYMMDD-XXXX
 */
function buatKodeMember(PDO $pdo): string
{
    $prefix = 'MBR-' . date('Ymd') . '-';
    do {
        $acak = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $kode = $prefix . $acak;
        $stmt = $pdo->prepare("SELECT id FROM members WHERE member_code = ?");
        $stmt->execute([$kode]);
    } while ($stmt->fetch());

    return $kode;
}

/**
 * Generate QR Code untuk sebuah member dan simpan sebagai file PNG.
 * Isi QR = data inti kartu (kode member, nama, tier) dalam format JSON,
 * sehingga saat discan oleh sistem (halaman admin/scan.php) datanya
 * bisa langsung dibaca & dicocokkan dengan database.
 *
 * Return: path relatif file QR (untuk disimpan di kolom qr_code)
 */
function buatQRCodeMember(string $memberCode, string $nama, string $tier): string
{
    $isiQR = json_encode([
        'code' => $memberCode,
        'nama' => $nama,
        'tier' => $tier,
    ], JSON_UNESCAPED_UNICODE);

    $namaFile   = 'qr_' . $memberCode . '.png';
    $pathAbsolut = __DIR__ . '/../uploads/qrcode/' . $namaFile;

    QRcode::png($isiQR, $pathAbsolut, QR_ECLEVEL_M, 6, 2);

    return 'uploads/qrcode/' . $namaFile;
}

/**
 * Tentukan tier berdasarkan total poin, lalu update kolom tier_id pada
 * tabel members apabila terjadi kenaikan/penurunan level.
 * Dipanggil setiap kali total_poin seorang member berubah.
 */
function perbaruiTierMember(PDO $pdo, int $memberId, int $totalPoin): void
{
    $stmt = $pdo->prepare(
        "SELECT id FROM membership_tiers WHERE min_poin <= ? ORDER BY min_poin DESC LIMIT 1"
    );
    $stmt->execute([$totalPoin]);
    $tier = $stmt->fetch();

    if ($tier) {
        $upd = $pdo->prepare("UPDATE members SET tier_id = ? WHERE id = ?");
        $upd->execute([$tier['id'], $memberId]);
    }
}

/**
 * Tambah/kurangi poin seorang member + catat ke riwayat transaksi,
 * lalu otomatis mengecek kenaikan tier.
 */
function catatTransaksiPoin(
    PDO $pdo,
    int $memberId,
    string $jenis,       // 'earn' atau 'redeem'
    int $jumlahPoin,
    string $keterangan,
    ?int $adminId = null
): void {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO points_transactions (member_id, jenis, jumlah_poin, keterangan, admin_id)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$memberId, $jenis, $jumlahPoin, $keterangan, $adminId]);

        $delta = ($jenis === 'earn') ? $jumlahPoin : -$jumlahPoin;
        $pdo->prepare("UPDATE members SET total_poin = GREATEST(0, total_poin + ?) WHERE id = ?")
            ->execute([$delta, $memberId]);

        $stmt = $pdo->prepare("SELECT total_poin FROM members WHERE id = ?");
        $stmt->execute([$memberId]);
        $totalBaru = (int) $stmt->fetchColumn();

        perbaruiTierMember($pdo, $memberId, $totalBaru);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/** Format tanggal Indonesia sederhana: 09 September 2026 */
function formatTanggal(?string $tanggal): string
{
    if (!$tanggal) return '-';
    $bulan = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
        7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];
    $ts = strtotime($tanggal);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

function esc(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
