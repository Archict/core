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

namespace Archict\Core;

use Archict\Core\Bricks\BricksLoaderStub;
use Archict\Core\Env\EnvironmentService;
use Archict\Core\Event\EventDispatcher;
use Archict\Core\Event\EventsLoaderStub;
use Archict\Core\Services\ServicesLoaderStub;
use PHPUnit\Framework\TestCase;

class CoreTest extends TestCase
{
    public function testItDoesntThrow(): void
    {
        self::expectNotToPerformAssertions();
        $core = new Core(BricksLoaderStub::build(), ServicesLoaderStub::build(), EventsLoaderStub::build());
        $core->load();
    }

    public function testItHasSomeServices(): void
    {
        $core     = new Core(BricksLoaderStub::build(), ServicesLoaderStub::build(), EventsLoaderStub::build());
        $services = $core->service_manager;

        self::assertTrue($services->has(EventDispatcher::class));
        self::assertTrue($services->has(EnvironmentService::class));
    }
}
