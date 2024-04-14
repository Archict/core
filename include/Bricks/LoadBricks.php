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

namespace Archict\Core\Bricks;

use Composer\InstalledVersions;

/**
 * @internal
 */
final class LoadBricks implements BrickLoader
{
    public function loadInstalledBricks(): array
    {
        $self_name = InstalledVersions::getRootPackage()['name'];
        $packages  = array_filter(
            array_unique(InstalledVersions::getInstalledPackagesByType(self::PACKAGE_TYPE)),
            static fn(string $package) => $package !== $self_name
        );

        $result = [];
        foreach ($packages as $package_name) {
            // Check package is not a dev requirement
            if (!InstalledVersions::isInstalled($package_name, false)) {
                continue;
            }

            $package_path = InstalledVersions::getInstallPath($package_name);
            if ($package_path !== null) {
                $result[] = new BrickRepresentation($package_name, $package_path);
            }
        }

        return $result;
    }
}
