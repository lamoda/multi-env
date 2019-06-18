<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Formatter;

use Lamoda\MultiEnv\Model\HostId;

interface FormatterInterface
{
    public function formatName(string $originalName, HostId $hostId): string;
}
