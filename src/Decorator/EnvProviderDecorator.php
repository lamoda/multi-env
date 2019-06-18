<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Decorator;

use Lamoda\MultiEnv\Decorator\Exception\EnvProviderDecoratorException;
use Lamoda\MultiEnv\Strategy\EnvResolvingStrategyInterface;

final class EnvProviderDecorator
{
    /**
     * @var EnvResolvingStrategyInterface|null $resolvingStrategy
     */
    private static $resolvingStrategy;

    public static function init(EnvResolvingStrategyInterface $resolvingStrategy): void
    {
        self::$resolvingStrategy = $resolvingStrategy;
    }

    public static function resetStrategy(): void
    {
        self::$resolvingStrategy = null;
    }

    /**
     * @param string $envName
     * @throws EnvProviderDecoratorException
     * @return string
     */
    public static function getEnv(string $envName): string
    {
        if (self::$resolvingStrategy === null) {
            throw EnvProviderDecoratorException::becauseDecoratorNotInitialised();
        }

        return self::$resolvingStrategy->getEnv($envName);
    }
}
