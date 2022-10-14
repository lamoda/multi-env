<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\HostDetector;

use GetOpt\GetOpt;
use Lamoda\MultiEnv\HostDetector\CliArgsBasedHostDetector;
use Lamoda\MultiEnv\HostDetector\Exception\HostDetectorException;
use Lamoda\MultiEnv\HostDetector\Factory\GetOptAdapterFactory;
use Lamoda\MultiEnv\Model\HostId;
use Lamoda\MultiEnvTests\Support\TestCliArgsManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CliArgsBasedHostDetectorTest extends TestCase
{
    use TestCliArgsManager;

    private GetOpt $getOptAdapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getOptAdapter = GetOptAdapterFactory::build();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->removeTestCliArgs();
    }

    /**
     * @dataProvider successfulCreationDataProvider
     */
    public function testSuccessfulCreation(string $needle): void
    {
        $detector = new CliArgsBasedHostDetector($needle, $this->getOptAdapter);
        $this->assertInstanceOf(CliArgsBasedHostDetector::class, $detector);
    }

    public function successfulCreationDataProvider(): array
    {
        return [
            'needleWithoutSpaces' => [
                'needle' => 'testNeedle',
            ],
            'needleWithBracedSpaces' => [
                'needle' => '     testNeedle1     ',
            ],
            'needleWithSystemSymbols' => [
                'needle' => "    \t\n\n\t\0\x0B   testNeedleTest \t\t\t\t\t\r\t\n\n\0\x0B",
            ],
        ];
    }

    /**
     * @throws HostDetectorException
     *
     * @dataProvider exceptionRaisedWhenConstructCalledWithWrongNeedle
     */
    public function testExceptionRaisedWhenConstructCalledWithWrongNeedle(
        string $needle,
        GetOpt $getOptAdapter,
        \Exception $expectedException
    ): void {
        $this->expectException(get_class($expectedException));
        $this->expectExceptionMessage($expectedException->getMessage());
        new CliArgsBasedHostDetector($needle, $getOptAdapter);
    }

    public function exceptionRaisedWhenConstructCalledWithWrongNeedle(): array
    {
        $emptyNeedleException = HostDetectorException::becauseEmptyNeedlePassed(CliArgsBasedHostDetector::class);
        $incorrectGetOptAdapterParamsException = HostDetectorException::becauseGetOptAdapterConfiguredIncorrect(
            GetOpt::class,
            GetOpt::SETTING_STRICT_OPTIONS,
            'false'
        );

        return [
            'emptyNeedle' => [
                'needle' => '',
                'getOptAdapter' => new GetOpt(null, [GetOpt::SETTING_STRICT_OPTIONS => false]),
                'expectedException' => $emptyNeedleException,
            ],
            'emptyNeedleWithSpaces' => [
                'needle' => '         ',
                'getOptAdapter' => new GetOpt(null, [GetOpt::SETTING_STRICT_OPTIONS => false]),
                'expectedException' => $emptyNeedleException,
            ],
            'emptyNeedleWithSystemChars' => [
                'needle' => "\t\n\r\n\n\n\r\t\n     \n\t\r\0\x0B     ",
                'getOptAdapter' => new GetOpt(null, [GetOpt::SETTING_STRICT_OPTIONS => false]),
                'expectedException' => $emptyNeedleException,
            ],
            'correctNeedleIncorrectGetOpt' => [
                'needle' => 'test',
                'getOptAdapter' => new GetOpt(),
                'expectedException' => $incorrectGetOptAdapterParamsException,
            ],
        ];
    }

    /**
     * @throws HostDetectorException
     *
     * @dataProvider getCurrentHostDataProvider
     */
    public function testGetCurrentHost(string $needle, array $cliArgs, HostId $expected): void
    {
        $detector = new CliArgsBasedHostDetector($needle, $this->getOptAdapter);
        $this->addTestCliArgs($cliArgs);
        $hostId = $detector->getCurrentHost();
        $this->assertEquals((string) $expected, (string) $hostId);
    }

    public function getCurrentHostDataProvider(): array
    {
        return [
            'emptyArgs' => [
                'needle' => 'host_id',
                'cliArgs' => [],
                'expected' => new HostId(''),
            ],
            'notEmptySingleCliArg' => [
                'needle' => 'host_id',
                'cliArgs' => [
                    'host_id' => [
                        'useKeyValueSeparator' => true,
                        'value' => 'test-host-id',
                    ],
                ],
                'expected' => new HostId('test-host-id'),
            ],
            'noEmptyMultipleCliArgsWithSeparators' => [
                'needle' => 'a',
                'cliArgs' => [
                    'file' => [
                        'useKeyValueSeparator' => true,
                        'value' => '/temp/test_file.php',
                    ],
                    'a' => [
                        'useKeyValueSeparator' => true,
                        'value' => 'test-result',
                    ],
                    'bbb' => [
                        'useKeyValueSeparator' => true,
                        'value' => 'anotherTestValue',
                    ],
                ],
                'expected' => new HostId('test-result'),
            ],
            'notEmptyMultipleCliArgsWithoutSeparators' => [
                'needle' => 'bbc',
                'cliArgs' => [
                    'a' => [
                        'useKeyValueSeparator' => false,
                        'value' => 'test',
                    ],
                    'b' => [
                        'useKeyValueSeparator' => false,
                        'value' => 'test1',
                    ],
                    'bb' => [
                        'useKeyValueSeparator' => false,
                        'value' => 'test2',
                    ],
                    'bc' => [
                        'useKeyValueSeparator' => false,
                        'value' => 'test3',
                    ],
                    'bbc' => [
                        'useKeyValueSeparator' => false,
                        'value' => 'test4',
                    ],
                    'abbc' => [
                        'useKeyValueSeparator' => false,
                        'value' => 'test5',
                    ],
                ],
                'expected' => new HostId('test4'),
            ],
            'notEmptyMultipleCliArgsMixed' => [
                'needle' => 'abc',
                'cliArgs' => [
                    'a' => [
                        'useKeyValueSeparator' => true,
                        'value' => 'test-test',
                    ],
                    'b' => [
                        'useKeyValueSeparator' => false,
                        'value' => 'test-test1',
                    ],
                    'ab' => [
                        'useKeyValueSeparator' => false,
                        'value' => 'test-test2',
                    ],
                    'bb' => [
                        'useKeyValueSeparator' => true,
                        'value' => 'test-test3',
                    ],
                    'abc' => [
                        'useKeyValueSeparator' => true,
                        'value' => 'test-test-test',
                    ],
                ],
                'expected' => new HostId('test-test-test'),
            ],
        ];
    }

    public function testThatGetOptAdapterProcessMethodCallOnlyOnce(): void
    {
        /** @var GetOpt|MockObject $adapterMock */
        $adapterMock = $this->createMock(GetOpt::class);
        $adapterMock->expects(self::once())->method('process');
        $cliDetector = new CliArgsBasedHostDetector('test', $adapterMock);
        $cliDetector->getCurrentHost();
        $cliDetector->getCurrentHost();
    }
}
