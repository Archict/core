# Archict/core

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
