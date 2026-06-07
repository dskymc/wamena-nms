<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TopologyLink;
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

echo "=== UAT Fase 2d ===\n";

t('device_interfaces table', Schema::hasTable('device_interfaces'));
t('topology_links table', Schema::hasTable('topology_links'));
t('topology_oids config', is_array(config('nms.topology_oids.lldp')) && config('nms.topology_oids.lldp.rem_sys_name') !== null);
t('TopologyWalker resolves', app(\App\Services\Topology\TopologyWalker::class) instanceof \App\Services\Topology\TopologyWalker);
t('TopologyMatcher resolves', app(\App\Services\Topology\TopologyMatcher::class) instanceof \App\Services\Topology\TopologyMatcher);
t('TopologySyncService resolves', app(\App\Services\Topology\TopologySyncService::class) instanceof \App\Services\Topology\TopologySyncService);
t('TopologyGraphService resolves', app(\App\Services\Topology\TopologyGraphService::class) instanceof \App\Services\Topology\TopologyGraphService);
t('discover-topology command registered', array_key_exists('nms:discover-topology', Artisan::all()));

auth()->login(User::first());

$r = $app->handle(Illuminate\Http\Request::create('/admin/topology', 'GET'));
t('GET topology page', $r->getStatusCode() === 200);
t('Topology page has map root', str_contains($r->getContent(), 'topology-map-root'));

$rGraph = $app->handle(Illuminate\Http\Request::create('/admin/topology/graph', 'GET'));
t('GET topology graph JSON', $rGraph->getStatusCode() === 200);
$graph = json_decode($rGraph->getContent(), true);
t('Graph has nodes array', is_array($graph['nodes'] ?? null));
t('Graph has edges array', is_array($graph['edges'] ?? null));

$graphService = app(\App\Services\Topology\TopologyGraphService::class);
$sample = $graphService->buildGraph();
t('GraphService returns nodes', array_key_exists('nodes', $sample));
t('TopologyLink model', TopologyLink::query()->count() >= 0);

echo "PASS:$pass FAIL:$fail\n";
exit($fail > 0 ? 1 : 0);
