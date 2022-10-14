<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\HostDetector;

use Lamoda\MultiEnv\HostDetector\Exception\HostDetectorException;
use Lamoda\MultiEnv\Model\HostId;

final class ServerHeadersBasedHostDetector implements HostDetectorInterface
{
    private string $needle;

    /**
     * @throws HostDetectorException
     */
    public function __construct(string $needle)
    {
        $needle = trim($needle);

        if (empty($needle)) {
            throw HostDetectorException::becauseEmptyNeedlePassed(self::class);
        }

        $this->needle = $needle;
    }

    public function getCurrentHost(): HostId
    {
        $hostId = (string) ($_SERVER[$this->needle] ?? '');

        return new HostId($hostId);
    }
}
