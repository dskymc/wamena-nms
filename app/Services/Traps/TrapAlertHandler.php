<?php

namespace App\Services\Traps;

use App\Enums\AlertState;
use App\Enums\SnmpTrapName;
use App\Models\AlertEvent;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\SnmpTrap;
use App\Services\Alerts\AlertNotifier;
use Illuminate\Support\Facades\Cache;

class TrapAlertHandler
{
    public function __construct(
        protected AlertNotifier $notifier,
    ) {}

    public function handle(SnmpTrap $trap, Device $device, ?string $ifIndex): void
    {
        match ($trap->trap_name) {
            SnmpTrapName::LinkDown => $this->handleLinkDown($trap, $device, $ifIndex),
            SnmpTrapName::LinkUp => $this->handleLinkUp($trap, $device, $ifIndex),
            default => null,
        };
    }

    protected function handleLinkDown(SnmpTrap $trap, Device $device, ?string $ifIndex): void
    {
        $rule = $this->linkDownRule();

        if ($rule === null || ! $rule->appliesTo($device)) {
            return;
        }

        if ($this->isCoolingDown($device, $ifIndex, 'link_down')) {
            return;
        }

        if ($this->hasOpenLinkEvent($rule, $device, $ifIndex)) {
            return;
        }

        $detail = $this->encodeDetail($ifIndex, $trap->id);
        $event = AlertEvent::create([
            'alert_rule_id' => $rule->id,
            'device_id' => $device->id,
            'severity' => $rule->severity,
            'state' => AlertState::Open,
            'message' => $trap->summary ?? 'Interface link down (SNMP trap)',
            'detail' => $detail,
            'fired_at' => now(),
        ]);

        $this->notifier->notify($event, $rule, $device);
        $this->markCooldown($device, $ifIndex, 'link_down');
    }

    protected function handleLinkUp(SnmpTrap $trap, Device $device, ?string $ifIndex): void
    {
        $downRule = $this->linkDownRule();
        $upRule = $this->linkUpRule();

        if ($downRule === null) {
            return;
        }

        $events = AlertEvent::query()
            ->where('alert_rule_id', $downRule->id)
            ->where('device_id', $device->id)
            ->where('state', AlertState::Open)
            ->get()
            ->filter(fn (AlertEvent $event) => $this->detailMatchesIfIndex($event->detail, $ifIndex));

        foreach ($events as $event) {
            $event->update([
                'state' => AlertState::Resolved,
                'resolved_at' => now(),
                'detail' => trim(($event->detail ?? '').' | resolved by linkUp trap #'.$trap->id),
            ]);

            if ($upRule !== null && $upRule->is_enabled && $upRule->appliesTo($device)) {
                $recovery = AlertEvent::create([
                    'alert_rule_id' => $upRule->id,
                    'device_id' => $device->id,
                    'severity' => $upRule->severity,
                    'state' => AlertState::Open,
                    'message' => $trap->summary ?? 'Interface link up (SNMP trap)',
                    'detail' => $this->encodeDetail($ifIndex, $trap->id),
                    'fired_at' => now(),
                ]);

                $this->notifier->notify($recovery, $upRule, $device, isRecovery: true);
                $recovery->update([
                    'state' => AlertState::Resolved,
                    'resolved_at' => now(),
                ]);
            }
        }
    }

    protected function linkDownRule(): ?AlertRule
    {
        return AlertRule::query()
            ->where('name', 'Default — SNMP Link Down')
            ->where('is_enabled', true)
            ->first();
    }

    protected function linkUpRule(): ?AlertRule
    {
        return AlertRule::query()
            ->where('name', 'Default — SNMP Link Up')
            ->where('is_enabled', true)
            ->first();
    }

    protected function hasOpenLinkEvent(AlertRule $rule, Device $device, ?string $ifIndex): bool
    {
        return AlertEvent::query()
            ->where('alert_rule_id', $rule->id)
            ->where('device_id', $device->id)
            ->where('state', AlertState::Open)
            ->get()
            ->contains(fn (AlertEvent $event) => $this->detailMatchesIfIndex($event->detail, $ifIndex));
    }

    protected function detailMatchesIfIndex(?string $detail, ?string $ifIndex): bool
    {
        if ($ifIndex === null) {
            return true;
        }

        $decoded = json_decode($detail ?? '', true);

        if (! is_array($decoded)) {
            return false;
        }

        return ($decoded['if_index'] ?? null) === $ifIndex;
    }

    protected function encodeDetail(?string $ifIndex, int $trapId): string
    {
        return json_encode([
            'if_index' => $ifIndex,
            'trap_id' => $trapId,
        ], JSON_THROW_ON_ERROR);
    }

    protected function isCoolingDown(Device $device, ?string $ifIndex, string $type): bool
    {
        $key = sprintf('trap_alert:%s:%s:%s', $type, $device->id, $ifIndex ?? 'any');

        return Cache::has($key);
    }

    protected function markCooldown(Device $device, ?string $ifIndex, string $type): void
    {
        $key = sprintf('trap_alert:%s:%s:%s', $type, $device->id, $ifIndex ?? 'any');
        $minutes = (int) config('nms.traps.alert_cooldown_minutes', 15);

        Cache::put($key, true, now()->addMinutes($minutes));
    }
}
