<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Formatter;

use Lamoda\MultiEnv\Model\HostId;

interface EnvNameFormatterInterface
{
    public function formatEnvName(string $originalEnvName, HostId $hostId): string;
}
