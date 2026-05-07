<?php
declare(strict_types=1);

namespace Iranimij\Core\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

abstract class ConfigProviderAbstract
{
    public function __construct(
        protected readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    protected function readString(string $path, ?int $storeId = null, string $default = ''): string
    {
        $value = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
        return is_scalar($value) ? (string) $value : $default;
    }

    protected function readInt(string $path, ?int $storeId = null, int $default = 0): int
    {
        $value = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
        return is_numeric($value) ? (int) $value : $default;
    }

    protected function readFloat(string $path, ?int $storeId = null, float $default = 0.0): float
    {
        $value = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
        return is_numeric($value) ? (float) $value : $default;
    }

    protected function readFlag(string $path, ?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
