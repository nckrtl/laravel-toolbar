# Laravel Toolbar

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nckrtl/laravel-toolbar.svg?style=flat-square)](https://packagist.org/packages/nckrtl/laravel-toolbar)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/nckrtl/laravel-toolbar/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/nckrtl/laravel-toolbar/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/nckrtl/laravel-toolbar/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/nckrtl/laravel-toolbar/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/nckrtl/laravel-toolbar.svg?style=flat-square)](https://packagist.org/packages/nckrtl/laravel-toolbar)

A powerful development toolbar for Laravel that sits at the bottom of your screen, providing real-time insights into your application's performance, database queries, request lifecycle, and more. Built with Vue.js and works standalone with optional Laravel Telescope integration for enhanced data collection.

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

## Installation

Install the package via Composer:

```bash
composer require nckrtl/laravel-toolbar
```

The package will automatically register itself via Laravel's auto-discovery.

### Optional Dependencies

While Laravel Toolbar works perfectly standalone, it includes optional integration with Laravel Telescope for enhanced data collection:

```bash
composer require laravel/telescope
```

**When Telescope is installed:**
- The `ModelsCollector` can track Eloquent model operations
- Additional request data can be sourced from Telescope entries

**Note:** All other collectors work independently without Telescope. The toolbar will automatically detect if Telescope is installed and enable enhanced features accordingly.

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
use NckRtl\Toolbar\Collectors\ModelsCollector;
use NckRtl\Toolbar\Collectors\ResponseCollector;

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
            new ResponseCollector,
            // new VueCollector, // Disable Vue collector
            // new ModelsCollector, // Requires Telescope
        ]);

        // Granular collector configuration example
        // Customize LaravelCollector to show only specific fields
        $laravelCollector = new LaravelCollector;
        $laravelCollector->config->version = true;
        $laravelCollector->config->environment = true;
        $laravelCollector->config->debug = true;
        $laravelCollector->config->timezone = false; // Hide timezone
        $laravelCollector->config->locale = false; // Hide locale

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
Monitors database operations using Laravel's native query events:
- All executed queries with SQL and bindings
- Query execution time and percentage of total
- Duplicate query detection (queries with identical SQL are automatically flagged)
- Slow query identification (queries taking ≥100ms are marked as slow)
- Database connections and drivers used
- File and line number where query was executed (non-production environments)
- Memory usage per query

### LaravelCollector
Displays Laravel environment information with granular configuration options:
- Laravel version
- Application environment (local, production, etc.)
- Timezone configuration
- Locale settings
- Debug mode status

Each field can be individually enabled or disabled in your configuration.

### PhpCollector
Shows PHP runtime information:
- PHP version
- Configuration details

### VueCollector
Tracks Vue.js related data in your application.

### ModelsCollector
**Requires Laravel Telescope**

Monitors Eloquent model operations via Telescope integration. This collector will automatically disable itself if Telescope is not installed.

### ResponseCollector
Inspects response data including headers and content.

## MCP Integration for AI Assistants

Laravel Toolbar includes a Model Context Protocol (MCP) server that allows AI assistants like Claude to access your application's request data during development. This enables AI to help debug issues, optimize queries, and provide insights based on real application data.

The MCP server is automatically registered and provides:
- Request data access for AI tools
- Collector information
- Real-time debugging assistance

## How It Works

1. **Middleware Integration**: The toolbar registers two middleware:
   - `WebStart`: Prepended to capture the beginning of request handling
   - `WebEnd`: Appended to capture the end of request handling

2. **Event Listeners**: Hooks into Laravel's native events to record 11 timing checkpoints:
   - Framework initialization
   - Service providers booting
   - Request pipeline preparation
   - Routing
   - Middleware pipeline (in and out)
   - Controller execution
   - View rendering
   - Response preparation

3. **Query Monitoring**: Uses Laravel's native `QueryExecuted` event (no Telescope required) to track:
   - All executed queries with bindings
   - Execution time and memory usage
   - Duplicate detection via SQL hash comparison
   - Slow query detection (≥100ms threshold)
   - Stack traces to identify query origin

4. **Optional Telescope Integration**: If Telescope is installed, provides enhanced data for:
   - Eloquent model operations (ModelsCollector)
   - Alternative request data source (RequestCollector)

5. **Data Collection**: After the request is handled, all enabled collectors gather their data

6. **Response Injection**:
   - **HTML responses**: Toolbar UI is injected before the closing `</body>` tag
   - **Inertia.js requests**: Data is sent via `x-toolbar` header (base64-encoded JSON)
   - **AJAX requests**: Automatically skipped to avoid interference
   - **Non-HTML responses**: Automatically skipped

7. **Caching**: Request data is cached for 30 seconds for MCP server access

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
- Automatically skips AJAX requests and non-HTML responses
- Supports both Vite HMR (development) and compiled assets (production)
- Can be completely disabled via configuration
- CSP nonce support for strict Content Security Policy headers

**For production deployments**, it's strongly recommended to:

1. **Don't install in production** (preferred): Only require the package in your `composer.json` dev dependencies
2. **Or disable via configuration**:

```php
if (app()->environment('production')) {
    $toolbarConfig->disable();
}
```

**Security note**: The toolbar shows sensitive application data including queries, environment variables, and request details. Never enable this in production environments.

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
