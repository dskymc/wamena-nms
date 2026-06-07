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

## Fitur Fase 2a — Polling & Status Up/Down

- Polling SNMP otomatis untuk perangkat **MikroTik, Ruijie, Ubiquiti** yang dimonitor (`is_monitored = true`)
- Status perangkat: **Up**, **Down**, atau **Unknown**
- Dashboard kartu Up/Down/Unknown + daftar perangkat down
- Filter status di daftar perangkat + kolom **Terakhir Terlihat**
- Tombol **Poll Sekarang** di halaman edit perangkat
- Perintah Artisan `nms:poll-devices` (sync, tanpa queue worker wajib)

### Kriteria polling

Perangkat di-poll jika:

- `is_monitored = true`
- Memiliki profil SNMP
- Vendor **MikroTik, Ruijie, atau Ubiquiti** (vendor `other` tetap `unknown`)
- Sudah lewat `poll_interval_sec` sejak `last_seen_at`, atau belum pernah di-poll

OID yang di-GET: `sysUpTime`, `sysName`, dan identity MikroTik (konfigurasi di `config/nms.php`).

### Setup Task Scheduler (Windows / XAMPP)

Polling berjalan lewat Laravel Scheduler. Buat task di **Task Scheduler** yang menjalankan setiap **1 menit**:

**Program/script:**

```
C:\xampp\php\php.exe
```

**Add arguments:**

```
C:\xampp\htdocs\nms\artisan schedule:run
```

**Start in:**

```
C:\xampp\htdocs\nms
```

Centang **Run whether user is logged on or not** jika server headless.

Uji manual tanpa scheduler:

```bash
php artisan nms:poll-devices
php artisan schedule:list
```

Opsi `--limit=10` membatasi jumlah perangkat per eksekusi (default: 50, lihat `config/nms.php`).

### Polling sync vs queue

Fase 2a menggunakan polling **sync** (`dispatchSync`) sehingga tidak perlu menjalankan `queue:work` di XAMPP. Class `PollDeviceJob` sudah disiapkan untuk upgrade async di Fase 2b.

Jika nanti ingin async, jalankan worker terpisah:

```bash
php artisan queue:work --stop-when-empty
```

Pastikan `QUEUE_CONNECTION=database` di `.env` (migrasi tabel `jobs` sudah tersedia).

## Fitur Fase 2b — Metrik Historis & Grafik

- Tabel time-series `metric_samples` (CPU, memori, traffic interface)
- Koleksi metrik otomatis setiap poll SNMP sukses
- Halaman **Metrik** per perangkat dengan grafik Chart.js (1 jam / 24 jam / 7 hari)
- Traffic dihitung dari delta counter IF-MIB (`ifHCInOctets` / `ifHCOutOctets`)
- Polling status Up/Down diperluas ke **Ruijie** dan **Ubiquiti**
- Retensi data default **7 hari** (`nms:prune-metrics` dijadwalkan harian)

### Metrik yang dikumpulkan

| Metrik | Sumber OID | Vendor |
|--------|-----------|--------|
| CPU (%) | MikroTik `mtxrHlCpuLoad` / HOST-RESOURCES `hrProcessorLoad` | Semua |
| Memori (%) | MikroTik memory used/total / HOST-RESOURCES RAM | Semua |
| Traffic in/out (bps) | IF-MIB counter delta per interface | Semua |

Konfigurasi OID metrik: `config/nms.php` → `metric_oids`, `interface_oids`.

### Perintah metrik

```bash
php artisan nms:prune-metrics          # hapus sampel > 7 hari
php artisan nms:prune-metrics --days=14
```

Akses grafik: **Perangkat → Metrik** atau tombol **Lihat Metrik** di halaman edit.

Setelah perubahan frontend: `npm run build`

## Fitur Fase 2c — Alerting & Notifikasi

- **Alert rules** — trigger perangkat down, recovery up, threshold metrik (CPU/memori/traffic)
- **Alert log** — riwayat alert open/resolved dengan cooldown anti-spam
- **Notifikasi multi-channel:** Telegram, Email (SMTP), WhatsApp (**Fonnte**)
- **Dashboard** — kartu & daftar alert aktif
- Evaluasi otomatis setelah poll + command `nms:evaluate-alerts`

### Konfigurasi notifikasi

Semua pengaturan channel alert dikelola dari menu **Notifikasi** (`/admin/notification-settings`):

| Channel | Field yang perlu diisi |
|---------|------------------------|
| **Telegram** | Bot token, Chat ID |
| **Email** | SMTP host/port/username/password, from address, daftar penerima |
| **WhatsApp (Fonnte)** | Token API, nomor NOC |

Token dan password disimpan terenkripsi di tabel `nms_settings`. Kosongkan field password/token saat simpan jika tidak ingin mengubah nilai yang sudah ada.

Gunakan tombol **Test Telegram / Test Email / Test WhatsApp** untuk verifikasi sebelum alert produksi.

### Perintah alert

```bash
php artisan nms:evaluate-alerts
php artisan db:seed --class=AlertRuleSeeder
```

Default rules (down, recovery, CPU>80%, memori>90%) dibuat oleh seeder.

## Fitur Fase 2d — Topology (LLDP/CDP)

- **Discovery SNMP** — walk **LLDP-MIB** (utama) dan **CDP-MIB** (Ruijie/Cisco-like) dari perangkat Up + monitoring aktif
- **Penyimpanan** — tabel `topology_links` (port lokal → neighbor) dan `device_interfaces` (cache IF-MIB)
- **Matching** — neighbor dicocokkan ke inventori NMS via IP manajemen, sysName, atau chassis/MAC
- **Peta topologi** — menu **Topologi** (`/admin/topology`), graf interaktif **vis-network**
- **Filter** — lokasi, vendor, hanya link ke perangkat terdaftar
- Neighbor tidak terdaftar ditampilkan sebagai node abu-abu
- Command terpisah dari poll (walk LLDP lebih berat)

### Perintah topologi

```bash
php artisan nms:discover-topology
php artisan nms:discover-topology --device=1
php artisan nms:discover-topology --limit=20
```

Scheduled otomatis setiap **30 menit** via `routes/console.php`. Untuk Windows Task Scheduler, tambahkan entry serupa poll:

```bash
php artisan nms:discover-topology
```

### Mengaktifkan LLDP di perangkat

**MikroTik (RouterOS)** — LLDP harus aktif agar walk tidak kosong:

```
/interface lldp set enabled=yes
```

Atau per interface:

```
/interface ethernet set [find] lldp-enabled=yes
```

Pastikan SNMP sudah aktif (lihat bagian MikroTik di bawah).

**Ruijie** — aktifkan LLDP global (CLI contoh):

```
lldp enable
```

Beberapa model Ruijie juga expose **CDP**; NMS akan walk keduanya untuk vendor Ruijie.

**Ubiquiti / UniFi** — aktifkan LLDP di switch settings (Device → Services → LLDP).

Tanpa LLDP/CDP aktif di perangkat, discovery berjalan sukses tetapi **tidak menghasilkan link**.

## Fitur Fase 2e — SNMP Trap Receiver

- **Trap listener** — command daemon `nms:trap-listen` (UDP, default port **1162** di Windows dev)
- **Protokol** — SNMP v1, v2c, v3 trap via FreeDSx `TrapSink`
- **Penyimpanan** — tabel `snmp_traps` (source IP, OID, varbinds, summary)
- **Matching** — source IP → perangkat inventori (`management_ip`)
- **Parsing** — trap standar: linkDown, linkUp, coldStart, warmStart
- **Alerting** — linkDown/linkUp memicu notifikasi (rule default "SNMP Link Down/Up")
- **UI** — menu **SNMP Traps** (`/admin/snmp-traps`)

### Perintah trap

```bash
php artisan nms:trap-listen
php artisan nms:trap-listen --port=1162
php artisan nms:prune-traps
php artisan db:seed --class=AlertRuleSeeder
```

Jalankan `nms:trap-listen` di **terminal terpisah** (proses daemon, bukan scheduled). Di production Linux bisa pakai port **162** dan systemd/supervisor.

Retensi log trap: `nms:prune-traps` (daily, default 30 hari).

### Mengaktifkan trap di MikroTik

```
/snmp set enabled=yes
/snmp community add name=public addresses=<IP_NMS>/32
/snmp trap target add name=nms address=<IP_NMS> port=1162 community=public
/snmp trap interface add numbers=all
```

Ganti `<IP_NMS>`, community, dan port sesuai lingkungan. Buka firewall UDP inbound ke port listener di server NMS.

### Role

| Role | Hak akses |
|------|-----------|
| `super_admin` | Semua + kelola pengguna |
| `admin` | CRUD lokasi, profil SNMP, perangkat; lihat pengguna |
| `operator` | CRUD perangkat & profil SNMP; lihat lokasi |
| `viewer` | Read-only (termasuk lihat topologi) |

Permission topologi: `topology.view` (peta), `topology.discover` (tombol Discover Sekarang).

## Mengaktifkan SNMP di Perangkat

### MikroTik (RouterOS)

```
/snmp set enabled=yes contact="NMS" location="DC"
/snmp community add name=public addresses=0.0.0.0/0
```

Ganti `public` dan `addresses` sesuai community string profil SNMP di NMS — **batasi IP server NMS** di production, jangan gunakan `0.0.0.0/0`.

Untuk polling Fase 2a, pastikan perangkat MikroTik:

- SNMP enabled
- Community/credential v3 cocok dengan profil di NMS
- Firewall RouterOS mengizinkan UDP/161 dari IP server NMS
- Perangkat di NMS: vendor **MikroTik**, **Monitoring aktif**, interval poll sesuai kebutuhan (default 300 detik)

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
| 2a | ✅ Polling SNMP berkala, status Up/Down |
| 2b | ✅ Metrik historis & grafik (traffic, CPU, RAM) |
| 2c | ✅ Alerting (threshold, Telegram/email/Fonnte WA) |
| 2d | ✅ Topology (LLDP/CDP walk, peta graf) |
| 2e | ✅ SNMP trap receiver |

Skema database menyiapkan kolom `is_monitored`, `poll_interval_sec`, `last_seen_at`, `last_poll_error`, dan `status` untuk polling.

## Struktur Penting

```
app/
├── Enums/              # DeviceVendor, DeviceStatus, SnmpVersion, ...
├── Http/Controllers/Admin/
├── Models/             # Device, Location, SnmpProfile
├── Policies/
└── Services/
    ├── Snmp/           # SnmpClient, PollResult, DevicePollService
    └── Metrics/        # MetricCollector, MetricSampleWriter, MetricQueryService
    └── Alerts/         # AlertEvaluator, AlertNotifier
    └── Fonnte/         # FonnteClient, PhoneNormalizer
    └── Topology/       # TopologyWalker, TopologySyncService
    └── Traps/          # TrapProcessor, NmsTrapListener, TrapAlertHandler
config/nms.php
```

## Perintah Berguna

```bash
php artisan migrate:fresh --seed
php artisan nms:poll-devices
php artisan schedule:list
php artisan serve
npm run dev
```

## Keamanan

- Community dan passphrase SNMP disimpan terenkripsi (Laravel `encrypted` cast).
- Ganti password admin default sebelum production.
- Batasi akses SNMP di perangkat hanya dari IP server NMS.
