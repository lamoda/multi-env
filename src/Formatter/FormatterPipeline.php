<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Formatter;

use Lamoda\MultiEnv\Model\HostId;

final class FormatterPipeline implements FormatterInterface
{
    /**
     * @var FormatterInterface[]
     */
    private array $formatters;

    public function __construct(array $formatters)
    {
        $this->formatters = $formatters;
    }

    public function formatName(string $originalName, HostId $hostId): string
    {
        $processResult = $originalName;
        foreach ($this->formatters as $formatter) {
            $processResult = $formatter->formatName($processResult, $hostId);
        }

        return $processResult;
    }
}
