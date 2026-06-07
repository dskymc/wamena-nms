<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AlertRule;
use App\Models\SnmpTrap;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

$pass = 0;
$fail = 0;

function t(string $n, bool $ok): void
{
    global $pass, $fail;
    echo ($ok ? '[PASS]' : '[FAIL]')." $n\n";
    $ok ? $pass++ : $fail++;
}

echo "=== UAT Fase 2e ===\n";

t('snmp_traps table', Schema::hasTable('snmp_traps'));
t('traps config', is_array(config('nms.traps')) && config('nms.traps.port') === 1162);
t('TrapParser resolves', app(\App\Services\Traps\TrapParser::class) instanceof \App\Services\Traps\TrapParser);
t('TrapProcessor resolves', app(\App\Services\Traps\TrapProcessor::class) instanceof \App\Services\Traps\TrapProcessor);
t('NmsTrapListener resolves', app(\App\Services\Traps\NmsTrapListener::class) instanceof \App\Services\Traps\NmsTrapListener);
t('TrapAlertHandler resolves', app(\App\Services\Traps\TrapAlertHandler::class) instanceof \App\Services\Traps\TrapAlertHandler);
t('trap-listen command registered', array_key_exists('nms:trap-listen', Artisan::all()));
t('prune-traps command registered', array_key_exists('nms:prune-traps', Artisan::all()));

auth()->login(User::first());

$r = $app->handle(Illuminate\Http\Request::create('/admin/snmp-traps', 'GET'));
t('GET snmp-traps page', $r->getStatusCode() === 200);
t('SNMP traps page content', str_contains($r->getContent(), 'SNMP Trap Log'));

$trap = SnmpTrap::create([
    'source_ip' => '127.0.0.1',
    'snmp_version' => '2c',
    'trap_oid' => '1.3.6.1.6.3.1.1.5.3',
    'trap_name' => 'linkDown',
    'varbinds' => [['oid' => '1.3.6.1.2.1.2.2.1.1.1', 'value' => '1']],
    'summary' => 'UAT test trap',
    'received_at' => now(),
]);

$rShow = $app->handle(Illuminate\Http\Request::create('/admin/snmp-traps/'.$trap->id, 'GET'));
t('GET snmp-trap detail', $rShow->getStatusCode() === 200);

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\AlertRuleSeeder', '--force' => true]);
t('SNMP trap alert rules seeded', AlertRule::where('name', 'Default — SNMP Link Down')->exists());

echo "PASS:$pass FAIL:$fail\n";
exit($fail > 0 ? 1 : 0);
