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

use Archict\Core\Bricks\BricksLoader;
use Archict\Core\Bricks\LoadBricks;
use Archict\Core\Cache\CacheLoader;
use Archict\Core\Env\Environment;
use Archict\Core\Event\EventManager;
use Archict\Core\Event\EventsLoader;
use Archict\Core\Event\LoadEvents;
use Archict\Core\Services\LoadServices;
use Archict\Core\Services\ServiceManager;
use Archict\Core\Services\ServicesLoader;
use CuyZ\Valinor\MapperBuilder;

/**
 * Core of library ;)
 *
 * This class load and manage Services, Events, ...
 */
final readonly class Core
{
    public ServiceManager $service_manager;
    private EventManager $event_manager;

    public function __construct(
        private BricksLoader $brick_loader,
        private ServicesLoader $services_loader,
        private EventsLoader $events_loader,
    ) {
        $this->service_manager = new ServiceManager((new MapperBuilder())->enableFlexibleCasting()->allowSuperfluousKeys()->allowPermissiveTypes()->mapper());
        $this->event_manager   = new EventManager($this->service_manager);
        $environment           = new Environment();
        $this->service_manager->add($this->event_manager);
        $this->service_manager->add($environment);
        $this->service_manager->add(CacheLoader::loadCache($environment));
    }

    public function load(): void
    {
        $bricks   = $this->brick_loader->loadInstalledBricks();
        $services = [];
        foreach ($bricks as $brick) {
            $services = [...$services, ...$brick->services];
        }

        $this->services_loader->loadServicesIntoManager($this->service_manager, array_values($services));
        $this->events_loader->loadEventsListeners($this->event_manager, array_values($services));
    }

    public static function build(): self
    {
        return new self(
            new LoadBricks(),
            new LoadServices(),
            new LoadEvents(),
        );
    }
}
