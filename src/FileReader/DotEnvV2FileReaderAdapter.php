<?php

declare(strict_types=1);

namespace Lamoda\MultiEnv\FileReader;

use Dotenv\Dotenv;
use Dotenv\Exception\ExceptionInterface;
use Lamoda\MultiEnv\FileReader\Exception\EnvFileReaderException;
use Lamoda\MultiEnv\FileReader\FileNameResolver\FileNameResolverInterface;
use Lamoda\MultiEnv\FileReader\PathResolver\PathResolverInterface;
use Lamoda\MultiEnv\Model\HostId;

final class DotEnvV2FileReaderAdapter implements EnvFileReaderInterface
{
    private PathResolverInterface $pathResolver;

    private FileNameResolverInterface $fileNameResolver;

    private bool $isEnvFileAlreadyLoaded;

    public function __construct(PathResolverInterface $pathResolver, FileNameResolverInterface $fileNameResolver)
    {
        // @codeCoverageIgnoreStart
        if (!class_exists(Dotenv::class)) {
            throw EnvFileReaderException::becauseAdapterCanNotBeCreated(__CLASS__, Dotenv::class);
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
            $dotEnv = Dotenv::create(
                $this->pathResolver->resolvePathToEnvFile($hostId),
                $this->fileNameResolver->resolveEnvFileName($hostId)
            );
            $dotEnv->overload();
            $this->isEnvFileAlreadyLoaded = true;
        } catch (ExceptionInterface $exception) {
            throw EnvFileReaderException::becauseEnvFileCanNotBeProcessed($exception);
        }
    }
}
