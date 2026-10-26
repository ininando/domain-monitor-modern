# Monitoring & Pemetaan Domain OPD — Diskominfo Kota Tomohon (v2)

Sistem informasi berbasis web untuk memonitor masa berlaku dan status domain milik Organisasi Perangkat Daerah (OPD) di lingkungan Pemerintah Kota Tomohon, memetakan kepemilikan domain per OPD, memberi peringatan domain yang akan expired, dan menghasilkan laporan PDF/Excel.

Pengguna: pegawai Diskominfo dengan peran **Super Admin** dan **Admin**.

## Fitur

| Modul | Isi |
|---|---|
| **Autentikasi** | Login email + password, pesan "Email/Password salah", batas 5 percobaan, regenerasi session, logout |
| **Dashboard** | Kartu Total / Aktif / Tidak Aktif / Akan Expired (30 hari), banner domain yang sudah expired, daftar "Perlu perhatian" (paling mendesak dulu), bar chart domain per OPD, line chart pertumbuhan 12 bulan |
| **Notifikasi** | Lonceng di topbar dengan jumlah domain Critical dan 5 domain paling mendesak |
| **OPD** | CRUD, pencarian, pagination, hapus lewat modal. OPD yang masih memiliki domain tidak bisa dihapus |
| **Domain** | CRUD dengan validasi FormRequest, filter (OPD, status, level, rentang tanggal expired), pencarian, urutan, badge countdown berwarna, halaman detail (countdown, info umum, info teknis) |
| **Pemetaan** | Tabel jumlah domain per OPD + detail per OPD, bar chart, donut chart, export tabel ke Excel, export chart ke PNG |
| **Laporan** | Per OPD (dengan periode), per status, domain expired. Pratinjau di halaman, unduh PDF (A4 landscape, kop Diskominfo) dan Excel |
| **Manajemen User** | Khusus Super Admin: CRUD user, Super Admin tidak bisa menghapus atau menurunkan peran akunnya sendiri |

### Aturan status expired

Dihitung di model `App\Models\Domain` (accessor `sisa_hari`, `level_expired`, `label_expired`):

| Level | Sisa hari | Warna |
|---|---|---|
| Critical | ≤ 7 hari atau sudah lewat | Merah |
| Warning | 8–30 hari | Kuning |
| Normal | > 30 hari | Hijau |

Domain yang sudah lewat tampil sebagai "Expired X hari lalu". Scope `Domain::akanExpired($days = 30)` mengambil domain yang expired dari hari ini sampai `$days` hari ke depan.

### Hak akses

| Menu | Super Admin | Admin |
|---|:---:|:---:|
| Dashboard, OPD, Domain, Pemetaan, Laporan | ✓ | ✓ |
| Manajemen User | ✓ | 403 |

Hak akses diterapkan lewat middleware `role` pada route group (`routes/web.php`), bukan hanya dengan menyembunyikan menu.

## Teknologi

- **Laravel 12**, PHP 8.3, MySQL 8
  - Versi framework dikunci di `laravel/framework: ^12.0` dan **tidak boleh di-upgrade** ke Laravel 13 atau lebih baru.
- Blade + Tailwind CSS v4 (Vite) + Alpine.js
- Chart.js, ikon Lucide (`mallardduck/blade-lucide-icons`), font Plus Jakarta Sans (self-host)
- `barryvdh/laravel-dompdf` (PDF), `maatwebsite/excel` (Excel)
- Dark mode (mengikuti sistem, bisa diganti, tersimpan di `localStorage`)

Design system: [`design-system/domain-monitor-opd-tomohon/MASTER.md`](design-system/domain-monitor-opd-tomohon/MASTER.md).

## Instalasi

Prasyarat: PHP 8.3+ (ekstensi `pdo_mysql`, `gd`, `zip`, `mbstring`, `dom`), Composer 2, Node.js 20+, MySQL 8.

```bash
git clone <url-repo> domain-monitor-modern
cd domain-monitor-modern

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Buat database kosong (contoh: `domain_opd_modern`), lalu sesuaikan `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=domain_opd_modern
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migration dan seeder, lalu build aset:

```bash
php artisan migrate --seed
npm run build
```

Jalankan aplikasi:

```bash
php artisan serve
```

Buka `http://localhost:8000`. Untuk pengembangan dengan hot reload, jalankan `npm run dev` bersamaan dengan `php artisan serve`, atau sekaligus lewat `composer run dev` (server, queue, log, dan Vite).

> **Versi framework:** gunakan `composer install` (mengikuti `composer.lock`, Laravel 12). Jangan mengubah batasan `laravel/framework` di `composer.json` atau meng-upgrade ke Laravel 13.

Zona waktu aplikasi default `Asia/Makassar` (WITA). Ubah lewat `APP_TIMEZONE` di `.env` bila perlu.

## Data awal (seeder)

`php artisan migrate --seed` mengisi:

- 2 akun default (lihat tabel di bawah)
- 10 OPD Kota Tomohon (nama kepala dan kontak fiktif)
- 30 domain dengan tanggal expired relatif terhadap hari seeding: sudah lewat, ≤ 7 hari, 8–30 hari, dan > 30 hari

Untuk mengulang data dari awal: `php artisan migrate:fresh --seed`.

### Akun default

| Peran | Email | Password |
|---|---|---|
| Super Admin | `superadmin@tomohonkota.go.id` | `password` |
| Admin | `admin@tomohonkota.go.id` | `password` |

> **Segera ganti password kedua akun ini** setelah instalasi, lewat menu **Manajemen User** (login sebagai Super Admin).

## Pengujian

```bash
php artisan test
```

Test memakai SQLite in-memory (lihat `phpunit.xml`), sehingga tidak menyentuh database MySQL. Cakupan utama:

- `AuthTest`: login, pesan error, throttle, regenerasi session, logout
- `RoleAuthorizationTest`, `UserManagementTest`, `HalamanSmokeTest`: otorisasi per peran (403 untuk Admin di menu User), semua halaman untuk kedua peran
- `DomainTest`, `OpdTest`: CRUD, validasi, filter, pencarian, urutan
- `DomainExpiryTest`: batas level Critical/Warning/Normal dan label expired
- `DashboardTest`, `PemetaanTest`: statistik, notifikasi, pertumbuhan, export Excel
- `LaporanTest`: filter laporan per OPD/periode, status, expired, unduh PDF dan Excel

## Struktur penting

```
app/
  Enums/            LevelExpired, StatusAktif, UserRole
  Exports/          LaporanExport, PemetaanExport
  Http/Controllers/ Dashboard, Opd, Domain, Pemetaan, Laporan, User, Auth/Login
  Http/Middleware/  EnsureUserHasRole (alias "role")
  Services/         StatistikDomain, LaporanDomain
resources/
  css/app.css       token warna (terang/gelap) dan komponen dasar
  js/app.js         Alpine: tema, sidebar, toast, modal konfirmasi
  js/charts.js      komponen chart (Chart.js)
  views/components/ button, input, select, textarea, badge-status, stat-card,
                    table, chart, modal-confirm, toast, empty-state, pagination, ...
```

## Di luar cakupan

Registrasi domain otomatis, pembayaran perpanjangan, monitoring uptime website, dan CMS website OPD tidak termasuk dalam sistem ini.
