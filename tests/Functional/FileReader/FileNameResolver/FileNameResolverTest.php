<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Functional\FileReader\FileNameResolver;

use Lamoda\MultiEnv\FileReader\FileNameResolver\FileNameResolver;
use Lamoda\MultiEnv\Formatter\CharReplaceFormatter;
use Lamoda\MultiEnv\Formatter\FormatterInterface;
use Lamoda\MultiEnv\Formatter\FormatterPipeline;
use Lamoda\MultiEnv\Formatter\PrefixAppendFormatter;
use Lamoda\MultiEnv\Formatter\SuffixAppendFormatter;
use Lamoda\MultiEnv\Model\HostId;
use PHPUnit\Framework\TestCase;

class FileNameResolverTest extends TestCase
{
    /**
     * @dataProvider fileNameResolveDataProvider
     */
    public function testFileNameResolve(
        string $originalFileName,
        ?FormatterInterface $formatter,
        HostId $hostId,
        string $expected
    ): void {
        $resolver = new FileNameResolver($originalFileName, $formatter);
        $this->assertEquals($expected, $resolver->resolveEnvFileName($hostId));
    }

    public function fileNameResolveDataProvider(): array
    {
        return [
            'emptyAll' => [
                'originalName' => '',
                'formatter' => null,
                'hostId' => new HostId(''),
                'expected' => '.env',
            ],
            'notEmptyOriginalName' => [
                'originalName' => '.some_not_default_file_name.env',
                'formatter' => null,
                'hostId' => new HostId(''),
                'expected' => '.some_not_default_file_name.env',
            ],
            'notEmptyOriginalNameWithoutDot' => [
                'originalName' => 'another_file_name.env',
                'formatter' => null,
                'hostId' => new HostId(''),
                'expected' => 'another_file_name.env',
            ],
            'emptyFormatterOnly' => [
                'originalName' => '.test_file',
                'formatter' => null,
                'hostId' => new HostId('test_host'),
                'expected' => '.test_file',
            ],
            'prefixFormatter' => [
                'originalName' => '.env',
                'formatter' => new PrefixAppendFormatter('.'),
                'hostId' => new HostId('test_host'),
                'expected' => '.test_host.env',
            ],
            'prefixFormatterWithoutDot' => [
                'originalName' => 'some-name',
                'formatter' => new PrefixAppendFormatter('-'),
                'hostId' => new HostId('some-host'),
                'expected' => 'some-host-some-name',
            ],
            'prefixFormatterWithoutDotAndDelimiter' => [
                'originalName' => 'env',
                'formatter' => new PrefixAppendFormatter(''),
                'hostId' => new HostId('test'),
                'expected' => 'testenv',
            ],
            'suffixFormatter' => [
                'originalName' => '.env',
                'formatter' => new SuffixAppendFormatter('-'),
                'hostId' => new HostId('test_name'),
                'expected' => '.env-test_name',
            ],
            'suffixFormatterWithoutDot' => [
                'originalName' => 'test-env-file',
                'formatter' => new SuffixAppendFormatter('_'),
                'hostId' => new HostId('some_host'),
                'expected' => 'test-env-file_some_host',
            ],
            'suffixFormatterWithoutDotAndDelimiter' => [
                'originalName' => 'env',
                'formatter' => new SuffixAppendFormatter(''),
                'hostId' => new HostId('env'),
                'expected' => 'envenv',
            ],
            'formatterPipeline' => [
                'originalName' => 'test-file',
                'formatter' => new FormatterPipeline([
                    new SuffixAppendFormatter('-'),
                    new CharReplaceFormatter('-', '_'),
                ]),
                'hostId' => new HostId('env'),
                'expected' => 'test_file_env',
            ],
            'anotherFormatterPipeline' => [
                'originalName' => '.env',
                'formatter' => new FormatterPipeline([
                    new SuffixAppendFormatter('-'),
                    new PrefixAppendFormatter('||'),
                    new CharReplaceFormatter('|', '_'),
                    new CharReplaceFormatter('-', '_'),
                ]),
                'hostId' => new HostId('test'),
                'expected' => '.test__env_test',
            ],
        ];
    }
}
