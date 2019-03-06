<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Strategy;

interface EnvResolvingStrategyInterface
{
    public function getEnv(string $envName): string;
}
