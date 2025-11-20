<?php

namespace NckRtl\Toolbar\Providers;

use Illuminate\Support\ServiceProvider;
use NckRtl\Toolbar\Data\ToolbarConfig;
use NckRtl\Toolbar\Toolbar;

class ToolbarProvider extends ServiceProvider
{
    public function boot(Toolbar $toolbar): void
    {
        $this->update($toolbar->config);
    }

    public function update(ToolbarConfig $toolbarConfig): void {}
}
