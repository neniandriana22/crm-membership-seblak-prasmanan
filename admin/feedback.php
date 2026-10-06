<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
require_once __DIR__ . '/../includes/auth.php';
wajibLoginAdmin();

$filterRating = $_GET['rating'] ?? '';

$sql = "SELECT f.*, m.nama_lengkap, m.member_code FROM feedbacks f
        JOIN members m ON f.member_id = m.id";
$params = [];
if ($filterRating !== '' && in_array($filterRating, ['1','2','3','4','5'])) {
    $sql .= " WHERE f.rating = ?";
    $params[] = $filterRating;
}
$sql .= " ORDER BY f.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftarFeedback = $stmt->fetchAll();

$judulHalaman = 'Feedback Pelanggan';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<h1>Feedback Pelanggan</h1>

<div class="card-box">
    <form method="GET" style="display:flex;gap:10px;align-items:flex-end;">
        <div>
            <label>Filter Rating</label>
            <select name="rating">
                <option value="">Semua Rating</option>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                <option value="<?= $i ?>" <?= $filterRating == $i ? 'selected' : '' ?>><?= $i ?> Bintang</option>
                <?php endfor; ?>
            </select>
        </div>
        <button type="submit" class="btn">Tampilkan</button>
    </form>
</div>

<div class="card-box">
    <table>
        <tr><th>Tanggal</th><th>Member</th><th>Kode</th><th>Rating</th><th>Komentar</th></tr>
        <?php foreach ($daftarFeedback as $f): ?>
        <tr>
            <td><?= formatTanggal($f['created_at']) ?></td>
            <td><?= esc($f['nama_lengkap']) ?></td>
            <td><?= esc($f['member_code']) ?></td>
            <td class="stars"><?= str_repeat('★', (int)$f['rating']) . str_repeat('☆', 5 - (int)$f['rating']) ?></td>
            <td><?= esc($f['komentar']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$daftarFeedback): ?><tr><td colspan="5">Belum ada feedback.</td></tr><?php endif; ?>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
