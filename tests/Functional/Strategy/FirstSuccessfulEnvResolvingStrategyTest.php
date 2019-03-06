<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Functional\Strategy;

use Lamoda\MultiEnv\Formatter\PrefixEnvNameFormatter;
use Lamoda\MultiEnv\HostDetector\CliArgsBasedHostDetector;
use Lamoda\MultiEnv\HostDetector\Exception\HostDetectorException;
use Lamoda\MultiEnv\HostDetector\Factory\GetOptAdapterFactory;
use Lamoda\MultiEnv\HostDetector\FirstSuccessfulHostDetector;
use Lamoda\MultiEnv\HostDetector\ServerHeadersBasedHostDetector;
use Lamoda\MultiEnv\Strategy\FirstSuccessfulEnvResolvingStrategy;
use Lamoda\MultiEnv\Strategy\HostBasedEnvResolvingStrategy;
use Lamoda\MultiEnv\Strategy\RawEnvResolvingStrategy;
use Lamoda\MultiEnvTests\Support\TestCliArgsManager;
use Lamoda\MultiEnvTests\Support\TestEnvManager;
use Lamoda\MultiEnvTests\Support\TestHeadersManager;
use PHPUnit\Framework\TestCase;

class FirstSuccessfulEnvResolvingStrategyTest extends TestCase
{
    use TestHeadersManager, TestCliArgsManager, TestEnvManager;

    protected function tearDown()
    {
        parent::tearDown();
        $this->removeTestHeaders();
        $this->removeTestCliArgs();
        $this->removeTestEnv();
    }

    /**
     * @param string $envToSearch
     * @param array $strategies
     * @param array $testHeaders
     * @param array $testCliArgs
     * @param array $testEnvs
     * @param string $expected
     * @dataProvider getEnvDataProvider
     */
    public function testGetEnv(
        string $envToSearch,
        array $strategies,
        array $testHeaders,
        array $testCliArgs,
        array $testEnvs,
        string $expected
    ): void {
        $this->addTestHeaders($testHeaders);
        $this->addTestCliArgs($testCliArgs);
        $this->addTestEnv($testEnvs);
        $firstSuccessfulEncResolvingStrategy = new FirstSuccessfulEnvResolvingStrategy($strategies);
        $this->assertEquals($expected, $firstSuccessfulEncResolvingStrategy->getEnv($envToSearch));
    }

    /**
     * @throws HostDetectorException
     * @return array
     */
    public function getEnvDataProvider(): array
    {
        return [
            'emptyStrategies' => [
                'env' => 'DB_HOST',
                'strategies' => [],
                'headers' => [
                    'HTTP_X_HOST_ID' => 'moscow'
                ],
                'cliArgs' => [
                    'host_id' => [
                        'value' => 'piter'
                    ]
                ],
                'envs' => [
                    'DB_HOST' => 'wrong',
                    'MOSCOW_DB_HOST' => 'wrong-wrong',
                    'PITER_DB_HOST' => 'definitely_wrong'
                ],
                'expected' => ''
            ],
            'rawStrategyGoFirstAndWork' => [
                'env' => 'DB_HOST',
                'strategies' => [
                    new RawEnvResolvingStrategy(),
                    new HostBasedEnvResolvingStrategy(
                        new FirstSuccessfulHostDetector([
                            new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                            new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                        ]),
                        new PrefixEnvNameFormatter('-')
                    )
                ],
                'headers' => [
                    'HTTP_X_HOST_ID' => 'moscow'
                ],
                'cliArgs' => [
                    'host_id' => [
                        'value' => 'piter'
                    ]
                ],
                'envs' => [
                    'DB_HOST' => 'correct',
                    'MOSCOW_DB_HOST' => 'wrong',
                    'PITER_DB_HOST' => 'incorrect'
                ],
                'expected' => 'correct'
            ],
            'rawStrategyGoFirstAndDontWork' => [
                'env' => 'DB_HOST',
                'strategies' => [
                    new RawEnvResolvingStrategy(),
                    new HostBasedEnvResolvingStrategy(
                        new FirstSuccessfulHostDetector([
                            new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                            new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build())
                        ]),
                        new PrefixEnvNameFormatter('-')
                    )
                ],
                'headers' => [
                    'HTTP_X_HOST_ID' => 'moscow'
                ],
                'cliArgs' => [
                    'host-id' => [
                        'value' => 'piter'
                    ]
                ],
                'envs' => [
                    'piter_DB_HOST' => 'wrong',
                    'moscow_DB_HOST' => 'correct'
                ],
                'expected' => 'correct'
            ],
            'hostBasedGoFirstAndWork' => [
                'env' => 'DB_HOST',
                'strategies' => [
                    new HostBasedEnvResolvingStrategy(
                        new FirstSuccessfulHostDetector([
                            new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                            new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build())
                        ]),
                        new PrefixEnvNameFormatter('-')
                    ),
                    new RawEnvResolvingStrategy(),
                ],
                'headers' => [
                    'HTTP_X_HOST_ID' => 'MOSCOW'
                ],
                'cliArgs' => [
                    'host_id' => [
                        'value' => 'PITER'
                    ]
                ],
                'envs' => [
                    'DB_HOST' => 'incorrect',
                    'MOSCOW_DB_HOST' => '0',
                    'PITER_DB_HOST' => 'incorrect'
                ],
                'expected' => '0'
            ],
            'hostBasedGoFirstAndWorkCli' => [
                'env' => 'DB_HOST',
                'strategies' => [
                    new HostBasedEnvResolvingStrategy(
                        new FirstSuccessfulHostDetector([
                            new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                            new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build())
                        ]),
                        new PrefixEnvNameFormatter('-')
                    ),
                    new RawEnvResolvingStrategy(),
                ],
                'headers' => [
                    'HTTP_X_HOST_ID_1' => 'MOSCOW'
                ],
                'cliArgs' => [
                    'host_id' => [
                        'value' => 'PITER'
                    ]
                ],
                'envs' => [
                    'DB_HOST' => 'incorrect',
                    'MOSCOW_DB_HOST' => 'incorrect',
                    'PITER_DB_HOST' => 'correct'
                ],
                'expected' => 'correct'
            ],
            'hostBasedGoFirstAndDontWork' => [
                'env' => 'DB_HOST',
                'strategies' => [
                    new HostBasedEnvResolvingStrategy(
                        new FirstSuccessfulHostDetector([
                            new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                            new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build())
                        ]),
                        new PrefixEnvNameFormatter('-')
                    ),
                    new RawEnvResolvingStrategy(),
                ],
                'headers' => [
                    'HTTP_X_HOST_ID' => 'moscow'
                ],
                'cliArgs' => [],
                'envs' => [
                    'DB_HOST' => 'correct',
                    'PITER_DB_HOST' => 'incorrect'
                ],
                'expected' => 'correct'
            ]
        ];
    }
}
