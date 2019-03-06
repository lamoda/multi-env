<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Model;

final class HostId
{
    /**
     * @var string $id
     */
    private $id;

    public function __construct(string $id)
    {
        $this->id = trim($id);
    }

    public function __toString()
    {
        return $this->id;
    }
}
