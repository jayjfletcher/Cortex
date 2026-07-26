<?php

declare(strict_types=1);

namespace JayI\Cortex\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \JayI\Cortex\Tools\ToolRegistry tools()
 * @method static \JayI\Cortex\Runtime\DbAgent agent(string $slug)
 * @method static \Laravel\Ai\Responses\AgentResponse run(string $slug, string $input)
 *
 * @see \JayI\Cortex\Cortex
 */
class Cortex extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JayI\Cortex\Cortex::class;
    }
}
