<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\FileReader\FileNameResolver;

use Lamoda\MultiEnv\Model\HostId;

interface FileNameResolverInterface
{
    public function resolveEnvFileName(HostId $hostId): string;
}
