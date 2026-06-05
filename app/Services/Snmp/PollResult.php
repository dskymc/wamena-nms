<?php

namespace App\Services\Snmp;

readonly class PollResult
{
    public function __construct(
        public bool $success,
        public ?string $sysUpTime = null,
        public ?string $sysName = null,
        public ?string $identity = null,
        public ?string $error = null,
    ) {}

    public static function ok(string $sysUpTime, ?string $sysName = null, ?string $identity = null): self
    {
        return new self(true, $sysUpTime, $sysName, $identity);
    }

    public static function fail(string $error): self
    {
        return new self(false, error: $error);
    }
}
