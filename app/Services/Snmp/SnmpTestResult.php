<?php

namespace App\Services\Snmp;

readonly class SnmpTestResult
{
    public function __construct(
        public bool $success,
        public ?string $sysDescr = null,
        public ?string $error = null,
    ) {}

    public static function ok(string $sysDescr): self
    {
        return new self(true, $sysDescr);
    }

    public static function fail(string $error): self
    {
        return new self(false, error: $error);
    }
}
