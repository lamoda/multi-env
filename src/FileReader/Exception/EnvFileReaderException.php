<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\FileReader\Exception;

use Lamoda\MultiEnv\Exception\EnvProviderExceptionInterface;
use Throwable;

final class EnvFileReaderException extends \RuntimeException implements EnvProviderExceptionInterface
{
    public static function becauseEnvFileCanNotBeProcessed(?Throwable $previous): self
    {
        return new self('Can\'t process file with env', 0, $previous);
    }

    public static function becauseAdapterCanNotBeCreated(string $adapterName, string $required): self
    {
        return new self('Can\t create adapter "' . $adapterName . '". It\'s require "' . $required . '"');
    }
}
