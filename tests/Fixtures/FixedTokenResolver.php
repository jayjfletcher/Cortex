<?php

declare(strict_types=1);

namespace JayI\Cortex\Tests\Fixtures;

use Illuminate\Http\Request;
use JayI\Cortex\Contracts\UiTokenResolver;

final class FixedTokenResolver implements UiTokenResolver
{
    public function resolve(Request $request): ?string
    {
        return 'fixed-token';
    }
}
