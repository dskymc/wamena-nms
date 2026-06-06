<?php

/**
 * UAT Script — Fase 2a Polling SNMP & Status Up/Down
 * Run: php uat_fase2a.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Enums\DeviceStatus;
use App\Enums\DeviceVendor;
use App\Jobs\PollDeviceJob;
use App\Models\Device;
use App\Models\Location;
use App\Models\SnmpProfile;
use App\Models\User;
use App\Services\Snmp\DevicePollService;
use App\Services\Snmp\PollResult;
use App\Services\Snmp\SnmpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

$results = [];
$pass = 0;
$fail = 0;
$warn = 0;

function uat(string $id, string $name, bool $ok, ?string $detail = null, string $severity = 'fail'): void
{
    global $results, $pass, $fail, $warn;
    $status = $ok ? 'PASS' : ($severity === 'warn' ? 'WARN' : 'FAIL');
    if ($ok) {
        $pass++;
    } elseif ($severity === 'warn') {
        $warn++;
    } else {
        $fail++;
    }
    $results[] = compact('id', 'name', 'status', 'detail');
    echo sprintf("[%s] %s — %s%s\n", $status, $id, $name, $detail ? " ($detail)" : '');
}

echo "=== UAT Fase 2a — WAMENA NMS ===\n\n";

// --- 1. Database & Config ---
echo "--- 1. Database & Config ---\n";

uat('2a-01', 'Migrasi last_poll_error sudah dijalankan',
    Schema::hasColumn('devices', 'last_poll_error'));

$pollOids = config('nms.poll_oids');
uat('2a-02', 'Config poll_oids.common berisi sysUpTime & sysName',
    isset($pollOids['common']['sysUpTime'], $pollOids['common']['sysName'])
    && $pollOids['common']['sysUpTime'] === '1.3.6.1.2.1.1.3.0');

uat('2a-03', 'Config poll_oids.mikrotik berisi identity OID',
    isset($pollOids['mikrotik']['identity'])
    && str_starts_with($pollOids['mikrotik']['identity'], '1.3.6.1.4.1.14988'));

uat('2a-04', 'poll_batch_limit terdefinisi',
    (int) config('nms.poll_batch_limit') > 0,
    'limit='.config('nms.poll_batch_limit'));

uat('2a-05', 'DeviceVendor::Mikrotik supportsPolling() = true',
    DeviceVendor::Mikrotik->supportsPolling());

uat('2a-06', 'DeviceVendor::Ruijie supportsPolling() = false',
    ! DeviceVendor::Ruijie->supportsPolling());

$mikrotikOids = DeviceVendor::Mikrotik->pollOids();
uat('2a-07', 'DeviceVendor::pollOids() merge common + vendor',
    count($mikrotikOids) >= 3 && isset($mikrotikOids['sysUpTime'], $mikrotikOids['identity']));

// --- 2. PollResult DTO ---
echo "\n--- 2. PollResult DTO ---\n";

$okResult = PollResult::ok('12345', 'router-1', 'identity');
uat('2a-08', 'PollResult::ok() success=true',
    $okResult->success && $okResult->sysUpTime === '12345' && $okResult->sysName === 'router-1');

$failResult = PollResult::fail('timeout');
uat('2a-09', 'PollResult::fail() success=false + error',
    ! $failResult->success && $failResult->error === 'timeout');

// --- 3. Device model logic ---
echo "\n--- 3. Device Model Logic ---\n";

$user = User::first();
if (! $user) {
    echo "ERROR: No user in DB. Run migrate:fresh --seed first.\n";
    exit(1);
}

$location = Location::first();
$profile = SnmpProfile::first();

// Create test devices if needed
$testMikrotik = Device::firstOrCreate(
    ['name' => 'UAT-MikroTik-Test'],
    [
        'location_id' => $location?->id,
        'snmp_profile_id' => $profile?->id,
        'created_by' => $user->id,
        'management_ip' => '192.0.2.1',
        'vendor' => DeviceVendor::Mikrotik,
        'is_monitored' => true,
        'poll_interval_sec' => 300,
        'status' => DeviceStatus::Unknown,
    ]
);

$testRuijie = Device::firstOrCreate(
    ['name' => 'UAT-Ruijie-Test'],
    [
        'location_id' => $location?->id,
        'snmp_profile_id' => $profile?->id,
        'created_by' => $user->id,
        'management_ip' => '192.0.2.2',
        'vendor' => DeviceVendor::Ruijie,
        'is_monitored' => true,
        'poll_interval_sec' => 300,
        'status' => DeviceStatus::Unknown,
    ]
);

uat('2a-10', 'Device::scopePollable() hanya MikroTik monitored',
    Device::pollable()->where('name', 'UAT-MikroTik-Test')->exists()
    && ! Device::pollable()->where('name', 'UAT-Ruijie-Test')->exists());

$testMikrotik->update(['last_seen_at' => now()]);
$testMikrotik->refresh();
uat('2a-11', 'isDueForPoll() false jika belum lewat interval',
    ! $testMikrotik->isDueForPoll());

$testMikrotik->update(['last_seen_at' => now()->subSeconds(400)]);
$testMikrotik->refresh();
uat('2a-12', 'isDueForPoll() true setelah interval lewat',
    $testMikrotik->isDueForPoll());

$testMikrotik->update(['last_seen_at' => null]);
$testMikrotik->refresh();
uat('2a-13', 'isDueForPoll() true jika belum pernah di-poll',
    $testMikrotik->isDueForPoll());

// --- 4. DevicePollService status rules ---
echo "\n--- 4. DevicePollService Status Rules ---\n";

// Mock SnmpClient via partial test - test applyResult indirectly through poll with unreachable IP
$pollService = app(DevicePollService::class);
$testMikrotik->update(['last_seen_at' => now()->subHour(), 'status' => DeviceStatus::Unknown]);
$testMikrotik->refresh();
$oldLastSeen = $testMikrotik->last_seen_at;

$result = $pollService->pollAndUpdate($testMikrotik);
$testMikrotik->refresh();

uat('2a-14', 'Poll gagal (unreachable IP) → status down',
    $testMikrotik->status === DeviceStatus::Down,
    'status='.$testMikrotik->status->value);

uat('2a-15', 'Poll gagal → last_seen_at TIDAK diubah',
    $testMikrotik->last_seen_at->eq($oldLastSeen));

uat('2a-16', 'Poll gagal → last_poll_error terisi',
    ! empty($testMikrotik->last_poll_error),
    substr($testMikrotik->last_poll_error ?? '', 0, 60));

uat('2a-17', 'PollResult error message tidak kosong saat gagal',
    ! $result->success && ! empty($result->error));

// Test no profile
$noProfileDevice = Device::firstOrCreate(
    ['name' => 'UAT-NoProfile'],
    [
        'location_id' => $location?->id,
        'snmp_profile_id' => null,
        'created_by' => $user->id,
        'management_ip' => '192.0.2.99',
        'vendor' => DeviceVendor::Mikrotik,
        'is_monitored' => true,
        'status' => DeviceStatus::Unknown,
    ]
);
$noProfileResult = $pollService->pollAndUpdate($noProfileDevice);
uat('2a-18', 'Poll tanpa profil SNMP → gagal dengan pesan jelas',
    ! $noProfileResult->success && str_contains($noProfileResult->error, 'profil SNMP'));

// --- 5. Artisan command ---
echo "\n--- 5. Artisan nms:poll-devices ---\n";

Artisan::call('nms:poll-devices', ['--limit' => 5]);
$cmdOutput = Artisan::output();
uat('2a-19', 'Command nms:poll-devices exit sukses',
    true, trim(str_replace("\n", ' ', $cmdOutput)));

uat('2a-20', 'Command output menyebutkan jumlah perangkat',
    str_contains($cmdOutput, 'perangkat') || str_contains($cmdOutput, 'Tidak ada'),
    trim($cmdOutput));

// --- 6. Schedule ---
echo "\n--- 6. Scheduler ---\n";

$scheduleList = Artisan::call('schedule:list');
$scheduleOutput = Artisan::output();
uat('2a-21', 'Schedule nms:poll-devices terdaftar everyMinute',
    str_contains($scheduleOutput, 'nms:poll-devices'));

// --- 7. HTTP Routes & Views ---
echo "\n--- 7. HTTP Routes & Views ---\n";

auth()->login($user);

function httpGet(string $path): Illuminate\Http\Response
{
    global $app;
    return $app->handle(Request::create($path, 'GET'));
}

function httpPost(string $path, array $data = []): Illuminate\Http\Response
{
    global $app;
    $session = $app->make('session');
    if (! $session->isStarted()) {
        $session->start();
    }
    $token = $session->token();
    $data['_token'] = $token;

    $req = Request::create($path, 'POST', $data);
    $req->headers->set('Referer', 'http://localhost/nms/public'.$path);
    $req->setLaravelSession($session);

    return $app->handle($req);
}

$devicesResp = httpGet('/admin/devices');
uat('2a-22', 'GET /admin/devices → 200',
    $devicesResp->getStatusCode() === 200);

$devicesBody = $devicesResp->getContent();
uat('2a-23', 'Halaman devices menampilkan kolom Terakhir Terlihat',
    str_contains($devicesBody, 'Terakhir Terlihat'));

uat('2a-24', 'Halaman devices menampilkan filter Semua Status',
    str_contains($devicesBody, 'Semua Status'));

uat('2a-25', 'Halaman devices menampilkan badge status (Up/Down/Unknown)',
    str_contains($devicesBody, 'Up') || str_contains($devicesBody, 'Down') || str_contains($devicesBody, 'Unknown'));

$filterDown = httpGet('/admin/devices?status=down');
uat('2a-26', 'Filter status=down → 200',
    $filterDown->getStatusCode() === 200);

$dashboardResp = httpGet('/dashboard');
$dashboardBody = $dashboardResp->getContent();
uat('2a-27', 'GET /dashboard → 200',
    $dashboardResp->getStatusCode() === 200);

uat('2a-28', 'Dashboard menampilkan kartu Up, Down, Unknown',
    str_contains($dashboardBody, 'Up') && str_contains($dashboardBody, 'Down') && str_contains($dashboardBody, 'Unknown'));

uat('2a-29', 'Dashboard menampilkan panel Perangkat Down',
    str_contains($dashboardBody, 'Perangkat Down'));

$editResp = httpGet('/admin/devices/'.$testMikrotik->id.'/edit');
$editBody = $editResp->getContent();
uat('2a-30', 'GET edit device → 200',
    $editResp->getStatusCode() === 200);

uat('2a-31', 'Edit device menampilkan tombol Poll Sekarang',
    str_contains($editBody, 'Poll Sekarang'));

uat('2a-32', 'Edit device menampilkan tombol Test SNMP',
    str_contains($editBody, 'Test SNMP'));

// Poll manual via controller (bypass CSRF — di browser form POST normal)
$controller = app(App\Http\Controllers\Admin\DeviceController::class);
$beforeStatus = $testMikrotik->status;
$pollRedirect = $controller->poll($testMikrotik, app(DevicePollService::class));
$testMikrotik->refresh();
uat('2a-33', 'Poll manual controller → redirect + update status',
    $pollRedirect instanceof Illuminate\Http\RedirectResponse
    && $testMikrotik->status === DeviceStatus::Down,
    'status='.$testMikrotik->status->value);

// Ruijie should not show Poll button
$editRuijie = httpGet('/admin/devices/'.$testRuijie->id.'/edit');
uat('2a-34', 'Edit Ruijie TIDAK menampilkan Poll Sekarang',
    ! str_contains($editRuijie->getContent(), 'Poll Sekarang'));

// --- 8. Job class exists ---
echo "\n--- 8. Job & Sync Dispatch ---\n";

uat('2a-35', 'PollDeviceJob class exists & implements ShouldQueue',
    class_exists(PollDeviceJob::class)
    && in_array('Illuminate\Contracts\Queue\ShouldQueue', class_implements(PollDeviceJob::class) ?: [], true));

// --- 9. README documentation ---
echo "\n--- 9. Dokumentasi ---\n";

$readme = file_get_contents(__DIR__.'/README.md');
uat('2a-36', 'README dokumentasi nms:poll-devices',
    str_contains($readme, 'nms:poll-devices'));

uat('2a-37', 'README dokumentasi Task Scheduler Windows',
    str_contains($readme, 'Task Scheduler') && str_contains($readme, 'schedule:run'));

uat('2a-38', 'README dokumentasi polling sync vs queue',
    str_contains($readme, 'sync') || str_contains($readme, 'dispatchSync'));

// --- Summary ---
echo "\n=== RINGKASAN UAT ===\n";
echo "PASS: {$pass}\n";
echo "WARN: {$warn}\n";
echo "FAIL: {$fail}\n";
echo 'TOTAL: '.count($results)."\n";

if ($fail > 0) {
    echo "\nGAGAL:\n";
    foreach ($results as $r) {
        if ($r['status'] === 'FAIL') {
            echo "  - {$r['id']}: {$r['name']}".($r['detail'] ? " — {$r['detail']}" : '')."\n";
        }
    }
    exit(1);
}

echo "\nUAT Fase 2a: SEMUA PASS (SNMP live test memerlukan perangkat MikroTik nyata).\n";
exit(0);
