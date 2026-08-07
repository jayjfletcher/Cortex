<?php

declare(strict_types=1);

namespace JayI\Cortex\Tests\Fixtures;

use JayI\Cortex\Mcp\Server;

final class PlainServer extends Server
{
    protected string $instructions = 'Property-declared plain server instructions.';
}
