<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
require_once __DIR__ . '/../includes/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_nama'] = $admin['nama_lengkap'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}

$judulHalaman = 'Login Admin VIP';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="card-box" style="max-width: 440px; margin: 50px auto; padding: 40px; border-top: 6px solid var(--primary-light); box-shadow: 0 15px 35px rgba(198, 40, 40, 0.25); border-radius: 16px;">
    
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="font-size: 40px; margin-bottom: 10px;">
            <i class="fa-solid fa-fire icon-api" style="color: #ff9800;"></i>
        </div>
        <h2 style="font-size: 24px; margin: 0; color: var(--primary); letter-spacing: 1px;">
            ADMINISTRATOR LOGIN
        </h2>
        <p style="color: #666; font-size: 14px; margin-top: 5px;">Panel Pengelola <strong>Seblak Prasmanan Neni</strong></p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error" style="border-left: 5px solid #c62828;">
            <i class="fa-solid fa-circle-exclamation"></i> <?= esc($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div style="margin-bottom: 15px;">
            <label><i class="fa-solid fa-user-shield" style="color: #ff5252;"></i> Username</label>
            <input type="text" name="username" required autofocus placeholder="Masukkan username..." style="padding-left: 15px; border-radius: 8px;">
        </div>
        
        <div style="margin-bottom: 25px;">
            <label><i class="fa-solid fa-lock" style="color: #ff5252;"></i> Password</label>
            <input type="password" name="password" required placeholder="Masukkan password..." style="padding-left: 15px; border-radius: 8px;">
        </div>
        
        <div style="margin-top: 20px;">
            <button type="submit" class="btn" style="width: 100%; padding: 14px; font-size: 16px; border-radius: 50px; letter-spacing: 0.5px;">
                <i class="fa-solid fa-right-to-bracket"></i> MASUK SISTEM
            </button>
        </div>
    </form>
    
    <div style="margin-top: 25px; padding-top: 15px; border-top: 1px dashed #ffcdd2; text-align: center;">
        <p style="font-size: 12px; color: #777; margin: 0;">
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>