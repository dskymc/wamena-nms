<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Pengaturan Notifikasi</h2></x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('admin.partials.alerts')

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.notification-settings.update') }}">
                    @csrf @method('PUT')
                    <div class="space-y-4">
                        <label class="flex items-center gap-2"><input type="checkbox" name="telegram_enabled" value="1" class="rounded border-gray-300 text-indigo-600" @checked($settings['telegram_enabled']) /> Telegram aktif</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="email_enabled" value="1" class="rounded border-gray-300 text-indigo-600" @checked($settings['email_enabled']) /> Email aktif</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="fonnte_enabled" value="1" class="rounded border-gray-300 text-indigo-600" @checked($settings['fonnte_enabled']) /> WhatsApp (Fonnte) aktif</label>
                    </div>
                    <p class="mt-4 text-xs text-gray-500">Token/credential di `.env`: NMS_TELEGRAM_*, FONNTE_*, NMS_ALERT_EMAIL_*</p>
                    <div class="mt-6"><x-primary-button>Simpan</x-primary-button></div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.notification-settings.test-telegram') }}">@csrf<x-primary-button type="submit">Test Telegram</x-primary-button></form>
                <form method="POST" action="{{ route('admin.notification-settings.test-whatsapp') }}">@csrf<button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 rounded-md text-xs font-semibold text-white uppercase tracking-widest hover:bg-green-700">Test WhatsApp</button></form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">Log WhatsApp Terakhir</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50"><tr><th class="px-4 py-2 text-left">Target</th><th class="px-4 py-2 text-left">Status</th><th class="px-4 py-2 text-left">Waktu</th></tr></thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($waMessages as $msg)
                                <tr>
                                    <td class="px-4 py-2 font-mono">{{ $msg->target }}</td>
                                    <td class="px-4 py-2">{{ $msg->status }}</td>
                                    <td class="px-4 py-2">{{ $msg->sent_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Belum ada log WA.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
