<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Support;

trait TestCliArgsManager
{
    private array $testCliArgs = [];

    public function addTestCliArgs(array $cliArgs): void
    {
        $cliArgsValues = &$_SERVER['argv'];
        $cliArgsCount = &$_SERVER['argc'];
        foreach ($cliArgs as $argKey => $argParams) {
            $prefix = mb_strlen($argKey) > 1 ? '--' : '-';
            $argKey = $prefix . $argKey;
            foreach ($this->getCliValuesToSetUp($argKey, $argParams) as $value) {
                $this->testCliArgs[] = $value;
                $cliArgsValues[] = $value;
            }
        }
        $cliArgsCount = \count($cliArgsValues);
    }

    public function removeTestCliArgs(): void
    {
        $cliArgsValues = &$_SERVER['argv'];
        $cliArgsCount = &$_SERVER['argc'];
        $cliArgsValues = array_filter($cliArgsValues, function ($value) {
            return !in_array($value, $this->testCliArgs, true);
        });
        $cliArgsCount = \count($cliArgsValues);
    }

    private function getCliValuesToSetUp(string $cliArgKey, array $cliArgParams): array
    {
        if (empty($cliArgParams['value'])) {
            return [$cliArgKey];
        }

        if ($cliArgParams['useKeySeparator'] ?? false === true) {
            return [$cliArgKey . '=' . $cliArgParams['value']];
        }

        return [$cliArgKey, $cliArgParams['value']];
    }
}
