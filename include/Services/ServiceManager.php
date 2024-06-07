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

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

/**
 * ServiceManager is a Service
 */
final class ServiceManager
{
    /**
     * @var array<class-string, object>
     */
    private array $services = [];

    public function __construct()
    {
        $this->add($this);
    }

    /**
     * @param class-string $classname
     */
    public function has(string $classname): bool
    {
        foreach ($this->services as $service_class => $service) {
            if ($this->isServiceMatchClassName($service_class, $classname)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @psalm-template S of object
     * @psalm-param class-string<S> $classname
     * @psalm-return ?S
     * @psalm-suppress InvalidReturnType,InvalidReturnStatement
     */
    public function get(string $classname): ?object
    {
        foreach ($this->services as $service_class => $service) {
            if ($this->isServiceMatchClassName($service_class, $classname)) {
                return $service; // @phpstan-ignore-line
            }
        }

        return null;
    }

    public function add(object $service): void
    {
        $this->services[$service::class] = $service;
    }

    /**
     * @psalm-template C of object
     * @param class-string<C> $class
     * @return ?C
     * @psalm-suppress InvalidReturnType,InvalidReturnStatement
     */
    public function instantiateWithServices(string $class): ?object
    {
        if (!class_exists($class)) {
            return null;
        }

        $reflection  = new ReflectionClass($class);
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
            $type = $parameter->getType();
            if (!($type instanceof ReflectionNamedType) || $type->isBuiltin()) {
                return null;
            }

            $type_name = $type->getName(); // Assert it's a class-string
            if ($this->has($type_name)) { // @phpstan-ignore-line
                $args[] = $this->get($type_name); // @phpstan-ignore-line
            } else {
                return null;
            }
        }

        try {
            return $reflection->newInstanceArgs($args);
        } catch (ReflectionException) {
            return null;
        }
    }

    /**
     * @param class-string $service
     * @param class-string $classname
     */
    private function isServiceMatchClassName(string $service, string $classname): bool
    {
        return is_a($service, $classname, true);
    }
}
