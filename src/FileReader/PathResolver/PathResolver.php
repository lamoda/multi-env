<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\FileReader\PathResolver;

use Lamoda\MultiEnv\Formatter\FormatterInterface;
use Lamoda\MultiEnv\Model\HostId;

final class PathResolver implements PathResolverInterface
{
    /**
     * @var string
     */
    private $originalPath;

    /**
     * @var FormatterInterface|null
     */
    private $formatter;

    public function __construct(string $originalPath, FormatterInterface $formatter = null)
    {
        $this->originalPath = trim($originalPath);
        $this->formatter = $formatter;
    }

    public function resolvePathToEnvFile(HostId $hostId): string
    {
        return $this->formatter !== null ? $this->formatter->formatName($this->originalPath, $hostId) : $this->originalPath;
    }
}
