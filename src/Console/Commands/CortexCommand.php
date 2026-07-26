<?php

declare(strict_types=1);

namespace JayI\Cortex\Console\Commands;

use Illuminate\Console\Command;

class CortexCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'cortex:placeholder';

    /**
     * The command description.
     */
    protected $description = 'Placeholder Artisan command shipped by the package cortex.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('Cortex placeholder command executed.');

        return self::SUCCESS;
    }
}
