# Changelog

All notable changes to `laravel-toolbar` will be documented in this file.

## v0.1.11 - 2026-03-03

Widen laravel/mcp constraint to `^0.5.1 || ^0.6.0` for compatibility with projects using laravel/boost (which requires mcp ^0.5.1).

## v0.1.10 - 2026-03-03

### Bug fixes

Addresses 16 bugs from a bughunt audit (12 confirmed, 4 partially confirmed):

- **Error resilience**: CollectorManager wraps each collector in try-catch
- **Null safety**: Null guards in QueriesCollector, ModelsCollector, and ToolbarConfig
- **Unit conversion**: Fixed inverted formula branches in DataSizeUnit and TimeUnit
- **Memory tracking**: QueryObserver and ModelObserver now track per-query/model deltas correctly
- **View profiling**: Records BEFORE_VIEW_RENDERING checkpoint before rendering, not after
- **Data integrity**: JSON_INVALID_UTF8_SUBSTITUTE flag, preg_replace_callback for bindings, regex session query detection
- **Config**: Added `config/toolbar.php`, replaced `env()` with `config()`, fixed CSP nonce check, portable PHP binary fallback
- **Octane state**: Profiler::resetState() + observer reset after each request, Toolbar static state reset at request start

## v0.1.6 - 2026-01-30

**Full Changelog**: https://github.com/nckrtl/laravel-toolbar/compare/v0.1.4...v0.1.6

## v0.1.4 - 2026-01-29

### What's Changed

* Fix incorrect protocol check in DatabaseData by @nckrtl in https://github.com/nckrtl/laravel-toolbar/pull/6

### New Contributors

* @nckrtl made their first contribution in https://github.com/nckrtl/laravel-toolbar/pull/6

**Full Changelog**: https://github.com/nckrtl/laravel-toolbar/compare/v0.1.3...v0.1.4

## v0.1.2 - 2026-01-15

### Documentation

- Added AGENTS.md for OpenCode compatibility
- CLAUDE.md now symlinks to AGENTS.md for cross-tool support

### Maintenance

- Updated build assets
- Removed unused boost guidelines

**Full Changelog**: https://github.com/nckrtl/laravel-toolbar/compare/v0.1.1...v0.1.2

## v0.1.1 - 2026-01-08

### New Features

- Added Laravel Boost integration with AI guidelines for toolbar usage
- Added release skill for standardized GitHub releases

### Documentation

- Document available collectors and configuration options
- Include ToolbarConfigProvider example
- Add Telescope integration guide

**Full Changelog**: https://github.com/nckrtl/laravel-toolbar/compare/v0.1.0...v0.1.1

## v0.0.8 - 2026-01-07

### Fixes

- Accept `RedirectResponse` in `CollectorManager` constructor
- Add `RedirectResponse` to `injectToolbarData()` return type

Fixes TypeError when Laravel actions return redirects (e.g., deleting a resource).

## v0.0.7 - 2026-01-07

### What's Changed

#### Maintenance

- Remove backup files from source distribution
- Add comprehensive CHANGELOG with full version history
- Add CONTRIBUTING.md with development setup guide
- Add SECURITY.md with vulnerability reporting info

## v0.0.6 - 2026-01-07

### Added

- Priority-based ordering system for `GroupConfig` - groups within sections can now be ordered using priority values (lower values render first)
- Layout customization documentation in README

### Fixed

- Route not defined error when using `route()` helper - changed to `url()` to avoid timing issues with route registration

## v0.0.5 - 2026-01-07

### Changed

- Default toolbar layout now center-aligns all groups instead of left/right positioning

## v0.0.1 - 2026-01-07

### Added

- Initial release of Laravel Toolbar
  
- **Request Profiling**: Detailed breakdown of request lifecycle with 9 timing checkpoints
  
  - Application bootstrapping
  - Service providers booting
  - Request pipeline preparation
  - Routing (before/after)
  - Middleware pipeline (in/out)
  - Controller execution
  - View rendering
  - Response preparation
  
- **Database Query Monitoring**: Track all queries using Laravel's native `QueryExecuted` event
  
  - SQL with bindings replaced
  - Execution time and percentage of total
  - Duplicate query detection via SQL hash
  - Slow query identification (>=100ms threshold)
  - Memory usage per query
  - File and line number where query originated (non-production only)
  
- **Request Information Collector**: HTTP method, URI, IP, controller action, middleware stack
  
- **Response Collector**: Status code, headers, content size
  
- **Laravel Environment Collector**: Version, environment, timezone, locale, debug mode
  
- **PHP Information Collector**: PHP version, memory limit, max execution time
  
- **Vue.js Collector**: Vue.js version detection
  
- **Models Collector**: Eloquent model operations (requires Telescope)
  
- **Inertia.js Support**: Data sent via `x-toolbar` header for SPA navigation
  
- **MCP Server Integration**: AI assistants can access request data for debugging
  
- **Shadow DOM Isolation**: Toolbar styles completely isolated from host application
  
- **HTML Shell Caching**: Instant toolbar display using sessionStorage cache
  
- **Constructable Stylesheets**: Fast CSS updates without page reload
  
- **HMR Support**: Hot module replacement during development
  
- **Customizable Layout**: Sections (LEFT, CENTER, RIGHT) with tool groups
  
- **Collector Configuration**: Enable/disable collectors and individual fields
  
- **CSP Nonce Support**: Works with strict Content Security Policy headers
  
- **Production Safety**: Automatic skipping of AJAX, non-HTML, and console requests
  
