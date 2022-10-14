<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\HostDetector;

use Lamoda\MultiEnv\Model\HostId;

final class FirstSuccessfulHostDetector implements HostDetectorInterface
{
    /**
     * @var array|HostDetectorInterface[]
     */
    private array $detectors = [];

    public function __construct(array $detectors = [])
    {
        $this->addDetectors($detectors);
    }

    public function getCurrentHost(): HostId
    {
        foreach ($this->detectors as $detector) {
            $hostId = $detector->getCurrentHost();
            if ((string) $hostId !== '') {
                return $hostId;
            }
        }

        return new HostId('');
    }

    /**
     * @param array|HostDetectorInterface[] $detectors
     */
    private function addDetectors(array $detectors): void
    {
        foreach ($detectors as $detector) {
            $this->addDetector($detector);
        }
    }

    private function addDetector(HostDetectorInterface $detector): void
    {
        $this->detectors[] = $detector;
    }
}
