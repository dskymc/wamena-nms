<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Detail SNMP Trap</h2>
                <p class="mt-1 font-mono text-sm text-gray-500">{{ $snmpTrap->source_ip }} — {{ $snmpTrap->received_at->format('d/m/Y H:i:s') }}</p>
            </div>
            <a href="{{ route('admin.snmp-traps.index') }}"
               class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-indigo-700">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-sm text-gray-500">Trap</p>
                        <p class="font-medium text-gray-900">{{ $snmpTrap->trap_name->label() }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">SNMP Version</p>
                        <p class="font-medium text-gray-900">{{ $snmpTrap->snmp_version }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-sm text-gray-500">Trap OID / Enterprise</p>
                        <p class="font-mono text-sm text-gray-900">{{ $snmpTrap->trap_oid ?? '—' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-sm text-gray-500">Ringkasan</p>
                        <p class="font-medium text-gray-900">{{ $snmpTrap->summary ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Perangkat</p>
                        @if ($snmpTrap->device)
                            <a href="{{ route('admin.devices.show', $snmpTrap->device) }}" class="font-medium text-indigo-600 hover:text-indigo-800">
                                {{ $snmpTrap->device->name }}
                            </a>
                        @else
                            <p class="font-medium text-gray-900">Tidak cocok inventori</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">Varbinds</h3>
                @if ($snmpTrap->varbinds)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">OID</th>
                                    <th class="px-4 py-2 text-left">Value</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($snmpTrap->varbinds as $varbind)
                                    <tr>
                                        <td class="px-4 py-2 font-mono text-xs">{{ $varbind['oid'] ?? '' }}</td>
                                        <td class="px-4 py-2 font-mono text-xs">{{ $varbind['value'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Tidak ada varbind tersimpan.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
