<?php

namespace App\Console\Commands;

use App\Models\SnmpTrap;
use Illuminate\Console\Command;

class PruneTrapsCommand extends Command
{
    protected $signature = 'nms:prune-traps
                            {--days= : Retensi hari (default dari config)}';

    protected $description = 'Hapus log SNMP trap lebih lama dari retensi yang dikonfigurasi';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('nms.traps.retention_days', 30));
        $cutoff = now()->subDays($days);

        $deleted = SnmpTrap::query()
            ->where('received_at', '<', $cutoff)
            ->delete();

        $this->info("SNMP trap dihapus: {$deleted} entri (lebih lama dari {$days} hari).");

        return self::SUCCESS;
    }
}
