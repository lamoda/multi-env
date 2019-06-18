<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Unit\Formatter;

use Exception;
use Lamoda\MultiEnv\Formatter\Exception\FormatterException;
use Lamoda\MultiEnv\Formatter\SuffixAppendFormatter;
use Lamoda\MultiEnv\Model\HostId;
use PHPUnit\Framework\TestCase;

class SuffixFormatterTest extends TestCase
{
    /**
     * @param string $delimiter
     * @param string $originalNam
     * @param HostId $hostId
     * @param string $expected
     * @dataProvider suffixFormattingDataProvider
     */
    public function testSuffixFormatting(string $delimiter, string $originalNam, HostId $hostId, string $expected): void
    {
        $formatter = new SuffixAppendFormatter($delimiter);
        $this->assertEquals($expected, $formatter->formatName($originalNam, $hostId));
    }

    public function suffixFormattingDataProvider(): array
    {
        return [
            'notEmptyOriginalName' => [
                'delimiter' => '',
                'originalName' => 'DB_HOST',
                'hostId' => new HostId(''),
                'expected' => 'DB_HOST'
            ],
            'notEmptyOriginalNameHaveBracedSpaces' => [
                'delimiter' => '',
                'originalName' => '   DB_HOST   ',
                'hostId' => new HostId(''),
                'expected' => 'DB_HOST'
            ],
            'notEmptyOriginalNameHaveDashSeparator' => [
                'delimiter' => '',
                'originalName' => 'DB-HOST',
                'hostId' => new HostId(''),
                'expected' => 'DB-HOST'
            ],
            'notEmptyOriginalNameAndDelimiter' => [
                'delimiter' => '__',
                'originalName' => 'DB_HOST',
                'hostId' => new HostId(''),
                'expected' => 'DB_HOST__'
            ],
            'notEmptyOriginalNameAndDelimiterAllHaveDashInValue' => [
                'delimiter' => '---',
                'originalName' => '--DB-HOST--',
                'hostId' => new HostId(''),
                'expected' => '--DB-HOST-----'
            ],
            'notEmptyAll' => [
                'delimiter' => '__',
                'originalName' => 'DB_HOST',
                'hostId' => new HostId('TEST_HOST'),
                'expected' => 'DB_HOST__TEST_HOST'
            ],
            'notEmptyAllDelimiterIsDash' => [
                'delimiter' => '-',
                'originalName' => 'DB_HOST',
                'hostId' => new HostId('TEST_HOST'),
                'expected' => 'DB_HOST-TEST_HOST'
            ],
            'notEmptyAllHaveDash' => [
                'delimiter' => '-_-',
                'originalName' => 'DB-HOST',
                'hostId' => new HostId('TEST-HOST'),
                'expected' => 'DB-HOST-_-TEST-HOST'
            ],
            'notEmptyAllOriginalNameAndHostIdInLowerCase' => [
                'delimiter' => '_',
                'originalName' => 'db_host',
                'hostId' => new HostId('test_host'),
                'expected' => 'db_host_test_host'
            ]
        ];
    }

    /**
     * @param string $delimiter
     * @param string $originalName
     * @param HostId $hostId
     * @param Exception $expected
     * @dataProvider exceptionRaisedWhileFormatDataProvider
     */
    public function testExceptionRaisedWhileFormat(
        string $delimiter,
        string $originalName,
        HostId $hostId,
        Exception $expected
    ): void {
        $this->expectException(get_class($expected));
        $this->expectExceptionMessage($expected->getMessage());
        $formatter = new SuffixAppendFormatter($delimiter);
        $formatter->formatName($originalName, $hostId);
    }

    public function exceptionRaisedWhileFormatDataProvider(): array
    {
        return [
            'emptyAll' => [
                'delimiter' => '',
                'originalName' => '',
                'hostId' => new HostId(''),
                'expected' => FormatterException::becauseEmptyNamePassed()
            ],
            'notEmptyDelimiter' => [
                'delimiter' => '---',
                'originalName' => '',
                'hostId' => new HostId(''),
                'expected' => FormatterException::becauseEmptyNamePassed()
            ],
            'notEmptyDelimiterAndHostId' => [
                'delimiter' => '---',
                'originalName' => '',
                'hostId' => new HostId('test_host'),
                'expected' => FormatterException::becauseEmptyNamePassed()
            ],
        ];
    }
}
