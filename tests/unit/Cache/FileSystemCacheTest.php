<?php
/**
 * MIT License
 *
 * Copyright (c) 2024-Present Kevin Traini
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Archict\Core\Cache;

use Archict\Core\Fixtures\SerializableObject;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Format;
use PHPUnit\Framework\TestCase;
use function Psl\Filesystem\exists;
use function Psl\Filesystem\is_directory;

class FileSystemCacheTest extends TestCase
{
    private const CACHE_DIR = __DIR__ . '/../../../cache';
    private FileSystemCache $cache;

    protected function setUp(): void
    {
        $this->cache = new FileSystemCache(
            (new MapperBuilder())->allowPermissiveTypes()->mapper(),
            (new MapperBuilder())->normalizer(Format::json()),
            '',
        );
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
    }

    public function testItCreateCacheDir(): void
    {
        self::assertTrue(exists(self::CACHE_DIR));
        self::assertTrue(is_directory(self::CACHE_DIR));
    }

    public function testItThrowIfKeyIsEmpty(): void
    {
        self::expectException(InvalidArgumentException::class);
        $this->cache->has('');
    }

    public function testItStoreData(): void
    {
        $key  = 'My awesome array';
        $data = ['key' => 'value'];
        $this->cache->set($key, $data);
        self::assertTrue($this->cache->has($key));
        self::assertSame($data, $this->cache->get($key));
    }

    public function testItCannotFoundNotStoredData(): void
    {
        self::assertFalse($this->cache->has('something'));
        self::assertNull($this->cache->get('something'));
    }

    public function testItCanStoreMultipleData(): void
    {
        $values = [
            'key1' => 'A text value',
            'key2' => 12,
            'key3' => 3.45,
            'key4' => true,
            'key5' => ['key' => 'value'],
        ];

        $this->cache->setMultiple($values);

        foreach ($values as $key => $value) {
            self::assertSame($value, $this->cache->get($key));
        }
    }

    public function testItCannotFoundFileWithTTLExpired(): void
    {
        $this->cache->set('key', 'value', 1);
        self::assertTrue($this->cache->has('key'));
        self::assertSame('value', $this->cache->get('key'));
        sleep(2);
        self::assertFalse($this->cache->has('key'));
        self::assertNull($this->cache->get('key'));
    }

    public function testItCanStoreSerializableClass(): void
    {
        $object = new SerializableObject('value');

        self::assertTrue($this->cache->set('MyObject', $object));
        self::assertTrue($this->cache->has('MyObject'));
        self::assertEquals($object, $this->cache->get('MyObject'));
    }

    public function testItCreatesDirAsTellInEnv(): void
    {
        $cache = new FileSystemCache(
            (new MapperBuilder())->allowPermissiveTypes()->mapper(),
            (new MapperBuilder())->normalizer(Format::json()),
            '/custom-dir',
        );
        self::assertTrue(exists(__DIR__ . '/../../../custom-dir'));
        self::assertTrue(is_directory(__DIR__ . '/../../../custom-dir'));
    }
}
