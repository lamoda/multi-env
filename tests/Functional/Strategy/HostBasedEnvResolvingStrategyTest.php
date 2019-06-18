<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Functional\Strategy;

use Lamoda\MultiEnv\Formatter\CharReplaceFormatter;
use Lamoda\MultiEnv\Formatter\Exception\FormatterException;
use Lamoda\MultiEnv\Formatter\FormatterInterface;
use Lamoda\MultiEnv\Formatter\FormatterPipeline;
use Lamoda\MultiEnv\Formatter\PrefixAppendFormatter;
use Lamoda\MultiEnv\HostDetector\CliArgsBasedHostDetector;
use Lamoda\MultiEnv\HostDetector\Exception\HostDetectorException;
use Lamoda\MultiEnv\HostDetector\Factory\GetOptAdapterFactory;
use Lamoda\MultiEnv\HostDetector\FirstSuccessfulHostDetector;
use Lamoda\MultiEnv\HostDetector\HostDetectorInterface;
use Lamoda\MultiEnv\HostDetector\ServerHeadersBasedHostDetector;
use Lamoda\MultiEnv\Strategy\HostBasedEnvResolvingStrategy;
use Lamoda\MultiEnvTests\Support\TestCliArgsManager;
use Lamoda\MultiEnvTests\Support\TestEnvManager;
use Lamoda\MultiEnvTests\Support\TestHeadersManager;
use PHPUnit\Framework\TestCase;

class HostBasedEnvResolvingStrategyTest extends TestCase
{
    use TestHeadersManager, TestCliArgsManager, TestEnvManager;

    public function tearDown(): void
    {
        parent::tearDown();
        $this->removeTestHeaders();
        $this->removeTestCliArgs();
        $this->removeTestEnv();
    }

    /**
     * @param string $env
     * @param HostDetectorInterface $hostDetector
     * @param FormatterInterface $nameFormatter
     * @param array $testHeaders
     * @param array $testCliArgs
     * @param array $testEnvs
     * @param string $expected
     * @dataProvider getEnvDataProvider
     */
    public function testGetEnv(
        string $env,
        HostDetectorInterface $hostDetector,
        FormatterInterface $nameFormatter,
        array $testHeaders,
        array $testCliArgs,
        array $testEnvs,
        string $expected
    ): void {
        $this->addTestHeaders($testHeaders);
        $this->addTestCliArgs($testCliArgs);
        $this->addTestEnv($testEnvs);
        $strategy = new HostBasedEnvResolvingStrategy($hostDetector, $nameFormatter);
        $this->assertEquals($expected, $strategy->getEnv($env));
    }

    /**
     * @throws HostDetectorException
     * @return array
     */
    public function getEnvDataProvider(): array
    {
        $nameFormatter = new FormatterPipeline([
            new PrefixAppendFormatter('-'),
            new CharReplaceFormatter('-', '_')
        ]);
        $headerBasedDetector = new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID');

        return [
            'getEnvBasedOnHeadersHeaderNotExistEnvNotExist' => [
                'env' => 'DB_HOST',
                'hostDetector' => $headerBasedDetector,
                'nameFormatter' => $nameFormatter,
                'headers' => [],
                'cliArgs' => [],
                'envs' => [],
                'expected' => ''
            ],
            'getEnvBasedOnHeadersHeaderNotExistEnvExist' => [
                'env' => 'DB_HOST',
                'hostDetector' => $headerBasedDetector,
                'nameFormatter' => $nameFormatter,
                'headers' => [],
                'cliArgs' => [],
                'envs' => [
                    'DB_HOST' => 'correct_value',
                    'moscow2_DB_HOST' => 'incorrect_value'
                ],
                'expected' => ''
            ],
            'getEnvBasedOnHeadersHeaderExistEnvNotExist' => [
                'env' => 'DB_HOST',
                'hostDetector' => $headerBasedDetector,
                'nameFormatter' => $nameFormatter,
                'headers' => [
                    'HTTP_X_HOST_ID' => 'moscow2',
                    'HTTP_X_HOST' => 'wrong_id'
                ],
                'cliArgs' => [],
                'envs' => [
                    'DB_HOST' => 'wrong_value'
                ],
                'expected' => ''
            ],
            'getEnvBasedOnHeadersEnvExist' => [
                'env' => 'DB_HOST',
                'hostDetector' => $headerBasedDetector,
                'nameFormatter' => $nameFormatter,
                'header' => [
                    'HTTP_X_HOST_ID' => 'moscow2',
                    'HTTP_X_HOST' => 'wrong_host'
                ],
                'cliArgs' => [],
                'envs' => [
                    'moscow2_DB_HOST' => 'correct_db_host',
                    'DB_HOST' => 'incorrect_db_host'
                ],
                'expected' => 'correct_db_host'
            ],
            'getEnvBasedOnCliArgArgNotExistEnvNotExist' => [
                'env' => 'DB_HOST',
                'hostDetector' => new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                'nameFormatter' => $nameFormatter,
                'headers' => [],
                'cliArgs' => [],
                'envs' => [],
                'expected' => ''
            ],
            'getEnvBasedOnCliArgsArgNotExitsEnvExist' => [
                'env' => 'DB_HOST',
                'hostDetector' => new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                'nameFormatter' => $nameFormatter,
                'headers' => [],
                'cliArgs' => [],
                'envs' => [
                    'DB_HOST' => 'correct_value',
                    'moscow_DB_HOST' => 'incorrect_value'
                ],
                'expected' => ''
            ],
            'getEnvBasedOnCliArgsArgExistEnvNotExist' => [
                'env' => 'DB_HOST',
                'hostDetector' => new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                'nameFormatter' => $nameFormatter,
                'headers' => [],
                'cliArgs' => [
                    'host_id' => [
                        'value' => 'moscow2'
                    ],
                    'test_cli_arg' => [
                        'value' => 'moscow3'
                    ]
                ],
                'envs' => [
                    'DB_HOST' => 'wrong_value',
                    'moscow3_DB_HOST' => 'wrong_value'
                ],
                'expected' => ''
            ],
            'getEnvBasedOnCliArgsArgExistEnvExist' => [
                'env' => 'DB_HOST',
                'hostDetector' => new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                'nameFormatter' => $nameFormatter,
                'headers' => [],
                'cliArgs' => [
                    'host_id' => [
                        'value' => 'moscow2'
                    ],
                    'host_id_test' => [
                        'value' => 'moscow3'
                    ]
                ],
                'envs' => [
                    'DB_HOST' => 'wrong_value',
                    'moscow2_DB_HOST' => 'correct_value',
                    'moscow3_DB_HOST' => 'incorrect_value'
                ],
                'expected' => 'correct_value'
            ],
            'getEnvBasedOnHeaderHeaderExist' => [
                'env' => 'DB_HOST',
                'hostDetector' => new FirstSuccessfulHostDetector([
                    $headerBasedDetector,
                    new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build())
                ]),
                'nameFormatter' => $nameFormatter,
                'headers' => [
                    'HTTP_X_HOST_ID' => 'kursk',
                    'HOST_ID' => 'moscow'
                ],
                'cliArgs' => [
                    'host_id' => [
                        'value' => 'piter',
                        'useKeyValueSeparator' => true
                    ]
                ],
                'envs' => [
                    'DB_HOST' => 'wrong0',
                    'kursk_DB_HOST' => 'correct',
                    'moscow_DB_HOST' => 'wrong1',
                    'piter_DB_HOST' => 'wrong2'
                ],
                'expected' => 'correct'
            ],
            'getEnvBasedOnHeaderHeaderNotExist' => [
                'env' => 'DB_HOST',
                'hostDetector' => new FirstSuccessfulHostDetector([
                    $headerBasedDetector,
                    new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build())
                ]),
                'nameFormatter' => $nameFormatter,
                'headers' => [
                    'HOST_ID' => 'test'
                ],
                'cliArgs' => [
                    'host_id' => [
                        'value' => 'test-1'
                    ]
                ],
                'envs' => [
                    'DB_HOST' => 'incorrect',
                    'test_DB_HOST' => 'wrong',
                    'test_1_DB_HOST' => 'correct'
                ],
                'expected' => 'correct'
            ],
            'getEnvBasedOnCliArgsArgExist' => [
                'env' => 'DB_HOST',
                'hostDetector' => new FirstSuccessfulHostDetector([
                    new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                    $headerBasedDetector
                ]),
                'nameFormatter' => $nameFormatter,
                'headers' => [
                    'HTTP_X_HOST_ID' => 'piter'
                ],
                'cliArgs' => [
                    'host_id' => [
                        'value' => 'MOSCOW'
                    ]
                ],
                'envs' => [
                    'DB_HOST' => 'wrong_value',
                    'piter_DB_HOST' => 'incorrect_value',
                    'MOSCOW_DB_HOST' => 'correct'
                ],
                'expected' => 'correct'
            ],
            'getEnvBasedOnCliArgsArgNotExist' => [
                'env' => 'DB_HOST',
                'hostDetector' => new FirstSuccessfulHostDetector([
                    new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                    $headerBasedDetector
                ]),
                'nameFormatter' => $nameFormatter,
                'headers' => [
                    'HTTP_X_HOST_ID' => 'piter',
                ],
                'cliArgs' => [
                    'host' => [
                        'value' => 'kursk'
                    ]
                ],
                'envs' => [
                    'DB_HOST' => 'wrong',
                    'piter_DB_HOST' => 'correct',
                    'kursk_DB_HOST' => 'incorrect'
                ],
                'expected' => 'correct'
            ]
        ];
    }

    /**
     * @param string $env
     * @param HostDetectorInterface $hostDetector
     * @param FormatterInterface $nameFormatter
     * @param \Exception $expected
     * @dataProvider exceptionRaisedWhenGetEnvMethodCalled
     */
    public function testExceptionRaisedWhenGetEnvMethodCalled(
        string $env,
        HostDetectorInterface $hostDetector,
        FormatterInterface $nameFormatter,
        \Exception $expected
    ): void {
        $this->expectException(get_class($expected));
        $this->expectExceptionMessage($expected->getMessage());
        $strategy = new HostBasedEnvResolvingStrategy($hostDetector, $nameFormatter);
        $strategy->getEnv($env);
    }

    public function exceptionRaisedWhenGetEnvMethodCalled(): array
    {
        return [
            'emptyAll' => [
                'env' => '',
                'hostDetector' => new FirstSuccessfulHostDetector(),
                'nameFormatter' => new PrefixAppendFormatter('-'),
                'expected' => FormatterException::becauseEmptyNamePassed()
            ],
        ];
    }
}
