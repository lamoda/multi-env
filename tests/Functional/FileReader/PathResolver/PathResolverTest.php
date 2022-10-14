<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Functional\FileReader\PathResolver;

use Lamoda\MultiEnv\FileReader\PathResolver\PathResolver;
use Lamoda\MultiEnv\Formatter\CharReplaceFormatter;
use Lamoda\MultiEnv\Formatter\FormatterInterface;
use Lamoda\MultiEnv\Formatter\FormatterPipeline;
use Lamoda\MultiEnv\Formatter\PrefixAppendFormatter;
use Lamoda\MultiEnv\Formatter\SuffixAppendFormatter;
use Lamoda\MultiEnv\Model\HostId;
use PHPUnit\Framework\TestCase;

class PathResolverTest extends TestCase
{
    /**
     * @dataProvider pathResolveDataProvider
     */
    public function testPathResolve(
        string $originalPath,
        ?FormatterInterface $formatter,
        HostId $hostId,
        string $expected
    ): void {
        $resolver = new PathResolver($originalPath, $formatter);
        $this->assertEquals($expected, $resolver->resolvePathToEnvFile($hostId));
    }

    public function pathResolveDataProvider(): array
    {
        return [
            'emptyAll' => [
                'originalPath' => '',
                'formatter' => null,
                'hostId' => new HostId(''),
                'expected' => '',
            ],
            'notEmptyOriginalPath' => [
                'originalPath' => '/var/config',
                'formatter' => null,
                'hostId' => new HostId(''),
                'expected' => '/var/config',
            ],
            'onlyFormatterEmpty' => [
                'originalPath' => '/var/test/env',
                'formatter' => null,
                'hostId' => new HostId('some_element'),
                'expected' => '/var/test/env',
            ],
            'prefixFormatter' => [
                'originalPath' => '/bin/console',
                'formatter' => new PrefixAppendFormatter(DIRECTORY_SEPARATOR),
                'hostId' => new HostId('test-element'),
                'expected' => 'test-element' . DIRECTORY_SEPARATOR . '/bin/console',
            ],
            'suffixFormatter' => [
                'originalPath' => '/test/some-folder',
                'formatter' => new SuffixAppendFormatter(DIRECTORY_SEPARATOR),
                'hostId' => new HostId('test-instance'),
                'expected' => '/test/some-folder' . DIRECTORY_SEPARATOR . 'test-instance',
            ],
            'formatterPipeline' => [
                'originalPath' => '/root/another-folder',
                'formatter' => new FormatterPipeline([
                    new SuffixAppendFormatter(DIRECTORY_SEPARATOR),
                    new CharReplaceFormatter('-', '_'),
                ]),
                'hostId' => new HostId('test-folder'),
                'expected' => '/root/another_folder' . DIRECTORY_SEPARATOR . 'test_folder',
            ],
            'anotherFormatterPipeline' => [
                'originalPath' => '/test/env',
                'formatter' => new FormatterPipeline([
                    new PrefixAppendFormatter(DIRECTORY_SEPARATOR),
                    new SuffixAppendFormatter('---'),
                    new CharReplaceFormatter('-', '_'),
                ]),
                'hostId' => new HostId('some-folder'),
                'expected' => 'some_folder' . DIRECTORY_SEPARATOR . '/test/env___some_folder',
            ],
        ];
    }
}
