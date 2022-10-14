<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Unit\Formatter;

use Lamoda\MultiEnv\Formatter\Exception\FormatterException;
use Lamoda\MultiEnv\Formatter\PrefixAppendFormatter;
use Lamoda\MultiEnv\Model\HostId;
use PHPUnit\Framework\TestCase;

class PrefixFormatterTest extends TestCase
{
    /**
     * @dataProvider formatEnvNameDataProvider
     */
    public function testFormatEnvName(string $delimiter, string $originalName, HostId $hostId, string $expected): void
    {
        $formatter = new PrefixAppendFormatter($delimiter);
        $this->assertEquals($expected, $formatter->formatName($originalName, $hostId));
    }

    public function formatEnvNameDataProvider(): array
    {
        return [
            'notEmptyOriginalName' => [
                'delimiter' => '',
                'originalName' => 'DB_HOST',
                'hostId' => new HostId(''),
                'expected' => 'DB_HOST',
            ],
            'notEmptyOriginalNameHaveBracedSpaces' => [
                'delimiter' => '',
                'originalName' => '   DB_HOST   ',
                'hostId' => new HostId(''),
                'expected' => 'DB_HOST',
            ],
            'notEmptyOriginalNameHaveDashSeparator' => [
                'delimiter' => '',
                'originalName' => 'DB-HOST',
                'hostId' => new HostId(''),
                'expected' => 'DB-HOST',
            ],
            'notEmptyOriginalNameAndDelimiter' => [
                'delimiter' => '__',
                'originalName' => 'DB_HOST',
                'hostId' => new HostId(''),
                'expected' => '__DB_HOST',
            ],
            'notEmptyOriginalNameAndDelimiterAllHaveDashInValue' => [
                'delimiter' => '---',
                'originalName' => '--DB-HOST--',
                'hostId' => new HostId(''),
                'expected' => '-----DB-HOST--',
            ],
            'notEmptyAll' => [
                'delimiter' => '__',
                'originalName' => 'DB_HOST',
                'hostId' => new HostId('TEST_HOST'),
                'expected' => 'TEST_HOST__DB_HOST',
            ],
            'notEmptyAllDelimiterIsDash' => [
                'delimiter' => '-',
                'originalName' => 'DB_HOST',
                'hostId' => new HostId('TEST_HOST'),
                'expected' => 'TEST_HOST-DB_HOST',
            ],
            'notEmptyAllHaveDash' => [
                'delimiter' => '-_-',
                'originalName' => 'DB-HOST',
                'hostId' => new HostId('TEST-HOST'),
                'expected' => 'TEST-HOST-_-DB-HOST',
            ],
            'notEmptyAllOriginalNameAndHostIdInLowerCase' => [
                'delimiter' => '_',
                'originalName' => 'db_host',
                'hostId' => new HostId('test_host'),
                'expected' => 'test_host_db_host',
            ],
        ];
    }

    /**
     * @dataProvider exceptionRaisedWhileEnvNameFormatDataProvider
     */
    public function testExceptionRaisedWhileEnvNameFormat(
        string $delimiter,
        string $originalName,
        HostId $hostId,
        \Exception $expected
    ): void {
        $this->expectException(get_class($expected));
        $this->expectExceptionMessage($expected->getMessage());
        $formatter = new PrefixAppendFormatter($delimiter);
        $formatter->formatName($originalName, $hostId);
    }

    public function exceptionRaisedWhileEnvNameFormatDataProvider(): array
    {
        return [
            'emptyAll' => [
                'delimiter' => '',
                'originalName' => '',
                'hostId' => new HostId(''),
                'expected' => FormatterException::becauseEmptyNamePassed(),
            ],
            'notEmptyDelimiter' => [
                'delimiter' => '---',
                'originalName' => '',
                'hostId' => new HostId(''),
                'expected' => FormatterException::becauseEmptyNamePassed(),
            ],
            'notEmptyDelimiterAndHostId' => [
                'delimiter' => '---',
                'originalName' => '',
                'hostId' => new HostId('test_host'),
                'expected' => FormatterException::becauseEmptyNamePassed(),
            ],
        ];
    }
}
