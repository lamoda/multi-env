<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\HostDetector\Exception;

use Lamoda\MultiEnv\Exception\EnvProviderExceptionInterface;

final class HostDetectorException extends \InvalidArgumentException implements EnvProviderExceptionInterface
{
    public static function becauseEmptyNeedlePassed(string $className): self
    {
        return new self("Empty needle passed to __construct method of $className");
    }

    public static function becauseGetOptAdapterConfiguredIncorrect(
        string $className,
        string $paramName,
        string $correctValue
    ): self {
        return new self("$className class configured incorrect. You should set '$paramName' to '$correctValue'");
    }
}
