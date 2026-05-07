<?php
declare(strict_types=1);

namespace Iranimij\Core\Test\Unit\Model\Config;

use Iranimij\Core\Model\Config\ConfigProviderAbstract;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigProviderAbstractTest extends TestCase
{
    private ScopeConfigInterface&MockObject $scopeConfig;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
    }

    public function testReadStringReturnsValue(): void
    {
        $provider = $this->makeProvider();
        $this->scopeConfig->method('getValue')
            ->with('demo/path/string', ScopeInterface::SCOPE_STORE, 3)
            ->willReturn('hello');

        self::assertSame('hello', $provider->publicReadString('demo/path/string', 3));
    }

    public function testReadStringDefaultsWhenNonScalar(): void
    {
        $provider = $this->makeProvider();
        $this->scopeConfig->method('getValue')->willReturn(['array']);

        self::assertSame('fallback', $provider->publicReadString('x', null, 'fallback'));
    }

    public function testReadIntCastsNumeric(): void
    {
        $provider = $this->makeProvider();
        $this->scopeConfig->method('getValue')->willReturn('42');
        self::assertSame(42, $provider->publicReadInt('x'));
    }

    public function testReadIntDefaultsWhenNonNumeric(): void
    {
        $provider = $this->makeProvider();
        $this->scopeConfig->method('getValue')->willReturn('not-a-number');
        self::assertSame(7, $provider->publicReadInt('x', null, 7));
    }

    public function testReadFloatCastsNumeric(): void
    {
        $provider = $this->makeProvider();
        $this->scopeConfig->method('getValue')->willReturn('3.14');
        self::assertSame(3.14, $provider->publicReadFloat('x'));
    }

    public function testReadFlagDelegatesToScopeConfig(): void
    {
        $provider = $this->makeProvider();
        $this->scopeConfig->method('isSetFlag')
            ->with('demo/flag', ScopeInterface::SCOPE_STORE, 1)
            ->willReturn(true);

        self::assertTrue($provider->publicReadFlag('demo/flag', 1));
    }

    private function makeProvider(): object
    {
        return new class($this->scopeConfig) extends ConfigProviderAbstract {
            public function publicReadString(string $p, ?int $s = null, string $d = ''): string
            {
                return $this->readString($p, $s, $d);
            }
            public function publicReadInt(string $p, ?int $s = null, int $d = 0): int
            {
                return $this->readInt($p, $s, $d);
            }
            public function publicReadFloat(string $p, ?int $s = null, float $d = 0.0): float
            {
                return $this->readFloat($p, $s, $d);
            }
            public function publicReadFlag(string $p, ?int $s = null): bool
            {
                return $this->readFlag($p, $s);
            }
        };
    }
}
