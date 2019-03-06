<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Unit\HostDetector\Factory;

use GetOpt\GetOpt;
use Lamoda\MultiEnv\HostDetector\Factory\GetOptAdapterFactory;
use PHPUnit\Framework\TestCase;

class GetOptAdapterFactoryTest extends TestCase
{
    public function testBuild(): void
    {
        $getOptAdapter = GetOptAdapterFactory::build();
        $param = $getOptAdapter->get(GetOpt::SETTING_STRICT_OPTIONS);
        $this->assertFalse($param);
    }
}
