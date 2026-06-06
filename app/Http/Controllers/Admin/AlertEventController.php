<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AlertSeverity;
use App\Enums\AlertState;
use App\Http\Controllers\Controller;
use App\Models\AlertEvent;
use App\Models\Device;
use Illuminate\View\View;

class AlertEventController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', AlertEvent::class);

        $events = AlertEvent::query()
            ->with(['device', 'rule'])
            ->when(request('state'), fn ($q, $state) => $q->where('state', $state))
            ->when(request('severity'), fn ($q, $severity) => $q->where('severity', $severity))
            ->when(request('device_id'), fn ($q, $id) => $q->where('device_id', $id))
            ->orderByDesc('fired_at')
            ->paginate(20)
            ->withQueryString();

        $devices = Device::orderBy('name')->get();
        $states = AlertState::cases();
        $severities = AlertSeverity::cases();

        return view('admin.alert-events.index', compact('events', 'devices', 'states', 'severities'));
    }
}
