<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('toolbar:mcp', 'Starts Laravel Toolbar (usually from mcp.json)')]
class StartMcpServerCommand extends Command
{
    public function handle(): int
    {
        return Artisan::call('mcp:start laravel-toolbar');
    }
}
