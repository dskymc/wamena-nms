<?php

/**
 * UAT Script — Fase 2b Metrik Historis & Grafik
 * Run: php uat_fase2b.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Enums\DeviceVendor;
use App\Enums\MetricType;
use App\Models\Device;
use App\Models\Location;
use App\Models\MetricSample;
use App\Models\SnmpProfile;
use App\Models\User;
use App\Services\Metrics\MetricQueryService;
use App\Services\Metrics\MetricReading;
use App\Services\Metrics\MetricSampleWriter;
use App\Services\Snmp\PollResult;
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

echo "=== UAT Fase 2b — WAMENA NMS ===\n\n";

// --- 1. Database & Config ---
echo "--- 1. Database & Config ---\n";

uat('2b-01', 'Tabel metric_samples ada',
    Schema::hasTable('metric_samples'));

$columns = ['device_id', 'metric', 'source', 'source_label', 'value', 'unit', 'recorded_at'];
foreach ($columns as $i => $col) {
    uat('2b-0'.(2 + $i), "Kolom metric_samples.{$col} ada",
        Schema::hasColumn('metric_samples', $col));
}

uat('2b-09', 'Config metrics.retention_days terdefinisi',
    (int) config('nms.metrics.retention_days') > 0,
    'days='.config('nms.metrics.retention_days'));

uat('2b-10', 'Config metric_oids.mikrotik (cpu, memory)',
    isset(config('nms.metric_oids.mikrotik')['cpu_load'], config('nms.metric_oids.mikrotik')['memory_total']));

uat('2b-11', 'Config metric_oids.ruijie & ubiquiti',
    isset(config('nms.metric_oids.ruijie')['cpu_load'], config('nms.metric_oids.ubiquiti')['cpu_load']));

uat('2b-12', 'Config interface_oids IF-MIB lengkap',
    isset(
        config('nms.interface_oids')['if_descr'],
        config('nms.interface_oids')['if_hc_in_octets'],
        config('nms.interface_oids')['if_hc_out_octets'],
    ));

// --- 2. Enums & Models ---
echo "\n--- 2. Enums & Models ---\n";

uat('2b-13', 'MetricType enum chartable metrics',
    MetricType::Cpu->isChartable()
    && MetricType::MemoryPercent->isChartable()
    && MetricType::TrafficInBps->isChartable()
    && ! MetricType::IfInOctets->isChartable());

uat('2b-14', 'DeviceVendor MikroTik/Ruijie/Ubiquiti supportsPolling',
    DeviceVendor::Mikrotik->supportsPolling()
    && DeviceVendor::Ruijie->supportsPolling()
    && DeviceVendor::Ubiquiti->supportsPolling());

uat('2b-15', 'DeviceVendor Other TIDAK supportsPolling',
    ! DeviceVendor::Other->supportsPolling());

uat('2b-16', 'Device::metricSamples() relasi exists',
    method_exists(Device::class, 'metricSamples'));

$user = User::first();
if (! $user) {
    echo "ERROR: No user. Run migrate:fresh --seed.\n";
    exit(1);
}

$location = Location::first();
$profile = SnmpProfile::first();

// --- 3. Vendor polling scope ---
echo "\n--- 3. Vendor Polling (perluasan 2b) ---\n";

$mikrotik = Device::firstOrCreate(['name' => 'UAT2b-MikroTik'], [
    'location_id' => $location?->id,
    'snmp_profile_id' => $profile?->id,
    'created_by' => $user->id,
    'management_ip' => '192.0.2.10',
    'vendor' => DeviceVendor::Mikrotik,
    'is_monitored' => true,
    'poll_interval_sec' => 300,
]);

$ruijie = Device::firstOrCreate(['name' => 'UAT2b-Ruijie'], [
    'location_id' => $location?->id,
    'snmp_profile_id' => $profile?->id,
    'created_by' => $user->id,
    'management_ip' => '192.0.2.11',
    'vendor' => DeviceVendor::Ruijie,
    'is_monitored' => true,
    'poll_interval_sec' => 300,
]);

$ubiquiti = Device::firstOrCreate(['name' => 'UAT2b-Ubiquiti'], [
    'location_id' => $location?->id,
    'snmp_profile_id' => $profile?->id,
    'created_by' => $user->id,
    'management_ip' => '192.0.2.12',
    'vendor' => DeviceVendor::Ubiquiti,
    'is_monitored' => true,
    'poll_interval_sec' => 300,
]);

uat('2b-17', 'scopePollable() mencakup MikroTik, Ruijie, Ubiquiti',
    Device::pollable()->where('name', 'UAT2b-MikroTik')->exists()
    && Device::pollable()->where('name', 'UAT2b-Ruijie')->exists()
    && Device::pollable()->where('name', 'UAT2b-Ubiquiti')->exists());

uat('2b-18', 'metricOids() MikroTik punya cpu_load',
    isset(DeviceVendor::Mikrotik->metricOids()['cpu_load']));

uat('2b-19', 'metricOids() Ruijie punya cpu_load (HOST-RESOURCES)',
    DeviceVendor::Ruijie->metricOids()['cpu_load'] === '1.3.6.1.2.1.25.3.3.1.2');

// --- 4. Metric storage & query ---
echo "\n--- 4. Metric Storage & Query ---\n";

MetricSample::where('device_id', $mikrotik->id)->delete();

$writer = app(MetricSampleWriter::class);
$recordedAt = now()->subMinutes(30);
$writer->store($mikrotik, [
    new MetricReading(MetricType::Cpu, 42.5),
    new MetricReading(MetricType::MemoryPercent, 61.2),
    new MetricReading(MetricType::IfInOctets, 1_000_000, '1', 'ether1'),
    new MetricReading(MetricType::IfOutOctets, 500_000, '1', 'ether1'),
], $recordedAt);

$writer->store($mikrotik, [
    new MetricReading(MetricType::Cpu, 55.0),
    new MetricReading(MetricType::MemoryPercent, 63.0),
    new MetricReading(MetricType::TrafficInBps, 1_024_000, '1', 'ether1'),
    new MetricReading(MetricType::TrafficOutBps, 512_000, '1', 'ether1'),
    new MetricReading(MetricType::IfInOctets, 1_076_000, '1', 'ether1'),
    new MetricReading(MetricType::IfOutOctets, 538_000, '1', 'ether1'),
]);

uat('2b-20', 'MetricSampleWriter menyimpan sampel ke DB',
    MetricSample::where('device_id', $mikrotik->id)->count() >= 6,
    'count='.MetricSample::where('device_id', $mikrotik->id)->count());

$query = app(MetricQueryService::class);
$chartData = $query->chartData($mikrotik, '24h');

uat('2b-21', 'MetricQueryService chartData CPU punya labels & values',
    count($chartData['cpu']['labels']) >= 1 && count($chartData['cpu']['values']) >= 1);

uat('2b-22', 'MetricQueryService chartData memory punya data',
    count($chartData['memory']['values']) >= 1);

uat('2b-23', 'MetricQueryService interface options terisi',
    isset($chartData['interfaces']['1']) && $chartData['interfaces']['1'] === 'ether1');

uat('2b-24', 'MetricQueryService traffic series punya in/out',
    count($chartData['traffic']['in']) >= 1 && count($chartData['traffic']['out']) >= 1);

$traffic1h = $query->trafficForInterface($mikrotik, '1', '1h');
uat('2b-25', 'trafficForInterface() per interface',
    array_key_exists('in', $traffic1h) && array_key_exists('out', $traffic1h));

// --- 5. PollResult metrics ---
echo "\n--- 5. PollResult & Integrasi Poll ---\n";

$pollWithMetrics = PollResult::ok('12345', 'router', 'identity', [
    new MetricReading(MetricType::Cpu, 10.0),
]);
uat('2b-26', 'PollResult::ok() menyertakan metrics array',
    $pollWithMetrics->success && count($pollWithMetrics->metrics) === 1);

uat('2b-27', 'MetricCollector & MetricSampleWriter dapat di-resolve',
    app(\App\Services\Metrics\MetricCollector::class) instanceof \App\Services\Metrics\MetricCollector
    && app(MetricSampleWriter::class) instanceof MetricSampleWriter);

// --- 6. Artisan & Schedule ---
echo "\n--- 6. Artisan & Schedule ---\n";

Artisan::call('nms:prune-metrics', ['--days' => 365]);
$pruneOutput = Artisan::output();
uat('2b-28', 'Command nms:prune-metrics sukses',
    str_contains($pruneOutput, 'Metrik dihapus'));

Artisan::call('schedule:list');
$scheduleOutput = Artisan::output();
uat('2b-29', 'Schedule nms:prune-metrics terdaftar daily',
    str_contains($scheduleOutput, 'nms:prune-metrics'));

uat('2b-30', 'Schedule nms:poll-devices masih aktif',
    str_contains($scheduleOutput, 'nms:poll-devices'));

// --- 7. HTTP Routes & UI ---
echo "\n--- 7. HTTP Routes & UI ---\n";

auth()->login($user);

function httpGet(string $path): \Symfony\Component\HttpFoundation\Response
{
    global $app;

    return $app->handle(Request::create($path, 'GET'));
}

$showResp = httpGet('/admin/devices/'.$mikrotik->id);
$showBody = $showResp->getContent();
uat('2b-31', 'GET devices/{id} show → 200',
    $showResp->getStatusCode() === 200);

uat('2b-32', 'Halaman metrik menampilkan canvas CPU & memory',
    str_contains($showBody, 'cpu-chart') && str_contains($showBody, 'memory-chart'));

uat('2b-33', 'Halaman metrik menampilkan canvas traffic',
    str_contains($showBody, 'traffic-chart'));

uat('2b-34', 'Halaman metrik rentang waktu 1h/24h/7d',
    str_contains($showBody, 'data-metric-range="1h"')
    && str_contains($showBody, 'data-metric-range="24h"')
    && str_contains($showBody, 'data-metric-range="7d"'));

uat('2b-35', 'Halaman metrik selector interface',
    str_contains($showBody, 'metric-interface'));

$metricsResp = httpGet('/admin/devices/'.$mikrotik->id.'/metrics?range=24h');
$metricsJson = json_decode($metricsResp->getContent(), true);
uat('2b-36', 'GET devices/{id}/metrics → 200 JSON',
    $metricsResp->getStatusCode() === 200 && is_array($metricsJson));

uat('2b-37', 'API metrics punya cpu, memory, traffic, interfaces',
    isset($metricsJson['cpu'], $metricsJson['memory'], $metricsJson['traffic'], $metricsJson['interfaces']));

$indexResp = httpGet('/admin/devices');
uat('2b-38', 'Daftar perangkat menampilkan link Metrik',
    str_contains($indexResp->getContent(), 'Metrik'));

$editResp = httpGet('/admin/devices/'.$mikrotik->id.'/edit');
uat('2b-39', 'Edit perangkat menampilkan Lihat Metrik',
    str_contains($editResp->getContent(), 'Lihat Metrik'));

$ruijieEdit = httpGet('/admin/devices/'.$ruijie->id.'/edit');
uat('2b-40', 'Edit Ruijie menampilkan Poll Sekarang (vendor 2b)',
    str_contains($ruijieEdit->getContent(), 'Poll Sekarang'));

// --- 8. Frontend assets ---
echo "\n--- 8. Frontend Assets ---\n";

$manifestPath = __DIR__.'/public/build/manifest.json';
$manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
$hasMetricsJs = false;
foreach ($manifest as $entry) {
    if (str_contains($entry['file'] ?? '', 'device-metrics')) {
        $hasMetricsJs = true;
        break;
    }
}
uat('2b-41', 'Vite build device-metrics.js ada di manifest',
    $hasMetricsJs,
    $hasMetricsJs ? 'built' : 'run npm run build');

uat('2b-42', 'Chart.js dependency di package.json',
    str_contains(file_get_contents(__DIR__.'/package.json'), 'chart.js'));

// --- 9. Dokumentasi ---
echo "\n--- 9. Dokumentasi ---\n";

$readme = file_get_contents(__DIR__.'/README.md');
uat('2b-43', 'README dokumentasi Fase 2b',
    str_contains($readme, 'Fase 2b') && str_contains($readme, 'metric_samples'));

uat('2b-44', 'README dokumentasi nms:prune-metrics',
    str_contains($readme, 'nms:prune-metrics'));

uat('2b-45', 'README dokumentasi grafik Chart.js / Metrik',
    str_contains($readme, 'Chart.js') || str_contains($readme, 'Metrik'));

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

echo "\nUAT Fase 2b: SEMUA PASS.\n";
echo "Catatan: Grafik traffic live & OID SNMP nyata memerlukan perangkat reachable + minimal 2 poll.\n";
exit(0);
