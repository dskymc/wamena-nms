<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Perangkat</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('admin.partials.alerts')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.devices.update', $device) }}">
                    @csrf
                    @method('PUT')
                    @include('admin.devices._form')
                    <div class="mt-6 flex gap-2">
                        <x-primary-button>Simpan</x-primary-button>
                        <a href="{{ route('admin.devices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-sm">Batal</a>
                    </div>
                </form>
            </div>

            @can('update', $device)
                @if ($device->snmpProfile)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Monitoring SNMP</h3>
                            <p class="text-sm text-gray-600 mb-1">
                                Profil: <strong>{{ $device->snmpProfile->name }}</strong>
                            </p>
                            <p class="text-sm text-gray-600 mb-4">
                                Status: <x-device-status-badge :status="$device->status" class="ml-1" />
                                @if ($device->last_seen_at)
                                    — terakhir terlihat {{ $device->last_seen_at->format('d/m/Y H:i') }}
                                @endif
                            </p>
                            @if ($device->last_poll_error)
                                <p class="text-sm text-red-600 mb-4">Error terakhir: {{ $device->last_poll_error }}</p>
                            @endif
                            <div class="flex flex-wrap gap-2">
                                @if ($device->vendor->supportsPolling())
                                    <form method="POST" action="{{ route('admin.devices.poll', $device) }}">
                                        @csrf
                                        <x-primary-button type="submit">Poll Sekarang</x-primary-button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.devices.snmp-test', $device) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">
                                        Test SNMP
                                    </button>
                                </form>
                            </div>
                            <p class="mt-3 text-xs text-gray-500">
                                Poll: GET sysUpTime, sysName{{ $device->vendor->supportsPolling() ? ', dan OID vendor' : '' }}.
                                Test SNMP: GET {{ config('nms.sys_descr_oid') }} (sysDescr).
                            </p>
                        </div>
                    </div>
                @endif
            @endcan
        </div>
    </div>
</x-app-layout>
