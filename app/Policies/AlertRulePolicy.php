<?php

namespace App\Policies;

use App\Models\AlertRule;
use App\Models\User;

class AlertRulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('alert_rules.view');
    }

    public function view(User $user, AlertRule $alertRule): bool
    {
        return $user->can('alert_rules.view');
    }

    public function create(User $user): bool
    {
        return $user->can('alert_rules.create');
    }

    public function update(User $user, AlertRule $alertRule): bool
    {
        return $user->can('alert_rules.update');
    }

    public function delete(User $user, AlertRule $alertRule): bool
    {
        return $user->can('alert_rules.delete');
    }
}
