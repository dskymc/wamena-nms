<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SnmpTrapName;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SnmpTrap;
use Illuminate\View\View;

class SnmpTrapController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', SnmpTrap::class);

        $traps = SnmpTrap::query()
            ->with('device')
            ->when(request('device_id'), fn ($q, $id) => $q->where('device_id', $id))
            ->when(request('source_ip'), fn ($q, $ip) => $q->where('source_ip', 'like', '%'.$ip.'%'))
            ->when(request('trap_name'), fn ($q, $name) => $q->where('trap_name', $name))
            ->when(request('date_from'), fn ($q, $date) => $q->whereDate('received_at', '>=', $date))
            ->when(request('date_to'), fn ($q, $date) => $q->whereDate('received_at', '<=', $date))
            ->orderByDesc('received_at')
            ->paginate(25)
            ->withQueryString();

        $devices = Device::orderBy('name')->get();
        $trapNames = SnmpTrapName::cases();

        return view('admin.snmp-traps.index', compact('traps', 'devices', 'trapNames'));
    }

    public function show(SnmpTrap $snmpTrap): View
    {
        $this->authorize('view', $snmpTrap);

        $snmpTrap->load('device');

        return view('admin.snmp-traps.show', compact('snmpTrap'));
    }
}
