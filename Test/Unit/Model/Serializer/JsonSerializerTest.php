<?php
declare(strict_types=1);

namespace Iranimij\Core\Test\Unit\Model\Serializer;

use Iranimij\Core\Model\Serializer\JsonSerializer;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;

class JsonSerializerTest extends TestCase
{
    private JsonSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new JsonSerializer(new Json());
    }

    public function testEncodeDecodeRoundTrip(): void
    {
        $payload = ['foo' => 'bar', 'nested' => ['a' => 1, 'b' => [2, 3]]];
        $encoded = $this->serializer->encode($payload);

        self::assertJson($encoded);
        self::assertSame($payload, $this->serializer->decode($encoded));
    }

    public function testDecodeEmptyReturnsFallback(): void
    {
        self::assertSame([], $this->serializer->decode('', []));
        self::assertNull($this->serializer->decode(null));
    }

    public function testDecodeMalformedReturnsFallback(): void
    {
        self::assertSame(['default'], $this->serializer->decode('not-json-at-all', ['default']));
    }

    public function testDecodeArrayAlwaysReturnsArray(): void
    {
        $encoded = $this->serializer->encode(['x' => 1]);
        self::assertSame(['x' => 1], $this->serializer->decodeArray($encoded));

        self::assertSame([], $this->serializer->decodeArray(null));
        self::assertSame([], $this->serializer->decodeArray('bogus'));
    }
}
