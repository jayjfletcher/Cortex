<?php

declare(strict_types=1);

namespace JayI\Cortex\Contracts;

use Illuminate\Http\Request;

interface UiTokenResolver
{
    /**
     * Resolve the bearer token the dashboard should use for API requests.
     */
    public function resolve(Request $request): ?string;
}
