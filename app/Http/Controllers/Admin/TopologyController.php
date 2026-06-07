<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\TopologyLink;
use App\Services\Topology\TopologyGraphService;
use App\Services\Topology\TopologySyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class TopologyController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', TopologyLink::class);

        $locations = Location::orderBy('name')->get();
        $vendors = config('nms.vendors');

        return view('admin.topology.index', compact('locations', 'vendors'));
    }

    public function graph(Request $request, TopologyGraphService $graphService): JsonResponse
    {
        $this->authorize('viewAny', TopologyLink::class);

        $locationId = $request->integer('location_id') ?: null;
        $vendor = $request->string('vendor')->toString() ?: null;
        $registeredOnly = $request->boolean('registered_only');

        if ($vendor === '') {
            $vendor = null;
        }

        return response()->json(
            $graphService->buildGraph($locationId, $vendor, $registeredOnly)
        );
    }

    public function discover(Request $request, TopologySyncService $syncService): RedirectResponse
    {
        $this->authorize('discover', TopologyLink::class);

        $deviceId = $request->integer('device_id') ?: null;

        if ($deviceId) {
            $device = \App\Models\Device::discoverable()->findOrFail($deviceId);
            $result = $syncService->syncDevice($device);
            $message = $result['error']
                ? "Discovery {$device->name}: {$result['error']}"
                : "Discovery {$device->name}: {$result['links']} link ditemukan.";

            return back()->with($result['error'] ? 'warning' : 'success', $message);
        }

        Artisan::call('nms:discover-topology', [
            '--limit' => config('nms.topology.batch_limit', 50),
        ]);

        return back()->with('success', 'Discovery topologi dijalankan. Refresh peta untuk melihat hasil terbaru.');
    }
}
