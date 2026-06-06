<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeviceStatus;
use App\Http\Controllers\Controller;
use App\Models\AlertEvent;
use App\Models\Device;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $devicesByVendor = Device::query()
            ->select('vendor', DB::raw('count(*) as total'))
            ->groupBy('vendor')
            ->pluck('total', 'vendor');

        $devicesByLocation = Location::query()
            ->withCount('devices')
            ->having('devices_count', '>', 0)
            ->orderByDesc('devices_count')
            ->limit(10)
            ->get();

        $totalDevices = Device::count();
        $totalLocations = Location::count();
        $upStatus = Device::where('status', DeviceStatus::Up)->count();
        $downStatus = Device::where('status', DeviceStatus::Down)->count();
        $unknownStatus = Device::where('status', DeviceStatus::Unknown)->count();

        $downDevices = Device::query()
            ->where('status', DeviceStatus::Down)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get(['id', 'name', 'management_ip', 'last_seen_at']);

        $openAlerts = AlertEvent::open()->count();
        $recentAlerts = AlertEvent::query()
            ->with('device')
            ->open()
            ->orderByDesc('fired_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'devicesByVendor',
            'devicesByLocation',
            'totalDevices',
            'totalLocations',
            'upStatus',
            'downStatus',
            'unknownStatus',
            'downDevices',
            'openAlerts',
            'recentAlerts',
        ));
    }
}
