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
use Archict\Core\Fixtures\brick1\Service1Configuration;
use Archict\Core\Fixtures\ServiceWithDependency;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LoadServicesTest extends TestCase
{
    private const PACKAGE_DIR = __DIR__ . '/../Fixtures/brick1';
    private TreeMapper $mapper;
    private ServiceRepresentation $service1;
    private ServiceRepresentation $service_with_dependency;

    protected function setUp(): void
    {
        $this->mapper                  = (new MapperBuilder())->allowPermissiveTypes()->mapper();
        $this->service1                = new ServiceRepresentation(
            new ReflectionClass(Service1::class),
            new Service(Service1Configuration::class, 'bar.yml'),
            self::PACKAGE_DIR,
        );
        $this->service_with_dependency = new ServiceRepresentation(
            new ReflectionClass(ServiceWithDependency::class),
            new Service(),
            self::PACKAGE_DIR,
        );
    }

    public function testItCanLoadNoServices(): void
    {
        self::expectNotToPerformAssertions();
        (new LoadServices($this->mapper))->loadServicesIntoManager(new ServiceManager(), []);
    }

    public function testItCanLoadServices(): void
    {
        $manager = new ServiceManager();
        (new LoadServices($this->mapper))->loadServicesIntoManager(
            $manager,
            [$this->service1]
        );

        self::assertTrue($manager->has(Service1::class));
    }

    public function testItCannotLoadServicesWithNonExistentDependencies(): void
    {
        $manager = new ServiceManager();
        self::expectException(ServicesCannotBeLoadedException::class);
        (new LoadServices($this->mapper))->loadServicesIntoManager(
            $manager,
            [$this->service_with_dependency]
        );
    }

    public function testItCanLoadServicesWithDependencies(): void
    {
        $manager = new ServiceManager();
        (new LoadServices($this->mapper))->loadServicesIntoManager(
            $manager,
            [$this->service1, $this->service_with_dependency]
        );

        self::assertTrue($manager->has(Service1::class));
        self::assertTrue($manager->has(ServiceWithDependency::class));
    }
}
