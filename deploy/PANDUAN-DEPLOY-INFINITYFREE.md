# Panduan Deploy Oceanindo ke InfinityFree (gratis)

> Karena InfinityFree **tidak punya SSH/Composer/terminal**, kita tidak bisa
> menjalankan `composer install` atau `php artisan migrate` di server.
> Solusinya: folder `vendor/` di-upload manual, dan database di-import lewat phpMyAdmin.

---

## ⚠️ Versi PHP — perlu diturunkan dulu ke 8.3

Project ini dikembangkan di **PHP 8.4** (`composer.json` → `"php": "^8.4"`, vendor
memakai Symfony 8.x). InfinityFree maksimal **PHP 8.3**, jadi sebelum upload kamu
harus membuat `vendor/` versi 8.3 **di komputer lokal**:

```bash
# simpan dulu setelan 8.4
cp composer.json deploy/composer.json.bak-8.4
cp composer.lock deploy/composer.lock.bak-8.4

# turunkan target PHP
composer config platform.php 8.3.0
composer require "php:^8.3" --no-update
composer update
```

Setelah `vendor/` 8.3 jadi dan sudah di-upload, **kembalikan repo lokal ke 8.4**
dengan memulihkan dua file backup di atas lalu `composer install` — supaya seluruh
tim tetap seragam di 8.4.

Lalu di hosting: **Control Panel → PHP → Select PHP Version** → pilih **PHP 8.3**.

> Jangan commit `composer.json`/`composer.lock` versi 8.3 ke repo tim.

---

## Langkah 1 — Buat akun & database

1. Buat akun hosting di InfinityFree (dapat subdomain gratis, mis. `oceanindo.infinityfreeapp.com`,
   atau pakai domainmu sendiri).
2. Control Panel → **MySQL Databases** → buat database baru (mis. nama `oceanindo`).
3. Catat 4 hal ini (akan dipakai di `.env`):
   - **MySQL Hostname** (mis. `sql123.infinityfree.com`)
   - **Database name** (mis. `if0_12345678_oceanindo`)
   - **Username** (mis. `if0_12345678`)
   - **Password** (password akun MySQL-mu)

## Langkah 2 — Import database

1. Control Panel → **phpMyAdmin** → pilih database yang tadi dibuat.
2. Tab **Import** → pilih file **`deploy/oceanindo_database.sql`** → **Go**.
3. Pastikan muncul 25 tabel (users, audit_findings, dst).

> Akun admin bawaan: **admin@oceanindo.com** / **Secret123**

## Langkah 3 — Siapkan file `.env`

1. Buka **`deploy/.env.production.example`**.
2. Isi `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` dengan data Langkah 1.
3. Ganti `APP_URL` dengan domain situsmu (mis. `https://oceanindo.infinityfreeapp.com`).
4. Simpan. File ini nanti diupload lalu **di-rename jadi `.env`** (tanpa `.example`).

## Langkah 4 — Susun struktur folder untuk di-upload

InfinityFree hanya mengakses folder **`htdocs/`** sebagai web root.
Susun seperti ini (paling aman — file Laravel tidak bisa diakses publik):

```
htdocs/
├── index.php          ← pakai file deploy/htdocs-index.php (path sudah disesuaikan)
├── .htaccess          ← SALIN dari public/.htaccess (apa adanya)
├── favicon.ico, robots.txt, logo-oceanindo.png
├── css/  js/  vendor/  ...  ← SEMUA isi folder public/ (KECUALI index.php & .htaccess di atas)
│
└── laravel/           ← SEMUA file project KECUALI isi folder public/
    ├── app/  bootstrap/  config/  database/  lang/  routes/  storage/  tests/
    ├── vendor/         ← WAJIB ikut (ini hasil composer, besar)
    ├── .env            ← dari Langkah 3 (rename dari .env.production.example)
    ├── .htaccess       ← pakai file deploy/laravel-protect.htaccess
    ├── artisan
    └── composer.json, composer.lock, dll
```

Ringkasnya:
- Isi folder **`public/`** → masuk ke **`htdocs/`** (root).
- **Sisa semua file** project → masuk ke **`htdocs/laravel/`**.
- Ganti `htdocs/index.php` dengan **`deploy/htdocs-index.php`**.
- Tambah **`htdocs/laravel/.htaccess`** dari **`deploy/laravel-protect.htaccess`**.

## Langkah 5 — Upload

Cara upload (pilih salah satu):
- **File Manager** di Control Panel (untuk file sedikit), atau
- **FTP** pakai FileZilla (DISARANKAN karena `vendor/` isinya ribuan file).
  Data FTP ada di Control Panel → **FTP Accounts**.

Upload semua sesuai struktur Langkah 4. `vendor/` besar, jadi sabar (bisa 10–30 menit via FTP).

## Langkah 6 — Optimasi & cek

1. Karena tidak ada terminal, **jangan** pakai config cache. Pastikan di `htdocs/laravel/`
   **tidak ada** file `bootstrap/cache/config.php` / `routes-*.php` (kalau ada, hapus —
   isinya path komputer lokalmu dan akan bikin error).
2. Pastikan folder **`htdocs/laravel/storage/`** writable (biasanya otomatis 755).
3. Buka domainmu di browser → harusnya muncul landing page.
4. Coba `/login` → masuk pakai admin@oceanindo.com / Secret123.

---

## Backup setelan 8.4

`deploy/composer.json.bak-8.4` dan `deploy/composer.lock.bak-8.4` adalah salinan
setelan PHP 8.4 — dipakai untuk memulihkan repo lokal setelah proses downgrade
sementara di bagian "Versi PHP" di atas.

---

## Catatan
- App ini **tanpa fitur upload file**, jadi tidak perlu `storage:link`.
- `APP_DEBUG=false` di produksi (jangan tampilkan error detail ke publik).
- Email pakai `log` (tidak benar-benar kirim email). Fitur "lupa password" tidak akan
  mengirim email kecuali kamu set SMTP sungguhan nanti.
```
