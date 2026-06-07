<?php

namespace Database\Seeders;

use App\Enums\AlertScopeType;
use App\Enums\AlertSeverity;
use App\Enums\AlertTriggerType;
use App\Enums\NotificationChannel;
use App\Models\AlertRule;
use Illuminate\Database\Seeder;

class AlertRuleSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'name' => 'Default — Perangkat Down',
                'trigger_type' => AlertTriggerType::DeviceDown,
                'severity' => AlertSeverity::Critical,
                'channels' => ['telegram', 'whatsapp', 'email'],
            ],
            [
                'name' => 'Default — Recovery Up',
                'trigger_type' => AlertTriggerType::DeviceRecovery,
                'severity' => AlertSeverity::Info,
                'channels' => ['telegram', 'whatsapp'],
                'notify_on_resolve' => true,
            ],
            [
                'name' => 'Default — CPU > 80%',
                'trigger_type' => AlertTriggerType::MetricThreshold,
                'severity' => AlertSeverity::Warning,
                'metric' => 'cpu',
                'operator' => 'gt',
                'threshold' => 80,
                'consecutive_breaches' => 2,
                'channels' => ['telegram', 'email'],
            ],
            [
                'name' => 'Default — Memori > 90%',
                'trigger_type' => AlertTriggerType::MetricThreshold,
                'severity' => AlertSeverity::Warning,
                'metric' => 'memory_percent',
                'operator' => 'gt',
                'threshold' => 90,
                'consecutive_breaches' => 2,
                'channels' => ['telegram', 'email'],
            ],
            [
                'name' => 'Default — SNMP Link Down',
                'trigger_type' => AlertTriggerType::SnmpTrap,
                'severity' => AlertSeverity::Warning,
                'channels' => ['telegram', 'whatsapp', 'email'],
            ],
            [
                'name' => 'Default — SNMP Link Up',
                'trigger_type' => AlertTriggerType::SnmpTrap,
                'severity' => AlertSeverity::Info,
                'channels' => ['telegram', 'whatsapp'],
            ],
        ];

        foreach ($defaults as $rule) {
            AlertRule::firstOrCreate(
                ['name' => $rule['name']],
                array_merge([
                    'is_enabled' => true,
                    'scope_type' => AlertScopeType::Global,
                    'cooldown_minutes' => 15,
                    'notify_on_resolve' => false,
                    'consecutive_breaches' => 1,
                ], $rule),
            );
        }
    }
}
