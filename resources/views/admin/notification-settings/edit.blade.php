<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Pengaturan Notifikasi</h2>
        <p class="mt-1 text-sm text-gray-500">Semua channel alert dikonfigurasi dari menu ini — tidak perlu edit `.env`.</p>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
            @include('admin.partials.alerts')

            <form method="POST" action="{{ route('admin.notification-settings.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-800">Telegram</h3>
                    <div class="mt-4 space-y-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="telegram_enabled" value="1" class="rounded border-gray-300 text-indigo-600"
                                   @checked(old('telegram_enabled', $settings['telegram_enabled'])) />
                            Aktifkan notifikasi Telegram
                        </label>
                        <div>
                            <x-input-label for="telegram_bot_token" value="Bot Token" />
                            <x-text-input id="telegram_bot_token" name="telegram_bot_token" type="password" class="mt-1 block w-full"
                                          placeholder="{{ $settings['has_telegram_bot_token'] ? '•••••••• (kosongkan jika tidak diubah)' : '123456:ABC-DEF...' }}" />
                        </div>
                        <div>
                            <x-input-label for="telegram_chat_id" value="Chat ID" />
                            <x-text-input id="telegram_chat_id" name="telegram_chat_id" type="text" class="mt-1 block w-full"
                                          value="{{ old('telegram_chat_id', $settings['telegram_chat_id']) }}"
                                          placeholder="-1001234567890" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-800">Email (SMTP)</h3>
                    <div class="mt-4 space-y-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="email_enabled" value="1" class="rounded border-gray-300 text-indigo-600"
                                   @checked(old('email_enabled', $settings['email_enabled'])) />
                            Aktifkan notifikasi Email
                        </label>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="email_smtp_host" value="SMTP Host" />
                                <x-text-input id="email_smtp_host" name="email_smtp_host" type="text" class="mt-1 block w-full"
                                              value="{{ old('email_smtp_host', $settings['email_smtp_host']) }}"
                                              placeholder="smtp.gmail.com" />
                            </div>
                            <div>
                                <x-input-label for="email_smtp_port" value="SMTP Port" />
                                <x-text-input id="email_smtp_port" name="email_smtp_port" type="number" class="mt-1 block w-full"
                                              value="{{ old('email_smtp_port', $settings['email_smtp_port']) }}" />
                            </div>
                            <div>
                                <x-input-label for="email_smtp_username" value="SMTP Username" />
                                <x-text-input id="email_smtp_username" name="email_smtp_username" type="text" class="mt-1 block w-full"
                                              value="{{ old('email_smtp_username', $settings['email_smtp_username']) }}" />
                            </div>
                            <div>
                                <x-input-label for="email_smtp_password" value="SMTP Password" />
                                <x-text-input id="email_smtp_password" name="email_smtp_password" type="password" class="mt-1 block w-full"
                                              placeholder="{{ $settings['has_email_smtp_password'] ? '•••••••• (kosongkan jika tidak diubah)' : '' }}" />
                            </div>
                            <div>
                                <x-input-label for="email_smtp_encryption" value="Enkripsi" />
                                <select id="email_smtp_encryption" name="email_smtp_encryption"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach (['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'Tanpa enkripsi'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('email_smtp_encryption', $settings['email_smtp_encryption']) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="email_from_address" value="From Address" />
                                <x-text-input id="email_from_address" name="email_from_address" type="email" class="mt-1 block w-full"
                                              value="{{ old('email_from_address', $settings['email_from_address']) }}" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="email_from_name" value="From Name" />
                                <x-text-input id="email_from_name" name="email_from_name" type="text" class="mt-1 block w-full"
                                              value="{{ old('email_from_name', $settings['email_from_name']) }}" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="email_recipients" value="Penerima Alert (satu per baris atau pisah koma)" />
                                <textarea id="email_recipients" name="email_recipients" rows="3"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('email_recipients', $settings['email_recipients']) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-800">WhatsApp (Fonnte)</h3>
                    <div class="mt-4 space-y-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="fonnte_enabled" value="1" class="rounded border-gray-300 text-indigo-600"
                                   @checked(old('fonnte_enabled', $settings['fonnte_enabled'])) />
                            Aktifkan notifikasi WhatsApp
                        </label>
                        <div>
                            <x-input-label for="fonnte_token" value="Fonnte Token" />
                            <x-text-input id="fonnte_token" name="fonnte_token" type="password" class="mt-1 block w-full"
                                          placeholder="{{ $settings['has_fonnte_token'] ? '•••••••• (kosongkan jika tidak diubah)' : '' }}" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="fonnte_api_url" value="API URL" />
                                <x-text-input id="fonnte_api_url" name="fonnte_api_url" type="url" class="mt-1 block w-full"
                                              value="{{ old('fonnte_api_url', $settings['fonnte_api_url']) }}" />
                            </div>
                            <div>
                                <x-input-label for="fonnte_country_code" value="Kode Negara (normalisasi nomor)" />
                                <x-text-input id="fonnte_country_code" name="fonnte_country_code" type="text" class="mt-1 block w-full"
                                              value="{{ old('fonnte_country_code', $settings['fonnte_country_code']) }}" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="fonnte_noc_numbers" value="Nomor NOC (satu per baris atau pisah koma)" />
                                <textarea id="fonnte_noc_numbers" name="fonnte_noc_numbers" rows="3"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('fonnte_noc_numbers', $settings['fonnte_noc_numbers']) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <x-primary-button>Simpan Pengaturan</x-primary-button>
                </div>
            </form>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-800">Uji Kirim</h3>
                <div class="mt-4 flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('admin.notification-settings.test-telegram') }}">
                        @csrf
                        <x-primary-button type="submit">Test Telegram</x-primary-button>
                    </form>
                    <form method="POST" action="{{ route('admin.notification-settings.test-email') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-md bg-sky-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-sky-700">
                            Test Email
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.notification-settings.test-whatsapp') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-green-700">
                            Test WhatsApp
                        </button>
                    </form>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-800 mb-4">Log WhatsApp Terakhir</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Target</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Waktu</th>
                            </tr>
                        </thead>
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
