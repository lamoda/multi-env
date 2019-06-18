<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Strategy;

use Lamoda\MultiEnv\FileReader\EnvFileReaderInterface;
use Lamoda\MultiEnv\HostDetector\HostDetectorInterface;

final class FileBasedEnvResolvingStrategy implements EnvResolvingStrategyInterface
{
    /**
     * @var HostDetectorInterface
     */
    private $hostDetector;

    /**
     * @var EnvFileReaderInterface
     */
    private $envFileReader;

    /**
     * @var EnvResolvingStrategyInterface
     */
    private $envResolvingStrategy;

    public function __construct(
        HostDetectorInterface $hostDetector,
        EnvFileReaderInterface $envFileReader,
        EnvResolvingStrategyInterface $envResolvingStrategy
    ) {
        $this->hostDetector = $hostDetector;
        $this->envFileReader = $envFileReader;
        $this->envResolvingStrategy = $envResolvingStrategy;
    }

    public function getEnv(string $envName): string
    {
        $hostId = $this->hostDetector->getCurrentHost();
        $this->envFileReader->readEnvFile($hostId);

        return $this->envResolvingStrategy->getEnv($envName);
    }
}
