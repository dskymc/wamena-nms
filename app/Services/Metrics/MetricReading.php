<?php

namespace App\Services\Metrics;

use App\Enums\MetricType;

readonly class MetricReading
{
    public function __construct(
        public MetricType $metric,
        public float $value,
        public ?string $source = null,
        public ?string $sourceLabel = null,
        public ?string $unit = null,
    ) {}

    public function unit(): string
    {
        return $this->unit ?? $this->metric->unit();
    }
}
