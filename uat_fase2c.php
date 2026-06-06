<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AlertRule;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

$pass = 0; $fail = 0;
function t(string $n, bool $ok): void { global $pass, $fail; echo ($ok ? '[PASS]' : '[FAIL]')." $n\n"; $ok ? $pass++ : $fail++; }

echo "=== UAT Fase 2c ===\n";
t('alert_rules table', Schema::hasTable('alert_rules'));
t('alert_events table', Schema::hasTable('alert_events'));
t('wa_messages table', Schema::hasTable('wa_messages'));
t('default rules seeded', AlertRule::count() >= 4);
t('FonnteClient resolves', app(\App\Services\Fonnte\FonnteClient::class) instanceof \App\Services\Fonnte\FonnteClient);
t('AlertEvaluator resolves', app(\App\Services\Alerts\AlertEvaluator::class) instanceof \App\Services\Alerts\AlertEvaluator);

auth()->login(User::first());
$r = $app->handle(Illuminate\Http\Request::create('/admin/alert-rules', 'GET'));
t('GET alert-rules', $r->getStatusCode() === 200);
$r2 = $app->handle(Illuminate\Http\Request::create('/admin/alert-events', 'GET'));
t('GET alert-events', $r2->getStatusCode() === 200);
$r3 = $app->handle(Illuminate\Http\Request::create('/dashboard', 'GET'));
t('Dashboard alert section', str_contains($r3->getContent(), 'Alert Aktif'));

echo "PASS:$pass FAIL:$fail\n";
exit($fail > 0 ? 1 : 0);
