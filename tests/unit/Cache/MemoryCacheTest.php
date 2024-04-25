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

use PHPUnit\Framework\TestCase;

class MemoryCacheTest extends TestCase
{
    private MemoryCache $cache;

    protected function setUp(): void
    {
        $this->cache = new MemoryCache();
    }

    public function testDelete(): void
    {
        $this->cache->set('foo', 'bar');
        self::assertTrue($this->cache->delete('foo'));
        self::assertNull($this->cache->get('foo'));
    }

    public function testClear(): void
    {
        $this->cache->set('foo', 'bar');
        self::assertTrue($this->cache->clear());
        self::assertNull($this->cache->get('foo'));
    }

    public function testGet(): void
    {
        $this->cache->set('foo', 'bar');
        self::assertSame('bar', $this->cache->get('foo'));
    }

    public function testDeleteMultiple(): void
    {
        $this->cache->set('foo', 'bar');
        $this->cache->set('bar', 'baz');
        self::assertTrue($this->cache->deleteMultiple(['foo', 'bar']));
        self::assertNull($this->cache->get('foo'));
        self::assertNull($this->cache->get('bar'));
    }

    public function testGetMultiple(): void
    {
        $this->cache->set('foo', 'bar');
        $this->cache->set('bar', 'baz');

        $iter = $this->cache->getMultiple(['foo', 'bar']);
        $i    = 0;
        foreach ($iter as $item) {
            if ($i === 0) {
                self::assertSame('bar', $item);
            } else if ($i === 1) {
                self::assertSame('baz', $item);
            } else {
                self::fail('Should not end up here');
            }

            $i++;
        }
    }

    public function testHas(): void
    {
        $this->cache->set('foo', 'bar');
        self::assertTrue($this->cache->has('foo'));
        self::assertFalse($this->cache->has('bar'));
    }

    public function testSetMultiple(): void
    {
        $this->cache->setMultiple(
            [
                'foo' => 'bar',
                'bar' => 'baz',
            ]
        );

        self::assertSame('bar', $this->cache->get('foo'));
        self::assertSame('baz', $this->cache->get('bar'));
    }
}
