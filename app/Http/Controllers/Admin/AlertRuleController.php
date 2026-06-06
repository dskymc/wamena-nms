<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AlertOperator;
use App\Enums\AlertScopeType;
use App\Enums\AlertSeverity;
use App\Enums\AlertTriggerType;
use App\Enums\MetricType;
use App\Enums\NotificationChannel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAlertRuleRequest;
use App\Http\Requests\Admin\UpdateAlertRuleRequest;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AlertRuleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', AlertRule::class);

        $rules = AlertRule::query()
            ->with(['location', 'device'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.alert-rules.index', compact('rules'));
    }

    public function create(): View
    {
        $this->authorize('create', AlertRule::class);

        return view('admin.alert-rules.create', $this->formData());
    }

    public function store(StoreAlertRuleRequest $request): RedirectResponse
    {
        $data = $this->normalize($request->validated());
        $data['created_by'] = $request->user()->id;

        AlertRule::create($data);

        return redirect()->route('admin.alert-rules.index')
            ->with('success', 'Alert rule berhasil ditambahkan.');
    }

    public function edit(AlertRule $alertRule): View
    {
        $this->authorize('update', $alertRule);

        return view('admin.alert-rules.edit', array_merge(
            ['rule' => $alertRule],
            $this->formData(),
        ));
    }

    public function update(UpdateAlertRuleRequest $request, AlertRule $alertRule): RedirectResponse
    {
        $alertRule->update($this->normalize($request->validated()));

        return redirect()->route('admin.alert-rules.index')
            ->with('success', 'Alert rule berhasil diperbarui.');
    }

    public function destroy(AlertRule $alertRule): RedirectResponse
    {
        $this->authorize('delete', $alertRule);

        $alertRule->delete();

        return redirect()->route('admin.alert-rules.index')
            ->with('success', 'Alert rule berhasil dihapus.');
    }

    protected function formData(): array
    {
        return [
            'triggerTypes' => AlertTriggerType::cases(),
            'scopeTypes' => AlertScopeType::cases(),
            'severities' => AlertSeverity::cases(),
            'metrics' => MetricType::cases(),
            'operators' => AlertOperator::cases(),
            'channels' => NotificationChannel::cases(),
            'locations' => Location::orderBy('name')->get(),
            'devices' => Device::orderBy('name')->get(),
        ];
    }

    protected function normalize(array $data): array
    {
        $data['is_enabled'] = request()->boolean('is_enabled');
        $data['notify_on_resolve'] = request()->boolean('notify_on_resolve');

        if (($data['scope_type'] ?? '') !== AlertScopeType::Location->value) {
            $data['location_id'] = null;
        }

        if (($data['scope_type'] ?? '') !== AlertScopeType::Device->value) {
            $data['device_id'] = null;
        }

        if (($data['trigger_type'] ?? '') !== AlertTriggerType::MetricThreshold->value) {
            $data['metric'] = null;
            $data['operator'] = null;
            $data['threshold'] = null;
        }

        return $data;
    }
}
