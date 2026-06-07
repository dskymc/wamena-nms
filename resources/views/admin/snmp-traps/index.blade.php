<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">SNMP Trap Log</h2>
        <p class="mt-1 text-sm text-gray-500">Event push dari perangkat jaringan (linkDown/linkUp, dll.)</p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('admin.partials.alerts')

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-2">
                    <select name="device_id" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">Semua Perangkat</option>
                        @foreach ($devices as $device)
                            <option value="{{ $device->id }}" @selected(request('device_id') == $device->id)>{{ $device->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="source_ip" value="{{ request('source_ip') }}" placeholder="Source IP"
                           class="rounded-md border-gray-300 shadow-sm" />
                    <select name="trap_name" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">Semua Trap</option>
                        @foreach ($trapNames as $trapName)
                            <option value="{{ $trapName->value }}" @selected(request('trap_name') === $trapName->value)>{{ $trapName->label() }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-md border-gray-300 shadow-sm" />
                    <div class="flex gap-2">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="flex-1 rounded-md border-gray-300 shadow-sm" />
                        <x-primary-button type="submit">Filter</x-primary-button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Waktu</th>
                                <th class="px-4 py-2 text-left">Perangkat / IP</th>
                                <th class="px-4 py-2 text-left">Trap</th>
                                <th class="px-4 py-2 text-left">Ringkasan</th>
                                <th class="px-4 py-2 text-left"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($traps as $trap)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $trap->received_at->format('d/m/Y H:i:s') }}</td>
                                    <td class="px-4 py-2">
                                        @if ($trap->device)
                                            <div>{{ $trap->device->name }}</div>
                                            <div class="font-mono text-xs text-gray-500">{{ $trap->source_ip }}</div>
                                        @else
                                            <span class="font-mono">{{ $trap->source_ip }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">{{ $trap->trap_name->label() }}</td>
                                    <td class="px-4 py-2">{{ $trap->summary }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('admin.snmp-traps.show', $trap) }}" class="text-indigo-600 hover:text-indigo-800">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada trap diterima. Jalankan <code class="text-xs">php artisan nms:trap-listen</code> dan arahkan perangkat ke port UDP {{ config('nms.traps.port') }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $traps->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
