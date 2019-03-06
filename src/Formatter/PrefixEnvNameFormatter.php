<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Formatter;

use Lamoda\MultiEnv\Formatter\Exception\FormatterException;
use Lamoda\MultiEnv\Model\HostId;

final class PrefixEnvNameFormatter implements EnvNameFormatterInterface
{
    /**
     * @var string $delimiter
     */
    private $delimiter;

    public function __construct(string $delimiter = '')
    {
        $this->delimiter = trim($delimiter);
    }

    /**
     * @param string $originalEnvName
     * @param HostId $hostId
     * @throws FormatterException
     * @return string
     */
    public function formatEnvName(string $originalEnvName, HostId $hostId): string
    {
        $originalEnvName = trim($originalEnvName);

        if (empty($originalEnvName)) {
            throw FormatterException::becauseEmptyEnvNamePassed();
        }

        $combinedEnvName = $hostId . $this->delimiter . $originalEnvName;

        return str_replace('-', '_', trim($combinedEnvName));
    }
}
