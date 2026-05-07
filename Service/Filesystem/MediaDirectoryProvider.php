<?php
declare(strict_types=1);

namespace Iranimij\Core\Service\Filesystem;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class MediaDirectoryProvider
{
    public function __construct(
        private readonly Filesystem $filesystem
    ) {
    }

    public function writable(): WriteInterface
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    public function ensureSubPath(string $relativePath): string
    {
        $writable = $this->writable();
        $normalized = trim($relativePath, '/');

        if (!$writable->isDirectory($normalized)) {
            $writable->create($normalized);
        }

        return $writable->getAbsolutePath($normalized);
    }

    public function relativeTo(string $relativePath): string
    {
        return $this->writable()->getAbsolutePath(trim($relativePath, '/'));
    }
}
