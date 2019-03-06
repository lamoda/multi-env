<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Unit\Model;

use Lamoda\MultiEnv\Model\HostId;
use PHPUnit\Framework\TestCase;

class HostIdTest extends TestCase
{
    /**
     * @param string $hostId
     * @param string $expected
     * @dataProvider creationDataProvider
     */
    public function testCreation(string $hostId, string $expected): void
    {
        $model = new HostId($hostId);
        $this->assertEquals($expected, (string)$model);
    }

    public function creationDataProvider(): array
    {
        return [
            'empty' => [
                'hostId' => '',
                'expected' => ''
            ],
            'filledWithSpaces' => [
                'hostId' => '      ',
                'expected' => '',
            ],
            'filledWithSystemSymbols' => [
                'hostId' => "\t\n\r\0\x0B",
                'expected' => ''
            ],
            'filled' => [
                'hostId' => 'testValue',
                'expected' => 'testValue'
            ],
            'filledAnother' => [
                'hostId' => '   testValue1',
                'expected' => 'testValue1'
            ]
        ];
    }
}
