<?php

namespace App\Http\Requests\Admin;

use App\Enums\DeviceVendor;
use App\Models\Device;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('devices.update');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Device $device */
        $device = $this->route('device');

        return [
            'name' => ['required', 'string', 'max:255'],
            'management_ip' => ['required', 'ip', Rule::unique('devices', 'management_ip')->ignore($device->id)],
            'hostname' => ['nullable', 'string', 'max:255'],
            'vendor' => ['required', Rule::enum(DeviceVendor::class)],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'snmp_profile_id' => ['nullable', 'exists:snmp_profiles,id'],
            'notes' => ['nullable', 'string'],
            'is_monitored' => ['boolean'],
            'poll_interval_sec' => ['nullable', 'integer', 'min:60', 'max:86400'],
        ];
    }
}
