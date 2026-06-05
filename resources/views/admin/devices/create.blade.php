<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Perangkat</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @include('admin.partials.alerts')

                <form method="POST" action="{{ route('admin.devices.store') }}">
                    @csrf
                    @include('admin.devices._form', ['device' => null])
                    <div class="mt-6 flex gap-2">
                        <x-primary-button>Simpan</x-primary-button>
                        <a href="{{ route('admin.devices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-sm">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
