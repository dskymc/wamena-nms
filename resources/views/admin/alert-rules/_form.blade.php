@php
    $selectedChannels = old('channels', isset($rule) ? ($rule->channels ?? []) : ['telegram', 'whatsapp']);
    $triggerType = old('trigger_type', isset($rule) ? $rule->trigger_type->value : 'device_down');
    $scopeType = old('scope_type', isset($rule) ? $rule->scope_type->value : 'global');
@endphp

<div class="space-y-4">
    <div>
        <x-input-label for="name" value="Nama Rule" />
        <input id="name" name="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('name', $rule->name ?? '') }}" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="flex items-center gap-2">
        <input id="is_enabled" name="is_enabled" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_enabled', $rule->is_enabled ?? true)) />
        <label for="is_enabled" class="text-sm text-gray-700">Aktif</label>
    </div>

    <div>
        <x-input-label for="trigger_type" value="Trigger" />
        <select id="trigger_type" name="trigger_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach ($triggerTypes as $type)
                <option value="{{ $type->value }}" @selected($triggerType === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="scope_type" value="Scope" />
        <select id="scope_type" name="scope_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach ($scopeTypes as $scope)
                <option value="{{ $scope->value }}" @selected($scopeType === $scope->value)>{{ $scope->label() }}</option>
            @endforeach
        </select>
    </div>

    <div id="scope-location" class="{{ $scopeType === 'location' ? '' : 'hidden' }}">
        <x-input-label for="location_id" value="Lokasi" />
        <select id="location_id" name="location_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">— Pilih —</option>
            @foreach ($locations as $location)
                <option value="{{ $location->id }}" @selected(old('location_id', $rule->location_id ?? '') == $location->id)>{{ $location->name }}</option>
            @endforeach
        </select>
    </div>

    <div id="scope-device" class="{{ $scopeType === 'device' ? '' : 'hidden' }}">
        <x-input-label for="device_id" value="Perangkat" />
        <select id="device_id" name="device_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">— Pilih —</option>
            @foreach ($devices as $device)
                <option value="{{ $device->id }}" @selected(old('device_id', $rule->device_id ?? '') == $device->id)>{{ $device->name }}</option>
            @endforeach
        </select>
    </div>

    <div id="metric-fields" class="{{ $triggerType === 'metric_threshold' ? '' : 'hidden' }} space-y-4 border-t border-gray-100 pt-4">
        <div>
            <x-input-label for="metric" value="Metrik" />
            <select id="metric" name="metric" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @foreach ($metrics as $metric)
                    @if ($metric->isChartable())
                        <option value="{{ $metric->value }}" @selected(old('metric', isset($rule) && $rule->metric ? $rule->metric->value : 'cpu') === $metric->value)>{{ $metric->label() }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="operator" value="Operator" />
                <select id="operator" name="operator" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach ($operators as $op)
                        <option value="{{ $op->value }}" @selected(old('operator', isset($rule) && $rule->operator ? $rule->operator->value : 'gt') === $op->value)>{{ $op->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="threshold" value="Threshold" />
                <input id="threshold" name="threshold" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('threshold', $rule->threshold ?? '') }}" />
            </div>
        </div>
        <div>
            <x-input-label for="consecutive_breaches" value="Pelanggaran berturut-turut (poll)" />
            <input id="consecutive_breaches" name="consecutive_breaches" type="number" min="1" max="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('consecutive_breaches', $rule->consecutive_breaches ?? 2) }}" />
        </div>
    </div>

    <div>
        <x-input-label for="severity" value="Severity" />
        <select id="severity" name="severity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @foreach ($severities as $severity)
                <option value="{{ $severity->value }}" @selected(old('severity', isset($rule) ? $rule->severity->value : 'warning') === $severity->value)>{{ $severity->label() }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="cooldown_minutes" value="Cooldown (menit)" />
        <input id="cooldown_minutes" name="cooldown_minutes" type="number" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('cooldown_minutes', $rule->cooldown_minutes ?? 15) }}" required />
    </div>

    <div>
        <x-input-label value="Channel Notifikasi" />
        <div class="mt-2 space-y-2">
            @foreach ($channels as $channel)
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="channels[]" value="{{ $channel->value }}" class="rounded border-gray-300 text-indigo-600" @checked(in_array($channel->value, $selectedChannels, true)) />
                    {{ $channel->label() }}
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('channels')" class="mt-2" />
    </div>

    <div class="flex items-center gap-2">
        <input id="notify_on_resolve" name="notify_on_resolve" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm" @checked(old('notify_on_resolve', $rule->notify_on_resolve ?? false)) />
        <label for="notify_on_resolve" class="text-sm text-gray-700">Kirim notifikasi saat resolved</label>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.getElementById('trigger_type');
    const scope = document.getElementById('scope_type');
    const metricFields = document.getElementById('metric-fields');
    const scopeLocation = document.getElementById('scope-location');
    const scopeDevice = document.getElementById('scope-device');

    trigger?.addEventListener('change', () => {
        metricFields?.classList.toggle('hidden', trigger.value !== 'metric_threshold');
    });
    scope?.addEventListener('change', () => {
        scopeLocation?.classList.toggle('hidden', scope.value !== 'location');
        scopeDevice?.classList.toggle('hidden', scope.value !== 'device');
    });
});
</script>
