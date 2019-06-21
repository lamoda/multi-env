<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\FileReader;

use Lamoda\MultiEnv\FileReader\Exception\EnvFileReaderException;
use Lamoda\MultiEnv\FileReader\FileNameResolver\FileNameResolverInterface;
use Lamoda\MultiEnv\FileReader\PathResolver\PathResolverInterface;
use Lamoda\MultiEnv\Model\HostId;

final class DotEnvV2FileReaderAdapter implements EnvFileReaderInterface
{
    /**
     * @var PathResolverInterface
     */
    private $pathResolver;

    /**
     * @var FileNameResolverInterface
     */
    private $fileNameResolver;

    /**
     * @var bool
     */
    private $isEnvFileAlreadyLoaded;

    public function __construct(PathResolverInterface $pathResolver, FileNameResolverInterface $fileNameResolver)
    {
        // @codeCoverageIgnoreStart
        if (!class_exists(\Dotenv\Dotenv::class)) {
            throw EnvFileReaderException::becauseAdapterCanNotBeCreated(__CLASS__, \Dotenv\Dotenv::class);
        }
        // @codeCoverageIgnoreEnd
        $this->pathResolver = $pathResolver;
        $this->fileNameResolver = $fileNameResolver;
        $this->isEnvFileAlreadyLoaded = false;
    }

    public function readEnvFile(HostId $hostId): void
    {
        if ($this->isEnvFileAlreadyLoaded) {
            return;
        }

        try {
            $dotEnv = new \Dotenv\Dotenv(
                $this->pathResolver->resolvePathToEnvFile($hostId),
                $this->fileNameResolver->resolveEnvFileName($hostId)
            );
            $dotEnv->overload();
            $this->isEnvFileAlreadyLoaded = true;
        } catch (\Dotenv\Exception\ExceptionInterface $exception) {
            throw EnvFileReaderException::becauseEnvFileCanNotBeProcessed($exception);
        }
    }
}
