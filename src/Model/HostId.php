<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Model;

final class HostId
{
    private string $id;

    public function __construct(string $id)
    {
        $this->id = trim($id);
    }

    public function __toString()
    {
        return $this->id;
    }
}
