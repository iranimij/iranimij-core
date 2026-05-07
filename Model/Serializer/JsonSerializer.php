<?php
declare(strict_types=1);

namespace Iranimij\Core\Model\Serializer;

use Magento\Framework\Serialize\Serializer\Json;

class JsonSerializer
{
    public function __construct(
        private readonly Json $json
    ) {
    }

    public function encode(mixed $value): string
    {
        $encoded = $this->json->serialize($value);
        return is_string($encoded) ? $encoded : '';
    }

    public function decode(?string $payload, mixed $fallback = null): mixed
    {
        if ($payload === null || $payload === '') {
            return $fallback;
        }

        try {
            return $this->json->unserialize($payload);
        } catch (\InvalidArgumentException) {
            return $fallback;
        }
    }

    public function decodeArray(?string $payload): array
    {
        $value = $this->decode($payload, []);
        return is_array($value) ? $value : [];
    }
}
