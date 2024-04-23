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

class NoopCacheTest extends TestCase
{
    private NoopCache $cache;

    protected function setUp(): void
    {
        $this->cache = new NoopCache();
    }

    public function testSetMultiple(): void
    {
        self::assertFalse($this->cache->setMultiple(['foo' => 'bar']));
    }

    public function testDeleteMultiple(): void
    {
        self::assertFalse($this->cache->deleteMultiple(['foo']));
    }

    public function testGet(): void
    {
        self::assertNull($this->cache->get('foo'));
    }

    public function testGetMultiple(): void
    {
        $iter = $this->cache->getMultiple(['foo', 'bar']);
        foreach ($iter as $item) {
            self::assertNull($item);
        }
    }

    public function testDelete(): void
    {
        self::assertFalse($this->cache->delete('foo'));
    }

    public function testSet(): void
    {
        self::assertFalse($this->cache->set('foo', 'bar'));
    }

    public function testClear(): void
    {
        self::assertFalse($this->cache->clear());
    }

    public function testHas(): void
    {
        self::assertFalse($this->cache->has('foo'));
    }
}
