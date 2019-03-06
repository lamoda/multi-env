<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Decorator\Exception;

use Lamoda\MultiEnv\Exception\EnvProviderExceptionInterface;

final class EnvProviderDecoratorException extends \RuntimeException implements EnvProviderExceptionInterface
{
    public static function becauseDecoratorNotInitialised(): self
    {
        return new self('Decorator not initialised. Run init method first');
    }
}
