<div class="space-y-4">
    <div>
        <x-input-label for="name" value="Nama Perangkat" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $device->name ?? '')" placeholder="Contoh: Core Router Wamena, SW-Lantai-2" required />
        <x-form-hint text="Nama identifikasi perangkat yang mudah dikenali operator." />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="management_ip" value="IP Management" />
            <x-text-input id="management_ip" name="management_ip" type="text" class="mt-1 block w-full" :value="old('management_ip', $device->management_ip ?? '')" placeholder="Contoh: 192.168.1.1" required />
            <x-form-hint text="Alamat IP yang dipakai untuk akses SNMP ke perangkat." />
            <x-input-error :messages="$errors->get('management_ip')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="hostname" value="Hostname" />
            <x-text-input id="hostname" name="hostname" type="text" class="mt-1 block w-full" :value="old('hostname', $device->hostname ?? '')" placeholder="Contoh: mikrotik-core.wamena.local" />
            <x-form-hint text="Nama host perangkat jika tersedia (opsional)." />
            <x-input-error :messages="$errors->get('hostname')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="vendor" value="Vendor" />
            <select id="vendor" name="vendor" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->value }}" @selected(old('vendor', $device?->vendor?->value ?? '') === $vendor->value)>
                        {{ $vendor->label() }}
                    </option>
                @endforeach
            </select>
            <x-form-hint text="Pilih merek perangkat: MikroTik, Ruijie, Ubiquiti, atau Lainnya." />
            <x-input-error :messages="$errors->get('vendor')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="snmp_profile_id" value="Profil SNMP" />
            <select id="snmp_profile_id" name="snmp_profile_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">— Pilih profil SNMP —</option>
                @foreach ($snmpProfiles as $profile)
                    <option value="{{ $profile->id }}" @selected(old('snmp_profile_id', $device->snmp_profile_id ?? '') == $profile->id)>
                        {{ $profile->name }} ({{ $profile->version->label() }})
                    </option>
                @endforeach
            </select>
            <x-form-hint text="Hubungkan perangkat dengan profil SNMP yang sudah dibuat. Wajib untuk uji koneksi SNMP." />
            <x-input-error :messages="$errors->get('snmp_profile_id')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="model" value="Model" />
            <x-text-input id="model" name="model" type="text" class="mt-1 block w-full" :value="old('model', $device->model ?? '')" placeholder="Contoh: RB4011, USW-24-POE, RG-S2928" />
            <x-input-error :messages="$errors->get('model')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="serial_number" value="Serial Number" />
            <x-text-input id="serial_number" name="serial_number" type="text" class="mt-1 block w-full" :value="old('serial_number', $device->serial_number ?? '')" placeholder="Contoh: HFF09123456" />
            <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="location_id" value="Lokasi" />
        <select id="location_id" name="location_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">— Pilih lokasi —</option>
            @foreach ($locations as $location)
                <option value="{{ $location->id }}" @selected(old('location_id', $device->location_id ?? '') == $location->id)>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>
        <x-form-hint text="Tempat fisik perangkat berada (opsional)." />
        <x-input-error :messages="$errors->get('location_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="notes" value="Catatan" />
        <textarea id="notes" name="notes" rows="3" placeholder="Contoh: Perangkat utama backbone, akses via VLAN management" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes', $device->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>

    <div class="flex items-center gap-4">
        <label class="flex items-center">
            <input type="checkbox" name="is_monitored" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                @checked(old('is_monitored', $device->is_monitored ?? true))>
            <span class="ms-2 text-sm text-gray-600">Aktifkan monitoring (fase 2)</span>
        </label>
    </div>

    <div>
        <x-input-label for="poll_interval_sec" value="Interval Polling (detik)" />
        <x-text-input id="poll_interval_sec" name="poll_interval_sec" type="number" class="mt-1 block w-full" :value="old('poll_interval_sec', $device->poll_interval_sec ?? 300)" placeholder="300" />
        <x-form-hint text="Seberapa sering perangkat dicek (detik). Default: 300 (5 menit). Digunakan saat fase monitoring aktif." />
        <x-input-error :messages="$errors->get('poll_interval_sec')" class="mt-2" />
    </div>
</div>
