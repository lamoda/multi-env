<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Strategy;

use Lamoda\MultiEnv\Formatter\Exception\FormatterException;
use Lamoda\MultiEnv\Formatter\FormatterInterface;
use Lamoda\MultiEnv\HostDetector\HostDetectorInterface;

final class HostBasedEnvResolvingStrategy implements EnvResolvingStrategyInterface
{
    /**
     * @var HostDetectorInterface
     */
    private $hostDetector;

    /**
     * @var FormatterInterface $envNameFormatter
     */
    private $envNameFormatter;

    public function __construct(HostDetectorInterface $hostDetector, FormatterInterface $envNameFormatter)
    {
        $this->hostDetector = $hostDetector;
        $this->envNameFormatter = $envNameFormatter;
    }

    /**
     * @param string $envName
     * @throws FormatterException
     * @return string
     */
    public function getEnv(string $envName): string
    {
        $hostId = $this->hostDetector->getCurrentHost();
        $envName = $this->envNameFormatter->formatName($envName, $hostId);

        return (string)getenv($envName);
    }
}
