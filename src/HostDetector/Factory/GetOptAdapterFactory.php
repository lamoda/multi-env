<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\HostDetector\Factory;

use GetOpt\GetOpt;

final class GetOptAdapterFactory
{
    public static function build(): GetOpt
    {
        $getOptAdapter = new GetOpt();
        $getOptAdapter->set(GetOpt::SETTING_STRICT_OPTIONS, false);

        return $getOptAdapter;
    }
}
