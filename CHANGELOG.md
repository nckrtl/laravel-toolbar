# Changelog

All notable changes to `laravel-toolbar` will be documented in this file.

## v0.3.8 - 2026-10-03

Richer PHP data for the PHP tool.

- `php` now also reports the SAPI, request and upload limits (`post_max_size`, `upload_max_filesize`, `max_file_uploads`, `max_input_vars`, `max_input_time`, `default_socket_timeout`), error settings, OPcache settings and usage (hit rate, memory, cached scripts), and the loaded extensions.
- Under PHP-FPM, `php.fpm` holds the pool's live status (active, idle and total workers, listen queue, max children reached, slow requests, accepted connections, uptime) and its process manager settings (`pm`, `pm.max_children`, spare servers, `pm.max_requests`, idle and request timeouts, listen socket), read once per worker from the pool file next to the loaded php.ini. Outside PHP-FPM it is null.

Validation: 384 PHP tests pass; verified under PHP-FPM 8.5 on an Orbit pool. Known limitations, unchanged: pre-existing PHPStan errors (optional Inertia SSR classes) and the pre-commit `vp check` hook cannot load `vite.config.ts`.

## v0.3.7 - 2026-10-02

Adds Inertia prop metadata, follow-up request marks and a hosted mode for apps that embed your pages.

- `inertia.props`: per top-level prop, whether it is shared, its Inertia type (always, defer, optional, merge, scroll, once) and the line that defines it. It comes from the DevTools data of inertia-laravel 3.3+ (enabled by default in `local`). Deferred props that the first response leaves out are listed as not loaded. Older Inertia versions get the shared prop keys. Also `inertia.render_source` and `inertia.component_path`.
- History rows get `follow_up`: `redirect` for the next hop of a redirect, `partial` for a partial reload.
- Fix: partial reloads (such as deferred props) now join the page's request history instead of replacing it.
- Hosted mode: when `window.__LARAVEL_TOOLBAR_HOST__` is set before the page loads, the toolbar draws no UI and only reports requests through the `laravel-toolbar:update` event. T3 Code's browser uses this to draw the toolbar natively.

Validation: 383 PHP tests and 52 JS tests pass. Known limitations, unchanged: pre-existing PHPStan errors (optional Inertia SSR classes) and the pre-commit `vp check` hook cannot load `vite.config.ts`.

## v0.3.6 - 2026-09-15

Adds Laravel MCP 1.0 support while retaining compatibility with MCP 0.5–0.7. Existing toolbar:mcp clients continue to work. Laravel MCP 1.0 needs Laravel 11.45.3+, 12.41.1+, or 13.x.

Validation: 379 PHP tests pass locally on MCP 1.0; all 12 PHP CI matrix combinations pass. Added protocol compatibility tests cover legacy initialization, modern discovery, and mismatched headers.

Known validation limitations: pre-existing PHPStan errors and an unchanged frontend negative fixture that expects a minifier-dependent Lodash collision. The production frontend bundle is unchanged by this release's MCP compatibility change.

See PR #19 and https://github.com/laravel/mcp/blob/v1.0.0/UPGRADE.md.

## Unreleased

### Fixed

- Production toolbar classic script is IIFE-wrapped before content hashing so minified top-level bindings stay lexical. Host apps that assign Lodash to `window._` no longer overwrite Vue `withCtx` (which returned Lodash wrappers and broke `renderSlot` with `r is not a function`, blank tools/panels). Browser fixtures use a parser-time host ES module that clobbers `window._`, prove the unwrapped bundle fails post-host slot switches, and assert all six tools with nested panel content after host boot.
- Large Inertia navigations now send bounded toolbar metadata instead of copying the full collected profile into `X-Toolbar`; full request data remains available through request history.

## v0.3.1 - 2026-07-19

**Full Changelog**: https://github.com/nckrtl/laravel-toolbar/compare/v0.3.0...v0.3.1

## v0.3.0 - 2026-07-19

**Full Changelog**: https://github.com/nckrtl/laravel-toolbar/compare/v0.2.5...v0.3.0

## v0.2.4 - 2026-07-18

**Full Changelog**: https://github.com/nckrtl/laravel-toolbar/compare/v0.2.2...v0.2.4

## v0.2.2 - 2026-05-12

### What's changed

- Widen `laravel/mcp` constraint to `^0.5.1 || ^0.6.0 || ^0.7.0` so apps on the newer MCP release can install the toolbar without downgrading.
- Bundle styling fix from `8aa4c70`.

**Full Changelog**: https://github.com/nckrtl/laravel-toolbar/compare/v0.2.1...v0.2.2

## v0.2.0 - 2026-04-16

### What's new

- **Pinned panels** — click any toolbar tool to pin its panel open; click again to dismiss. Panel state persists across page loads via localStorage.
- **Shared panel architecture** — all tool panels moved to tools/panels/*.vue and loaded dynamically via a single SharedPanel component.
- **Request panel overhaul**: dedicated tabs for middleware (inbound/outbound), route params, query params, and view/Inertia data; middleware editor links and short names; outbound detection via ReflectionMethod; tab persistence; search/filter with empty states; fixed panel height locked to summary.
- **Scroll fade on mount** — overflow fade indicator applies immediately on render, not only after the first scroll.
- **Consistent panel heights** — database, models, and requests panels all use a fixed 305px height.
- **DataList component** for consistent spacing between DataListItem rows.
- **z-index fix** for the shared panel overlay.

**Full Changelog**: https://github.com/nckrtl/laravel-toolbar/compare/v0.1.22...v0.2.0

## v0.1.22 - 2026-04-15

### What's new

- **Inertia SSR shows up as its own stage** in the profiler breakdown. Previously the 20–40ms that Inertia's dispatch (HTTP to vite's /__inertia_ssr in dev, or to the pre-built worker in prod) costs was hidden inside the Controller stage. Now it's a distinct bar with color #005FFF, inserted between Controller and View rendering whenever SSR actually runs.
- No change for apps without Inertia or with SSR disabled — the stage is simply absent.

Both the full collector pipeline (toolbar UI) and the lightweight `X-Toolbar-Summary` header (consumed by tools like `orbit profile`) reflect the new stage.

**Full Changelog**: https://github.com/nckrtl/laravel-toolbar/compare/v0.1.21...v0.1.22

## v0.1.21 - 2026-04-15

### What's new

- `X-Toolbar-Summary` response header for profiled requests (enables `orbit profile` breakdown rendering on workspaces, not just the main checkout)
- Deferred full data collection to `terminate()` via `fastcgi_finish_request()` for faster profiled responses
- Reduced toolbar data collection overhead
- Request history and redirect chain tracking
- Styling fixes

**Full Changelog**: https://github.com/nckrtl/laravel-toolbar/compare/v0.1.20...v0.1.21

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
  
