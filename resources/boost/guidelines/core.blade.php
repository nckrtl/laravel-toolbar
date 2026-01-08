## Laravel Toolbar

A powerful development toolbar for Laravel that provides real-time insights into your application's performance, database queries, request lifecycle, and more.

### Quick Decision Guide

| User Request | Action | Why |
|--------------|--------|-----|
| "Add dev toolbar" | `composer require nckrtl/laravel-toolbar` | Auto-registers via Laravel discovery |
| "Customize toolbar" | `php artisan toolbar:customize` | Publishes ToolbarConfigProvider |
| "Disable a collector" | Edit ToolbarConfigProvider | Remove collector from array |
| "Enable debug mode" | `$toolbarConfig->debug()` | Shows additional timing metadata |
| "Use with Telescope" | Install Telescope separately | Enhanced model tracking |

### Installation

@verbatim
<code-snippet name="Install Laravel Toolbar" lang="bash">
composer require nckrtl/laravel-toolbar
</code-snippet>
@endverbatim

The toolbar auto-registers and works immediately with sensible defaults.

### Customization

To customize collectors or enable debug mode:

@verbatim
<code-snippet name="Customize toolbar configuration" lang="bash">
php artisan toolbar:customize
</code-snippet>
@endverbatim

This publishes `app/Providers/ToolbarConfigProvider.php`:

@verbatim
<code-snippet name="Example ToolbarConfigProvider" lang="php">
<?php

namespace App\Providers;

use NckRtl\Toolbar\Data\ToolbarConfig;
use NckRtl\Toolbar\Providers\ToolbarProvider;
use NckRtl\Toolbar\Collectors\PhpCollector;
use NckRtl\Toolbar\Collectors\LaravelCollector;
use NckRtl\Toolbar\Collectors\QueriesCollector;
use NckRtl\Toolbar\Collectors\RequestCollector;
use NckRtl\Toolbar\Collectors\ProfilerCollector;
use NckRtl\Toolbar\Collectors\ResponseCollector;

class ToolbarConfigProvider extends ToolbarProvider
{
    public function update(ToolbarConfig $toolbarConfig): void
    {
        // Enable debug mode for additional metadata
        $toolbarConfig->debug();

        // Configure which collectors to use
        $toolbarConfig->collectors([
            ProfilerCollector::class,
            QueriesCollector::class,
            RequestCollector::class,
            LaravelCollector::class,
            PhpCollector::class,
            ResponseCollector::class,
        ]);
    }
}
</code-snippet>
@endverbatim

### Available Collectors

| Collector | What It Shows |
|-----------|---------------|
| `ProfilerCollector` | Request lifecycle timing (bootstrap, routing, middleware, controller, view) |
| `QueriesCollector` | Database queries with timing, duplicates, slow query detection |
| `RequestCollector` | HTTP method, URI, IP, controller action, middleware stack |
| `LaravelCollector` | Laravel version, environment, timezone, locale, debug mode |
| `PhpCollector` | PHP version and configuration |
| `ResponseCollector` | Response data and headers |
| `ModelsCollector` | Eloquent model operations (requires Telescope) |
| `VueCollector` | Vue.js related data |
| `InertiaCollector` | Inertia.js request data |
| `TailwindCollector` | Tailwind CSS information |

### Telescope Integration (Optional)

For enhanced model tracking, install Telescope:

@verbatim
<code-snippet name="Install Telescope for enhanced features" lang="bash">
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
</code-snippet>
@endverbatim

When Telescope is installed:
- `ModelsCollector` can track Eloquent model operations
- Additional request data is available from Telescope entries

### MCP Server for AI Assistants

The toolbar includes an MCP (Model Context Protocol) server that allows AI assistants to access request data programmatically. This enables AI tools to analyze performance issues, query patterns, and request details.

### Important Notes

1. **Development only** - The toolbar is for development environments
2. **Auto-injection** - The toolbar automatically injects into HTML responses
3. **Inertia.js support** - Works seamlessly with Inertia.js via custom headers
4. **No configuration required** - Works out of the box with all collectors enabled
5. **Customize when needed** - Only run `toolbar:customize` if you need to change defaults
