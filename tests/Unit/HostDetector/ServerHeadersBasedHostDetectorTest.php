<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\HostDetector;

use Lamoda\MultiEnv\HostDetector\Exception\HostDetectorException;
use Lamoda\MultiEnv\HostDetector\ServerHeadersBasedHostDetector;
use Lamoda\MultiEnv\Model\HostId;
use Lamoda\MultiEnvTests\Support\TestHeadersManager;
use PHPUnit\Framework\TestCase;

class ServerHeadersBasedHostDetectorTest extends TestCase
{
    use TestHeadersManager;

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->removeTestHeaders();
    }

    /**
     * @param string $needle
     * @dataProvider successCreationDataProvider
     */
    public function testSuccessfulCreation(string $needle): void
    {
        $detector = new ServerHeadersBasedHostDetector($needle);
        $this->assertInstanceOf(ServerHeadersBasedHostDetector::class, $detector);
    }

    public function successCreationDataProvider(): array
    {
        return [
            'needleWithoutBracedSpaces' => [
                'needle' => 'test',
            ],
            'needleWithBracedSpaces' => [
                'needle' => '     test1   ',
            ],
            'needleWithWithSystemSymbols' => [
                'needle' => "    \t\n\r\0\x0B   test-test-test   \t\n\r\0\x0B   ",
            ]
        ];
    }

    /**
     * @param string $needle
     * @param \Exception $expectedException
     * @dataProvider exceptionRaisedWhenConstructCalledWithWrongNeedle
     * @throws HostDetectorException
     */
    public function testExceptionRaisedWhenConstructCalledWithWrongNeedle(
        string $needle,
        \Exception $expectedException
    ): void {
        $this->expectException(get_class($expectedException));
        $this->expectExceptionMessage($expectedException->getMessage());
        new ServerHeadersBasedHostDetector($needle);
    }

    public function exceptionRaisedWhenConstructCalledWithWrongNeedle(): array
    {
        $expectedException = HostDetectorException::becauseEmptyNeedlePassed(ServerHeadersBasedHostDetector::class);

        return [
            'emptyNeedle' => [
                'needle' => '',
                'expectedException' => $expectedException
            ],
            'onlySpacesInNeedle' => [
                'needle' => '       ',
                'expectedException' => $expectedException
            ],
            'differentCharacters' => [
                'needle' => "\t\n\r\0\x0B",
                'expectedException' => $expectedException
            ]
        ];
    }

    /**
     * @param string $needle
     * @param array $headers
     * @param HostId $expectedHost
     * @throws HostDetectorException
     * @dataProvider getCurrentHostDataProvider
     */
    public function testGetCurrentHost(string $needle, array $headers, HostId $expectedHost): void
    {
        $detector = new ServerHeadersBasedHostDetector($needle);
        $this->addTestHeaders($headers);
        $currentHost = $detector->getCurrentHost();
        $this->assertEquals((string)$expectedHost, (string)$currentHost);
    }

    public function getCurrentHostDataProvider(): array
    {
        return [
            'needleExist' => [
                'needle' => 'HTTP_X_HOST_ID',
                'headers' => [
                    'HTTP_HOST' => 'localhost',
                    'HTTP_X_TEST_HEADER' => 'some-value',
                    'HTTP_X_HOST_ID' => 'test'
                ],
                'expectedHost' => new HostId('test')
            ],
            'needleExistAnotherSet' => [
                'needle' => 'X_TEST',
                'headers' => [
                    'HTTP_HOST' => 'test_host',
                    'X_TEST' => 'x_test'
                ],
                'expectedHost' => new HostId('x_test')
            ],
            'needleNotExist' => [
                'needle' => 'TEST',
                'headers' => [
                    'HTTP_HOST' => 'localhost',
                    'HTTP_X_TEST_HEADER' => 'x-test',
                    'HTTP_X_ANOTHER_TEST_HEADER' => 'x-another-test'
                ],
                'expectedHost' => new HostId('')
            ],
            'needleInDifferentRegister' => [
                'needle' => 'http_x_host_id',
                'headers' => [
                    'HTTP_HOST' => 'localhost',
                    'HTTP_X_TEST' => 'some-value',
                    'HTTP_X_HOST_ID' => 'test1',
                ],
                'expectedHost' => new HostId('')
            ],
            'needleNotMath' => [
                'needle' => 'HTTP-X-TEST',
                'headers' => [
                    'HTTP_HOST' => 'localhost',
                    'HTTP_X_TEST' => 'some_test_value',
                    'HTTP_X_HOST_ID' => 'host_id'
                ],
                'expectedHost' => new HostId('')
            ],
            'emptyHeaders' => [
                'needle' => 'HTTP_X_TEST',
                'headers' => [],
                'expectedHost' => new HostId('')
            ]
        ];
    }
}
