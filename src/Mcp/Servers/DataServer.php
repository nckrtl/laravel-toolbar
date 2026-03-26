<?php

namespace NckRtl\Toolbar\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Tool;
use NckRtl\Toolbar\Mcp\Resources\ToolbarDataResource;
use NckRtl\Toolbar\Mcp\Tools\GetRequestDataTool;

class DataServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Data Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.0.0';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = 'This server provides detailed data about web requests during webdevelopment.';

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<Tool>>
     */
    protected array $tools = [
        GetRequestDataTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<Server\Resource>>
     */
    protected array $resources = [
        ToolbarDataResource::class,
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<Prompt>>
     */
    protected array $prompts = [
        // DescribeWeatherPrompt::class,
    ];
}
