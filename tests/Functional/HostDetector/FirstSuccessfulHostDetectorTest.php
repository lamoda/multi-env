<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Functional\HostDetector;

use Lamoda\MultiEnv\HostDetector\CliArgsBasedHostDetector;
use Lamoda\MultiEnv\HostDetector\Exception\HostDetectorException;
use Lamoda\MultiEnv\HostDetector\Factory\GetOptAdapterFactory;
use Lamoda\MultiEnv\HostDetector\FirstSuccessfulHostDetector;
use Lamoda\MultiEnv\HostDetector\ServerHeadersBasedHostDetector;
use Lamoda\MultiEnv\Model\HostId;
use Lamoda\MultiEnvTests\Support\TestCliArgsManager;
use Lamoda\MultiEnvTests\Support\TestHeadersManager;
use PHPUnit\Framework\TestCase;

class FirstSuccessfulHostDetectorTest extends TestCase
{
    use TestHeadersManager;
    use TestCliArgsManager;

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->removeTestHeaders();
        $this->removeTestCliArgs();
    }

    /**
     * @dataProvider getCurrentHostDataProvider
     */
    public function testGetCurrentHost(array $hostDetectors, HostId $expected, array $headers, array $cliArgs): void
    {
        $detector = new FirstSuccessfulHostDetector($hostDetectors);
        $this->addTestHeaders($headers);
        $this->addTestCliArgs($cliArgs);
        $this->assertEquals($expected, $detector->getCurrentHost());
    }

    /**
     * @throws HostDetectorException
     */
    public function getCurrentHostDataProvider(): array
    {
        return [
            'emptyAll' => [
                'detectors' => [],
                'expected' => new HostId(''),
                'headers' => [],
                'cliArgs' => [],
            ],
            'emptyHeaderAndCliArgs' => [
                'detectors' => [
                    new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                    new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                ],
                'expected' => new HostId(''),
                'headers' => [],
                'cliArgs' => [],
            ],
            'headersOnlySet' => [
                'detectors' => [
                    new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                    new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                ],
                'expected' => new HostId('host_id_from_header'),
                'headers' => [
                    'HTTP_X_TEST_TEST' => 'some_value',
                    'HTTP_X_HOST_ID' => 'host_id_from_header',
                    'X_HOST_ID' => 'wrong_host_id',
                ],
                'cliArgs' => [],
            ],
            'headersOnlySetAndHaveZeroValue' => [
                'detectors' => [
                    new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                    new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                ],
                'expected' => new HostId('0'),
                'headers' => [
                    'HTTP_X_TEST_TEST' => 'some_value',
                    'HTTP_X_HOST_ID' => '0',
                    'X_HOST_ID' => 'wrong_host_id',
                ],
                'cliArgs' => [],
            ],
            'headersOnlySetAnotherOrderOfDetectors' => [
                'detectors' => [
                    new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                    new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                ],
                'expected' => new HostId('host_id_from_header'),
                'headers' => [
                    'X_TEST_HEADER' => 'test',
                    'HTTP_X_HOST_ID' => 'host_id_from_header',
                    'ANOTHER_HEADER' => 'test1',
                ],
                'cliArgs' => [],
            ],
            'cliOnlySet' => [
                'detectors' => [
                    new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                    new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                ],
                'expected' => new HostId('host_id_from_cli'),
                'headers' => [],
                'cliArgs' => [
                    'a' => [
                        'useKeyValueSeparator' => false,
                        'value' => 'test',
                    ],
                    'bb' => [
                        'useKeyValueSeparator' => true,
                        'value' => 'someValue',
                    ],
                    'host_id' => [
                        'useKeyValueSeparator' => true,
                        'value' => 'host_id_from_cli',
                    ],
                ],
            ],
            'cliOrderSetAnotherOrderOfDetectors' => [
                'detectors' => [
                    new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                    new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                ],
                'expected' => new HostId('host_id_from_cli'),
                'headers' => [],
                'cliArgs' => [
                    'test' => [
                        'useKeyValueSeparator' => true,
                        'value' => 'test_value',
                    ],
                    'host_id' => [
                        'useKeyValueSeparator' => false,
                        'value' => 'host_id_from_cli',
                    ],
                    'another_test' => [
                        'useKeyValueSeparator' => true,
                        'value' => 'test-test',
                    ],
                ],
            ],
            'headersAndCliArgsSetHeaderBasedDetectorComeFirst' => [
                'detectors' => [
                    new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                    new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                ],
                'expected' => new HostId('host_id_from_header'),
                'headers' => [
                    'HTTP_X_HOST_ID' => 'host_id_from_header',
                    'X_TEST_HEADER' => 'test-value',
                ],
                'cliArgs' => [
                    'host_id' => [
                        'useKeyValueSeparator' => true,
                        'value' => 'host_id_from_cli',
                    ],
                    'test' => [
                        'useKeyValueSeparator' => false,
                        'value' => 'test-cli-value',
                    ],
                ],
            ],
            'headersAndCliArgsSetCliArgsBasedDetectorComeFirst' => [
                'detectors' => [
                    new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                    new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                ],
                'expected' => new HostId('host_id_from_cli'),
                'headers' => [
                    'HTTP_X_HOST_ID' => 'host_id_from_header',
                    'X_TEST_HEADER' => 'test-value',
                ],
                'cliArgs' => [
                    'host_id' => [
                        'useKeyValueSeparator' => true,
                        'value' => 'host_id_from_cli',
                    ],
                    'test' => [
                        'useKeyValueSeparator' => false,
                        'value' => 'test-cli-value',
                    ],
                ],
            ],
        ];
    }
}
