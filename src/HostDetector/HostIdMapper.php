<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\HostDetector;

use Lamoda\MultiEnv\Model\HostId;

final class HostIdMapper implements HostDetectorInterface
{
    /**
     * @var HostDetectorInterface
     */
    private $inner;
    /**
     * @var string[]
     */
    private $hostIdMap;

    /**
     * @param HostDetectorInterface $inner
     * @param string[] $hostIdMap
     */
    public function __construct(HostDetectorInterface $inner, array $hostIdMap = [])
    {
        $this->inner = $inner;
        $this->hostIdMap = $hostIdMap;
    }

    public function getCurrentHost(): HostId
    {
        $innerHostId = $this->inner->getCurrentHost();
        $value = $this->hostIdMap[(string)$innerHostId] ?? '';

        return new HostId($value);
    }
}