<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\Factory;

use Lamoda\MultiEnv\FileReader\DotEnvV2FileReaderAdapter;
use Lamoda\MultiEnv\FileReader\FileNameResolver\FileNameResolver;
use Lamoda\MultiEnv\FileReader\PathResolver\PathResolver;
use Lamoda\MultiEnv\Formatter\SuffixAppendFormatter;
use Lamoda\MultiEnv\HostDetector\CliArgsBasedHostDetector;
use Lamoda\MultiEnv\HostDetector\Factory\GetOptAdapterFactory;
use Lamoda\MultiEnv\HostDetector\FirstSuccessfulHostDetector;
use Lamoda\MultiEnv\HostDetector\ServerHeadersBasedHostDetector;
use Lamoda\MultiEnv\Strategy\FileBasedEnvResolvingStrategy;
use Lamoda\MultiEnv\Strategy\RawEnvResolvingStrategy;

class FileBasedEnvResolvingStrategyFactory
{
    public static function createStrategy(
        string $serverHeaderToSearch,
        string $cliArgToSearch,
        string $envFileName,
        string $basePathToEnvFile
    ): FileBasedEnvResolvingStrategy {
        return new FileBasedEnvResolvingStrategy(
            new FirstSuccessfulHostDetector([
                new ServerHeadersBasedHostDetector($serverHeaderToSearch),
                new CliArgsBasedHostDetector($cliArgToSearch, GetOptAdapterFactory::build())
            ]),
            new DotEnvV2FileReaderAdapter(
                new PathResolver($basePathToEnvFile, new SuffixAppendFormatter(DIRECTORY_SEPARATOR)),
                new FileNameResolver($envFileName)
            ),
            new RawEnvResolvingStrategy()
        );
    }
}
