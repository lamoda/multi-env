<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Formatter\Exception;

use Lamoda\MultiEnv\Exception\EnvProviderExceptionInterface;

class FormatterException extends \InvalidArgumentException implements EnvProviderExceptionInterface
{
    public static function becauseEmptyEnvNamePassed(): self
    {
        return new self('Env name to format can not be empty');
    }
}
