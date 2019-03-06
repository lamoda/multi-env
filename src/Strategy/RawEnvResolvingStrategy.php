<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Strategy;

final class RawEnvResolvingStrategy implements EnvResolvingStrategyInterface
{
    public function getEnv(string $envName): string
    {
        return (string)getenv($envName);
    }
}
