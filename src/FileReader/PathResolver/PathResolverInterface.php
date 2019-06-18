<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\FileReader\PathResolver;

use Lamoda\MultiEnv\Model\HostId;

interface PathResolverInterface
{
    public function resolvePathToEnvFile(HostId $hostId): string;
}
