<?php

namespace App\Services\Alerts;

use App\Enums\AlertState;
use App\Enums\AlertTriggerType;
use App\Enums\DeviceStatus;
use App\Enums\MetricType;
use App\Models\AlertEvent;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\MetricSample;

class AlertEvaluator
{
    public function __construct(
        protected AlertNotifier $notifier,
    ) {}

    public function evaluateDevice(Device $device, ?DeviceStatus $previousStatus): void
    {
        if (! $device->is_monitored) {
            return;
        }

        $device->refresh();
        $rules = AlertRule::query()->where('is_enabled', true)->get();

        foreach ($rules as $rule) {
            if (! $rule->appliesTo($device)) {
                continue;
            }

            match ($rule->trigger_type) {
                AlertTriggerType::DeviceDown => $this->evaluateDeviceDown($device, $previousStatus, $rule),
                AlertTriggerType::DeviceRecovery => $this->evaluateDeviceRecovery($device, $previousStatus, $rule),
                AlertTriggerType::MetricThreshold => $this->evaluateMetricThreshold($device, $rule),
            };
        }
    }

    protected function evaluateDeviceDown(Device $device, ?DeviceStatus $previousStatus, AlertRule $rule): void
    {
        if ($device->status !== DeviceStatus::Down || $previousStatus === DeviceStatus::Down) {
            return;
        }

        if ($this->hasOpenEvent($rule, $device)) {
            return;
        }

        $event = $this->fireEvent($rule, $device, 'Perangkat DOWN', $device->last_poll_error);
        $this->notifier->notify($event, $rule, $device);
    }

    protected function evaluateDeviceRecovery(Device $device, ?DeviceStatus $previousStatus, AlertRule $rule): void
    {
        if ($device->status !== DeviceStatus::Up || $previousStatus !== DeviceStatus::Down) {
            return;
        }

        $this->resolveOpenEvents($rule, $device, 'Perangkat kembali UP');

        if (! $rule->notify_on_resolve) {
            return;
        }

        $event = $this->fireEvent($rule, $device, 'Recovery — Perangkat UP');
        $this->notifier->notify($event, $rule, $device, isRecovery: true);
    }

    protected function evaluateMetricThreshold(Device $device, AlertRule $rule): void
    {
        if ($rule->metric === null || $rule->operator === null || $rule->threshold === null) {
            return;
        }

        if ($device->status !== DeviceStatus::Up) {
            $this->resolveOpenEvents($rule, $device, 'Metrik resolved — perangkat tidak up');

            return;
        }

        $samples = MetricSample::query()
            ->where('device_id', $device->id)
            ->where('metric', $rule->metric)
            ->orderByDesc('recorded_at')
            ->limit($rule->consecutive_breaches)
            ->get();

        if ($samples->count() < $rule->consecutive_breaches) {
            return;
        }

        $breaching = $samples->every(fn (MetricSample $s) => $rule->operator->compare((float) $s->value, (float) $rule->threshold));

        if ($breaching) {
            if ($this->hasOpenEvent($rule, $device)) {
                return;
            }

            $latest = $samples->first();
            $event = $this->fireEvent(
                $rule,
                $device,
                sprintf('%s melewati threshold', $rule->metric->label()),
                metricValue: (float) $latest->value,
            );
            $this->notifier->notify($event, $rule, $device);

            return;
        }

        $this->resolveOpenEvents($rule, $device, 'Metrik kembali normal');
    }

    protected function hasOpenEvent(AlertRule $rule, Device $device): bool
    {
        return AlertEvent::query()
            ->where('alert_rule_id', $rule->id)
            ->where('device_id', $device->id)
            ->where('state', AlertState::Open)
            ->exists();
    }

    protected function fireEvent(
        AlertRule $rule,
        Device $device,
        string $message,
        ?string $detail = null,
        ?float $metricValue = null,
    ): AlertEvent {
        return AlertEvent::create([
            'alert_rule_id' => $rule->id,
            'device_id' => $device->id,
            'severity' => $rule->severity,
            'state' => AlertState::Open,
            'message' => $message,
            'detail' => $detail,
            'metric_value' => $metricValue,
            'fired_at' => now(),
        ]);
    }

    protected function resolveOpenEvents(AlertRule $rule, Device $device, string $reason): void
    {
        $events = AlertEvent::query()
            ->where('alert_rule_id', $rule->id)
            ->where('device_id', $device->id)
            ->where('state', AlertState::Open)
            ->get();

        foreach ($events as $event) {
            $event->update([
                'state' => AlertState::Resolved,
                'resolved_at' => now(),
                'detail' => trim(($event->detail ?? '').' | '.$reason),
            ]);

            if ($rule->notify_on_resolve) {
                $this->notifier->notify($event, $rule, $device, isRecovery: true);
            }
        }
    }

    public function evaluateAllMonitored(): int
    {
        $count = 0;
        $devices = Device::query()->where('is_monitored', true)->get();

        foreach ($devices as $device) {
            $this->evaluateDevice($device, $device->status);
            $count++;
        }

        return $count;
    }
}
