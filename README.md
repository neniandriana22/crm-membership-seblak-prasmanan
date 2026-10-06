# Sistem Membership CRM (Pendaftaran, Kartu Barcode, Poin & Feedback)

Dibuat untuk tugas mata kuliah **Customer Relationship Management (CRM)**.
Stack: **PHP native (PDO) + MySQL**, berjalan langsung di **Laragon**, tanpa perlu
Composer/instalasi tambahan apa pun.

---

## 1. Analisis Kebutuhan (Ringkasan)

**Data pelanggan/member yang dikumpulkan saat pendaftaran:**
Nama lengkap, email (unik), no. HP, alamat, tanggal lahir, jenis kelamin, foto (opsional).

**Cara kerja sistem setiap kali digunakan:**
1. Pelanggan mendaftar lewat `daftar.php` → sistem membuat **kode member unik**
   (format `MBR-YYYYMMDD-XXXX`) dan sebuah **QR code** yang isinya data inti
   kartu (`{"code":..., "nama":..., "tier":...}`) dalam format JSON.
2. Member mendapat **kartu digital siap cetak** (`kartu.php`) berisi foto,
   nama, kode member, tier, tanggal berlaku, dan QR code tadi.
3. Setiap kali member bertransaksi/berkunjung, staf membuka **`admin/scan.php`**
   dan memindai QR pada kartu (pakai scanner USB *maupun* kamera HP yang
   mengetik hasil scan) → sistem mencocokkan `code` ke database → staf
   menambahkan poin → **tier member naik otomatis** begitu poin melewati
   ambang batas (Silver → Gold di 500 poin, Gold → Platinum di 1500 poin).
4. Member bisa mengecek poin & memberi **feedback/rating** kapan saja lewat
   `portal.php` cukup dengan memasukkan kode membernya.
5. Admin memantau semuanya lewat dashboard: total member, distribusi tier,
   rata-rata rating, feedback terbaru.

**Struktur data (ERD ringkas):**
```
membership_tiers (1) ───< (banyak) members (1) ───< (banyak) points_transactions
                                      │                          (dicatat oleh admins)
                                      └──< (banyak) feedbacks
```

**Catatan soal "barcode":** kartu ini memakai **QR code** (bukan barcode garis
Code128) karena QR bisa memuat data terstruktur (kode + nama + tier) sekaligus
lebih mudah dipindai pakai kamera HP biasa selain scanner USB. Ini praktik yang
umum dipakai untuk kartu member modern. Kalau dosen mewajibkan barcode garis
klasik, tinggal ganti pemanggilan `QRcode::png()` di `includes/fungsi.php`
dengan library barcode Code128 — strukturnya (kode unik → gambar → disimpan
ke kolom `qr_code`) tetap sama persis.

---

## 2. Kebutuhan Teknologi

| Komponen | Yang dipakai |
|---|---|
| Server lokal | Laragon (Apache + PHP + MySQL) |
| Bahasa | PHP 8.x (native, pakai PDO — bukan framework) |
| Database | MySQL / MariaDB |
| Library QR | `phpqrcode` (sudah disertakan di `vendor/`, murni PHP, **tanpa Composer**) |
| Ekstensi PHP wajib | `pdo_mysql`, `gd` (biasanya sudah aktif default di Laragon) |
| Editor | VS Code |

Tidak ada dependency eksternal yang perlu diunduh lagi — semua sudah ada di dalam folder ini.

---

## 3. Langkah Instalasi di Laragon

1. **Salin folder project**
   Salin seluruh folder `crm_membership` ini ke dalam folder `www` Laragon, contoh:
   `C:\laragon\www\crm_membership`

2. **Jalankan Laragon**
   Klik **Start All** (Apache & MySQL menyala).

3. **Buat database**
   - Buka `http://localhost/phpmyadmin`
   - Buat database baru **atau** langsung import: klik tab **Import** →
     pilih file `database/db_membership_crm.sql` → klik **Go**.
   - File ini otomatis: membuat database `db_membership_crm`, semua tabel,
     3 tier default (Silver/Gold/Platinum), dan 1 akun admin default.

4. **Cek koneksi database**
   Buka `config/koneksi.php`. Default-nya sudah cocok dengan Laragon standar:
   ```php
   DB_HOST = 'localhost'
   DB_USER = 'root'
   DB_PASS = ''  (kosong)
   DB_NAME = 'db_membership_crm'
   ```
   Kalau MySQL Laragon-mu pakai password lain, sesuaikan `DB_PASS`.

5. **Cek BASE_URL**
   Masih di `config/koneksi.php`, pastikan sesuai nama folder project:
   ```php
   define('BASE_URL', '/crm_membership/');
   ```
   Kalau kamu menaruh project dengan nama folder lain, ganti bagian ini.

6. **Buka di browser**
   `http://localhost/crm_membership/`

7. **Login admin default**
   - URL: `http://localhost/crm_membership/admin/login.php`
   - Username: `admin`
   - Password: `admin123`
   - ⚠️ Untuk pemakaian nyata, segera ganti password ini langsung di tabel `admins` (pakai `password_hash()`), jangan dipakai apa adanya untuk data sungguhan.

---

## 4. Struktur Folder

```
crm_membership/
├── admin/              → seluruh halaman admin (login, dashboard, data member,
│                          detail member, scan kartu, feedback)
├── assets/css/         → style.css (termasuk desain kartu member & mode cetak)
├── config/koneksi.php  → koneksi database (PDO) + konfigurasi BASE_URL
├── database/           → file .sql untuk import database
├── includes/           → fungsi.php (helper), header/footer, auth.php
├── uploads/foto/       → hasil upload foto member
├── uploads/qrcode/     → QR code hasil generate tiap member
├── vendor/phpqrcode/   → library QR code (murni PHP, tanpa Composer)
├── index.php           → halaman beranda publik
├── daftar.php          → formulir pendaftaran member
├── kartu.php           → kartu member (siap cetak) — akses: kartu.php?kode=...
├── portal.php          → portal member: cek poin + kirim feedback
```

---

## 5. Alur Pemakaian (Demo)

1. Buka beranda → klik **Daftar Member Baru** → isi data → submit.
2. Otomatis diarahkan ke halaman **kartu member** lengkap dengan QR → klik
   **Cetak Kartu** (akan otomatis rapi ke ukuran kartu ID lewat CSS `@media print`).
3. Login sebagai admin → menu **Scan Kartu** → salin isi teks QR (atau scan
   pakai alat barcode/QR scanner USB kalau tersedia) → tempel ke kolom scan →
   tambahkan poin.
4. Tambahkan poin ≥ 500 → lihat tier member otomatis berubah jadi **Gold**.
5. Buka **Portal Member** (`portal.php`) → masukkan kode member → beri feedback bintang 1–5.
6. Kembali ke **Dashboard admin** → lihat statistik & feedback ter-update otomatis.

---

## 6. Pengembangan Lanjutan (opsional, kalau ingin ditambah)

- Tambah field NIK / nomor identitas resmi pada tabel `members` bila diperlukan.
- Ganti QR code jadi barcode Code128 (lihat catatan di bagian Analisis di atas).
- Tambah role admin bertingkat (superadmin vs kasir).
- Ekspor laporan member/poin ke Excel (bisa pakai library PhpSpreadsheet).
- Kirim notifikasi WhatsApp/email otomatis saat tier member naik.

Semua fondasi (struktur database, relasi antar tabel, fungsi helper) sudah
disiapkan supaya pengembangan lanjutan ini tinggal ditambahkan, tidak perlu ubah struktur inti.
