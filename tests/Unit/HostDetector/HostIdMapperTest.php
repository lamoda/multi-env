<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Unit\HostDetector;

use Lamoda\MultiEnv\HostDetector\HostDetectorInterface;
use Lamoda\MultiEnv\HostDetector\HostIdMapper;
use Lamoda\MultiEnv\Model\HostId;
use PHPUnit\Framework\TestCase;

final class HostIdMapperTest extends TestCase
{
    /**
     * @var HostDetectorInterface
     */
    private $inner;

    private HostIdMapper $mapper;

    protected function setUp(): void
    {
        $this->inner = $this->createMock(HostDetectorInterface::class);
        $this->mapper = new HostIdMapper($this->inner, [
            'host_from_1' => 'host_to_1',
            'host_from_2' => 'host_to_2',
        ]);
    }

    /**
     * @dataProvider dataGetCurrentHost
     */
    public function testGetCurrentHost(HostId $innerResponse, HostId $expected): void
    {
        $this->inner
            ->method('getCurrentHost')
            ->willReturn($innerResponse);

        $result = $this->mapper->getCurrentHost();

        $this->assertEquals($expected, $result);
    }

    public function dataGetCurrentHost(): iterable
    {
        yield [
            new HostId(''),
            new HostId(''),
        ];

        yield [
            new HostId('host_from_1'),
            new HostId('host_to_1'),
        ];

        yield [
            new HostId('unknown'),
            new HostId(''),
        ];
    }
}
