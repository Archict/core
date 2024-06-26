# Archict/core

[![Tests](https://github.com/Archict/core/actions/workflows/tests.yml/badge.svg?branch=master)](https://github.com/Archict/core/actions/workflows/tests.yml)

> In the Land of PHP where the Shadows lie.
>
> One Brick to rule them all, One Brick to find them,
>
> One Brick to bring them all, and in the darkness bind them
>
> In the Land of PHP where the Shadows lie.

Heart of Archict, this library load and manage Bricks.

## Usage

```php
<?php

use Archict\Core\Core;
use Archict\Core\Event\EventDispatcher;

$core = Core::build();
// Load Services and Events
$core->load();

// Access to all Services
$manager    = $core->service_manager;
$dispatcher = $manager->get(EventDispatcher::class);
$dispatcher->dispatch(new MyEvent());
```

That's all!

Please note that if your package is also a Brick, it will scan only `src` and `include` directory for Services.

## Supplied Services

**ServiceManager**

With `ServiceManager::get(class-string)` you can retrieve any Service. But it's better if you use automatic Service
injection in your own Service constructor.

**EventDispatcher**

With `EventDispatcher::dispatch(mixed)` you can dispatch an Event to all its listeners.

**CacheInterface**

Implementation of [PSR-16](https://www.php-fig.org/psr/psr-16/) cache system.
