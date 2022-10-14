<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Strategy;

use Lamoda\MultiEnv\Formatter\Exception\FormatterException;
use Lamoda\MultiEnv\Formatter\FormatterInterface;
use Lamoda\MultiEnv\HostDetector\HostDetectorInterface;

final class HostBasedEnvResolvingStrategy implements EnvResolvingStrategyInterface
{
    private HostDetectorInterface $hostDetector;

    private FormatterInterface $envNameFormatter;

    public function __construct(HostDetectorInterface $hostDetector, FormatterInterface $envNameFormatter)
    {
        $this->hostDetector = $hostDetector;
        $this->envNameFormatter = $envNameFormatter;
    }

    /**
     * @throws FormatterException
     */
    public function getEnv(string $envName): string
    {
        $hostId = $this->hostDetector->getCurrentHost();
        $envName = $this->envNameFormatter->formatName($envName, $hostId);

        return (string) getenv($envName);
    }
}
