<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $unknownStatus = Device::where('status', 'unknown')->count();

        return view('admin.dashboard', compact(
            'devicesByVendor',
            'devicesByLocation',
            'totalDevices',
            'totalLocations',
            'unknownStatus',
        ));
    }
}
