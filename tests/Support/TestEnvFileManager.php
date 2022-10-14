<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Support;

use RuntimeException;

trait TestEnvFileManager
{
    private string $basePathToData = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data/env';

    public function createFileWithEnvs(array $envs, string $pathToDir = '', string $fileName = '.env'): void
    {
        $fullPathToDir = $this->buildFullPathToDir($pathToDir);
        $this->createDirectoryIfNotExist($fullPathToDir);
        $this->writeEnvToFile($fileName, $fullPathToDir, $envs);
    }

    public function getBasePathToDataFolder(): string
    {
        return $this->basePathToData;
    }

    public function removeAllTestEnvFilesFromDir(string $target): void
    {
        if (!file_exists($target)) {
            return;
        }

        if (is_dir($target)) {
            $dirHandler = opendir($target);
            if (!$dirHandler) {
                return;
            }
            while ($file = readdir($dirHandler)) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                if (!is_dir($target . DIRECTORY_SEPARATOR . $file)) {
                    unlink($target . DIRECTORY_SEPARATOR . $file);
                } else {
                    $this->removeAllTestEnvFilesFromDir($target . DIRECTORY_SEPARATOR . $file);
                }
            }
            closedir($dirHandler);
            rmdir($target);
        }
    }

    private function buildFullPathToDir(string $pathToFile): string
    {
        $trimmedPathToFile = trim($pathToFile);

        return empty($trimmedPathToFile) ? $this->basePathToData : $this->basePathToData . DIRECTORY_SEPARATOR . $trimmedPathToFile;
    }

    private function createDirectoryIfNotExist(string $fullPathToDir): void
    {
        if (!file_exists($fullPathToDir) && !mkdir($fullPathToDir, 0777, true) && !is_dir($fullPathToDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $fullPathToDir));
        }
        if (!is_writable($fullPathToDir)) {
            chmod($fullPathToDir, 0777);
        }
    }

    private function writeEnvToFile(string $fileName, string $fullPathToDir, array $envs): void
    {
        $fileHandler = fopen($fullPathToDir . DIRECTORY_SEPARATOR . $fileName, 'wb');
        if (!$fileHandler) {
            throw new RuntimeException(sprintf('Can\'t write to file "%s"', $fileName));
        }
        fwrite($fileHandler, $this->prepareDataToWrite($envs));
        fclose($fileHandler);
    }

    private function prepareDataToWrite(array $envs): string
    {
        $dataToWrite = '';
        foreach ($envs as $envName => $envValue) {
            $dataToWrite .= $envName . '=' . $envValue . PHP_EOL;
        }

        return $dataToWrite;
    }
}
