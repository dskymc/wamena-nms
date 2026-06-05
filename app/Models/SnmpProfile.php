<?php

namespace App\Models;

use App\Enums\SnmpSecurityLevel;
use App\Enums\SnmpVersion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SnmpProfile extends Model
{
    protected $fillable = [
        'name',
        'version',
        'port',
        'timeout_ms',
        'retries',
        'community',
        'security_level',
        'username',
        'auth_protocol',
        'auth_passphrase',
        'priv_protocol',
        'priv_passphrase',
        'context_name',
    ];

    protected function casts(): array
    {
        return [
            'version' => SnmpVersion::class,
            'security_level' => SnmpSecurityLevel::class,
            'community' => 'encrypted',
            'auth_passphrase' => 'encrypted',
            'priv_passphrase' => 'encrypted',
            'port' => 'integer',
            'timeout_ms' => 'integer',
            'retries' => 'integer',
        ];
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function isV2c(): bool
    {
        return $this->version === SnmpVersion::V2c;
    }

    public function isV3(): bool
    {
        return $this->version === SnmpVersion::V3;
    }
}
