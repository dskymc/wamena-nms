<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ $device->name }}</h2>
                <p class="mt-1 font-mono text-sm text-gray-500">{{ $device->management_ip }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @include('admin.partials.device-status-badge', ['status' => $device->status])
                @can('update', $device)
                    <a href="{{ route('admin.devices.edit', $device) }}"
                       class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-800 hover:bg-gray-300">
                        Edit
                    </a>
                @endcan
                <a href="{{ route('admin.devices.index') }}"
                   class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-indigo-700">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('admin.partials.alerts')

        <div class="nms-card">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm text-gray-500">Vendor</p>
                    <p class="font-medium text-gray-900">{{ $device->vendor->label() }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Lokasi</p>
                    <p class="font-medium text-gray-900">{{ $device->location?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Terakhir Terlihat</p>
                    <p class="font-medium text-gray-900">
                        {{ $device->last_seen_at ? $device->last_seen_at->format('d/m/Y H:i') : '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Interval Poll</p>
                    <p class="font-medium text-gray-900">{{ $device->poll_interval_sec }} detik</p>
                </div>
            </div>
        </div>

        @if (! $device->vendor->supportsPolling() || ! $device->is_monitored)
            <div class="nms-card">
                <p class="text-sm text-gray-600">
                    Metrik historis hanya tersedia untuk perangkat dengan monitoring aktif
                    (MikroTik, Ruijie, Ubiquiti).
                </p>
            </div>
        @else
            <div id="device-metrics-root"
                 class="space-y-6"
                 data-metrics-url="{{ route('admin.devices.metrics', $device) }}"
                 data-range="24h">

                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-medium text-gray-700">Rentang:</span>
                    <button type="button" data-metric-range="1h"
                            class="rounded-md bg-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-800">1 Jam</button>
                    <button type="button" data-metric-range="24h"
                            class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">24 Jam</button>
                    <button type="button" data-metric-range="7d"
                            class="rounded-md bg-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-800">7 Hari</button>

                    <label class="ml-auto flex items-center gap-2 text-sm text-gray-700">
                        Interface
                        <select id="metric-interface"
                                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Memuat...</option>
                        </select>
                    </label>
                </div>

                <div class="nms-panel-grid">
                    <div class="nms-card">
                        <h3 class="mb-4 text-base font-semibold text-gray-800">CPU (%)</h3>
                        <div class="h-64">
                            <canvas id="cpu-chart"></canvas>
                        </div>
                    </div>
                    <div class="nms-card">
                        <h3 class="mb-4 text-base font-semibold text-gray-800">Memori (%)</h3>
                        <div class="h-64">
                            <canvas id="memory-chart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="nms-card">
                    <h3 class="mb-4 text-base font-semibold text-gray-800">Traffic Interface (bps)</h3>
                    <div class="h-72">
                        <canvas id="traffic-chart"></canvas>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">
                        Traffic dihitung dari delta counter IF-MIB antar poll. Poll pertama tidak menampilkan traffic.
                    </p>
                </div>
            </div>
        @endif
    </div>

    @if ($device->vendor->supportsPolling() && $device->is_monitored)
        @push('scripts')
            @vite('resources/js/device-metrics.js')
        @endpush
    @endif
</x-app-layout>
