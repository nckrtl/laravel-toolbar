# Laravel Toolbar

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nckrtl/laravel-toolbar.svg?style=flat-square)](https://packagist.org/packages/nckrtl/laravel-toolbar)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/nckrtl/laravel-toolbar/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/nckrtl/laravel-toolbar/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/nckrtl/laravel-toolbar/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/nckrtl/laravel-toolbar/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/nckrtl/laravel-toolbar.svg?style=flat-square)](https://packagist.org/packages/nckrtl/laravel-toolbar)

A powerful development toolbar for Laravel that sits at the bottom of your screen, providing real-time insights into your application's performance, database queries, request lifecycle, and more. Built with Vue.js and deeply integrated with Laravel Telescope for comprehensive data collection.

## Features

- **Request Profiling**: Detailed breakdown of your request lifecycle with timing for each stage (bootstrapping, service providers, routing, middleware, controller, view rendering)
- **Database Query Monitoring**: Track all queries with timing, detect duplicates and slow queries, see SQL with bindings
- **Request Information**: View HTTP method, URI, IP address, controller action, and middleware stack
- **Laravel Environment**: Display Laravel version, environment, timezone, locale, and debug mode
- **PHP Information**: PHP version and configuration details
- **Eloquent Models**: Track model operations via Telescope integration
- **Vue.js Integration**: Monitor Vue.js related data in your application
- **Response Details**: Inspect response data and headers
- **Inertia.js Support**: Works seamlessly with Inertia.js requests via custom headers
- **AI Integration**: MCP (Model Context Protocol) server for AI assistants to access request data
- **Customizable Collectors**: Enable/disable specific data collectors based on your needs
- **Visual Timeline**: Color-coded request stage visualization showing where time is spent
- **Debug Mode**: Additional metadata and timing information for development

## Requirements

- PHP 8.4+
- Laravel 11.x or 12.x
- Laravel Telescope 5.15+

## Installation

Install the package via Composer:

```bash
composer require nckrtl/laravel-toolbar
```

The package will automatically register itself via Laravel's auto-discovery.

## Configuration

### Basic Setup

By default, the toolbar will work out of the box with sensible defaults. All collectors are enabled and will start gathering data immediately.

### Advanced Customization

To customize the toolbar configuration, run the customize command:

```bash
php artisan toolbar:customize
```

This will:
1. Publish a `ToolbarConfigProvider` to `app/Providers/ToolbarConfigProvider.php`
2. Register the provider in your application

You can then customize the toolbar by editing the provider:

```php
<?php

namespace App\Providers;

use NckRtl\Toolbar\Toolbar;
use NckRtl\Toolbar\Data\ToolbarConfig;
use NckRtl\Toolbar\Providers\ToolbarProvider;
use NckRtl\Toolbar\Collectors\PhpCollector;
use NckRtl\Toolbar\Collectors\VueCollector;
use NckRtl\Toolbar\Collectors\LaravelCollector;
use NckRtl\Toolbar\Collectors\QueriesCollector;
use NckRtl\Toolbar\Collectors\RequestCollector;
use NckRtl\Toolbar\Collectors\ProfilerCollector;

class ToolbarConfigProvider extends ToolbarProvider
{
    public function update(ToolbarConfig $toolbarConfig): void
    {
        // Enable debug mode for additional metadata
        $toolbarConfig->debug();

        // Configure which collectors to use
        $toolbarConfig->collectors([
            new ProfilerCollector,
            new RequestCollector,
            new QueriesCollector,
            new LaravelCollector,
            new PhpCollector,
            // new VueCollector, // Disable Vue collector
        ]);

        // Disable the toolbar completely (useful for production)
        // $toolbarConfig->disable();
    }
}
```

## Available Collectors

### ProfilerCollector
Tracks the request lifecycle with detailed timing information for each stage:
- Application bootstrapping
- Service providers booting
- Request pipeline preparation
- Routing
- Middleware pipeline (in and out)
- Controller execution
- View rendering
- Response preparation

### RequestCollector
Collects HTTP request information:
- Request method (GET, POST, etc.)
- URI and IP address
- Controller action
- Middleware stack
- Request duration
- Memory usage

### QueriesCollector
Monitors database operations:
- All executed queries with SQL and bindings
- Query execution time and percentage of total
- Duplicate query detection
- Slow query identification
- Database connections and drivers used

### LaravelCollector
Displays Laravel environment information:
- Laravel version
- Application environment (local, production, etc.)
- Timezone configuration
- Locale settings
- Debug mode status

### PhpCollector
Shows PHP runtime information:
- PHP version
- Configuration details

### VueCollector
Tracks Vue.js related data in your application.

### ModelsCollector
Monitors Eloquent model operations via Telescope integration.

### ResponseCollector
Inspects response data including headers and content.

## MCP Integration for AI Assistants

Laravel Toolbar includes a Model Context Protocol (MCP) server that allows AI assistants like Claude to access your application's request data during development. This enables AI to help debug issues, optimize queries, and provide insights based on real application data.

The MCP server is automatically registered and provides:
- Request data access for AI tools
- Collector information
- Real-time debugging assistance

## How It Works

1. **Middleware Integration**: The toolbar registers middleware at the beginning and end of the web middleware group to capture the entire request lifecycle
2. **Event Listeners**: Hooks into Laravel events (routing, request handling) to record checkpoints
3. **Telescope Integration**: Leverages Laravel Telescope's data collection for queries, models, and other operations
4. **Data Collection**: After the request is handled, all enabled collectors gather their data
5. **Injection**: For HTML responses, the toolbar UI is injected before the closing `</body>` tag. For Inertia.js requests, data is sent via a custom header
6. **Caching**: Request data is cached briefly for MCP server access

## Usage in Different Contexts

### Standard HTML Responses
The toolbar automatically injects itself into any HTML response containing a `</body>` tag.

### Inertia.js Applications
For Inertia.js requests (detected via `X-Inertia` header), toolbar data is sent via the `x-toolbar` header in base64-encoded JSON format.

### API Routes
The toolbar automatically excludes AJAX requests and non-HTML responses to avoid interfering with your API endpoints.

## Development vs Production

The toolbar includes built-in safety features:

- Automatically disabled when running in console
- Respects Telescope's `ignore_paths` configuration
- Supports both Vite HMR (development) and compiled assets (production)
- Can be completely disabled via configuration

For production deployments, it's recommended to not install this package or disable it in your configuration:

```php
if (app()->environment('production')) {
    $toolbarConfig->disable();
}
```

## Testing

```bash
composer test
```

## Code Quality

```bash
# Run static analysis
composer analyse

# Fix code style
composer format
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [nckrtl](https://github.com/nckrtl)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
