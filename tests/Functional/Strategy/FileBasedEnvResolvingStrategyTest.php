<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Functional\Strategy;

use Lamoda\MultiEnv\FileReader\DotEnvV2FileReaderAdapter;
use Lamoda\MultiEnv\FileReader\EnvFileReaderInterface;
use Lamoda\MultiEnv\FileReader\FileNameResolver\FileNameResolver;
use Lamoda\MultiEnv\FileReader\PathResolver\PathResolver;
use Lamoda\MultiEnv\Formatter\CharReplaceFormatter;
use Lamoda\MultiEnv\Formatter\FormatterPipeline;
use Lamoda\MultiEnv\Formatter\PrefixAppendFormatter;
use Lamoda\MultiEnv\Formatter\SuffixAppendFormatter;
use Lamoda\MultiEnv\HostDetector\CliArgsBasedHostDetector;
use Lamoda\MultiEnv\HostDetector\Factory\GetOptAdapterFactory;
use Lamoda\MultiEnv\HostDetector\HostDetectorInterface;
use Lamoda\MultiEnv\HostDetector\ServerHeadersBasedHostDetector;
use Lamoda\MultiEnv\Strategy\EnvResolvingStrategyInterface;
use Lamoda\MultiEnv\Strategy\FileBasedEnvResolvingStrategy;
use Lamoda\MultiEnv\Strategy\HostBasedEnvResolvingStrategy;
use Lamoda\MultiEnv\Strategy\RawEnvResolvingStrategy;
use Lamoda\MultiEnvTests\Support\TestCliArgsManager;
use Lamoda\MultiEnvTests\Support\TestEnvFileManager;
use Lamoda\MultiEnvTests\Support\TestHeadersManager;
use PHPUnit\Framework\TestCase;

final class FileBasedEnvResolvingStrategyTest extends TestCase
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
     * @param HostDetectorInterface $hostDetector
     * @param EnvFileReaderInterface $envFileReader
     * @param EnvResolvingStrategyInterface $envResolvingStrategy
     * @dataProvider envResolvingDataProvider
     */
    public function testEnvResolvingByStrategy(
        string $envToSearch,
        string $expected,
        array $testHeaders,
        array $testCliArgs,
        array $testEnvs,
        HostDetectorInterface $hostDetector,
        EnvFileReaderInterface $envFileReader,
        EnvResolvingStrategyInterface $envResolvingStrategy
    ): void {
        $this->addTestHeaders($testHeaders);
        $this->addTestCliArgs($testCliArgs);
        $this->createFileWithEnvs($testEnvs['envs'], $testEnvs['relativePathToFile'], $testEnvs['fileName']);

        $strategy = new FileBasedEnvResolvingStrategy($hostDetector, $envFileReader, $envResolvingStrategy);
        $this->assertEquals($expected, $strategy->getEnv($envToSearch));
    }

    public function envResolvingDataProvider(): array
    {
        return [
            'commonEnvFileInRoot' => [
                'envToSearch' => 'TEST_ENV',
                'expected' => 'correct_value',
                'headers' => [
                    'HTTP_X_HOST_ID' => 'test_host'
                ],
                'cliArgs' => [
                    'host_id' => [
                        'value' => 'some_other_host'
                    ]
                ],
                'testEnvs' => [
                    'relativePathToFile' => '',
                    'fileName' => '.env',
                    'envs' => [
                        'TEST_ENV' => 'correct_value',
                        'WRONG_ENV' => 'wrong'
                    ]
                ],
                'hostDetector' => new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                'fileReader' => new DotEnvV2FileReaderAdapter(
                    new PathResolver($this->getBasePathToDataFolder()),
                    new FileNameResolver()
                ),
                'strategy' => new RawEnvResolvingStrategy()
            ],
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
                'hostDetector' => new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                'fileReader' => new DotEnvV2FileReaderAdapter(
                    new PathResolver($this->getBasePathToDataFolder(), new SuffixAppendFormatter(DIRECTORY_SEPARATOR)),
                    new FileNameResolver()
                ),
                'strategy' => new RawEnvResolvingStrategy()
            ],
            'commonEnvFileNotInRootFoundByCliArg' => [
                'envToSearch' => 'RANDOM_ENV',
                'expected' => 'correct',
                'headers' => [
                    'HTTP_X_HOST_ID' => 'incorrect_id'
                ],
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
                'hostDetector' => new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                'fileReader' => new DotEnvV2FileReaderAdapter(
                    new PathResolver($this->getBasePathToDataFolder(), new SuffixAppendFormatter(DIRECTORY_SEPARATOR)),
                    new FileNameResolver()
                ),
                'strategy' => new RawEnvResolvingStrategy()
            ],
            'uncommonEnvFileInRootFoundByHeader' => [
                'envToSearch' => 'TEST_ENV',
                'expected' => 'test_env_value',
                'headers' => [
                    'HTTP_X_HOST_ID' => 'test_instance_id',
                ],
                'cliArgs' => [
                    'host_id' => [
                        'value' => 'incorrect_instance_id'
                    ]
                ],
                'testEnvs' => [
                    'relativePathToFile' => '',
                    'fileName' => '.test_instance_id_env',
                    'envs' => [
                        'TEST_ENV' => 'test_env_value'
                    ],
                ],
                'hostDetector' => new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                'fileReader' => new DotEnvV2FileReaderAdapter(
                    new PathResolver($this->getBasePathToDataFolder()),
                    new FileNameResolver('.env', new PrefixAppendFormatter('_'))
                ),
                'strategy' => new RawEnvResolvingStrategy()
            ],
            'uncommonEnvFileInRootFoundByCliArg' => [
                'envToSearch' => 'SOME_ENV',
                'expected' => 'test',
                'headers' => [],
                'cliArgs' => [
                    'host_id' => [
                        'value' => 'instance_id'
                    ]
                ],
                'testEnvs' => [
                    'relativePathToFile' => '',
                    'fileName' => 'instance_id.env',
                    'envs' => [
                        'SOME_ENV' => 'test'
                    ]
                ],
                'hostDetector' => new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build()),
                'fileReader' => new DotEnvV2FileReaderAdapter(
                    new PathResolver($this->getBasePathToDataFolder()),
                    new FileNameResolver('env', new PrefixAppendFormatter('.'))
                ),
                'strategy' => new RawEnvResolvingStrategy()
            ],
            'commonEnvInRootWithEnvWithPrefixes' => [
                'envToSearch' => 'TEST_ENV',
                'expected' => 'correct_value',
                'headers' => [
                    'HTTP_X_HOST_ID' => 'test_instance'
                ],
                'cliArgs' => [],
                'testEnvs' => [
                    'relativePathToFile' => '',
                    'fileName' => '.env',
                    'envs' => [
                        'test_instance___TEST_ENV' => 'correct_value',
                        'TEST_ENV' => 'wrong_value'
                    ]
                ],
                'hostDetector' => new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                'fileReader' => new DotEnvV2FileReaderAdapter(
                    new PathResolver($this->getBasePathToDataFolder()),
                    new FileNameResolver('.env')
                ),
                'strategy' => new HostBasedEnvResolvingStrategy(
                    new ServerHeadersBasedHostDetector('HTTP_X_HOST_ID'),
                    new FormatterPipeline([
                        new PrefixAppendFormatter('---'),
                        new CharReplaceFormatter('-', '_')
                    ])
                )
            ]
        ];
    }
}
