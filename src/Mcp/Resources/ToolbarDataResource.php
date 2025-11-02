<?php

namespace NckRtl\Toolbar\Mcp\Resources;

use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class ToolbarDataResource extends Resource
{
    protected string $name = 'toolbar-data';

    protected string $description = 'The toolbar data resource.';

    protected string $mimeType = 'text/markdown';

    public function handle(): Response
    {
        return Response::text(file_get_contents(__DIR__.'/toolbar-data.md'));
    }
}
