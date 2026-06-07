<?php

namespace App\Console\Commands;

use App\Services\Traps\NmsTrapListener;
use FreeDSx\Snmp\TrapSink;
use Illuminate\Console\Command;

class ListenTrapsCommand extends Command
{
    protected $signature = 'nms:trap-listen
                            {--port= : UDP port (default dari config)}
                            {--ip= : Bind IP (default dari config)}';

    protected $description = 'Dengarkan SNMP trap (v1/v2c/v3) via UDP dan simpan ke snmp_traps';

    public function handle(NmsTrapListener $listener): int
    {
        $port = (int) ($this->option('port') ?: config('nms.traps.port', 1162));
        $ip = (string) ($this->option('ip') ?: config('nms.traps.bind_ip', '0.0.0.0'));

        $this->info("SNMP trap listener dimulai di {$ip}:{$port} (Ctrl+C untuk berhenti)...");

        $sink = new TrapSink($listener, [
            'ip' => $ip,
            'port' => $port,
        ]);

        $sink->listen();

        return self::SUCCESS;
    }
}
