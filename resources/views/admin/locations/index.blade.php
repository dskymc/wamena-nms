<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Lokasi</h2>
            @can('create', App\Models\Location::class)
                <a href="{{ route('admin.locations.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Tambah Lokasi
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('admin.partials.alerts')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="mb-4 flex gap-2">
                    <x-text-input name="search" type="text" placeholder="Cari nama lokasi atau kode..." :value="request('search')" class="w-64" />
                    <x-primary-button type="submit">Cari</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Nama</th>
                                <th class="px-4 py-2 text-left">Induk</th>
                                <th class="px-4 py-2 text-left">Kode</th>
                                <th class="px-4 py-2 text-left">Perangkat</th>
                                <th class="px-4 py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($locations as $location)
                                <tr>
                                    <td class="px-4 py-2">{{ $location->name }}</td>
                                    <td class="px-4 py-2">{{ $location->parent?->name ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ $location->code ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ $location->devices_count }}</td>
                                    <td class="px-4 py-2 text-right space-x-2">
                                        @can('update', $location)
                                            <a href="{{ route('admin.locations.edit', $location) }}" class="text-indigo-600 hover:underline">Edit</a>
                                        @endcan
                                        @can('delete', $location)
                                            <form action="{{ route('admin.locations.destroy', $location) }}" method="POST" class="inline" onsubmit="return confirm('Hapus lokasi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada lokasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $locations->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
