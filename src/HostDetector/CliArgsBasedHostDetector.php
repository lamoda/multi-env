<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\HostDetector;

use GetOpt\GetOpt;
use Lamoda\MultiEnv\HostDetector\Exception\HostDetectorException;
use Lamoda\MultiEnv\Model\HostId;

final class CliArgsBasedHostDetector implements HostDetectorInterface
{
    private string $needle;

    private GetOpt $getOptAdapter;

    private bool $isCliArgumentsParsed = false;

    /**
     * @throws HostDetectorException
     */
    public function __construct(string $needle, GetOpt $getOptAdapter)
    {
        $needle = trim($needle);
        $this->validateInitialParams($needle, $getOptAdapter);

        $this->getOptAdapter = $getOptAdapter;
        $this->needle = $needle;
    }

    public function getCurrentHost(): HostId
    {
        if (!$this->isCliArgumentsParsed) {
            $this->getOptAdapter->process();
            $this->isCliArgumentsParsed = true;
        }

        $hostId = (string) $this->getOptAdapter->getOption($this->needle);
        $hostId = trim($hostId, '=');

        return new HostId($hostId);
    }

    /**
     * @throws HostDetectorException
     */
    private function validateInitialParams(string $needle, GetOpt $getOptAdapter): void
    {
        if (empty($needle)) {
            throw HostDetectorException::becauseEmptyNeedlePassed(self::class);
        }

        if ((bool) $getOptAdapter->get(GetOpt::SETTING_STRICT_OPTIONS) !== false) {
            throw HostDetectorException::becauseGetOptAdapterConfiguredIncorrect(
                GetOpt::class,
                GetOpt::SETTING_STRICT_OPTIONS,
                'false'
            );
        }
    }
}
