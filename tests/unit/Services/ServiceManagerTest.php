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

use Archict\Core\Fixtures\brick1\src\IService1;
use Archict\Core\Fixtures\brick1\src\Service1;
use Archict\Core\Fixtures\brick1\src\Service1Configuration;
use Archict\Core\Fixtures\ServiceWithDependency;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use PHPUnit\Framework\TestCase;

final class ServiceManagerTest extends TestCase
{
    private TreeMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = (new MapperBuilder())->enableFlexibleCasting()->allowSuperfluousKeys()->allowPermissiveTypes()->mapper();
    }

    public function testItStoreItself(): void
    {
        $manager = new ServiceManager($this->mapper);
        self::assertTrue($manager->has(ServiceManager::class));
        self::assertSame($manager, $manager->get(ServiceManager::class));
    }

    public function testItCanStoreThenRetrieveService(): void
    {
        $manager = new ServiceManager($this->mapper);
        $service = new Service1(new Service1Configuration(0));
        $manager->add($service);

        self::assertTrue($manager->has(Service1::class));
        self::assertTrue($manager->has(IService1::class));
        self::assertSame($service, $manager->get(Service1::class));
        self::assertSame($service, $manager->get(IService1::class));
    }

    public function testItCanInstantiateWithService(): void
    {
        $manager = new ServiceManager($this->mapper);
        $service = new Service1(new Service1Configuration(0));
        $manager->add($service);

        $object = $manager->instantiateWithServices(ServiceWithDependency::class);
        self::assertInstanceOf(ServiceWithDependency::class, $object);
        self::assertSame($service, $object->service_1);
    }
}
