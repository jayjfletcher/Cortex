<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use JayI\Cortex\Contracts\UiTokenResolver;
use RuntimeException;

final class UiController
{
    public function __invoke(Request $request, Factory $view): View
    {
        $mode = (string) config('cortex.ui.auth.mode', 'session');

        return $view->make('cortex::app', [
            'cortexConfig' => [
                'apiBase' => url((string) config('cortex.routes.prefix', 'cortex')),
                'basePath' => '/'.trim((string) config('cortex.ui.path', 'cortex/ui'), '/'),
                'auth' => [
                    'mode' => $mode,
                    'token' => $this->resolveToken($request),
                    'oauth' => $mode === 'oauth' ? [
                        'clientId' => (string) config('cortex.ui.auth.oauth.client_id'),
                        'authorizeUrl' => url((string) config('cortex.ui.auth.oauth.authorize_url', '/oauth/authorize')),
                        'tokenUrl' => url((string) config('cortex.ui.auth.oauth.token_url', '/oauth/token')),
                        'scopes' => array_values((array) config('cortex.ui.auth.oauth.scopes', [])),
                    ] : null,
                ],
                'csrfToken' => $request->hasSession() ? $request->session()->token() : null,
            ],
            'assetVersion' => $this->assetVersion(),
        ]);
    }

    private function resolveToken(Request $request): ?string
    {
        if (config('cortex.ui.auth.mode', 'session') !== 'token') {
            return null;
        }

        /** @var class-string|null $class */
        $class = config('cortex.ui.auth.token_resolver');

        if ($class === null) {
            return null;
        }

        $resolver = app($class);

        if (! $resolver instanceof UiTokenResolver) {
            throw new RuntimeException(sprintf(
                'The [cortex.ui.auth.token_resolver] class [%s] must implement [%s].',
                $class,
                UiTokenResolver::class,
            ));
        }

        return $resolver->resolve($request);
    }

    /**
     * A cache-busting hash of the compiled dashboard bundle, if built.
     */
    private function assetVersion(): ?string
    {
        $bundle = dirname(__DIR__, 3).'/public/app.js';

        if (! is_file($bundle)) {
            return null;
        }

        $hash = md5_file($bundle);

        return $hash === false ? null : $hash;
    }
}
