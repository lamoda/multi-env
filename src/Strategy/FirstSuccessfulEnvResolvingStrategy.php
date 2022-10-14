<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Strategy;

final class FirstSuccessfulEnvResolvingStrategy implements EnvResolvingStrategyInterface
{
    /**
     * @var array|EnvResolvingStrategyInterface[]
     */
    private array $strategies = [];

    public function __construct(array $strategies = [])
    {
        $this->addStrategies($strategies);
    }

    public function getEnv(string $envName): string
    {
        foreach ($this->strategies as $strategy) {
            $envValue = $strategy->getEnv($envName);
            if ($envValue !== '') {
                return $envValue;
            }
        }

        return '';
    }

    /**
     * @param array|EnvResolvingStrategyInterface[] $strategies
     */
    private function addStrategies(array $strategies): void
    {
        foreach ($strategies as $strategy) {
            $this->addStrategy($strategy);
        }
    }

    private function addStrategy(EnvResolvingStrategyInterface $strategy): void
    {
        $this->strategies[] = $strategy;
    }
}
