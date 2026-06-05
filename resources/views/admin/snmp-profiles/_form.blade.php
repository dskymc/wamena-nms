@php
    $profile = $profile ?? null;
    $isEdit = $profile !== null;
    $selectedVersion = old('version', $profile?->version?->value ?? '2c');
    $selectedSecurity = old('security_level', $profile?->security_level?->value ?? 'authPriv');
@endphp

<div class="space-y-4" id="snmp-profile-form">
    <div>
        <x-input-label for="name" value="Nama Profil" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $profile->name ?? '')" placeholder="Contoh: SNMP MikroTik Kantor Pusat" required />
        <x-form-hint text="Nama mudah diingat untuk membedakan profil SNMP antar site atau vendor." />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="version" value="Versi SNMP" />
        <select id="version" name="version" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            @foreach ($versions as $ver)
                <option value="{{ $ver->value }}" @selected($selectedVersion === $ver->value)>{{ $ver->label() }}</option>
            @endforeach
        </select>
        <x-form-hint text="Pilih SNMPv2c jika perangkat memakai community string. Pilih SNMPv3 untuk autentikasi lebih aman." />
        <x-input-error :messages="$errors->get('version')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <x-input-label for="port" value="Port" />
            <x-text-input id="port" name="port" type="number" class="mt-1 block w-full" :value="old('port', $profile->port ?? 161)" placeholder="161" required />
            <x-form-hint text="Port SNMP standar: 161." />
            <x-input-error :messages="$errors->get('port')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="timeout_ms" value="Timeout (ms)" />
            <x-text-input id="timeout_ms" name="timeout_ms" type="number" class="mt-1 block w-full" :value="old('timeout_ms', $profile->timeout_ms ?? 5000)" placeholder="5000" required />
            <x-form-hint text="Batas waktu tunggu respons perangkat (milidetik)." />
            <x-input-error :messages="$errors->get('timeout_ms')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="retries" value="Retries" />
            <x-text-input id="retries" name="retries" type="number" class="mt-1 block w-full" :value="old('retries', $profile->retries ?? 3)" placeholder="3" required />
            <x-form-hint text="Berapa kali dicoba ulang jika permintaan SNMP gagal." />
            <x-input-error :messages="$errors->get('retries')" class="mt-2" />
        </div>
    </div>

    <div id="snmp-v2c-fields" class="space-y-4" @if($selectedVersion !== '2c') hidden @endif>
        <div>
            <x-input-label for="community" value="Community String" />
            <input
                type="text"
                id="community"
                name="community"
                value="{{ old('community') }}"
                placeholder="{{ $isEdit ? 'Kosongkan jika tidak diubah' : 'Contoh: public, private, nms-read' }}"
                autocomplete="off"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                @if(!$isEdit) required @endif
            >
            <x-form-hint text="Harus sama dengan community yang diaktifkan di perangkat (MikroTik/Ruijie/UniFi)." />
            @if ($isEdit)
                <p class="mt-1 text-xs text-gray-500">Biarkan kosong untuk mempertahankan community yang tersimpan.</p>
            @endif
            <x-input-error :messages="$errors->get('community')" class="mt-2" />
        </div>
    </div>

    <div id="snmp-v3-fields" class="space-y-4 border-t pt-4" @if($selectedVersion !== '3') hidden @endif>
        <div>
            <x-input-label for="security_level" value="Security Level" />
            <select id="security_level" name="security_level" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                @foreach ($securityLevels as $level)
                    <option value="{{ $level->value }}" @selected($selectedSecurity === $level->value)>{{ $level->label() }}</option>
                @endforeach
            </select>
            <x-form-hint text="Sesuaikan dengan konfigurasi SNMPv3 di perangkat jaringan." />
            <x-input-error :messages="$errors->get('security_level')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="username" value="Username" />
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $profile->username ?? '')" placeholder="Contoh: snmpuser" autocomplete="off" />
            <x-form-hint text="Username SNMPv3 yang sudah dibuat di perangkat." />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="context_name" value="Context Name" />
            <x-text-input id="context_name" name="context_name" type="text" class="mt-1 block w-full" :value="old('context_name', $profile->context_name ?? '')" placeholder="Opsional — kosongkan jika tidak dipakai" autocomplete="off" />
            <x-input-error :messages="$errors->get('context_name')" class="mt-2" />
        </div>

        <div id="snmp-v3-auth-fields" class="grid grid-cols-1 md:grid-cols-2 gap-4"
             @if(! in_array($selectedSecurity, ['authNoPriv', 'authPriv'])) hidden @endif>
            <div>
                <x-input-label for="auth_protocol" value="Auth Protocol" />
                <select id="auth_protocol" name="auth_protocol" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    @foreach (config('nms.snmp_auth_protocols') as $label => $value)
                        <option value="{{ $label }}" @selected(old('auth_protocol', $profile->auth_protocol ?? 'SHA') === $label)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-form-hint text="Metode autentikasi, umumnya SHA atau MD5." />
                <x-input-error :messages="$errors->get('auth_protocol')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="auth_passphrase" value="Auth Passphrase" />
                <x-text-input id="auth_passphrase" name="auth_passphrase" type="password" class="mt-1 block w-full"
                    placeholder="{{ $isEdit ? 'Kosongkan jika tidak diubah' : 'Password autentikasi SNMPv3' }}" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('auth_passphrase')" class="mt-2" />
            </div>
        </div>

        <div id="snmp-v3-priv-fields" class="grid grid-cols-1 md:grid-cols-2 gap-4"
             @if($selectedSecurity !== 'authPriv') hidden @endif>
            <div>
                <x-input-label for="priv_protocol" value="Priv Protocol" />
                <select id="priv_protocol" name="priv_protocol" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    @foreach (config('nms.snmp_priv_protocols') as $label => $value)
                        <option value="{{ $label }}" @selected(old('priv_protocol', $profile->priv_protocol ?? 'AES128') === $label)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-form-hint text="Metode enkripsi data SNMP, umumnya AES128." />
                <x-input-error :messages="$errors->get('priv_protocol')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="priv_passphrase" value="Priv Passphrase" />
                <x-text-input id="priv_passphrase" name="priv_passphrase" type="password" class="mt-1 block w-full"
                    placeholder="{{ $isEdit ? 'Kosongkan jika tidak diubah' : 'Password enkripsi SNMPv3' }}" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('priv_passphrase')" class="mt-2" />
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const form = document.getElementById('snmp-profile-form');
        if (!form) return;

        const versionSelect = form.querySelector('#version');
        const securitySelect = form.querySelector('#security_level');
        const v2cFields = form.querySelector('#snmp-v2c-fields');
        const v3Fields = form.querySelector('#snmp-v3-fields');
        const authFields = form.querySelector('#snmp-v3-auth-fields');
        const privFields = form.querySelector('#snmp-v3-priv-fields');
        const communityInput = form.querySelector('#community');
        const isEdit = @json($isEdit);

        function toggleVersion() {
            const isV2c = versionSelect.value === '2c';
            v2cFields.hidden = !isV2c;
            v3Fields.hidden = isV2c;

            if (communityInput) {
                communityInput.required = isV2c && !isEdit;
                communityInput.disabled = !isV2c;
            }

            if (!isV2c) {
                toggleSecurityLevel();
            }
        }

        function toggleSecurityLevel() {
            if (!securitySelect) return;

            const level = securitySelect.value;
            authFields.hidden = !(level === 'authNoPriv' || level === 'authPriv');
            privFields.hidden = level !== 'authPriv';
        }

        versionSelect.addEventListener('change', toggleVersion);
        securitySelect?.addEventListener('change', toggleSecurityLevel);
        toggleVersion();
    })();
</script>
