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

use Archict\Core\Services\ServiceManager;

/**
 * @internal
 */
final class EventManager implements EventDispatcher
{
    /**
     * @var array<class-string, array<class-string, string>>
     */
    private array $listeners = [];

    public function __construct(
        private readonly ServiceManager $service_manager,
    ) {
    }

    /**
     * @param class-string $event
     * @param class-string $service
     */
    public function addListener(string $event, string $service, string $method): void
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][$service] = $method;
    }

    public function dispatch(mixed $event): mixed
    {
        if (isset($this->listeners[$event::class])) {
            foreach ($this->listeners[$event::class] as $service => $method) {
                $service_instance = $this->service_manager->get($service);
                if ($service_instance) {
                    $service_instance->{$method}($event);
                }
            }
        }

        return $event;
    }
}
