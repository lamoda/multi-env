<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Unit\Strategy;

use Lamoda\MultiEnv\Strategy\RawEnvResolvingStrategy;
use Lamoda\MultiEnvTests\Support\TestEnvManager;
use PHPUnit\Framework\TestCase;

class RawEnvResolvingStrategyTest extends TestCase
{
    use TestEnvManager;
    /**
     * @var RawEnvResolvingStrategy $strategy
     */
    private $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new RawEnvResolvingStrategy();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->removeTestEnv();
    }

    /**
     * @param string $envToSearch
     * @param string $expected
     * @param array $testEnv
     * @dataProvider getEnvDataProvider
     */
    public function testGetEnv(string $envToSearch, string $expected, array $testEnv): void
    {
        $this->addTestEnv($testEnv);
        $this->assertEquals($expected, $this->strategy->getEnv($envToSearch));
    }

    public function getEnvDataProvider(): array
    {
        return [
            'emptyAll' => [
                'envToSearch' => '',
                'expected' => '',
                'testEnvs' => []
            ],
            'emptyEnvToSearch' => [
                'envToSearch' => '',
                'expected' => '',
                'testEnvs' => [
                    'PHP_TEST_ENV' => 'test-value',
                    'PHP_ANOTHER_TEST_ENV' => 'test-value1'
                ]
            ],
            'envToSearchContainsOnlySystemSymbols' => [
                'envToSearch' => "\t\r\n\0\x0B    ",
                'expected' => '',
                'testEnvs' => [
                    'PHP_TEST' => 'test',
                    'PHP_SOME_ENV' => 'test1',
                    'PHP_DB_HOST' => 'test2'
                ]
            ],
            'envNotContainsInEnvList' => [
                'envToSearch' => 'PHP_UNIQ_ENV',
                'expected' => '',
                'testEnvs' => [
                    'PHP_TEST' => 'test',
                    'PHP_TEST1' => 'test1'
                ]
            ],
            'envContains' => [
                'envToSearch' => 'PHP_UNIQ_ENV',
                'expected' => 'uniq_env_value',
                'testEnvs' => [
                    'PHP_SOME_ENV_TEST' => 'some,value',
                    'PHP_UNIQ_ENV' => 'uniq_env_value'
                ]
            ],
            'envContainsButPassedInIncorrectFormat' => [
                'envToSearch' => 'PHP-UNIQ-ENV   ',
                'expected' => '',
                'testEnvs' => [
                    'PHP_ENV' => 'yep,its_env_value',
                    'PHP_UNIQ_ENV' => 'some-random,value'
                ]
            ],
            'envToSearchInLowerRegister' => [
                'envToSearch' => 'php_test_env',
                'expected' => 'success',
                'testEnvs' => [
                    'PHP_ENV' => 'some value',
                    'php_test_env' => 'success'
                ]
            ],
            'envToSearchInUpperRegister' => [
                'envToSearch' => 'PHP_TEST_ENV',
                'expected' => 'success',
                'testEnvs' => [
                    'PHP_ENV' => 'some value',
                    'PHP_TEST_ENV' => 'success'
                ]
            ]
        ];
    }
}
