<?php
/**
 * Koneksi Database - Sistem Membership CRM
 * Default disesuaikan dengan konfigurasi standar Laragon:
 * host: localhost, user: root, password: '' (kosong)
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_membership_crm');

// Base URL project -> DIDETEKSI OTOMATIS, tidak perlu diubah manual.
// Ini menghitung selisih antara folder project ini dengan document root
// server, sehingga otomatis benar baik diakses lewat:
//   http://localhost/crm_membership/   (docroot = folder www)
//   http://crm_membership.test/        (auto virtual host Laragon, docroot = folder project ini sendiri)
$__projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$__docRoot     = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : false;

if ($__projectRoot && $__docRoot && strpos($__projectRoot, $__docRoot) === 0) {
    $__basePath = substr($__projectRoot, strlen($__docRoot));
} else {
    // Fallback aman jika deteksi gagal (mis. DOCUMENT_ROOT tidak terbaca)
    $__basePath = '';
}
define('BASE_URL', rtrim($__basePath, '/') . '/');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage() .
        '<br>Pastikan Laragon (Apache & MySQL) sudah menyala dan database "db_membership_crm" sudah di-import.');
}

date_default_timezone_set('Asia/Jakarta');
