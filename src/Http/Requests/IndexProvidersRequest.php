<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\ListProvidersAction;
use JayI\Cortex\Http\Request;

final class IndexProvidersRequest extends Request
{
    public function persist(): JsonResponse
    {
        return new JsonResponse([
            'data' => app(ListProvidersAction::class)->execute(),
        ]);
    }
}
