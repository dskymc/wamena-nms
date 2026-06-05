# WAMENA NMS

**Wide Area Monitoring & Enterprise Network Analytics**  
Diskominfosatik Provinsi Papua Pegunungan

Sistem manajemen jaringan berbasis Laravel 12 dengan inventori perangkat dan profil SNMP (v2c & v3). Mendukung vendor **MikroTik**, **Ruijie**, dan **Ubiquiti**.

## Persyaratan

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL/MariaDB (XAMPP)
- Akses UDP port 161 dari server ke perangkat jaringan (untuk uji SNMP)

## Instalasi (XAMPP)

```bash
cd c:\xampp\htdocs\nms
composer install
cp .env.example .env   # jika belum ada
php artisan key:generate
```

Buat database MySQL:

```sql
CREATE DATABASE nms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Sesuaikan `.env`:

```
APP_URL=http://localhost/nms/public
DB_CONNECTION=mysql
DB_DATABASE=nms
DB_USERNAME=root
DB_PASSWORD=

NMS_ADMIN_EMAIL=admin@nms.local
NMS_ADMIN_PASSWORD=password
```

Jalankan migrasi & seeder:

```bash
php artisan migrate:fresh --seed
npm install
npm run build
```

Akses aplikasi: **http://localhost/nms/public**

Login default: `admin@nms.local` / `password` (ubah di production).

## Fitur Fase 1 (MVP)

- Autentikasi (Laravel Breeze) + RBAC (Spatie Permission)
- CRUD Lokasi (hierarki parent/child)
- CRUD Profil SNMP (v2c & v3, secret terenkripsi)
- CRUD Perangkat (MikroTik, Ruijie, Ubiquiti, lainnya)
- Uji koneksi SNMP (GET sysDescr.0) per perangkat
- Dashboard ringkasan inventori

### Role

| Role | Hak akses |
|------|-----------|
| `super_admin` | Semua + kelola pengguna |
| `admin` | CRUD lokasi, profil SNMP, perangkat; lihat pengguna |
| `operator` | CRUD perangkat & profil SNMP; lihat lokasi |
| `viewer` | Read-only |

## Mengaktifkan SNMP di Perangkat

### MikroTik (RouterOS)

```
/snmp set enabled=yes contact="NMS" location="DC"
/snmp community add name=public addresses=0.0.0.0/0
```

Untuk SNMPv3, gunakan menu SNMP di Winbox atau perintah `/snmp community` sesuai dokumentasi RouterOS.

### Ruijie

Aktifkan SNMP di konfigurasi global (CLI/Web), contoh:

```
snmp-server enable
snmp-server community public ro
```

Sesuaikan ACL agar hanya IP server NMS yang dapat mengakses.

### Ubiquiti / UniFi

- **UniFi Controller**: Settings → System → SNMP (aktifkan v1/v2c atau v3).
- **EdgeSwitch / perangkat standalone**: aktifkan SNMP di Device Settings, buka UDP/161 ke server NMS.

## Uji SNMP dari Aplikasi

1. Buat **Profil SNMP** (community atau kredensial v3).
2. Tambah **Perangkat** dan pilih profil SNMP.
3. Di halaman edit perangkat, klik **Test SNMP**.

Library: [freedsx/snmp](https://github.com/FreeDSx/snmp) (pure PHP, tanpa ekstensi `php_snmp` wajib).

## Roadmap Fase 2+

| Fase | Fitur |
|------|--------|
| 2a | Scheduler polling berkala (`PollDeviceJob`) |
| 2b | Metrik historis & grafik (traffic, CPU, RAM) |
| 2c | Alerting (threshold, email/Telegram) |
| 2d | Topology (LLDP/CDP walk) |
| 2e | SNMP trap receiver |

Skema database saat ini sudah menyiapkan kolom `is_monitored`, `poll_interval_sec`, `last_seen_at`, dan `status` untuk fase polling.

## Struktur Penting

```
app/
├── Enums/              # DeviceVendor, DeviceStatus, SnmpVersion, ...
├── Http/Controllers/Admin/
├── Models/             # Device, Location, SnmpProfile
├── Policies/
└── Services/Snmp/      # SnmpClient, SnmpTestResult
config/nms.php          # Vendor OID & protokol SNMP
```

## Perintah Berguna

```bash
php artisan migrate:fresh --seed
php artisan serve
npm run dev
```

## Keamanan

- Community dan passphrase SNMP disimpan terenkripsi (Laravel `encrypted` cast).
- Ganti password admin default sebelum production.
- Batasi akses SNMP di perangkat hanya dari IP server NMS.
