<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use Laravel\Mcp\Facades\Mcp;
use NckRtl\Toolbar\Mcp\Servers\DataServer;

beforeEach(function () {
    config()->set('toolbar.request_data_allowed_environments', ['testing']);

    Mcp::web('/toolbar-protocol-test', DataServer::class);
});

it('serves existing initialize clients and lists toolbar tools', function () {
    $this->postJson('/toolbar-protocol-test', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-06-18',
            'capabilities' => (object) [],
            'clientInfo' => ['name' => 'toolbar-test', 'version' => '1.0.0'],
        ],
    ])->assertOk()->assertJsonPath('result.protocolVersion', '2025-06-18');

    $this->postJson('/toolbar-protocol-test', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/list',
        'params' => (object) [],
    ])->assertOk()->assertJsonPath('error', null)->assertJsonPath('result.tools.0.name', 'get-request-data-tool');
});

it('serves MCP 1.0 discovery and validates modern request headers', function () {
    if (version_compare(InstalledVersions::getVersion('laravel/mcp'), '1.0.0', '<')) {
        $this->markTestSkipped('Modern discovery is available in Laravel MCP 1.0.');
    }

    $message = [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'server/discover',
        'params' => [
            '_meta' => [
                'io.modelcontextprotocol/protocolVersion' => '2026-07-28',
                'io.modelcontextprotocol/clientCapabilities' => (object) [],
            ],
        ],
    ];

    $this->postJson('/toolbar-protocol-test', $message, [
        'MCP-Protocol-Version' => '2026-07-28',
        'Mcp-Method' => 'server/discover',
    ])->assertOk()->assertJsonPath('result.supportedVersions.0', '2026-07-28');

    $this->postJson('/toolbar-protocol-test', $message, [
        'MCP-Protocol-Version' => '2026-07-28',
        'Mcp-Method' => 'tools/list',
    ])->assertStatus(400)->assertJsonPath('error.code', -32020);
});
