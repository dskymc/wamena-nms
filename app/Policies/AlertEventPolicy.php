<?php

namespace App\Policies;

use App\Models\AlertEvent;
use App\Models\User;

class AlertEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('alert_events.view');
    }

    public function view(User $user, AlertEvent $alertEvent): bool
    {
        return $user->can('alert_events.view');
    }
}
