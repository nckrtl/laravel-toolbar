<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Console;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('toolbar:customize', 'Customize Laravel Toolbar')]
class CustomizeToolbarCommand extends Command
{
    public function handle(): int
    {
        $this->newLine();

        $this->info('Publishing the toolbar configuration provider.');

        $this->newLine();

        collect([
            'Publishing config provider' => fn () => $this->callSilent('vendor:publish', ['--tag' => 'toolbar-provider']) == 0,
            'Registering config provider' => fn () => $this->registerToolbarConfigProvider(),
        ])->each(fn ($task, $description) => $this->components->task($description, $task));

        $this->newLine();
        $this->components->success('Laravel toolbar ready to be customized!');

        $providerPath = app_path('Providers/ToolbarConfigProvider.php');

        $this->components->twoColumnDetail(
            'Provider location',
            "<fg=gray>$providerPath</>"
        );

        $this->newLine();

        // Display "Next Steps" header separately
        $this->line('<fg=bright-blue>Configuration examples:</>');
        $this->components->bulletList([
            'Enable debug mode: <fg=yellow>$toolbarConfig->debug()</>',
            'Configure collectors: <fg=yellow>$toolbarConfig->collectors([...])</>',
        ]);

        $this->newLine();

        return Command::SUCCESS;
    }

    protected function registerToolbarConfigProvider(): bool
    {
        $namespace = Str::replaceLast('\\', '', $this->laravel->getNamespace());

        if (file_exists($this->laravel->bootstrapPath('providers.php'))) {
            ServiceProvider::addProviderToBootstrapFile("{$namespace}\\Providers\\ToolbarConfigProvider");
            return true;
        }

        $appConfig = file_get_contents(config_path('app.php'));

        if (Str::contains($appConfig, $namespace.'\\Providers\\ToolbarConfigProvider::class')) {
            return true;
        }

        file_put_contents(config_path('app.php'), str_replace(
            "{$namespace}\\Providers\\EventServiceProvider::class,".PHP_EOL,
            "{$namespace}\\Providers\\EventServiceProvider::class,".PHP_EOL."        {$namespace}\\Providers\\ToolbarConfigProvider::class,".PHP_EOL,
            $appConfig
        ));

        file_put_contents(app_path('Providers/ToolbarConfigProvider.php'), str_replace(
            "namespace App\\Providers;",
            "namespace {$namespace}\\Providers;",
            file_get_contents(app_path('Providers/ToolbarConfigProvider.php'))
        ));

        return true;
    }
}