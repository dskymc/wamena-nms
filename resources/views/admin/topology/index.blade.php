<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Peta Topologi</h2>
                <p class="mt-1 text-sm text-gray-500">Relasi port-to-port dari discovery LLDP/CDP</p>
            </div>
            @can('discover', \App\Models\TopologyLink::class)
                <form method="POST" action="{{ route('admin.topology.discover') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-indigo-700">
                        Discover Sekarang
                    </button>
                </form>
            @endcan
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('admin.partials.alerts')

        <div class="nms-card">
            <form id="topology-filters" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="location_id" class="block text-sm font-medium text-gray-700">Lokasi</label>
                    <select id="location_id" name="location_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua lokasi</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="vendor" class="block text-sm font-medium text-gray-700">Vendor</label>
                    <select id="vendor" name="vendor"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua vendor</option>
                        @foreach ($vendors as $key => $vendor)
                            @if ($key !== 'other')
                                <option value="{{ $key }}">{{ $vendor['label'] }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" id="registered_only" name="registered_only" value="1"
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        Hanya link ke perangkat terdaftar
                    </label>
                </div>
                <div class="flex items-end">
                    <button type="button" id="topology-refresh"
                            class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-800 hover:bg-gray-300">
                        Muat Ulang
                    </button>
                </div>
            </form>
        </div>

        <div class="nms-card">
            <div class="mb-4 flex flex-wrap gap-4 text-xs text-gray-600">
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-emerald-500"></span> Up</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-red-500"></span> Down</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-gray-400"></span> Unknown status</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-slate-500"></span> Neighbor tidak terdaftar</span>
            </div>
            <div id="topology-map-root"
                 class="relative h-[560px] w-full overflow-hidden rounded-lg border border-gray-200 bg-white"
                 data-graph-url="{{ route('admin.topology.graph') }}">
                <div id="topology-network" class="h-full w-full"></div>
                <p id="topology-empty" class="absolute inset-0 hidden items-center justify-center text-sm text-gray-500">
                    Belum ada link topologi. Jalankan discovery setelah LLDP diaktifkan di perangkat.
                </p>
            </div>
        </div>

        <div id="topology-detail" class="nms-card hidden">
            <h3 class="text-sm font-semibold text-gray-800">Detail Node</h3>
            <div id="topology-detail-body" class="mt-2 text-sm text-gray-600"></div>
        </div>
    </div>

    @vite(['resources/js/topology-map.js'])
</x-app-layout>
