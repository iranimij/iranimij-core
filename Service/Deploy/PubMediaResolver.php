<?php
declare(strict_types=1);

namespace Iranimij\Core\Service\Deploy;

use Iranimij\Core\Service\Filesystem\MediaDirectoryProvider;
use Magento\Framework\Filesystem\Io\File as IoFile;

class PubMediaResolver
{
    public function __construct(
        private readonly MediaDirectoryProvider $mediaDirectory,
        private readonly IoFile $ioFile
    ) {
    }

    public function resolve(string $relativePath): string
    {
        return $this->mediaDirectory->ensureSubPath($relativePath);
    }

    public function write(string $relativePath, string $filename, string $contents): string
    {
        $directory = $this->resolve($relativePath);
        $fullPath = rtrim($directory, '/') . '/' . ltrim($filename, '/');
        $this->ioFile->write($fullPath, $contents);
        return $fullPath;
    }
}
