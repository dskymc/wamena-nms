<div class="space-y-4">
    <div>
        <x-input-label for="name" value="Nama Lokasi" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $location->name ?? '')" placeholder="Contoh: Kantor Gubernur, DC Wamena" required />
        <x-form-hint text="Nama site atau gedung tempat perangkat berada." />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="parent_id" value="Lokasi Induk" />
        <select id="parent_id" name="parent_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">— Tidak ada (lokasi utama) —</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $location->parent_id ?? '') == $parent->id)>
                    {{ $parent->fullName() }}
                </option>
            @endforeach
        </select>
        <x-form-hint text="Pilih lokasi induk jika ini sub-lokasi, misalnya Ruang Server di bawah Kantor Pusat." />
        <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="code" value="Kode" />
        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $location->code ?? '')" placeholder="Contoh: KNG-01, DC-WMN" />
        <x-form-hint text="Kode singkat lokasi (opsional) untuk memudahkan pencarian." />
        <x-input-error :messages="$errors->get('code')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="address" value="Alamat" />
        <textarea id="address" name="address" rows="2" placeholder="Contoh: Jl. Yos Sudarso No. 1, Wamena, Papua Pegunungan" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('address', $location->address ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" value="Deskripsi" />
        <textarea id="description" name="description" rows="3" placeholder="Contoh: Ruang data center lantai 2, berisi core switch dan router utama" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $location->description ?? '') }}</textarea>
        <x-form-hint text="Catatan tambahan tentang lokasi (opsional)." />
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>
</div>
