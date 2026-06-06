<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
            <p class="mt-1 text-sm text-gray-500">{{ config('nms.branding.full_name') }}</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('admin.partials.alerts')

        <div class="nms-stat-grid">
            <div class="nms-card">
                <p class="text-sm text-gray-500">Total Perangkat</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalDevices }}</p>
            </div>
            <div class="nms-card">
                <p class="text-sm text-gray-500">Total Lokasi</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalLocations }}</p>
            </div>
        </div>

        <div class="nms-stat-grid">
            <div class="nms-card">
                <p class="text-sm text-gray-500">Up</p>
                <p class="mt-2 text-3xl font-bold text-green-600">{{ $upStatus }}</p>
            </div>
            <div class="nms-card">
                <p class="text-sm text-gray-500">Down</p>
                <p class="mt-2 text-3xl font-bold text-red-600">{{ $downStatus }}</p>
            </div>
            <div class="nms-card">
                <p class="text-sm text-gray-500">Unknown</p>
                <p class="mt-2 text-3xl font-bold text-gray-500">{{ $unknownStatus }}</p>
            </div>
        </div>

        <div class="nms-stat-grid">
            <div class="nms-card">
                <p class="text-sm text-gray-500">Alert Aktif</p>
                <p class="mt-2 text-3xl font-bold text-orange-600">{{ $openAlerts }}</p>
            </div>
        </div>

        <div class="nms-panel-grid">
            <div class="nms-card">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800">Alert Aktif</h3>
                    @can('alert_events.view')
                        <a href="{{ route('admin.alert-events.index', ['state' => 'open']) }}" class="text-sm text-indigo-600 hover:underline">Lihat semua</a>
                    @endcan
                </div>
                @forelse ($recentAlerts as $alert)
                    <div class="border-b border-gray-100 py-2.5 last:border-0">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-medium text-gray-800">{{ $alert->message }}</span>
                            @include('admin.partials.alert-severity-badge', ['severity' => $alert->severity])
                        </div>
                        <p class="text-xs text-gray-500">{{ $alert->device?->name }} — {{ $alert->fired_at->format('d/m/Y H:i') }}</p>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-500">Tidak ada alert aktif.</p>
                @endforelse
            </div>

            <div class="nms-card">
                <h3 class="mb-4 text-base font-semibold text-gray-800">Perangkat Down</h3>
                @forelse ($downDevices as $device)
                    <div class="flex items-center justify-between border-b border-gray-100 py-2.5 last:border-0">
                        <div>
                            <a href="{{ route('admin.devices.edit', $device) }}" class="text-sm font-medium text-gray-800 hover:text-indigo-600">
                                {{ $device->name }}
                            </a>
                            <p class="font-mono text-xs text-gray-500">{{ $device->management_ip }}</p>
                        </div>
                        <span class="text-xs text-gray-500">
                            {{ $device->last_seen_at ? $device->last_seen_at->format('d/m/Y H:i') : 'Belum pernah' }}
                        </span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-500">Tidak ada perangkat down.</p>
                @endforelse
            </div>

            <div class="nms-card">
                <h3 class="mb-4 text-base font-semibold text-gray-800">Perangkat per Vendor</h3>
                @forelse ($devicesByVendor as $vendor => $total)
                    <div class="flex items-center justify-between border-b border-gray-100 py-2.5 last:border-0">
                        <span class="text-sm text-gray-700">{{ config("nms.vendors.{$vendor}.label", ucfirst($vendor)) }}</span>
                        <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-sm font-semibold text-indigo-700">{{ $total }}</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-500">Belum ada perangkat terdaftar.</p>
                @endforelse
            </div>
        </div>

        <div class="nms-card">
            <h3 class="mb-4 text-base font-semibold text-gray-800">Perangkat per Lokasi</h3>
            @forelse ($devicesByLocation as $location)
                <div class="flex items-center justify-between border-b border-gray-100 py-2.5 last:border-0">
                    <span class="text-sm text-gray-700">{{ $location->name }}</span>
                    <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-sm font-semibold text-indigo-700">{{ $location->devices_count }}</span>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-gray-500">Belum ada perangkat di lokasi.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
