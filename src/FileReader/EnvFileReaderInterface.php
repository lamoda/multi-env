<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\FileReader;

use Lamoda\MultiEnv\Model\HostId;

interface EnvFileReaderInterface
{
    public function readEnvFile(HostId $hostId): void;
}
