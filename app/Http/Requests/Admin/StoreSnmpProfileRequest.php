<?php

namespace App\Http\Requests\Admin;

use App\Enums\SnmpSecurityLevel;
use App\Enums\SnmpVersion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSnmpProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('snmp_profiles.create');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'version' => ['required', Rule::enum(SnmpVersion::class)],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'timeout_ms' => ['required', 'integer', 'min:500', 'max:60000'],
            'retries' => ['required', 'integer', 'min:0', 'max:10'],
        ];

        if ($this->input('version') === SnmpVersion::V2c->value) {
            $rules['community'] = ['required', 'string', 'max:255'];
        }

        if ($this->input('version') === SnmpVersion::V3->value) {
            $rules['security_level'] = ['required', Rule::enum(SnmpSecurityLevel::class)];
            $rules['username'] = ['required', 'string', 'max:255'];
            $rules['context_name'] = ['nullable', 'string', 'max:255'];

            $level = $this->input('security_level');

            if (in_array($level, [SnmpSecurityLevel::AuthNoPriv->value, SnmpSecurityLevel::AuthPriv->value], true)) {
                $rules['auth_protocol'] = ['required', Rule::in(array_keys(config('nms.snmp_auth_protocols')))];
                $rules['auth_passphrase'] = ['required', 'string', 'max:255'];
            }

            if ($level === SnmpSecurityLevel::AuthPriv->value) {
                $rules['priv_protocol'] = ['required', Rule::in(array_keys(config('nms.snmp_priv_protocols')))];
                $rules['priv_passphrase'] = ['required', 'string', 'max:255'];
            }
        }

        return $rules;
    }
}
