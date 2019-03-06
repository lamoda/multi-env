<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Support;

trait TestHeadersManager
{
    private $testHeaders = [];

    public function addTestHeaders(array $headers): void
    {
        foreach ($headers as $key => $value) {
            $_SERVER[$key] = $value;
            $this->testHeaders[] = $key;
        }
    }

    public function removeTestHeaders(): void
    {
        foreach ($this->testHeaders as $testHeader) {
            unset($_SERVER[$testHeader]);
        }
    }
}
