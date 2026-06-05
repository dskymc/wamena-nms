<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Perangkat</h2>
            @can('create', App\Models\Device::class)
                <a href="{{ route('admin.devices.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Tambah Perangkat
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('admin.partials.alerts')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-2">
                    <x-text-input name="search" type="text" placeholder="Cari nama, IP, atau hostname..." :value="request('search')" />
                    <select name="vendor" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Semua Vendor</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->value }}" @selected(request('vendor') === $vendor->value)>{{ $vendor->label() }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <select name="location_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Semua Lokasi</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected(request('location_id') == $location->id)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                    <x-primary-button type="submit">Filter</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Nama</th>
                                <th class="px-4 py-2 text-left">IP</th>
                                <th class="px-4 py-2 text-left">Vendor</th>
                                <th class="px-4 py-2 text-left">Lokasi</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Terakhir Terlihat</th>
                                <th class="px-4 py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($devices as $device)
                                <tr>
                                    <td class="px-4 py-2 font-medium">{{ $device->name }}</td>
                                    <td class="px-4 py-2 font-mono">{{ $device->management_ip }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs bg-indigo-100 text-indigo-800">
                                            {{ $device->vendor->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ $device->location?->name ?? '—' }}</td>
                                    <td class="px-4 py-2">
                                        <x-device-status-badge :status="$device->status" />
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $device->last_seen_at ? $device->last_seen_at->format('d/m/Y H:i') : '—' }}
                                    </td>
                                    <td class="px-4 py-2 text-right space-x-2">
                                        @can('update', $device)
                                            <a href="{{ route('admin.devices.edit', $device) }}" class="text-indigo-600 hover:underline">Edit</a>
                                        @endcan
                                        @can('delete', $device)
                                            <form action="{{ route('admin.devices.destroy', $device) }}" method="POST" class="inline" onsubmit="return confirm('Hapus perangkat ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada perangkat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $devices->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
