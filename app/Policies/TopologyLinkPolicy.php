<?php

namespace App\Policies;

use App\Models\TopologyLink;
use App\Models\User;

class TopologyLinkPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('topology.view');
    }

    public function discover(User $user): bool
    {
        return $user->can('topology.discover');
    }
}
