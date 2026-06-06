<?php

namespace App\Services\Snmp;

readonly class PollResult
{
    /**
     * @param  list<\App\Services\Metrics\MetricReading>  $metrics
     */
    public function __construct(
        public bool $success,
        public ?string $sysUpTime = null,
        public ?string $sysName = null,
        public ?string $identity = null,
        public ?string $error = null,
        public array $metrics = [],
    ) {}

    public static function ok(
        string $sysUpTime,
        ?string $sysName = null,
        ?string $identity = null,
        array $metrics = [],
    ): self {
        return new self(true, $sysUpTime, $sysName, $identity, metrics: $metrics);
    }

    public static function fail(string $error): self
    {
        return new self(false, error: $error);
    }
}
