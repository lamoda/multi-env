<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Formatter;

use Lamoda\MultiEnv\Formatter\Exception\FormatterException;
use Lamoda\MultiEnv\Model\HostId;

final class SuffixAppendFormatter implements FormatterInterface
{
    private string $delimiter;

    public function __construct(string $delimiter = '')
    {
        $this->delimiter = trim($delimiter);
    }

    /**
     * @throws FormatterException
     */
    public function formatName(string $originalName, HostId $hostId): string
    {
        $originalName = trim($originalName);

        if (empty($originalName)) {
            throw FormatterException::becauseEmptyNamePassed();
        }

        return $originalName . $this->delimiter . $hostId;
    }
}
