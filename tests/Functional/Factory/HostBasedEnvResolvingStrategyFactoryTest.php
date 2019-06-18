<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Functional\Factory;

use Lamoda\MultiEnv\Factory\HostBasedEnvResolvingStrategyFactory;
use Lamoda\MultiEnvTests\Support\TestCliArgsManager;
use Lamoda\MultiEnvTests\Support\TestEnvManager;
use Lamoda\MultiEnvTests\Support\TestHeadersManager;
use PHPUnit\Framework\TestCase;

class HostBasedEnvResolvingStrategyFactoryTest extends TestCase
{
    use TestHeadersManager, TestCliArgsManager, TestEnvManager;

    protected function tearDown(): void
    {
        $this->removeTestHeaders();
        $this->removeTestCliArgs();
        $this->removeTestEnv();
    }

    /**
     * @param string $envToSearch
     * @param string $expected
     * @param array $testHeaders
     * @param array $testCliArgs
     * @param array $testEnvs
     * @param array $builderConfig
     * @dataProvider createStrategyDataProvider
     */
    public function testCreateStrategy(
        string $envToSearch,
        string $expected,
        array $testHeaders,
        array $testCliArgs,
        array $testEnvs,
        array $builderConfig
    ): void {
        $this->addTestHeaders($testHeaders);
        $this->addTestCliArgs($testCliArgs);
        $this->addTestEnv($testEnvs);

        $strategy = HostBasedEnvResolvingStrategyFactory::createStrategy(
            $builderConfig['header'],
            $builderConfig['cliArg'],
            $builderConfig['delimiter']
        );
        $this->assertEquals($expected, $strategy->getEnv($envToSearch));
    }

    public function createStrategyDataProvider(): array
    {
        return [
            'foundByHeader' => [
                'envToSearch' => 'DB_HOST',
                'expected' => 'correct',
                'headers' => [
                    'HTTP_X_HOST_ID' => 'test_id',
                    'HOST_ID' => 'moscow'
                ],
                'cliArgs' => [
                    'host_id' => [
                        'value' => 'wrong_id'
                    ]
                ],
                'envs' => [
                    'DB_HOST' => 'wrong',
                    'test_id___DB_HOST' => 'correct',
                    'wrong_id___DB_HOST' => 'wrong'
                ],
                'builderConfig' => [
                    'header' => 'HTTP_X_HOST_ID',
                    'cliArg' => 'host_id',
                    'delimiter' => '___'
                ]
            ],
            'foundByCli' => [
                'envToSearch' => 'DB_HOST',
                'expected' => 'correct',
                'headers' => [
                    'TEST_ID' => 'test_id',
                    'HOST_ID' => 'moscow'
                ],
                'cliArgs' => [
                    'host_id' => [
                        'value' => 'correct_id'
                    ]
                ],
                'envs' => [
                    'DB_HOST' => 'wrong',
                    'test_id___DB_HOST' => 'wrong',
                    'wrong_id___DB_HOST' => 'wrong',
                    'correct_id___DB_HOST' => 'correct'
                ],
                'builderConfig' => [
                    'header' => 'HTTP_X_HOST_ID',
                    'cliArg' => 'host_id',
                    'delimiter' => '___'
                ]
            ]
        ];
    }
}
