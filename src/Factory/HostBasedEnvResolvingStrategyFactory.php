<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Factory;

use Lamoda\MultiEnv\Formatter\CharReplaceFormatter;
use Lamoda\MultiEnv\Formatter\FormatterPipeline;
use Lamoda\MultiEnv\Formatter\PrefixAppendFormatter;
use Lamoda\MultiEnv\HostDetector\CliArgsBasedHostDetector;
use Lamoda\MultiEnv\HostDetector\Factory\GetOptAdapterFactory;
use Lamoda\MultiEnv\HostDetector\FirstSuccessfulHostDetector;
use Lamoda\MultiEnv\HostDetector\ServerHeadersBasedHostDetector;
use Lamoda\MultiEnv\Strategy\HostBasedEnvResolvingStrategy;

/**
 * @deprecated This factory must be implemented in the client code. It will be removed in version 1.0
 */
class HostBasedEnvResolvingStrategyFactory
{
    public static function createStrategy(
        string $serverHeaderToSearch,
        string $cliArgToSearch,
        string $delimiter
    ): HostBasedEnvResolvingStrategy {
        @trigger_error(
            sprintf('Factory %s is deprecated. It must be implemented in the client code. It will be removed in version 1.0', self::class),
            E_USER_DEPRECATED
        );

        return new HostBasedEnvResolvingStrategy(
            new FirstSuccessfulHostDetector([
                new ServerHeadersBasedHostDetector($serverHeaderToSearch),
                new CliArgsBasedHostDetector($cliArgToSearch, GetOptAdapterFactory::build()),
            ]),
            new FormatterPipeline([
                new PrefixAppendFormatter($delimiter),
                new CharReplaceFormatter('-', '_'),
            ])
        );
    }
}
