<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Formatter\Exception;

use Lamoda\MultiEnv\Exception\EnvProviderExceptionInterface;

class FormatterException extends \InvalidArgumentException implements EnvProviderExceptionInterface
{
    public static function becauseEmptyNamePassed(): self
    {
        return new self('Name to format can not be empty');
    }
}
