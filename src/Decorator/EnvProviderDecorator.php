<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Decorator;

use Lamoda\MultiEnv\Decorator\Exception\EnvProviderDecoratorException;
use Lamoda\MultiEnv\Strategy\EnvResolvingStrategyInterface;

final class EnvProviderDecorator
{
    private static ?EnvResolvingStrategyInterface $resolvingStrategy;

    public static function init(EnvResolvingStrategyInterface $resolvingStrategy): void
    {
        self::$resolvingStrategy = $resolvingStrategy;
    }

    public static function resetStrategy(): void
    {
        self::$resolvingStrategy = null;
    }

    /**
     * @throws EnvProviderDecoratorException
     */
    public static function getEnv(string $envName): string
    {
        if (self::$resolvingStrategy === null) {
            throw EnvProviderDecoratorException::becauseDecoratorNotInitialised();
        }

        return self::$resolvingStrategy->getEnv($envName);
    }
}
