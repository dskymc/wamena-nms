@php
    $isEditUser = isset($user);
@endphp

<div class="space-y-4">
    <div>
        <x-input-label for="name" value="Nama" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name ?? '')" placeholder="Contoh: Budi Santoso" required />
        <x-form-hint text="Nama lengkap pengguna yang akan mengakses sistem." />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email ?? '')" placeholder="Contoh: operator@diskominfo.go.id" required />
        <x-form-hint text="Digunakan untuk login ke WAMENA NMS." />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" value="Password" />
        <input
            type="password"
            id="password"
            name="password"
            placeholder="{{ $isEditUser ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}"
            autocomplete="new-password"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            @if(!$isEditUser) required @endif
        >
        @if ($isEditUser)
            <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengubah password.</p>
        @endif
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Konfirmasi Password" />
        <input
            type="password"
            id="password_confirmation"
            name="password_confirmation"
            placeholder="Ulangi password yang sama"
            autocomplete="new-password"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="role" value="Role" />
        <select id="role" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected(old('role', isset($user) ? $user->roles->first()?->name : '') === $role)>
                    {{ $role }}
                </option>
            @endforeach
        </select>
        <x-form-hint text="super_admin: akses penuh | admin: kelola data | operator: kelola perangkat | viewer: lihat saja" />
        <x-input-error :messages="$errors->get('role')" class="mt-2" />
    </div>
</div>
