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

use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * @internal
 */
final class LoadServices implements ServicesLoader
{
    /**
     * @throws ServicesCannotBeLoadedException
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
            $arg = $this->getParameter($parameter, $manager);
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

    private function getParameter(ReflectionParameter $parameter, ServiceManager $manager): mixed
    {
        $type = $parameter->getType();
        if (!($type instanceof ReflectionNamedType) || $type->isBuiltin()) {
            return null;
        }

        return $manager->get($type->getName()); // @phpstan-ignore-line
    }
}
