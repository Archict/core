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

use Archict\Brick\ListeningEvent;
use ReflectionNamedType;

/**
 * @internal
 */
final class LoadEvents implements EventsLoader
{
    /**
     * @throws EventListenerWrongParameterAmountException
     * @throws EventListenerMissingEventTypeException
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function loadEventsListeners(EventManager $event_manager, array $services_representation): void
    {
        foreach ($services_representation as $service) {
            $reflection = $service->reflection;
            foreach ($reflection->getMethods() as $method) {
                $attributes = $method->getAttributes(ListeningEvent::class);
                if (empty($attributes)) {
                    continue;
                }

                $parameters = $method->getParameters();
                if (count($parameters) !== 1) {
                    throw new EventListenerWrongParameterAmountException($reflection->getName(), $method->getName(), count($parameters));
                }

                $event_type = $parameters[0]->getType();
                if (!($event_type instanceof ReflectionNamedType)) {
                    throw new EventListenerMissingEventTypeException($reflection->getName(), $method->getName());
                }

                $event = $event_type->getName();
                $event_manager->addListener($event, $reflection->getName(), $method->getName()); // @phpstan-ignore-line
            }
        }
    }
}
