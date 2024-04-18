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

namespace Archict\Core\Event;

use Archict\Core\Fixtures\brick1\MyEvent;
use Archict\Core\Services\ServiceManager;
use PHPUnit\Framework\TestCase;

final class EventManagerTest extends TestCase
{
    public function testItCanDispatchEventToService(): void
    {
        $event   = new MyEvent();
        $service = new class {
            public function listenEvent(MyEvent $event): void
            {
                $event->listened = true;
            }
        };

        $service_manager = new ServiceManager();
        $service_manager->add($service);
        $event_manager = new EventManager($service_manager);
        $event_manager->addListener(MyEvent::class, $service::class, 'listenEvent');

        $event->listened = false;
        $event_manager->dispatch($event);
        self::assertTrue($event->listened);
    }

    public function testItCanDispatchEventToMultipleServices(): void
    {
        $event    = new MyEvent();
        $service1 = new class {
            public function listenEvent(MyEvent $event): void
            {
                $event->nb_listened += 1;
            }
        };
        $service2 = new class {
            public function listenAnEvent(MyEvent $event): void
            {
                $event->nb_listened += 2;
            }
        };

        $service_manager = new ServiceManager();
        $service_manager->add($service1);
        $service_manager->add($service2);
        $event_manager = new EventManager($service_manager);
        $event_manager->addListener(MyEvent::class, $service1::class, 'listenEvent');
        $event_manager->addListener(MyEvent::class, $service2::class, 'listenAnEvent');

        $event->nb_listened = 0;
        $event_manager->dispatch($event);
        self::assertEquals(3, $event->nb_listened);
    }

    /**
     * @psalm-suppress RedundantCondition
     */
    public function testEventCanBeNotListened(): void
    {
        $event         = new MyEvent();
        $event_manager = new EventManager(new ServiceManager());

        $event->nb_listened = 0;
        $event->listened    = false;
        $event_manager->dispatch($event);
        self::assertEquals(0, $event->nb_listened);
        self::assertFalse($event->listened);
    }
}
