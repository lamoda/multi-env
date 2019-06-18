<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Functional\Factory;

use Lamoda\MultiEnv\Factory\FileBasedEnvResolvingStrategyFactory;
use Lamoda\MultiEnvTests\Support\TestCliArgsManager;
use Lamoda\MultiEnvTests\Support\TestEnvFileManager;
use Lamoda\MultiEnvTests\Support\TestHeadersManager;
use PHPUnit\Framework\TestCase;

class FileBasedEnvResolvingStrategyFactoryTest extends TestCase
{
    use TestHeadersManager, TestCliArgsManager, TestEnvFileManager;

    protected function tearDown(): void
    {
        $this->removeTestHeaders();
        $this->removeTestCliArgs();
        $this->removeAllTestEnvFilesFromDir($this->getBasePathToDataFolder());
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
        $this->createFileWithEnvs($testEnvs['envs'], $testEnvs['relativePathToFile'], $testEnvs['fileName']);

        $strategy = FileBasedEnvResolvingStrategyFactory::createStrategy(
            $builderConfig['header'],
            $builderConfig['cliArg'],
            $builderConfig['envFile'],
            $builderConfig['filePath']
        );
        $this->assertEquals($expected, $strategy->getEnv($envToSearch));
    }

    public function createStrategyDataProvider(): array
    {
        return [
            'commonEnvFileNotInRootFoundByHeader' => [
                'envToSearch' => 'TEST_ENV',
                'expected' => 'some_value',
                'headers' => [
                    'HTTP_X_HOST_ID' => 'correct_id',
                ],
                'cliArgs' => [
                    'host_id' => [
                        'value' => 'wrong_host'
                    ]
                ],
                'testEnvs' => [
                    'relativePathToFile' => 'correct_id',
                    'fileName' => '.env',
                    'envs' => [
                        'TEST_ENV' => 'some_value',
                        'ANOTHER_ENV' => 'incorrect'
                    ],
                ],
                'builderConfig' => [
                    'header' => 'HTTP_X_HOST_ID',
                    'cliArg' => 'host_id',
                    'envFile' => '.env',
                    'filePath' => $this->getBasePathToDataFolder()
                ],
            ],
            'commonEnvFileNotInRootFoundByCliArg' => [
                'envToSearch' => 'RANDOM_ENV',
                'expected' => 'correct',
                'headers' => [],
                'cliArgs' => [
                    'host_id' => [
                        'value' => 'correct_host_id'
                    ]
                ],
                'testEnvs' => [
                    'relativePathToFile' => 'correct_host_id',
                    'fileName' => '.env',
                    'envs' => [
                        'TEST_ENV' => 'incorrect',
                        'WRONG_ENV' => 'some_value',
                        'RANDOM_ENV' => 'correct'
                    ],
                ],
                'builderConfig' => [
                    'header' => 'HTTP_X_HOST_ID',
                    'cliArg' => 'host_id',
                    'envFile' => '.env',
                    'filePath' => $this->getBasePathToDataFolder()
                ],
            ],
        ];
    }
}
