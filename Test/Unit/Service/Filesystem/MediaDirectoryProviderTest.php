<?php
declare(strict_types=1);

namespace Iranimij\Core\Test\Unit\Service\Filesystem;

use Iranimij\Core\Service\Filesystem\MediaDirectoryProvider;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MediaDirectoryProviderTest extends TestCase
{
    private Filesystem&MockObject $filesystem;
    private WriteInterface&MockObject $writable;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->writable = $this->createMock(WriteInterface::class);
        $this->filesystem->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->writable);
    }

    public function testEnsureSubPathCreatesWhenMissing(): void
    {
        $this->writable->method('isDirectory')->with('foo/bar')->willReturn(false);
        $this->writable->expects(self::once())->method('create')->with('foo/bar');
        $this->writable->method('getAbsolutePath')->with('foo/bar')->willReturn('/m/foo/bar');

        $provider = new MediaDirectoryProvider($this->filesystem);
        self::assertSame('/m/foo/bar', $provider->ensureSubPath('/foo/bar/'));
    }

    public function testEnsureSubPathSkipsCreateWhenPresent(): void
    {
        $this->writable->method('isDirectory')->with('baz')->willReturn(true);
        $this->writable->expects(self::never())->method('create');
        $this->writable->method('getAbsolutePath')->with('baz')->willReturn('/m/baz');

        $provider = new MediaDirectoryProvider($this->filesystem);
        self::assertSame('/m/baz', $provider->ensureSubPath('baz'));
    }

    public function testRelativeToDoesNotCreate(): void
    {
        $this->writable->expects(self::never())->method('create');
        $this->writable->method('getAbsolutePath')->with('a/b')->willReturn('/m/a/b');

        $provider = new MediaDirectoryProvider($this->filesystem);
        self::assertSame('/m/a/b', $provider->relativeTo('/a/b/'));
    }
}
