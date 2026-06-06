<?php

namespace App\Console\Commands;

use App\Services\Alerts\AlertEvaluator;
use Illuminate\Console\Command;

class EvaluateAlertsCommand extends Command
{
    protected $signature = 'nms:evaluate-alerts';

    protected $description = 'Evaluasi alert rules untuk semua perangkat dimonitor';

    public function handle(AlertEvaluator $evaluator): int
    {
        $count = $evaluator->evaluateAllMonitored();

        $this->info("Evaluasi alert selesai untuk {$count} perangkat.");

        return self::SUCCESS;
    }
}
