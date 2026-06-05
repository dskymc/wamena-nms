<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeviceStatus;
use App\Enums\DeviceVendor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDeviceRequest;
use App\Http\Requests\Admin\UpdateDeviceRequest;
use App\Models\Device;
use App\Models\Location;
use App\Models\SnmpProfile;
use App\Services\Snmp\DevicePollService;
use App\Services\Snmp\SnmpClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Device::class);

        $devices = Device::query()
            ->with(['location', 'snmpProfile'])
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('management_ip', 'like', "%{$search}%")
                        ->orWhere('hostname', 'like', "%{$search}%");
                });
            })
            ->when(request('vendor'), fn ($q, $vendor) => $q->where('vendor', $vendor))
            ->when(request('location_id'), fn ($q, $id) => $q->where('location_id', $id))
            ->when(request('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $locations = Location::orderBy('name')->get();
        $vendors = DeviceVendor::cases();
        $statuses = DeviceStatus::cases();

        return view('admin.devices.index', compact('devices', 'locations', 'vendors', 'statuses'));
    }

    public function create(): View
    {
        $this->authorize('create', Device::class);

        return view('admin.devices.create', [
            'locations' => Location::orderBy('name')->get(),
            'snmpProfiles' => SnmpProfile::orderBy('name')->get(),
            'vendors' => DeviceVendor::cases(),
        ]);
    }

    public function store(StoreDeviceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $data['is_monitored'] = $request->boolean('is_monitored');

        Device::create($data);

        return redirect()->route('admin.devices.index')
            ->with('success', 'Perangkat berhasil ditambahkan.');
    }

    public function edit(Device $device): View
    {
        $this->authorize('update', $device);

        return view('admin.devices.edit', [
            'device' => $device,
            'locations' => Location::orderBy('name')->get(),
            'snmpProfiles' => SnmpProfile::orderBy('name')->get(),
            'vendors' => DeviceVendor::cases(),
        ]);
    }

    public function update(UpdateDeviceRequest $request, Device $device): RedirectResponse
    {
        $data = $request->validated();
        $data['is_monitored'] = $request->boolean('is_monitored');

        $device->update($data);

        return redirect()->route('admin.devices.index')
            ->with('success', 'Perangkat berhasil diperbarui.');
    }

    public function destroy(Device $device): RedirectResponse
    {
        $this->authorize('delete', $device);

        $device->delete();

        return redirect()->route('admin.devices.index')
            ->with('success', 'Perangkat berhasil dihapus.');
    }

    public function snmpTest(Device $device, SnmpClient $snmpClient): RedirectResponse
    {
        $this->authorize('update', $device);

        if (! $device->snmpProfile) {
            return back()->with('error', 'Perangkat belum memiliki profil SNMP.');
        }

        $result = $snmpClient->testConnection($device->snmpProfile, $device->management_ip);

        if ($result->success) {
            return back()->with('success', 'SNMP OK: '.$result->sysDescr);
        }

        return back()->with('error', 'SNMP gagal: '.$result->error);
    }

    public function poll(Device $device, DevicePollService $pollService): RedirectResponse
    {
        $this->authorize('update', $device);

        if (! $device->snmpProfile) {
            return back()->with('error', 'Perangkat belum memiliki profil SNMP.');
        }

        if (! $device->vendor->supportsPolling()) {
            return back()->with('error', 'Polling belum didukung untuk vendor '.$device->vendor->label().'.');
        }

        $result = $pollService->pollAndUpdate($device);
        $device->refresh();

        if ($result->success) {
            return back()->with(
                'success',
                'Poll berhasil — status Up. sysUpTime: '.$result->sysUpTime
                .($result->sysName ? ', sysName: '.$result->sysName : '')
            );
        }

        return back()->with('error', 'Poll gagal — status Down. '.$result->error);
    }
}
