<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLocationRequest;
use App\Http\Requests\Admin\UpdateLocationRequest;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Location::class);

        $locations = Location::query()
            ->with('parent')
            ->withCount('devices')
            ->when(request('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.locations.index', compact('locations'));
    }

    public function create(): View
    {
        $this->authorize('create', Location::class);

        $parents = Location::orderBy('name')->get();

        return view('admin.locations.create', compact('parents'));
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        Location::create($request->validated());

        return redirect()->route('admin.locations.index')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function edit(Location $location): View
    {
        $this->authorize('update', $location);

        $parents = Location::where('id', '!=', $location->id)->orderBy('name')->get();

        return view('admin.locations.edit', compact('location', 'parents'));
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        $location->update($request->validated());

        return redirect()->route('admin.locations.index')
            ->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        $this->authorize('delete', $location);

        if ($location->children()->exists()) {
            return back()->with('error', 'Lokasi masih memiliki sub-lokasi.');
        }

        if ($location->devices()->exists()) {
            return back()->with('error', 'Lokasi masih digunakan oleh perangkat.');
        }

        $location->delete();

        return redirect()->route('admin.locations.index')
            ->with('success', 'Lokasi berhasil dihapus.');
    }
}
