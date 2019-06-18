<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Functional\FileReader;

use Lamoda\MultiEnv\FileReader\DotEnvFileReaderAdapter;
use Lamoda\MultiEnv\FileReader\Exception\EnvFileReaderException;
use Lamoda\MultiEnv\FileReader\FileNameResolver\FileNameResolver;
use Lamoda\MultiEnv\FileReader\PathResolver\PathResolver;
use Lamoda\MultiEnv\Formatter\CharReplaceFormatter;
use Lamoda\MultiEnv\Formatter\FormatterPipeline;
use Lamoda\MultiEnv\Formatter\PrefixAppendFormatter;
use Lamoda\MultiEnv\Formatter\SuffixAppendFormatter;
use Lamoda\MultiEnv\Model\HostId;
use Lamoda\MultiEnvTests\Support\TestEnvFileManager;
use PHPUnit\Framework\TestCase;

class DotEnvFileReaderAdapterTest extends TestCase
{
    use TestEnvFileManager;

    protected function tearDown(): void
    {
        $this->removeAllTestEnvFilesFromDir($this->getBasePathToDataFolder());
    }

    /**
     * @param string $relativePathToFile
     * @param string $fileName
     * @param PathResolver $pathResolver
     * @param FileNameResolver $fileNameResolver
     * @param HostId $hostId
     * @param array $envsToWrite
     * @param string $envToSearch
     * @param string $expectedValue
     * @dataProvider readEnvFileDataProvider
     */
    public function testReadEnvFileHappyPath(
        string $relativePathToFile,
        string $fileName,
        PathResolver $pathResolver,
        FileNameResolver $fileNameResolver,
        HostId $hostId,
        array $envsToWrite,
        string $envToSearch,
        string $expectedValue
    ): void {
        $this->createFileWithEnvs($envsToWrite, $relativePathToFile, $fileName);

        $fileReader = new DotEnvFileReaderAdapter($pathResolver, $fileNameResolver);
        $fileReader->readEnvFile($hostId);

        $this->assertEquals($expectedValue, getenv($envToSearch));
    }

    public function readEnvFileDataProvider(): array
    {
        return [
            'commonFileInRoot' => [
                'relativePathToFile' => '',
                'fileName' => '.env',
                'pathResolver' => new PathResolver($this->getBasePathToDataFolder()),
                'fileNameResolver' => new FileNameResolver(),
                'hostId' => new HostId('some_host'),
                'envsToWrite' => [
                    'TEST_ENV' => 'some_value',
                    'ANOTHER_TEST_ENV' => 'another_value'
                ],
                'envToSearch' => 'TEST_ENV',
                'expected' => 'some_value',
            ],
            'fileInRootWithPrefix' => [
                'relativePathToFile' => '',
                'fileName' => '.some_value.env',
                'pathResolver' => new PathResolver($this->getBasePathToDataFolder()),
                'fileNameResolver' => new FileNameResolver(FileNameResolver::DEFAULT_FILE_NAME, new FormatterPipeline([
                    new PrefixAppendFormatter('.'),
                    new CharReplaceFormatter('-', '_')
                ])),
                'hostId' => new HostId('some_value'),
                'envsToWrite' => [
                    'TEST_ENV' => 'different_value',
                    'TEST_ENV1' => 'wrong',
                    'TEST_ENV2' => 'another_wrong'
                ],
                'envToSearch' => 'TEST_ENV',
                'expected' => 'different_value'
            ],
            'fileInRootWithSuffix' => [
                'relativePathToFile' => '',
                'fileName' => '.env_some_host',
                'pathResolver' => new PathResolver($this->getBasePathToDataFolder()),
                'fileNameResolver' => new FileNameResolver(FileNameResolver::DEFAULT_FILE_NAME, new FormatterPipeline([
                    new SuffixAppendFormatter('-'),
                    new CharReplaceFormatter('-', '_')
                ])),
                'hostId' => new HostId('some-host'),
                'envsToWrite' => [
                    'ENV_TO_SEARCH' => 'some_value',
                    'WRONG_ENV' => 'wrong'
                ],
                'envToSearch' => 'ENV_TO_SEARCH',
                'expected' => 'some_value'
            ],
            'defaultFileNotInRoot' => [
                'relativePathToFile' => 'test_host',
                'fileName' => '.env',
                'pathResolver' => new PathResolver($this->getBasePathToDataFolder(), new SuffixAppendFormatter(DIRECTORY_SEPARATOR)),
                'fileNameResolver' => new FileNameResolver(FileNameResolver::DEFAULT_FILE_NAME),
                'hostId' => new HostId('test_host'),
                'envToWrite' => [
                    'SOME_ENV' => 'correct_value',
                    'INCORRECT_ENV' => 'incorrect_value'
                ],
                'envToSearch' => 'SOME_ENV',
                'expected' => 'correct_value'
            ],
            'notDefaultFileNotInRoot' => [
                'relativePathToFile' => 'test-id',
                'fileName' => 'test_id_env',
                'pathResolve' => new PathResolver($this->getBasePathToDataFolder(), new SuffixAppendFormatter(DIRECTORY_SEPARATOR)),
                'fileNameResolver' => new FileNameResolver('env', new FormatterPipeline([
                    new PrefixAppendFormatter('-'),
                    new CharReplaceFormatter('-', '_')
                ])),
                'hostId' => new HostId('test-id'),
                'entToWrite' => [
                    'SOME_ENV' => 'totally_correct_value',
                    'TEST_ENV' => 'test_value'
                ],
                'envToSearch' => 'SOME_ENV',
                'expected' => 'totally_correct_value'
            ]
        ];
    }

    /**
     * @param string $envToSearch
     * @param string $expected
     * @param array $startEnv
     * @param array $finalEnv
     * @dataProvider readFileOnlyOnceDataProvider
     */
    public function testThatAdapterReadFileOnlyOnce(
        string $envToSearch,
        string $expected,
        array $startEnv,
        array $finalEnv
    ): void {
        $this->createFileWithEnvs($startEnv);

        $hostId = new HostId('test');
        $fileReader = new DotEnvFileReaderAdapter(
            new PathResolver($this->getBasePathToDataFolder()),
            new FileNameResolver()
        );
        $fileReader->readEnvFile($hostId);
        $this->assertEquals($expected, getenv($envToSearch));

        $this->createFileWithEnvs($finalEnv);
        $fileReader->readEnvFile($hostId);
        $this->assertEquals($expected, getenv($envToSearch));
    }

    public function readFileOnlyOnceDataProvider(): array
    {
        return [
            [
                'envToSearch' => 'TEST_ENV',
                'expected' => 'correct_value',
                'startEnvs' => [
                    'TEST_ENV' => 'correct_value',
                    'ANOTHER_ENV' => 'test_value'
                ],
                'endEnvs' => [
                    'TEST_ENV' => 'incorrect_value',
                    'ANOTHER_ENV' => 'some_value'
                ]
            ]
        ];
    }

    /**
     * @param string $relativePathToFile
     * @param string $fileName
     * @param PathResolver $pathResolver
     * @param FileNameResolver $fileNameResolver
     * @param HostId $hostId
     * @param array $envToWrite
     * @param \Throwable $expected
     * @dataProvider exceptionRaisedDataProvider
     */
    public function testExceptionRaisedWhenFileCantBeLoad(
        string $relativePathToFile,
        string $fileName,
        PathResolver $pathResolver,
        FileNameResolver $fileNameResolver,
        HostId $hostId,
        array $envToWrite,
        \Throwable $expected
    ): void {
        $this->createFileWithEnvs($envToWrite, $relativePathToFile, $fileName);

        $this->expectException(\get_class($expected));
        $this->expectExceptionMessage($expected->getMessage());

        $resolver = new DotEnvFileReaderAdapter($pathResolver, $fileNameResolver);
        $resolver->readEnvFile($hostId);
    }

    public function exceptionRaisedDataProvider(): array
    {
        return [
            'defaultFileInRoot' => [
                'relativePathToFile' => '',
                'fileName' => '.env',
                'pathResolver' => new PathResolver($this->getBasePathToDataFolder(), new SuffixAppendFormatter(DIRECTORY_SEPARATOR)),
                'fileNameResolver' => new FileNameResolver(FileNameResolver::DEFAULT_FILE_NAME, new FormatterPipeline([
                    new SuffixAppendFormatter('-'),
                    new CharReplaceFormatter('-', '_')
                ])),
                'hostId' => new HostId('test'),
                'envToWrite' => [
                    'TEST_ENV' => 'some_value'
                ],
                'expected' => EnvFileReaderException::becauseEnvFileCanNotBeProcessed(null)
            ]
        ];
    }
}
