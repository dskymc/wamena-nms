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
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-2">Uji Koneksi SNMP</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Profil: <strong>{{ $device->snmpProfile->name }}</strong> —
                            GET {{ config('nms.sys_descr_oid') }} (sysDescr)
                        </p>
                        <form method="POST" action="{{ route('admin.devices.snmp-test', $device) }}">
                            @csrf
                            <x-primary-button type="submit">Test SNMP</x-primary-button>
                        </form>
                    </div>
                @endif
            @endcan
        </div>
    </div>
</x-app-layout>
