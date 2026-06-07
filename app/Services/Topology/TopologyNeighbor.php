<?php

namespace App\Services\Topology;

use App\Enums\TopologyProtocol;

class TopologyNeighbor
{
    public function __construct(
        public readonly string $localPortIndex,
        public readonly ?string $localPortLabel,
        public readonly ?string $remoteSysName,
        public readonly ?string $remoteChassisId,
        public readonly ?string $remotePortLabel,
        public readonly ?string $remoteMgmtIp,
        public readonly TopologyProtocol $protocol,
    ) {}

    public function identityKey(): string
    {
        if ($this->remoteChassisId !== null && $this->remoteChassisId !== '') {
            return strtolower(trim($this->remoteChassisId));
        }

        $parts = array_filter([
            $this->remoteSysName,
            $this->remotePortLabel,
            $this->remoteMgmtIp,
        ]);

        return strtolower(implode('|', $parts));
    }
}
