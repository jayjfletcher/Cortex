<?php

declare(strict_types=1);

namespace JayI\Cortex\Tests\Fixtures;

final class InvalidTokenResolver
{
    public function resolve(): ?string
    {
        return 'invalid';
    }
}
