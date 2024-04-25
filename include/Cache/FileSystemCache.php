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

use Composer\InstalledVersions;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\Normalizer\Normalizer;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Exception;
use Psl\Encoding\Base64\Variant;
use Psl\File\WriteMode;
use Psr\SimpleCache\CacheInterface;
use function Psl\Encoding\Base64\encode;
use function Psl\File\read;
use function Psl\File\write;
use function Psl\Filesystem\create_directory;
use function Psl\Filesystem\delete_directory;
use function Psl\Filesystem\delete_file;
use function Psl\Filesystem\exists;
use function Psl\Filesystem\is_directory;
use function Psl\Filesystem\is_file;
use function Psl\Filesystem\is_readable;

/**
 * @internal
 */
final class FileSystemCache implements CacheInterface
{
    private const CACHE_INFO_FILE = '.cache.info';
    private const ID_CHUNK_SIZE   = 4;
    /**
     * @var non-empty-string
     */
    private readonly string $cache_root;
    private CacheInfo $cache_info;

    public function __construct(
        private readonly TreeMapper $mapper,
        private readonly Normalizer $normalizer,
    ) {
        $root             = InstalledVersions::getRootPackage()['install_path'];
        $root             = rtrim($root, '/');
        $this->cache_root = $root . '/cache';

        if (!exists($this->cache_root) || !is_directory($this->cache_root)) {
            create_directory($this->cache_root);
        }

        $this->loadCacheInfo();
    }

    private function loadCacheInfo(): void
    {
        $filename = $this->cache_root . '/' . self::CACHE_INFO_FILE;

        if (exists($filename) && is_file($filename) && is_readable($filename)) {
            try {
                $content          = read($filename);
                $this->cache_info = $this->mapper->map(CacheInfo::class, $content);
            } catch (MappingError) {
                $this->cache_info = new CacheInfo([]);
                $this->saveCacheInfo();
            }
        } else {
            $this->cache_info = new CacheInfo([]);
            $this->saveCacheInfo();
        }
    }

    private function saveCacheInfo(): void
    {
        $content = $this->normalizer->normalize($this->cache_info);
        assert(is_string($content));
        $filename = $this->cache_root . '/' . self::CACHE_INFO_FILE;
        write($filename, $content, WriteMode::TRUNCATE);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if ($key === '') {
            throw new InvalidArgumentException('$key must be non-empty');
        }

        $id = $this->getIdForKey($key);
        if (isset($this->cache_info->files_ttl[$id])) {
            $current = (new DateTimeImmutable('now'))->getTimestamp();
            if ($current > $this->cache_info->files_ttl[$id]) {
                return $default;
            }
        }

        $path = $this->getPathForId($id)['file'];
        if (!exists($path)) {
            return $default;
        }

        try {
            return unserialize(read($path));
        } catch (Exception) {
            return $default;
        }
    }

    public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
    {
        if ($key === '') {
            throw new InvalidArgumentException('$key must be non-empty');
        }

        $id   = $this->getIdForKey($key);
        $path = $this->getPathForId($id);

        if (!exists($path['dir']) || !is_directory($path['dir'])) {
            create_directory($path['dir']);
        }

        try {
            write($path['file'], serialize($value), WriteMode::TRUNCATE);
            if ($ttl !== null) {
                if (is_int($ttl)) {
                    if ($ttl < 1) {
                        throw new InvalidArgumentException('$ttl must be a positive integer');
                    }

                    $ttl = DateInterval::createFromDateString("$ttl second");
                }

                assert($ttl instanceof DateInterval);
                $this->cache_info->files_ttl[$id] = (new DateTime('now'))->add($ttl)->getTimestamp();
                $this->saveCacheInfo();
            }
        } catch (Exception) {
            try {
                delete_file($path['file']);
            } catch (Exception) { // phpcs:ignore
                // Ignore
            }

            return false;
        }

        return true;
    }

    public function delete(string $key): bool
    {
        if ($key === '') {
            throw new InvalidArgumentException('$key must be non-empty');
        }

        $id   = $this->getIdForKey($key);
        $path = $this->getPathForId($id)['file'];

        delete_file($path);

        return true;
    }

    public function clear(): bool
    {
        delete_directory($this->cache_root, true);
        create_directory($this->cache_root);

        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        foreach ($keys as $key) {
            yield $key => $this->get($key, $default);
        }
    }

    /**
     * @param iterable<string, mixed> $values
     * @throws InvalidArgumentException
     */
    public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool
    {
        $accumulator = true;
        foreach ($values as $key => $value) {
            $accumulator = $accumulator && $this->set($key, $value, $ttl);
        }

        return $accumulator;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $accumulator = true;
        foreach ($keys as $key) {
            $accumulator = $accumulator && $this->delete($key);
        }

        return $accumulator;
    }

    public function has(string $key): bool
    {
        if ($key === '') {
            throw new InvalidArgumentException('$key must be non-empty');
        }

        $id   = $this->getIdForKey($key);
        $path = $this->getPathForId($id)['file'];

        if (isset($this->cache_info->files_ttl[$id])) {
            $current_time = (new DateTimeImmutable('now'))->getTimestamp();
            $ttl_valid    = $current_time < $this->cache_info->files_ttl[$id];
        } else {
            $ttl_valid = true;
        }

        return exists($path) && is_file($path) && $ttl_valid;
    }

    /**
     * @param non-empty-string $key
     * @return non-empty-string
     */
    private function getIdForKey(string $key): string
    {
        $id = encode($key, Variant::UrlSafe);
        assert($id !== '');
        return $id;
    }

    /**
     * @param non-empty-string $id
     * @return array{
     *     dir: non-empty-string,
     *     file: non-empty-string,
     * }
     */
    private function getPathForId(string $id): array
    {
        $chunks = str_split($id, self::ID_CHUNK_SIZE);
        assert(count($chunks) >= 1);
        $dir_chunks = array_slice($chunks, 0, -1);
        $file       = $chunks[count($chunks) - 1];
        $dir        = implode('.d/', $dir_chunks);
        if ($dir !== '') {
            $dir .= '.d/';
        }

        return [
            'dir'  => $this->cache_root . '/' . $dir,
            'file' => $this->cache_root . '/' . $dir . $file,
        ];
    }
}
