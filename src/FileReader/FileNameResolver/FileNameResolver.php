<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\FileReader\FileNameResolver;

use Lamoda\MultiEnv\Formatter\FormatterInterface;
use Lamoda\MultiEnv\Model\HostId;

final class FileNameResolver implements FileNameResolverInterface
{
    public const DEFAULT_FILE_NAME = '.env';

    private string $originalFileName;

    private ?FormatterInterface $formatter;

    public function __construct(string $originalFileName = self::DEFAULT_FILE_NAME, FormatterInterface $formatter = null)
    {
        $this->originalFileName = trim($originalFileName);
        $this->originalFileName = !empty($this->originalFileName) ? $this->originalFileName : self::DEFAULT_FILE_NAME;
        $this->formatter = $formatter;
    }

    public function resolveEnvFileName(HostId $hostId): string
    {
        $resolvedFileName = $this->originalFileName;

        if ($this->formatter !== null) {
            $resolvedFileName = $this->formatter->formatName(trim($this->originalFileName, '.'), $hostId);
        }

        return $this->isDotShouldBeAddedToFileName($resolvedFileName) ? '.' . $resolvedFileName : $resolvedFileName;
    }

    private function isDotShouldBeAddedToFileName(string $resolvedFileName): bool
    {
        return strpos($this->originalFileName, '.') === 0 && strpos($resolvedFileName, '.') !== 0;
    }
}
