<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Alert Log</h2></x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('admin.partials.alerts')
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-2">
                    <select name="state" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">Semua Status</option>
                        @foreach ($states as $state)
                            <option value="{{ $state->value }}" @selected(request('state') === $state->value)>{{ $state->label() }}</option>
                        @endforeach
                    </select>
                    <select name="severity" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">Semua Severity</option>
                        @foreach ($severities as $severity)
                            <option value="{{ $severity->value }}" @selected(request('severity') === $severity->value)>{{ $severity->label() }}</option>
                        @endforeach
                    </select>
                    <select name="device_id" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">Semua Perangkat</option>
                        @foreach ($devices as $device)
                            <option value="{{ $device->id }}" @selected(request('device_id') == $device->id)>{{ $device->name }}</option>
                        @endforeach
                    </select>
                    <x-primary-button type="submit">Filter</x-primary-button>
                </form>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Waktu</th>
                                <th class="px-4 py-2 text-left">Perangkat</th>
                                <th class="px-4 py-2 text-left">Rule</th>
                                <th class="px-4 py-2 text-left">Pesan</th>
                                <th class="px-4 py-2 text-left">Severity</th>
                                <th class="px-4 py-2 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($events as $event)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $event->fired_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-2">{{ $event->device?->name ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ $event->rule?->name ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ $event->message }}</td>
                                    <td class="px-4 py-2">@include('admin.partials.alert-severity-badge', ['severity' => $event->severity])</td>
                                    <td class="px-4 py-2">{{ $event->state->label() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada alert.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $events->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
