<?php

namespace App\Policies;

use App\Models\SnmpProfile;
use App\Models\User;

class SnmpProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('snmp_profiles.view');
    }

    public function view(User $user, SnmpProfile $snmpProfile): bool
    {
        return $user->can('snmp_profiles.view');
    }

    public function create(User $user): bool
    {
        return $user->can('snmp_profiles.create');
    }

    public function update(User $user, SnmpProfile $snmpProfile): bool
    {
        return $user->can('snmp_profiles.update');
    }

    public function delete(User $user, SnmpProfile $snmpProfile): bool
    {
        return $user->can('snmp_profiles.delete');
    }
}
