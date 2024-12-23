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

namespace Archict\Core\Services;

use Archict\Core\Env\EnvironmentService;
use Composer\InstalledVersions;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\TreeMapper;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Symfony\Component\Yaml\Yaml;
use function Psl\File\read;
use function Psl\Filesystem\exists;

/**
 * @internal
 */
final readonly class LoadServices implements ServicesLoader
{
    public function __construct(
        private TreeMapper $mapper,
    ) {
    }

    /**
     * @throws ServicesCannotBeLoadedException
     * @throws ServiceConfigurationFileFormatInvalidException
     * @throws ServiceConfigurationFileNotFoundException
     */
    public function loadServicesIntoManager(ServiceManager $manager, array $services_representation): void
    {
        $has_changed      = true;
        $services_to_init = $services_representation;
        while ($has_changed && !empty($services_to_init)) {
            $has_changed = false;
            $leftovers   = [];

            foreach ($services_to_init as $service) {
                $instance = $this->instantiateService($service, $manager);
                if ($instance === null) {
                    $leftovers[] = $service;
                } else {
                    $manager->add($instance);
                    $has_changed = true;
                }
            }

            $services_to_init = $leftovers;
        }

        if (!empty($services_to_init)) {
            throw new ServicesCannotBeLoadedException(
                array_map(
                    static fn(ServiceRepresentation $representation) => $representation->reflection->name,
                    $services_to_init
                )
            );
        }
    }

    /**
     * @throws ServiceConfigurationFileNotFoundException
     * @throws ServiceConfigurationFileFormatInvalidException
     */
    private function instantiateService(ServiceRepresentation $representation, ServiceManager $manager): ?object
    {
        $reflection  = $representation->reflection;
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            try {
                return $reflection->newInstance();
            } catch (ReflectionException) {
                return null;
            }
        }

        $parameters = $constructor->getParameters();
        $args       = [];
        foreach ($parameters as $parameter) {
            $arg = $this->getParameter($parameter, $manager, $representation);
            if ($arg === null) {
                return null;
            }

            $args[] = $arg;
        }

        try {
            return $reflection->newInstanceArgs($args);
        } catch (ReflectionException) {
            return null;
        }
    }

    /**
     * @throws ServiceConfigurationFileNotFoundException
     * @throws ServiceConfigurationFileFormatInvalidException
     */
    private function getParameter(ReflectionParameter $parameter, ServiceManager $manager, ServiceRepresentation $service): mixed
    {
        $type = $parameter->getType();
        if (!($type instanceof ReflectionNamedType) || $type->isBuiltin()) {
            return null;
        }

        $type_name = $type->getName(); // Assert it's a class-string
        if ($manager->has($type_name)) { // @phpstan-ignore-line
            return $manager->get($type_name); // @phpstan-ignore-line
        }

        if ($service->service_attribute->configuration_classname === $type_name) {
            try {
                return $this->mapper->map(
                    $type_name,
                    Yaml::parse(read($this->getConfigurationFilenameForService($service, $manager->get(EnvironmentService::class))))
                );
            } catch (MappingError $error) {
                throw new ServiceConfigurationFileFormatInvalidException($service->reflection->getName(), $error);
            }
        }

        return null;
    }

    /**
     * @return non-empty-string
     * @throws ServiceConfigurationFileNotFoundException
     */
    private function getConfigurationFilenameForService(ServiceRepresentation $representation, ?EnvironmentService $env): string
    {
        $base_filename = $representation->service_attribute->configuration_filename ?? (strtolower($representation->reflection->getShortName()) . '.yml');

        $package_config = $representation->package_path . '/config/' . $base_filename;
        if ($env !== null) {
            $config_dir = (string) $env->get('CONFIG_DIR', InstalledVersions::getRootPackage()['install_path'] . '/config/');
            if ($config_dir !== '' && $config_dir[0] !== '/') {
                $config_dir = InstalledVersions::getRootPackage()['install_path'] . '/' . $config_dir;
            }
        } else {
            $config_dir = InstalledVersions::getRootPackage()['install_path'] . '/config/';
        }

        $root_config = $config_dir . $base_filename;

        if ($root_config !== '' && exists($root_config)) {
            return $root_config;
        }

        if (exists($package_config)) {
            return $package_config;
        }

        throw new ServiceConfigurationFileNotFoundException($representation->reflection->getName());
    }
}
