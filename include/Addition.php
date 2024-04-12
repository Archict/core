<?php

declare(strict_types=1);

namespace Archict\Core;

final readonly class Addition
{
    public function __construct(
        private int $left,
        private int $right,
    ) {
    }

    public function getResult(): int
    {
        return $this->left + $this->right;
    }
}
