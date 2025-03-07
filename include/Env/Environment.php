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

namespace Archict\Core\Env;

use Composer\InstalledVersions;
use Dotenv\Dotenv;

/**
 * @internal
 */
final class Environment implements EnvironmentService
{
    public function __construct()
    {
        $root_dir = InstalledVersions::getRootPackage()['install_path'];
        Dotenv::createImmutable($root_dir)->safeLoad();
    }

    public function has(string $key): bool
    {
        return isset($_ENV[$key]);
    }

    public function get(string $key, float|bool|int|string|null $default = null): float|bool|int|string|null
    {
        if (isset($_ENV[$key])) {
            return $_ENV[$key]; // @phpstan-ignore return.type
        }

        return $default;
    }
}
