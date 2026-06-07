<?php

namespace App\Policies;

use App\Models\SnmpTrap;
use App\Models\User;

class SnmpTrapPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('snmp_traps.view');
    }

    public function view(User $user, SnmpTrap $snmpTrap): bool
    {
        return $user->can('snmp_traps.view');
    }
}
