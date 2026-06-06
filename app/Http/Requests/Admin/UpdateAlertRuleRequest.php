<?php

namespace App\Http\Requests\Admin;

class UpdateAlertRuleRequest extends StoreAlertRuleRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('alert_rules.update');
    }
}
