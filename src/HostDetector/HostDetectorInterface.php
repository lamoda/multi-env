<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\HostDetector;

use Lamoda\MultiEnv\Model\HostId;

interface HostDetectorInterface
{
    public function getCurrentHost(): HostId;
}
