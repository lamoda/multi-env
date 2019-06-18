<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Formatter;

use Lamoda\MultiEnv\Model\HostId;

final class CharReplaceFormatter implements FormatterInterface
{
    /**
     * @var string
     */
    private $search;

    /**
     * @var string
     */
    private $replace;

    public function __construct(string $search, string $replace)
    {
        $this->search = $search;
        $this->replace = $replace;
    }

    public function formatName(string $originalName, HostId $hostId): string
    {
        return str_replace($this->search, $this->replace, $originalName);
    }
}
