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

use Archict\Brick\Service;
use Archict\Core\Fixtures\brick1\Service1;
use Archict\Core\Fixtures\ServiceWithDependency;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LoadServicesTest extends TestCase
{
    public function testItCanLoadNoServices(): void
    {
        self::expectNotToPerformAssertions();
        (new LoadServices())->loadServicesIntoManager(new ServiceManager(), []);
    }

    public function testItCanLoadServices(): void
    {
        $manager = new ServiceManager();
        (new LoadServices())->loadServicesIntoManager(
            $manager,
            [new ServiceRepresentation(new ReflectionClass(Service1::class), new Service(), null)]
        );

        self::assertTrue($manager->has(Service1::class));
    }

    public function testItCannotLoadServicesWithNonExistentDependencies(): void
    {
        $manager = new ServiceManager();
        self::expectException(ServicesCannotBeLoadedException::class);
        (new LoadServices())->loadServicesIntoManager(
            $manager,
            [new ServiceRepresentation(new ReflectionClass(ServiceWithDependency::class), new Service(), null)]
        );
    }

    public function testItCanLoadServicesWithDependencies(): void
    {
        $manager = new ServiceManager();
        (new LoadServices())->loadServicesIntoManager(
            $manager,
            [
                new ServiceRepresentation(new ReflectionClass(ServiceWithDependency::class), new Service(), null),
                new ServiceRepresentation(new ReflectionClass(Service1::class), new Service(), null),
            ]
        );

        self::assertTrue($manager->has(Service1::class));
        self::assertTrue($manager->has(ServiceWithDependency::class));
    }
}