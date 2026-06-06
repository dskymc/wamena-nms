<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Alert Rules</h2>
            @can('create', App\Models\AlertRule::class)
                <a href="{{ route('admin.alert-rules.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 rounded-md text-xs font-semibold text-white uppercase tracking-widest hover:bg-indigo-700">Tambah Rule</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('admin.partials.alerts')
            <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Nama</th>
                            <th class="px-4 py-2 text-left">Trigger</th>
                            <th class="px-4 py-2 text-left">Scope</th>
                            <th class="px-4 py-2 text-left">Severity</th>
                            <th class="px-4 py-2 text-left">Channel</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($rules as $rule)
                            <tr>
                                <td class="px-4 py-2 font-medium">{{ $rule->name }}</td>
                                <td class="px-4 py-2">{{ $rule->trigger_type->label() }}</td>
                                <td class="px-4 py-2">{{ $rule->scope_type->label() }}</td>
                                <td class="px-4 py-2">@include('admin.partials.alert-severity-badge', ['severity' => $rule->severity])</td>
                                <td class="px-4 py-2">{{ implode(', ', $rule->channels ?? []) }}</td>
                                <td class="px-4 py-2">{{ $rule->is_enabled ? 'Aktif' : 'Nonaktif' }}</td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    @can('update', $rule)
                                        <a href="{{ route('admin.alert-rules.edit', $rule) }}" class="text-indigo-600 hover:underline">Edit</a>
                                    @endcan
                                    @can('delete', $rule)
                                        <form action="{{ route('admin.alert-rules.destroy', $rule) }}" method="POST" class="inline" onsubmit="return confirm('Hapus rule ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada alert rule.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $rules->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
