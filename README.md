# Iranimij_Core

Shared foundation module for the Iranimij Magento 2 package family. Provides JSON serialization, typed configuration helpers, and media filesystem utilities — one canonical implementation used across all `iranimij/*` modules so the same logic is never duplicated.

## Requirements

| Requirement | Version |
|---|---|
| Magento | 2.4.6+ |
| PHP | 8.1+ |

## Installation

```bash
composer require iranimij/iranimij-core
bin/magento module:enable Iranimij_Core
bin/magento setup:upgrade
```

## Provided Classes

### `Model\Serializer\JsonSerializer`

A thin, null-safe wrapper around Magento's `Serialize\Serializer\Json`. All modules in the Iranimij package use this class as the single JSON path — no direct `json_encode` / `json_decode` calls.

```php
use Iranimij\Core\Model\Serializer\JsonSerializer;

// encode
$json = $serializer->encode(['key' => 'value']);   // '{"key":"value"}'

// decode with typed fallback — never throws
$data = $serializer->decode($json);                // ['key' => 'value']
$data = $serializer->decode(null);                 // null  (default fallback)
$data = $serializer->decode('bad', []);            // []    (fallback on parse error)

// guaranteed array return
$arr  = $serializer->decodeArray($json);           // ['key' => 'value']
$arr  = $serializer->decodeArray(null);            // []
$arr  = $serializer->decodeArray('bad');           // []
```

**Why not call `json_encode` directly?**  
Magento's serializer respects `JSON_THROW_ON_ERROR` and the framework's encoding flags consistently. Using the wrapper also makes it trivial to swap the underlying implementation (e.g. for testing) via DI.

---

### `Model\Config\ConfigProviderAbstract`

Base class for module-specific configuration providers. Extend it to get typed, scope-aware config getters without repeating the `ScopeConfigInterface` boilerplate.

```php
use Iranimij\Core\Model\Config\ConfigProviderAbstract;

class MyModuleConfig extends ConfigProviderAbstract
{
    public function isEnabled(?int $storeId = null): bool
    {
        return $this->readFlag('mymodule/general/enabled', $storeId);
    }

    public function getApiKey(?int $storeId = null): string
    {
        return $this->readString('mymodule/general/api_key', $storeId);
    }

    public function getMaxItems(?int $storeId = null): int
    {
        return $this->readInt('mymodule/general/max_items', $storeId, 10);
    }

    public function getMultiplier(?int $storeId = null): float
    {
        return $this->readFloat('mymodule/general/multiplier', $storeId, 1.0);
    }
}
```

**Available methods** (all `protected`, all store-scoped):

| Method | Return | Description |
|---|---|---|
| `readString($path, $storeId, $default)` | `string` | Casts to string; returns `$default` if value is non-scalar. |
| `readInt($path, $storeId, $default)` | `int` | Casts numeric values to int; returns `$default` otherwise. |
| `readFloat($path, $storeId, $default)` | `float` | Casts numeric values to float; returns `$default` otherwise. |
| `readFlag($path, $storeId)` | `bool` | Delegates to `ScopeConfigInterface::isSetFlag`. |

All methods default to `SCOPE_STORE`. Pass `$storeId = null` to read from the current store context.

---

### `Service\Filesystem\MediaDirectoryProvider`

Wraps Magento's filesystem layer to expose the writable media directory with two helper methods.

```php
use Iranimij\Core\Service\Filesystem\MediaDirectoryProvider;

// Get the writable WriteInterface for pub/media/
$write = $provider->writable();

// Ensure a sub-directory exists and return its absolute path
$absolutePath = $provider->ensureSubPath('mymodule/cache');
// → /var/www/html/pub/media/mymodule/cache  (created if missing)

// Resolve an absolute path without creating the directory
$absolutePath = $provider->relativeTo('mymodule/cache/file.png');
```

---

### `Service\Deploy\PubMediaResolver`

Combines `MediaDirectoryProvider` with Magento's `Filesystem\Io\File` to write content directly into `pub/media/`.

```php
use Iranimij\Core\Service\Deploy\PubMediaResolver;

// Ensure the directory exists
$resolver->resolve('mymodule/generated');

// Write content to a file
$fullPath = $resolver->write('mymodule/generated', 'badge.png', $pngBinaryContent);
// → /var/www/html/pub/media/mymodule/generated/badge.png
```

---

### `Exception\InvalidConfigurationException`

A `LocalizedException` subclass for signalling that a module configuration value is missing or invalid. Throw it from config provider or service classes when a required setting is not present.

```php
use Iranimij\Core\Exception\InvalidConfigurationException;
use Magento\Framework\Phrase;

if ($apiKey === '') {
    throw new InvalidConfigurationException(new Phrase('API key is not configured.'));
}
```

---

## Running Tests

```bash
# From the module directory
vendor/bin/phpunit -c phpunit.xml
```

Three unit-test classes ship with the module:

| Test | Coverage |
|---|---|
| `JsonSerializerTest` | encode/decode round-trip, null/empty fallback, malformed-JSON fallback, `decodeArray` always-array contract |
| `ConfigProviderAbstractTest` | `readString`, `readInt`, `readFloat`, `readFlag` with valid values, non-scalar values, and defaults |
| `MediaDirectoryProviderTest` | `writable()` delegation, `ensureSubPath` creation and idempotency, `relativeTo` path resolution |

## License

[OSL-3.0](https://opensource.org/licenses/OSL-3.0)
