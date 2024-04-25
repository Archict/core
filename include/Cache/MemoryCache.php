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

use DateInterval;
use Psr\SimpleCache\CacheInterface;

/**
 * @internal
 */
final class MemoryCache implements CacheInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $cache = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache[$key] ?? $default;
    }

    public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool // phpcs:ignore
    {
        $this->cache[$key] = $value;

        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->cache[$key]);

        return true;
    }

    public function clear(): bool
    {
        $this->cache = [];

        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        foreach ($keys as $key) {
            yield $key => $this->cache[$key] ?? $default;
        }
    }

    // @phpstan-ignore-next-line
    public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool // phpcs:ignore
    {
        foreach ($values as $key => $value) {
            $this->cache[(string) $key] = $value;
        }

        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            unset($this->cache[$key]);
        }

        return true;
    }

    public function has(string $key): bool
    {
        return isset($this->cache[$key]);
    }
}
