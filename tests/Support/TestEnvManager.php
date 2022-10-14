<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Support;

trait TestEnvManager
{
    private array $testEnv = [];

    public function addTestEnv(array $testEnvs): void
    {
        foreach ($testEnvs as $envKey => $envValue) {
            $envValue = trim($envValue);
            $envKey = trim($envKey);
            if ($envValue === '' || $envKey === '') {
                continue;
            }
            $envKey = str_replace('-', '_', $envKey);
            $this->testEnv[] = $envKey;
            putenv($envKey . '=' . $envValue);
        }
    }

    public function removeTestEnv(): void
    {
        foreach ($this->testEnv as $envKey) {
            putenv($envKey);
        }
    }
}
