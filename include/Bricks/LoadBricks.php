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

use Archict\Brick\Service;
use Archict\Brick\ServiceConfiguration;
use Archict\Core\Services\ServiceIntermediateRepresentation;
use Composer\ClassMapGenerator\ClassMapGenerator;
use Composer\InstalledVersions;
use ReflectionClass;
use ReflectionException;

/**
 * @internal
 */
final readonly class LoadBricks implements BricksLoader
{
    /**
     * @throws NotAServiceConfigurationException
     * @throws ReflectionException
     */
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
                $result[] = new BrickRepresentation($package_name, $package_path, $this->loadServicesOfPackage($package_path));
            }
        }

        return $result;
    }

    /**
     * @return ServiceIntermediateRepresentation[]
     * @throws NotAServiceConfigurationException
     * @throws ReflectionException
     */
    private function loadServicesOfPackage(string $package_path): array
    {
        $result = [];
        $map    = ClassMapGenerator::createMap($package_path);
        foreach ($map as $symbol => $_path) {
            $reflection = new ReflectionClass($symbol);

            $attributes = $reflection->getAttributes(Service::class);
            if (empty($attributes)) {
                // Class is not a Service
                continue;
            }

            $service_attribute       = $attributes[0]->newInstance();
            $configuration_attribute = null;
            if ($service_attribute->configuration_classname !== null) {
                $configuration_reflection = new ReflectionClass($service_attribute->configuration_classname);

                $configuration_attributes = $configuration_reflection->getAttributes(ServiceConfiguration::class);
                if (empty($configuration_attributes)) {
                    throw new NotAServiceConfigurationException($service_attribute->configuration_classname, $reflection->name);
                }

                $configuration_attribute = $configuration_attributes[0]->newInstance();
            }

            $result[] = new ServiceIntermediateRepresentation($reflection, $service_attribute, $configuration_attribute);
        }

        return $result;
    }
}
