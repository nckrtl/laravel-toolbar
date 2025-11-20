<?php

namespace NckRtl\Toolbar\Providers;

use NckRtl\Toolbar\Toolbar;
use NckRtl\Toolbar\Data\ToolbarConfig;
use Illuminate\Support\ServiceProvider;

class ToolbarProvider extends ServiceProvider
{
    public function boot(Toolbar $toolbar): void
    {
        $this->update($toolbar->config);
    }

    public function update(ToolbarConfig $toolbarConfig): void
    {
        return;
    }
}
