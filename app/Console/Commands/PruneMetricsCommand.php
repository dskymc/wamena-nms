<?php

namespace App\Console\Commands;

use App\Models\MetricSample;
use Illuminate\Console\Command;

class PruneMetricsCommand extends Command
{
    protected $signature = 'nms:prune-metrics
                            {--days= : Retensi hari (default dari config)}';

    protected $description = 'Hapus sampel metrik lebih lama dari retensi yang dikonfigurasi';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('nms.metrics.retention_days', 7));
        $cutoff = now()->subDays($days);

        $deleted = MetricSample::query()
            ->where('recorded_at', '<', $cutoff)
            ->delete();

        $this->info("Metrik dihapus: {$deleted} sampel (lebih lama dari {$days} hari).");

        return self::SUCCESS;
    }
}
