# Storify Farm — Versi Laravel

Ini adalah hasil konversi penuh aplikasi **Storify Farm** (tadinya satu file
HTML dengan semua data disimpan di `localStorage`) menjadi aplikasi
**Laravel** dengan database sungguhan (Eloquent), migration, seeder, login
session, dan REST API.

## Arsitektur

Desainnya sengaja dibuat mirip mungkin dengan aslinya:

- **Satu halaman** (`resources/views/spa.blade.php`) berisi landing page +
  login + register + shell aplikasi (dashboard, produk, transaksi, FIFO,
  Storify View, kapasitas, planner, reports, users, settings, Storify AI) —
  persis seperti file HTML aslinya. Perpindahan antar "halaman" di dalamnya
  tetap 100% ditangani JavaScript lewat `location.hash`, sama seperti versi
  asli — Laravel hanya menyajikan shell ini satu kali di route `GET /`.
- **`public/js/app.js`** adalah hasil adaptasi dari script asli (≈1200
  baris): hampir seluruh logika UI (render tabel, FIFO, barcode, Storify
  View, Warehouse Planner, notifikasi, AI chat rule-based) **tidak diubah
  sama sekali**. Yang diganti hanya lapisan data: yang tadinya baca/tulis
  `localStorage`, sekarang memanggil endpoint `/api/...` ke database lewat
  `fetch()`.
- **Backend** (`app/Models`, `app/Http/Controllers`, migrations, seeders)
  adalah implementasi Laravel sungguhan: Eloquent models dengan relasi asli
  (`Product hasMany Batch`, `Batch hasMany Transaction`, dst.), migration
  database, seeder data awal, autentikasi berbasis session (`Auth::attempt`),
  dan validasi server-side di setiap endpoint.
- **Storify AI**: pemanggilan ke model Claude sekarang lewat backend
  (`AiChatController` → Anthropic API) supaya API key tidak pernah terekspos
  ke browser. Kalau `ANTHROPIC_API_KEY` di `.env` kosong atau gagal
  dihubungi, frontend otomatis jatuh ke basis pengetahuan rule-based lokal
  (persis seperti fallback di versi asli).

## Menjalankan proyek ini

> **Catatan penting**: proyek ini dibuat/ditulis di lingkungan sandbox yang
> **tidak punya akses ke Packagist**, jadi `composer install` **belum bisa
> dijalankan & diverifikasi di sana**. Semua kode sudah lengkap dan
> mengikuti struktur standar Laravel 11 — jalankan langkah di bawah ini di
> komputer Anda sendiri.

```bash
composer install
cp .env.example .env
php artisan key:generate

# Database lokal (opsional, jika belum memakai Supabase)
touch database/database.sqlite

php artisan migrate --seed
php artisan storage:link   # supaya avatar user bisa diakses via URL

php artisan serve
```

Buka `http://localhost:8000`.

### Akun demo (dibuat oleh seeder)

| Role       | Email                        | Password   |
|------------|-------------------------------|------------|
| Admin      | admin@storifyfarm.test        | `password` |
| Supervisor | supervisor@storifyfarm.test   | `password` |
| Petugas    | petugas@storifyfarm.test      | `password` |

### Mengaktifkan Storify AI (opsional)

Isi `ANTHROPIC_API_KEY` di `.env` dengan API key Anthropic Anda. Tanpa ini,
chat AI tetap berfungsi memakai basis pengetahuan rule-based bawaan (tidak
error, cuma tidak "sepintar" model Claude sungguhan).

### Kalau ingin pakai MySQL, bukan SQLite

Ubah bagian database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=storify_farm
DB_USERNAME=root
DB_PASSWORD=
```

lalu buat database `storify_farm` di MySQL sebelum `php artisan migrate --seed`.

## Perbedaan sengaja dari versi front-end murni

1. **Daftar akun baru (Register)**: di versi asli, mendaftar akun baru
   menghapus SEMUA data gudang (karena semuanya cuma hidup di
   `localStorage` browser masing-masing orang). Di versi Laravel ini semua
   user berbagi satu database, jadi mendaftar hanya membuat **user Admin
   baru** — data produk/stok yang sudah ada tidak ikut terhapus. Lihat
   komentar di `app/Http/Controllers/Auth/AuthController::register()`.
2. **Password**: sekarang di-hash (`bcrypt`) di database, bukan disimpan
   polos seperti di array `localStorage` versi asli.
3. **Storify AI**: panggilan ke Claude API dipindah ke server (lihat di
   atas) demi keamanan API key.
4. Endpoint `PUT /api/products/{id}` (edit produk) & `GET/POST /api/reports`
   dan `/api/reports/export` (CSV) sudah tersedia di backend walau belum ada
   tombolnya di UI (UI aslinya memang tidak punya tombol edit produk /
   download-lewat-server — laporan CSV di versi asli dibuat murni di
   browser dari data yang sudah ter-sinkron, jadi itu tetap dipertahankan
   apa adanya dan JS tidak diarahkan ke endpoint export ini).

## Struktur folder penting

```
app/Models/{User,Product,Batch,Transaction}.php
app/Http/Controllers/Auth/AuthController.php        # login/register/logout
app/Http/Controllers/Api/BootstrapController.php    # GET /api/bootstrap — hidrasi semua state
app/Http/Controllers/Api/ProductController.php
app/Http/Controllers/Api/TransactionController.php  # barang masuk/keluar (FIFO)
app/Http/Controllers/Api/UserController.php
app/Http/Controllers/Api/SettingsController.php
app/Http/Controllers/Api/ReportController.php
app/Http/Controllers/Api/AiChatController.php        # proxy aman ke Anthropic API
app/Http/Middleware/EnsureRole.php                   # setara ROLE_RESTRICTED
database/migrations/                                 # users, products, batches, transactions
database/seeders/                                     # data awal (setara DEFAULT_USERS/PRODUCTS)
resources/views/layouts/base.blade.php               # <head> + wrapper (dari file asli)
resources/views/spa.blade.php                        # landing+login+register+app (dari file asli)
public/js/app.js                                      # script asli, diadaptasi ke API
routes/web.php                                        # 1 route halaman + semua endpoint /api/*
```
