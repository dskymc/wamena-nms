<?php

namespace App\Http\Requests\Admin;

use App\Enums\AlertOperator;
use App\Enums\AlertScopeType;
use App\Enums\AlertSeverity;
use App\Enums\AlertTriggerType;
use App\Enums\MetricType;
use App\Enums\NotificationChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAlertRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('alert_rules.create');
    }

    public function rules(): array
    {
        return $this->baseRules();
    }

    protected function baseRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'is_enabled' => ['boolean'],
            'trigger_type' => ['required', Rule::enum(AlertTriggerType::class)],
            'scope_type' => ['required', Rule::enum(AlertScopeType::class)],
            'location_id' => ['nullable', 'exists:locations,id'],
            'device_id' => ['nullable', 'exists:devices,id'],
            'metric' => ['nullable', Rule::enum(MetricType::class)],
            'operator' => ['nullable', Rule::enum(AlertOperator::class)],
            'threshold' => ['nullable', 'numeric', 'min:0'],
            'consecutive_breaches' => ['nullable', 'integer', 'min:1', 'max:10'],
            'severity' => ['required', Rule::enum(AlertSeverity::class)],
            'cooldown_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => [Rule::enum(NotificationChannel::class)],
            'notify_on_resolve' => ['boolean'],
        ];
    }
}
